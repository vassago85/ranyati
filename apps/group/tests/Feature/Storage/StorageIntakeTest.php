<?php

namespace Tests\Feature\Storage;

use App\Models\CustodyEvent;
use App\Models\RegisterBook;
use App\Models\StorageAgreement;
use App\Models\StorageItem;
use App\Models\User;
use App\Support\FirearmTypes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class StorageIntakeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        RegisterBook::updateOrCreate(['code' => 'D01'], [
            'type' => 'deceased_estate', 'pages' => 101, 'positions_per_page' => 26, 'status' => 'open',
        ]);
        RegisterBook::updateOrCreate(['code' => 'S01'], [
            'type' => 'self_storage', 'pages' => 101, 'positions_per_page' => 26, 'status' => 'open',
        ]);
    }

    private function admin(): User
    {
        return User::create([
            'name' => 'Storage Admin',
            'email' => 'storage-admin@example.com',
            'password' => 'password123',
            'role' => User::ROLE_DEVELOPER,
        ]);
    }

    private function baseSelfPayload(array $overrides = []): array
    {
        $book = RegisterBook::where('code', 'S01')->firstOrFail();

        return array_replace_recursive([
            'type' => StorageAgreement::TYPE_SELF_STORAGE,
            'client_name' => 'P.J. Smith',
            'email' => 'pj@example.com',
            'storage_rate' => '100.00',
            'items' => [
                [
                    'register_book_id' => $book->id,
                    'page' => 1, 'position' => 1,
                    'shelf' => 'AB', 'tag_colour' => 'R', 'tag_number' => 42,
                    'firearm_make' => 'Glock',
                    'cartridge' => '9mm Luger',
                    'serial_number' => 'ABC12345',
                    'firearm_type' => 'handgun',
                    'action_type' => 'Semi-auto',
                    'date_in' => now()->toDateString(),
                ],
            ],
        ], $overrides);
    }

    public function test_register_slot_uniqueness_is_enforced(): void
    {
        $admin = $this->admin();

        // First intake claims S01 page 1 pos 1.
        $this->actingAs($admin)
            ->post('/admin/storage/self', $this->baseSelfPayload())
            ->assertRedirect();

        // Second intake trying the same slot must fail validation.
        $this->actingAs($admin)
            ->from('/admin/storage/self/create')
            ->post('/admin/storage/self', $this->baseSelfPayload([
                'client_name' => 'A.B. Jones',
                'email' => 'ab@example.com',
                'items' => [[
                    'shelf' => 'CD', 'tag_colour' => 'B', 'tag_number' => 100,
                ]],
            ]))
            ->assertSessionHasErrors('items.0.position');

        $this->assertSame(1, StorageItem::count());
    }

    public function test_active_tag_uniqueness_is_enforced_and_frees_after_release(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post('/admin/storage/self', $this->baseSelfPayload())
            ->assertRedirect();

        $firstItem = StorageItem::first();

        // Second intake reusing the tag while first is in custody: rejected.
        $this->actingAs($admin)
            ->from('/admin/storage/self/create')
            ->post('/admin/storage/self', $this->baseSelfPayload([
                'client_name' => 'C.D. Brown',
                'email' => 'cd@example.com',
                'items' => [[
                    'page' => 1, 'position' => 2,
                    'shelf' => 'AB', 'tag_colour' => 'R', 'tag_number' => 42,
                ]],
            ]))
            ->assertSessionHasErrors('items.0.tag_number');

        // Release the first item.
        $this->actingAs($admin)->post("/admin/storage/items/{$firstItem->id}/collect", [
            'released_to_name' => 'Owner',
            'released_to_id_number' => '9001011234080',
        ])->assertRedirect();

        // Same tag combination is now accepted.
        $this->actingAs($admin)
            ->post('/admin/storage/self', $this->baseSelfPayload([
                'client_name' => 'C.D. Brown',
                'email' => 'cd@example.com',
                'items' => [[
                    'page' => 1, 'position' => 2,
                    'shelf' => 'AB', 'tag_colour' => 'R', 'tag_number' => 42,
                ]],
            ]))
            ->assertRedirect();

        $this->assertSame(2, StorageItem::count());
        $this->assertSame(1, StorageItem::where('status', 'in_custody')->count());
    }

    public function test_action_type_must_match_firearm_type(): void
    {
        $this->actingAs($this->admin())
            ->from('/admin/storage/self/create')
            ->post('/admin/storage/self', $this->baseSelfPayload([
                'items' => [[
                    'firearm_type' => 'handgun',
                    'action_type' => 'Bolt action', // rifle-only
                ]],
            ]))
            ->assertSessionHasErrors('items.0.action_type');

        // Sanity: FirearmTypes registry backs the validation.
        $this->assertTrue(FirearmTypes::isValid('handgun', 'Semi-auto'));
        $this->assertFalse(FirearmTypes::isValid('handgun', 'Bolt action'));
    }

    public function test_collect_flow_flips_status_records_release_event_and_calculates_fee(): void
    {
        $admin = $this->admin();

        // Backdate intake so the fee crosses a month boundary.
        $this->actingAs($admin)
            ->post('/admin/storage/self', $this->baseSelfPayload([
                'storage_rate' => '150.00',
                'items' => [[
                    'date_in' => now()->subMonthsNoOverflow(2)->toDateString(),
                ]],
            ]))
            ->assertRedirect();

        $item = StorageItem::first();
        $expectedMonths = $item->fullMonthsSinceIntake();
        $this->assertGreaterThanOrEqual(3, $expectedMonths);
        $this->assertSame(number_format($expectedMonths * 150, 2, '.', ''), $item->calculateFee());

        $this->actingAs($admin)
            ->post("/admin/storage/items/{$item->id}/collect", [
                'released_to_name' => 'P.J. Smith',
                'released_to_id_number' => '9001011234080',
            ])
            ->assertRedirect();

        $item->refresh();
        $this->assertSame(StorageItem::STATUS_RELEASED, $item->status);

        $release = $item->events()->where('event_type', CustodyEvent::TYPE_RELEASE)->first();
        $this->assertNotNull($release);
        $this->assertSame('P.J. Smith', $release->released_to_name);
    }

    public function test_custody_event_cannot_be_updated_or_deleted(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)
            ->post('/admin/storage/self', $this->baseSelfPayload())
            ->assertRedirect();

        $event = CustodyEvent::first();
        $this->assertNotNull($event);

        try {
            $event->update(['notes' => 'tampered']);
            $this->fail('Expected RuntimeException on update.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('append-only', $e->getMessage());
        }

        try {
            $event->delete();
            $this->fail('Expected RuntimeException on delete.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('append-only', $e->getMessage());
        }
    }
}
