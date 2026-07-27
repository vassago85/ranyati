<?php

namespace Tests\Feature\Storage;

use App\Models\CustodyEvent;
use App\Models\RegisterBook;
use App\Models\StorageAgreement;
use App\Models\StorageItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A client returning with another firearm months later belongs on their
 * existing agreement, not a duplicate one. Adding firearms after the fact
 * must enforce exactly the same register-slot and tag guarantees as first
 * intake — that shared validation is the whole point of the flow.
 */
class AddItemsToAgreementTest extends TestCase
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
            'name' => 'Add Items Admin',
            'email' => 'add-items@example.com',
            'password' => 'password123',
            'role' => User::ROLE_DEVELOPER,
        ]);
    }

    private function selfAgreement(): StorageAgreement
    {
        return StorageAgreement::create([
            'type' => StorageAgreement::TYPE_SELF_STORAGE,
            'status' => 'active',
            'client_name' => 'P.J. Smith',
            'email' => 'pj@example.com',
            'storage_rate' => '100.00',
        ]);
    }

    private function estateAgreement(): StorageAgreement
    {
        return StorageAgreement::create([
            'type' => StorageAgreement::TYPE_DECEASED_ESTATE,
            'status' => 'active',
            'estate_late' => 'J.M. Smith',
        ]);
    }

    private function itemPayload(string $bookCode = 'S01', array $overrides = []): array
    {
        $book = RegisterBook::where('code', $bookCode)->firstOrFail();

        return ['items' => [array_replace([
            'register_book_id' => $book->id,
            'page' => 4, 'position' => 7,
            'shelf' => 'CD', 'tag_colour' => 'B', 'tag_number' => 77,
            'firearm_make' => 'CZ',
            'cartridge' => '.308 Winchester',
            'serial_number' => 'LATER-001',
            'firearm_type' => 'rifle',
            'action_type' => 'Bolt action',
            'date_in' => now()->toDateString(),
        ], $overrides)]];
    }

    public function test_add_firearm_page_loads_for_both_agreement_types(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get('/admin/storage/agreements/'.$this->selfAgreement()->id.'/items/create')
            ->assertOk()
            ->assertSee('Add to this agreement', false);

        $this->actingAs($admin)
            ->get('/admin/storage/agreements/'.$this->estateAgreement()->id.'/items/create')
            ->assertOk()
            ->assertSee('Add to this agreement', false);
    }

    public function test_agreement_page_links_to_the_add_firearm_form(): void
    {
        $agreement = $this->selfAgreement();

        $this->actingAs($this->admin())
            ->get('/admin/storage/agreements/'.$agreement->id)
            ->assertOk()
            ->assertSee('+ Add firearm', false)
            ->assertSee('/admin/storage/agreements/'.$agreement->id.'/items/create', false);
    }

    public function test_firearm_is_appended_to_the_existing_agreement_with_an_intake_event(): void
    {
        $agreement = $this->selfAgreement();

        $this->actingAs($this->admin())
            ->post('/admin/storage/agreements/'.$agreement->id.'/items', $this->itemPayload())
            ->assertRedirect(route('admin.storage.agreements.show', $agreement));

        // No second agreement was created.
        $this->assertSame(1, StorageAgreement::count());
        $this->assertSame(1, $agreement->items()->count());

        $item = StorageItem::firstOrFail();
        $this->assertSame($agreement->id, $item->storage_agreement_id);
        $this->assertSame('LATER-001', $item->serial_number);
        $this->assertSame(StorageItem::STATUS_IN_CUSTODY, $item->status);

        $this->assertSame(1, CustodyEvent::where('storage_item_id', $item->id)
            ->where('event_type', CustodyEvent::TYPE_INTAKE)
            ->count());
    }

    public function test_multiple_firearms_can_be_added_in_one_submit(): void
    {
        $agreement = $this->selfAgreement();
        $book = RegisterBook::where('code', 'S01')->firstOrFail();

        $payload = ['items' => [
            $this->itemPayload()['items'][0],
            array_replace($this->itemPayload()['items'][0], [
                'page' => 4, 'position' => 8,
                'shelf' => 'CD', 'tag_colour' => 'B', 'tag_number' => 78,
                'serial_number' => 'LATER-002',
            ]),
        ]];

        $this->actingAs($this->admin())
            ->post('/admin/storage/agreements/'.$agreement->id.'/items', $payload)
            ->assertRedirect();

        $this->assertSame(2, $agreement->items()->count());
        $this->assertSame($book->id, StorageItem::firstOrFail()->register_book_id);
    }

    public function test_occupied_register_slot_is_rejected_when_adding_later(): void
    {
        $agreement = $this->selfAgreement();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post('/admin/storage/agreements/'.$agreement->id.'/items', $this->itemPayload())
            ->assertRedirect();

        // Same slot, different tag — the register slot must still be refused.
        $this->actingAs($admin)
            ->from('/admin/storage/agreements/'.$agreement->id.'/items/create')
            ->post('/admin/storage/agreements/'.$agreement->id.'/items', $this->itemPayload('S01', [
                'shelf' => 'EF', 'tag_colour' => 'G', 'tag_number' => 90,
                'serial_number' => 'LATER-DUP',
            ]))
            ->assertSessionHasErrors('items.0.position');

        $this->assertSame(1, StorageItem::count());
    }

    public function test_tag_in_use_by_an_in_custody_firearm_is_rejected_when_adding_later(): void
    {
        $agreement = $this->selfAgreement();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post('/admin/storage/agreements/'.$agreement->id.'/items', $this->itemPayload())
            ->assertRedirect();

        // Different register slot, same physical tag — must be refused.
        $this->actingAs($admin)
            ->from('/admin/storage/agreements/'.$agreement->id.'/items/create')
            ->post('/admin/storage/agreements/'.$agreement->id.'/items', $this->itemPayload('S01', [
                'page' => 5, 'position' => 1,
                'serial_number' => 'LATER-TAGDUP',
            ]))
            ->assertSessionHasErrors('items.0.tag_number');

        $this->assertSame(1, StorageItem::count());
    }

    public function test_register_book_from_the_other_series_is_rejected(): void
    {
        // The agreement type comes from the route, not a form field, so a
        // self-storage agreement must refuse a D01 (estate) register slot.
        $agreement = $this->selfAgreement();

        $this->actingAs($this->admin())
            ->from('/admin/storage/agreements/'.$agreement->id.'/items/create')
            ->post('/admin/storage/agreements/'.$agreement->id.'/items', $this->itemPayload('D01'))
            ->assertSessionHasErrors('items.0.register_book_id');

        $this->assertSame(0, StorageItem::count());
    }

    public function test_action_type_must_still_match_firearm_type(): void
    {
        $agreement = $this->selfAgreement();

        $this->actingAs($this->admin())
            ->from('/admin/storage/agreements/'.$agreement->id.'/items/create')
            ->post('/admin/storage/agreements/'.$agreement->id.'/items', $this->itemPayload('S01', [
                'firearm_type' => 'handgun',
                'action_type' => 'Bolt action',
            ]))
            ->assertSessionHasErrors('items.0.action_type');
    }

    public function test_guests_cannot_add_firearms(): void
    {
        $agreement = $this->selfAgreement();

        $this->get('/admin/storage/agreements/'.$agreement->id.'/items/create')
            ->assertRedirect('/admin/login');

        $this->post('/admin/storage/agreements/'.$agreement->id.'/items', $this->itemPayload())
            ->assertRedirect('/admin/login');

        $this->assertSame(0, StorageItem::count());
    }
}
