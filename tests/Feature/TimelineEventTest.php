<?php

namespace Tests\Feature;

use App\Models\TimelineEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimelineEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_timeline_event()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/timeline', [
            'title' => 'Test Event',
            'description' => 'Test Description',
            'date' => now()->toDateString(),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('timeline_events', [
            'title' => 'Test Event',
        ]);
    }

    public function test_authenticated_user_can_update_timeline_event()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $event = TimelineEvent::factory()->create(['user_id' => $user->id]);

        $response = $this->put("/timeline/{$event->id}", [
            'title' => 'Updated Event',
            'description' => $event->description,
            'date' => $event->date,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('timeline_events', [
            'id' => $event->id,
            'title' => 'Updated Event',
        ]);
    }

    public function test_authenticated_user_can_delete_timeline_event()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $event = TimelineEvent::factory()->create(['user_id' => $user->id]);

        $response = $this->delete("/timeline/{$event->id}");
        $response->assertRedirect();
        $this->assertDatabaseMissing('timeline_events', [
            'id' => $event->id,
        ]);
    }

    public function test_public_can_view_timeline_event()
    {
        $event = TimelineEvent::factory()->create();
        $response = $this->get("/timeline/{$event->id}");
        $response->assertStatus(200);
        $response->assertSee($event->title);
    }

    public function test_authenticated_user_can_view_timeline_events_index()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $response = $this->get('/timeline');
        $response->assertStatus(200);
    }
}
