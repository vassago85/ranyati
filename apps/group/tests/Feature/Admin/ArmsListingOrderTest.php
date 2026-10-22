<?php

namespace Tests\Feature\Admin;

use App\Models\ArmsListing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArmsListingOrderTest extends TestCase
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

    private function listing(User $admin, array $overrides): ArmsListing
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

    public function test_the_listings_page_opens_on_the_active_tab(): void
    {
        $admin = $this->admin();

        $this->listing($admin, ['make' => 'Ruger', 'model' => 'Blackhawk', 'status' => 'active']);
        $this->listing($admin, ['make' => 'Glock', 'model' => 'Gen3', 'status' => 'sold']);

        $this->actingAs($admin)
            ->get(route('admin.arms'))
            ->assertRedirect(route('admin.arms', ['filter' => 'active']));

        $this->actingAs($admin)
            ->get(route('admin.arms', ['filter' => 'active']))
            ->assertOk()
            ->assertSee('Ruger Blackhawk', false)
            ->assertDontSee('Glock Gen3', false);
    }

    public function test_each_tab_lists_featured_listings_before_newer_ones(): void
    {
        $admin = $this->admin();

        $older = $this->listing($admin, ['make' => 'CZ', 'model' => '75']);
        $older->forceFill(['created_at' => now()->subDays(3)])->save();

        $featured = $this->listing($admin, ['make' => 'Brno', 'model' => '601', 'is_featured' => true, 'featured_at' => now()->subDays(10)]);
        $featured->forceFill(['created_at' => now()->subDays(10)])->save();

        $newer = $this->listing($admin, ['make' => 'Ruger', 'model' => 'Blackhawk']);
        $newer->forceFill(['created_at' => now()->subDay()])->save();

        $this->actingAs($admin)
            ->get(route('admin.arms', ['filter' => 'active']))
            ->assertOk()
            ->assertSeeInOrder(['Brno 601', 'Ruger Blackhawk', 'CZ 75'], false);
    }
}
