<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    protected function adminUser()
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_admin_can_view_activity_logs_index()
    {
        $admin = $this->adminUser();
        $this->actingAs($admin);
        $response = $this->get('/admin/activity-logs');
        $response->assertStatus(200);
    }

    public function test_admin_can_view_activity_log_show()
    {
        $admin = $this->adminUser();
        $this->actingAs($admin);
        $log = ActivityLog::factory()->create();
        $response = $this->get("/admin/activity-logs/{$log->id}");
        $response->assertStatus(200);
    }

    public function test_admin_can_export_activity_logs()
    {
        $admin = $this->adminUser();
        $this->actingAs($admin);
        $response = $this->get('/admin/activity-logs/export');
        $response->assertStatus(200);
    }

    public function test_non_admin_cannot_access_activity_logs()
    {
        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user);
        $response = $this->get('/admin/activity-logs');
        $response->assertForbidden();
    }
}
