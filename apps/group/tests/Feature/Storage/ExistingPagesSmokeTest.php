<?php

namespace Tests\Feature\Storage;

use App\Models\User;
use Database\Seeders\StorageModuleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression smoke: the Storage ops module must not break existing
 * marketing pages, enquire flow entry points, or the shared admin shell.
 */
class ExistingPagesSmokeTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'name' => 'Smoke Admin',
            'email' => 'smoke-admin@example.com',
            'password' => 'password123',
            'role' => User::ROLE_DEVELOPER,
        ]);
    }

    public function test_apex_welcome_still_loads(): void
    {
        $this->get('/')
            ->assertOk();
    }

    public function test_public_storage_landing_still_loads(): void
    {
        $this->get('/storage')
            ->assertOk()
            ->assertSee('Ranyati Storage', false);
    }

    public function test_storage_seo_pages_still_load_on_storage_host(): void
    {
        $pages = [
            '/firearm-storage-pretoria',
            '/long-term-firearm-storage-south-africa',
            '/temporary-firearm-storage',
            '/secure-firearm-storage-faq',
        ];

        foreach ($pages as $path) {
            // Host-gated routes read request()->getHost(); absolute URL is the
            // reliable way to set that in Laravel's HTTP test client.
            $this->get('http://storage.ranyati.test'.$path)
                ->assertOk();
        }
    }

    public function test_storage_resources_hub_still_loads_on_storage_host(): void
    {
        $this->get('http://storage.ranyati.test/resources')
            ->assertOk();

        $this->get('http://storage.ranyati.test/resources/safe-custody')
            ->assertOk();
    }

    public function test_enquire_page_still_loads(): void
    {
        $this->get('/enquire')
            ->assertOk();
    }

    public function test_existing_admin_pages_still_load(): void
    {
        $admin = $this->admin();

        $paths = [
            '/admin',
            '/admin/enquiries',
            '/admin/documents',
            '/admin/arms',
            '/admin/settings',
            '/admin/questionnaires',
        ];

        foreach ($paths as $path) {
            $this->actingAs($admin)
                ->followingRedirects()
                ->get($path)
                ->assertOk();
        }
    }

    public function test_admin_shell_renders_the_area_switcher_and_motivations_nav(): void
    {
        // Phase 2 refactor: the sidebar is now area-aware. `/admin` lives
        // in the Motivations area, so Motivations links render inline
        // while the Arms and Storage areas are reachable via the
        // segmented area switcher at the top of the sidebar.
        $admin = $this->admin();

        $response = $this->actingAs($admin)->get('/admin')->assertOk();

        // Motivations area nav must be inline.
        $response
            ->assertSee('Dashboard', false)
            ->assertSee('Enquiries', false)
            ->assertSee('Mail settings', false);

        // The area switcher offers every available area, including the
        // ones whose link trees are hidden on this page. Assert on the
        // uniquely-identifying hidden input value for each area so
        // whitespace in the button label can't produce false negatives.
        $response
            ->assertSee('class="area-switcher"', false)
            ->assertSee('name="area" value="motivations"', false)
            ->assertSee('name="area" value="arms"', false)
            ->assertSee('name="area" value="storage"', false);
    }

    public function test_admin_storage_dashboard_loads_without_r2_configured(): void
    {
        $this->seed(StorageModuleSeeder::class);

        $this->actingAs($this->admin())
            ->get('/admin/storage')
            ->assertOk()
            ->assertSee('D01', false)
            ->assertSee('S01', false);
    }

    public function test_public_storage_path_is_not_hijacked_by_admin_routes(): void
    {
        // Public brochureware stays at /storage; ops lives under /admin/storage.
        $this->get('/storage')->assertOk();
        $this->get('/admin/storage')->assertRedirect('/admin/login');
    }
}
