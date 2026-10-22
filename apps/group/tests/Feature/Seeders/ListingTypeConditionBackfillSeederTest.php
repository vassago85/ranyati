<?php

namespace Tests\Feature\Seeders;

use App\Models\ArmsListing;
use App\Models\User;
use Database\Seeders\ListingTypeConditionBackfillSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListingTypeConditionBackfillSeederTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'name' => 'Admin',
            'email' => 'seeder-admin@example.com',
            'password' => 'password',
            'role' => User::ROLE_DEVELOPER,
        ]);
    }

    private function listing(User $admin, string $stock, array $overrides = []): ArmsListing
    {
        $defaults = [
            'title' => 'Listing',
            'stock_number' => $stock,
            'make' => 'Make',
            'model' => 'Model',
            'calibre' => '9mm',
            'price' => 7500,
            'status' => 'active',
            'is_featured' => false,
            'created_by' => $admin->id,
        ];

        return ArmsListing::create(array_merge($defaults, $overrides));
    }

    public function test_seeder_backfills_type_condition_and_strips_description_prefixes(): void
    {
        $admin = $this->admin();

        // A row that will be fully set (type + condition + description strip).
        $a0001 = $this->listing($admin, 'A-0001', [
            'type' => null,
            'condition' => null,
            'description' => '34) The rifle is in good order.',
        ]);

        // A row with type=null in mapping — condition should update, type stays.
        $a0050 = $this->listing($admin, 'A-0050', [
            'type' => 'shotgun',
            'condition' => null,
            'description' => 'Pre-existing text with no prefix.',
        ]);

        // A row with condition=null in mapping — type should update, condition stays.
        $a0051 = $this->listing($admin, 'A-0051', [
            'type' => null,
            'condition' => 'good',
            'description' => '7) Something.',
        ]);

        // "Very good" from the mapping has to resolve to the `very_good` enum key.
        $a0012 = $this->listing($admin, 'A-0012', [
            'type' => null,
            'condition' => null,
            'description' => null,
        ]);

        // A row that is already at the target state — the seeder should NOT
        // re-save it (idempotency), and `updated_at` should not move.
        $a0005 = $this->listing($admin, 'A-0005', [
            'type' => 'handgun',
            'condition' => 'good',
            'description' => 'Already clean.',
        ]);
        $a0005->forceFill(['updated_at' => now()->subYear()])->saveQuietly();
        $noopUpdatedAt = $a0005->fresh()->updated_at;

        $this->artisan('db:seed', ['--class' => ListingTypeConditionBackfillSeeder::class])
            ->assertSuccessful()
            // Every mapping row that doesn't exist in the DB should show up
            // in the warning line. We only inserted 5 of the 39, so the
            // other 34 must be reported as missing.
            ->expectsOutputToContain('Stock numbers not found in DB (34)');

        $this->assertSame('rifle', $a0001->fresh()->type);
        $this->assertSame('excellent', $a0001->fresh()->condition);
        $this->assertSame('The rifle is in good order.', $a0001->fresh()->description);

        // A-0050 mapping had type=null — the existing shotgun must be preserved.
        $this->assertSame('shotgun', $a0050->fresh()->type);
        $this->assertSame('excellent', $a0050->fresh()->condition);

        // A-0051 mapping had condition=null — the existing good must be preserved.
        $this->assertSame('handgun', $a0051->fresh()->type);
        $this->assertSame('good', $a0051->fresh()->condition);
        // Description prefix stripped.
        $this->assertSame('Something.', $a0051->fresh()->description);

        // "Very good" label resolves to the `very_good` enum key.
        $this->assertSame('very_good', $a0012->fresh()->condition);

        // Already-correct row must NOT have been rewritten (updated_at unchanged).
        $this->assertEquals(
            $noopUpdatedAt->toDateTimeString(),
            $a0005->fresh()->updated_at->toDateTimeString(),
            'Idempotent row should not be rewritten.',
        );
    }

    public function test_seeder_is_idempotent_on_a_second_run(): void
    {
        $admin = $this->admin();

        $listing = $this->listing($admin, 'A-0001', [
            'type' => null,
            'condition' => null,
            'description' => '12) Starts numbered.',
        ]);

        $this->artisan('db:seed', ['--class' => ListingTypeConditionBackfillSeeder::class])
            ->assertSuccessful();

        $afterFirst = $listing->fresh();
        $this->assertSame('rifle', $afterFirst->type);
        $this->assertSame('excellent', $afterFirst->condition);
        $this->assertSame('Starts numbered.', $afterFirst->description);
        $firstUpdatedAt = $afterFirst->updated_at;

        // Sleep so updated_at would move if the seeder re-saved.
        sleep(1);

        $this->artisan('db:seed', ['--class' => ListingTypeConditionBackfillSeeder::class])
            ->assertSuccessful()
            ->expectsOutputToContain('No-op rows: 1');

        $this->assertEquals(
            $firstUpdatedAt->toDateTimeString(),
            $listing->fresh()->updated_at->toDateTimeString(),
            'Second run must not rewrite rows that are already at the target.',
        );
    }

    public function test_seeder_never_overwrites_a_field_with_null_from_the_mapping(): void
    {
        $admin = $this->admin();

        // A-0050 mapping has type=null — the existing value MUST survive even
        // if someone later blanks the mapping entry for condition too.
        $listing = $this->listing($admin, 'A-0050', [
            'type' => 'handgun',
            'condition' => 'very_good',
            'description' => 'Keep me.',
        ]);

        $this->artisan('db:seed', ['--class' => ListingTypeConditionBackfillSeeder::class])
            ->assertSuccessful();

        $fresh = $listing->fresh();
        $this->assertSame('handgun', $fresh->type);
        // Mapping had condition=Excellent for A-0050, so that one DOES update.
        $this->assertSame('excellent', $fresh->condition);
        $this->assertSame('Keep me.', $fresh->description);
    }

    public function test_seeder_warns_about_missing_stock_numbers_without_failing(): void
    {
        $admin = $this->admin();

        // Not seeding any rows — every mapping row should be reported missing.
        $this->artisan('db:seed', ['--class' => ListingTypeConditionBackfillSeeder::class])
            ->assertSuccessful()
            ->expectsOutputToContain('Stock numbers not found in DB (39)');

        $this->assertSame(0, ArmsListing::count());

        // Create one listing — the next run should report 38 missing.
        $this->listing($admin, 'A-0001');
        $this->artisan('db:seed', ['--class' => ListingTypeConditionBackfillSeeder::class])
            ->assertSuccessful()
            ->expectsOutputToContain('Stock numbers not found in DB (38)');
    }

    public function test_seeder_prints_before_and_after_counts_without_a_reserved_word_crash(): void
    {
        // Regression: `condition` is a reserved word in MySQL and an earlier
        // version of this seeder built `selectRaw('condition, COUNT(*) ...')`
        // which crashed in production even though SQLite accepted it. Seed a
        // couple of rows so the aggregate queries have real data to group on,
        // then assert the output contains the formatted count lines. If the
        // raw SQL regresses, this test fails with an exception at `::call()`
        // instead of silently printing zeros.
        $admin = $this->admin();
        $this->listing($admin, 'A-0001', ['type' => 'rifle',   'condition' => 'excellent']);
        $this->listing($admin, 'A-0012', ['type' => 'shotgun', 'condition' => 'very_good']);
        $this->listing($admin, 'A-0022', ['type' => 'handgun', 'condition' => 'good']);

        $this->artisan('db:seed', ['--class' => ListingTypeConditionBackfillSeeder::class])
            ->assertSuccessful()
            ->expectsOutputToContain('39-row scope — type:')
            ->expectsOutputToContain('39-row scope — condition:')
            ->expectsOutputToContain('All listings  — type:');
    }

    public function test_seeder_rejects_unknown_condition_labels(): void
    {
        // Guard against someone adding a condition label to the mapping that
        // the model doesn't know about. The seeder throws rather than
        // silently writing a bogus enum value.
        $seeder = new ListingTypeConditionBackfillSeeder;
        $method = new \ReflectionMethod($seeder, 'conditionKey');
        $method->setAccessible(true);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown condition label: Mint');
        $method->invoke($seeder, 'Mint');
    }
}
