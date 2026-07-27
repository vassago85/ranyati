<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * After signing in an admin picks which area to work in — motivations, arms
 * or storage — and may save that choice to skip the chooser next time.
 */
class AreaChooserTest extends TestCase
{
    use RefreshDatabase;

    private function admin(array $attributes = []): User
    {
        return User::create(array_merge([
            'name' => 'Area Admin',
            'email' => 'area-admin@example.com',
            'password' => 'password123',
            'role' => User::ROLE_DEVELOPER,
        ], $attributes));
    }

    public function test_login_lands_on_the_chooser_when_no_default_is_saved(): void
    {
        $this->admin();

        $this->post('/admin/login', [
            'email' => 'area-admin@example.com',
            'password' => 'password123',
        ])->assertRedirect(route('admin.choose'));
    }

    public function test_chooser_offers_all_three_areas(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/choose')
            ->assertOk()
            ->assertSee('Motivations', false)
            ->assertSee('Arms', false)
            ->assertSee('Storage', false);
    }

    public function test_choosing_an_area_redirects_to_that_dashboard(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post('/admin/choose', ['area' => 'storage'])
            ->assertRedirect(route('admin.storage.dashboard'));

        $this->actingAs($admin)
            ->post('/admin/choose', ['area' => 'arms'])
            ->assertRedirect(route('admin.arms'));

        $this->actingAs($admin)
            ->post('/admin/choose', ['area' => 'motivations'])
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_choosing_without_remember_does_not_save_a_default(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post('/admin/choose', ['area' => 'arms']);

        $this->assertNull($admin->fresh()->default_admin_area);
    }

    public function test_remembering_a_choice_skips_the_chooser_on_next_login(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post('/admin/choose', ['area' => 'storage', 'remember_area' => '1']);

        $this->assertSame('storage', $admin->fresh()->default_admin_area);

        $this->post('/admin/logout');

        $this->post('/admin/login', [
            'email' => 'area-admin@example.com',
            'password' => 'password123',
        ])->assertRedirect(route('admin.storage.dashboard'));
    }

    public function test_chooser_stays_reachable_after_a_default_is_saved(): void
    {
        $admin = $this->admin(['default_admin_area' => 'arms']);

        // "Switch area" must not bounce the user straight back into their
        // saved area, otherwise the default could never be changed.
        $this->actingAs($admin)
            ->get('/admin/choose')
            ->assertOk()
            ->assertSee('Your default', false);
    }

    public function test_unticking_remember_clears_a_saved_default(): void
    {
        $admin = $this->admin(['default_admin_area' => 'arms']);

        $this->actingAs($admin)
            ->post('/admin/choose', ['area' => 'motivations']);

        $this->assertNull($admin->fresh()->default_admin_area);

        $this->post('/admin/logout');

        $this->post('/admin/login', [
            'email' => 'area-admin@example.com',
            'password' => 'password123',
        ])->assertRedirect(route('admin.choose'));
    }

    public function test_unknown_area_is_rejected(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/choose', ['area' => 'nonsense'])
            ->assertSessionHasErrors('area');
    }

    public function test_a_stale_saved_area_falls_back_to_the_chooser(): void
    {
        // A preference left behind by a module that is no longer registered
        // must not strand the user on a dead route at login.
        $admin = $this->admin(['default_admin_area' => 'retired-module']);

        $this->assertNull($admin->defaultAdminArea());

        $this->post('/admin/login', [
            'email' => 'area-admin@example.com',
            'password' => 'password123',
        ])->assertRedirect(route('admin.choose'));
    }

    public function test_guests_cannot_reach_the_chooser(): void
    {
        $this->get('/admin/choose')->assertRedirect('/admin/login');
    }

    public function test_switch_area_link_appears_in_the_admin_sidebar(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin')
            ->assertOk()
            ->assertSee('Switch area', false);
    }
}
