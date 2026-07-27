<?php

namespace Tests\Feature\Storage;

use App\Models\User;
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
                ->get($path)
                ->assertOk();
        }
    }

    public function test_new_storage_admin_nav_does_not_break_admin_shell(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('Storage', false)
            ->assertSee('Deceased estates', false)
            ->assertSee('Self storage', false)
            ->assertSee('Mail Settings', false)
            ->assertSee('Listings', false);
    }

    public function test_admin_storage_dashboard_loads_without_r2_configured(): void
    {
        $this->seed(\Database\Seeders\StorageModuleSeeder::class);

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
