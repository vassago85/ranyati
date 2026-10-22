<?php

namespace Tests\Feature\Admin;

use App\Models\ArmsEnquiry;
use App\Models\ArmsListing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArmsListingsPageTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'name' => 'Arms Admin',
            'email' => 'arms-admin@example.com',
            'password' => 'password123',
            'role' => User::ROLE_DEVELOPER,
        ]);
    }

    private function listing(User $admin, array $overrides = []): ArmsListing
    {
        return ArmsListing::create(array_merge([
            'title' => 'Listing',
            'make' => 'Make',
            'model' => 'Model',
            'calibre' => '9mm',
            'price' => 1000,
            'status' => 'active',
            'is_featured' => false,
            'created_by' => $admin->id,
        ], $overrides));
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Listing',
            'make' => 'Glock',
            'model' => '19',
            'calibre' => '9mm',
            'type' => 'handgun',
            'condition' => 'good',
            'price' => 15000,
        ], $overrides);
    }

    public function test_row_actions_follow_the_listing_status(): void
    {
        $admin = $this->admin();

        $this->listing($admin, ['make' => 'Glock', 'model' => '19', 'status' => 'active']);

        $active = $this->actingAs($admin)->get(route('admin.arms', ['filter' => 'active']));
        $active->assertOk()
            ->assertSee('>Edit</a>', false)
            ->assertSee('>Mark sold</button>', false)
            ->assertSee('>Feature</button>', false)
            ->assertSee('>Duplicate</button>', false)
            ->assertSee('>WhatsApp card</a>', false)
            ->assertSee('>Archive</button>', false)
            ->assertSee('>Delete</button>', false)
            ->assertSee('Delete Glock 19? This cannot be undone.', false)
            ->assertDontSee('Put back on sale', false);

        $this->listing($admin, ['make' => 'Winchester', 'model' => 'Defender', 'status' => 'sold']);

        $this->actingAs($admin)
            ->get(route('admin.arms', ['filter' => 'sold']))
            ->assertOk()
            ->assertSee('>Put back on sale</button>', false)
            ->assertSee('>Duplicate</button>', false)
            ->assertSee('>WhatsApp card</a>', false)
            ->assertSee('>Delete</button>', false)
            ->assertDontSee('>Mark sold</button>', false)
            ->assertDontSee('>Feature</button>', false)
            ->assertDontSee('>Remove from top</button>', false)
            ->assertDontSee('>Archive</button>', false);

        $this->listing($admin, ['make' => 'Thompson', 'model' => 'Center', 'status' => 'archived']);

        $this->actingAs($admin)
            ->get(route('admin.arms', ['filter' => 'archived']))
            ->assertOk()
            ->assertSee('>Put back on sale</button>', false)
            ->assertSee('>Duplicate</button>', false)
            ->assertSee('>Delete</button>', false)
            ->assertDontSee('>WhatsApp card</a>', false)
            ->assertDontSee('>Feature</button>', false)
            ->assertDontSee('>Archive</button>', false)
            ->assertDontSee('>Mark sold</button>', false);
    }

    public function test_featured_limit_is_enforced_in_the_list_and_on_the_server(): void
    {
        config(['arms.max_featured' => 1]);
        $admin = $this->admin();

        $this->listing($admin, ['make' => 'Brno', 'model' => '601', 'is_featured' => true]);
        $other = $this->listing($admin, ['make' => 'Ruger', 'model' => 'Blackhawk', 'is_featured' => false]);

        $this->actingAs($admin)
            ->get(route('admin.arms', ['filter' => 'active']))
            ->assertOk()
            ->assertSee('1 of 1 featured spots used', false)
            ->assertSee('Remove another listing from the top first.', false)
            ->assertSee('disabled', false);

        $this->actingAs($admin)
            ->post(route('admin.arms.feature', $other))
            ->assertRedirect()
            ->assertSessionHas('error', 'Remove another listing from the top first.');

        $this->assertFalse($other->fresh()->is_featured);

        $extra = $this->listing($admin, ['make' => 'CZ', 'model' => '75', 'is_featured' => true]);
        $this->assertTrue($extra->is_featured);

        $this->actingAs($admin)
            ->get(route('admin.arms', ['filter' => 'active']))
            ->assertOk()
            ->assertSee('2 of 1 featured spots used', false)
            ->assertSee('featured-usage is-over', false);
    }

    public function test_marking_sold_or_archived_removes_a_listing_from_the_top(): void
    {
        $admin = $this->admin();
        $sold = $this->listing($admin, ['make' => 'Glock', 'model' => '19', 'is_featured' => true]);
        $archived = $this->listing($admin, ['make' => 'Brno', 'model' => '601', 'is_featured' => true]);

        $this->actingAs($admin)->post(route('admin.arms.sold', $sold))->assertRedirect();
        $sold->refresh();
        $this->assertSame('sold', $sold->status);
        $this->assertFalse($sold->is_featured);

        $this->actingAs($admin)->post(route('admin.arms.archive', $archived))->assertRedirect();
        $archived->refresh();
        $this->assertSame('archived', $archived->status);
        $this->assertFalse($archived->is_featured);
    }

    public function test_placeholder_models_are_stored_blank_and_hidden_in_the_list(): void
    {
        $admin = $this->admin();

        foreach (['N/A', 'NA', 'n/a', '-'] as $index => $blank) {
            $this->actingAs($admin)
                ->post(route('admin.arms.store'), $this->payload([
                    'title' => 'Placeholder '.$index,
                    'make' => 'Make'.$index,
                    'model' => $blank,
                ]))
                ->assertRedirect(route('admin.arms', ['filter' => 'active']));

            $this->assertNull(ArmsListing::where('make', 'Make'.$index)->firstOrFail()->model);
        }

        $this->actingAs($admin)
            ->get(route('admin.arms', ['filter' => 'active']))
            ->assertOk()
            ->assertSee('Make0', false)
            ->assertDontSee('Make0 N/A', false);
    }

    public function test_a_repeated_model_is_saved_with_a_warning(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.arms.store'), $this->payload([
                'make' => 'Mauser K98',
                'model' => 'K98',
            ]))
            ->assertRedirect(route('admin.arms', ['filter' => 'active']))
            ->assertSessionHas('warning');

        $listing = ArmsListing::where('make', 'Mauser K98')->firstOrFail();
        $this->assertSame('K98', $listing->model);
    }

    public function test_the_create_form_makes_model_optional_and_searches_known_calibres(): void
    {
        $admin = $this->admin();
        $this->listing($admin, ['calibre' => '9mm Parabellum']);

        $listing = $this->listing($admin, ['calibre' => '9mm Parabellum', 'make' => 'Glock', 'model' => '19']);

        $this->actingAs($admin)
            ->get(route('admin.arms.create'))
            ->assertOk()
            ->assertSee('(optional)', false)
            ->assertSee('Search calibres', false)
            ->assertSee('9mm Parabellum', false)
            ->assertSee('Other', false);

        $this->actingAs($admin)
            ->get(route('admin.arms.edit', $listing))
            ->assertOk()
            ->assertSee('WhatsApp card', false)
            ->assertSee($listing->stock_number, false);

        $this->actingAs($admin)
            ->get(route('admin.arms.card', $listing))
            ->assertOk()
            ->assertSee($listing->stock_number, false);

        $this->actingAs($admin)
            ->get(route('admin.arms.replies'))
            ->assertOk()
            ->assertSee('Still available', false);
    }

    public function test_the_reduced_tab_shows_a_count(): void
    {
        $admin = $this->admin();
        $this->listing($admin, [
            'make' => 'Howa',
            'model' => '1500',
            'price' => 8000,
            'original_price' => 10000,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.arms', ['filter' => 'active']))
            ->assertOk()
            ->assertSee('Reduced <span class="count">1</span>', false);
    }

    public function test_active_listings_show_photo_and_age_badges(): void
    {
        config(['arms.stale_after_days' => 60]);
        $admin = $this->admin();

        $bare = $this->listing($admin, ['make' => 'Bare', 'model' => 'Rifle', 'images' => null]);
        $bare->forceFill(['created_at' => now()->subDays(61)])->save();

        $this->actingAs($admin)
            ->get(route('admin.arms', ['filter' => 'active']))
            ->assertOk()
            ->assertSee('No photo', false)
            ->assertSee('Listed 60+ days', false);

        ArmsListing::query()->delete();

        $photographed = $this->listing($admin, ['make' => 'Photographed', 'model' => 'Rifle', 'images' => ['arms/rifle.jpg']]);
        $photographed->forceFill(['created_at' => now()->subDays(10)])->save();
        $sold = $this->listing($admin, ['make' => 'Soldbare', 'model' => 'Rifle', 'status' => 'sold', 'images' => null]);
        $sold->forceFill(['created_at' => now()->subDays(90)])->save();

        $this->actingAs($admin)
            ->get(route('admin.arms', ['filter' => 'active']))
            ->assertOk()
            ->assertDontSee('No photo', false)
            ->assertDontSee('Listed 60+ days', false);
    }

    public function test_an_enquiry_count_opens_enquiries_for_that_listing(): void
    {
        $admin = $this->admin();
        $listing = $this->listing($admin, ['make' => 'Brno', 'model' => '601']);
        $other = $this->listing($admin, ['make' => 'Ruger', 'model' => 'Blackhawk']);

        ArmsEnquiry::create([
            'arms_listing_id' => $listing->id,
            'name' => 'Anna Buyer',
            'email' => 'anna@example.com',
            'message' => 'Is the Brno still available?',
        ]);
        ArmsEnquiry::create([
            'arms_listing_id' => $other->id,
            'name' => 'Ben Buyer',
            'email' => 'ben@example.com',
            'message' => 'Asking about the Ruger.',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.arms', ['filter' => 'active']))
            ->assertOk()
            ->assertSee(route('admin.arms.enquiries', ['listing' => $listing->id]), false);

        $this->actingAs($admin)
            ->followingRedirects()
            ->get(route('admin.arms.enquiries', ['listing' => $listing->id]))
            ->assertOk()
            ->assertSee('Anna Buyer', false)
            ->assertSee('Showing enquiries for Brno 601', false)
            ->assertDontSee('Ben Buyer', false);
    }

    public function test_normalise_command_prints_changes_and_writes_only_with_apply(): void
    {
        $admin = $this->admin();
        $listing = $this->listing($admin, [
            'model' => 'N/A',
            'calibre' => '8 X 68 mm S',
        ]);
        $sw = $this->listing($admin, [
            'make' => 'Smith',
            'model' => '29',
            'calibre' => '.40 S & W',
        ]);
        $rimfire = $this->listing($admin, [
            'make' => 'CZ',
            'model' => '452',
            'calibre' => '22 short/long/lr',
        ]);

        $this->artisan('arms:normalise-data', ['--dry-run' => true])
            ->expectsOutputToContain('['.$listing->id.'] model: "N/A" → (blank)')
            ->expectsOutputToContain('['.$listing->id.'] calibre: "8 X 68 mm S" → "8x68mm S"')
            ->expectsOutputToContain('['.$sw->id.'] calibre: ".40 S & W" → ".40 S&W"')
            ->expectsOutputToContain('['.$rimfire->id.'] calibre: "22 short/long/lr" → ".22 Short/Long/LR"')
            ->assertSuccessful();

        $this->assertSame('N/A', $listing->fresh()->model);
        $this->assertSame('8 X 68 mm S', $listing->fresh()->calibre);

        $this->artisan('arms:normalise-data')->assertSuccessful();
        $this->assertSame('N/A', $listing->fresh()->model);

        $this->artisan('arms:normalise-data', ['--apply' => true])->assertSuccessful();

        $listing->refresh();
        $this->assertNull($listing->model);
        $this->assertSame('8x68mm S', $listing->calibre);
        $this->assertSame('.40 S&W', $sw->fresh()->calibre);
        $this->assertSame('.22 Short/Long/LR', $rimfire->fresh()->calibre);

        $this->artisan('arms:normalise-data', ['--dry-run' => true])
            ->expectsOutputToContain('No model or calibre changes.')
            ->assertSuccessful();
    }
}
