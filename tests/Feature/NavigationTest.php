<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function guest_cannot_see_sidebar_or_access_protected_routes()
    {
        $protectedRoutes = [
            'dashboard',
            'trees.index',
            'individuals.index',
            'groups.index',
            'sources.index',
            'media.index',
            'stories.index',
            'events.index',
            'community.directory',
            'tools.templates',
            'search',
            'profile.settings',
        ];
        foreach ($protectedRoutes as $route) {
            $response = $this->get(route($route));
            $response->assertRedirect(route('login'));
        }
        $response = $this->get(route('login'));
        $response->assertOk();
        $response->assertDontSee('Dashboard'); // Sidebar not present
    }

    /** @test */
    public function user_sees_sidebar_but_not_admin_links()
    {
        $user = User::factory()->create(['role' => 'user']);
        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('Dashboard');
        $response->assertSee('My Trees');
        $response->assertDontSee('Admin');
        $response->assertSee('Logout');
    }

    /** @test */
    public function admin_sees_admin_links_in_sidebar()
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_admin' => true]);
        $response = $this->actingAs($admin)->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('Admin');
        $response->assertSee('User Management');
        $response->assertSee('Activity Logs');
        $response->assertSee('System Settings');
    }

    /** @test */
    public function user_cannot_access_admin_routes()
    {
        $user = User::factory()->create(['role' => 'user']);
        $adminRoutes = [
            'admin.users',
            'admin.logs',
            'admin.settings',
            'admin.notifications',
        ];
        foreach ($adminRoutes as $route) {
            $response = $this->actingAs($user)->get(route($route));
            $response->assertForbidden();
        }
    }

    /** @test */
    public function admin_can_access_admin_routes()
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_admin' => true]);
        $adminRoutes = [
            'admin.users',
            'admin.logs',
            'admin.settings',
            'admin.notifications',
        ];
        foreach ($adminRoutes as $route) {
            $response = $this->actingAs($admin)->get(route($route));
            $response->assertOk();
        }
    }

    /** @test */
    public function logout_button_is_present_and_works()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertSee('Logout');
        $response = $this->post(route('logout'));
        $response->assertRedirect('/');
        $this->assertGuest();
    }
}
