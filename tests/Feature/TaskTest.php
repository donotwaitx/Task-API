<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Passport\Passport;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test unauthenticated user cannot access tasks.
     */
    public function test_unauthenticated_user_cannot_access_tasks(): void
    {
        $response = $this->getJson('/api/tasks');

        $response->assertStatus(401);
    }

    /**
     * Test authenticated user can create a task.
     */
    public function test_authenticated_user_can_create_task(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $response = $this->postJson('/api/tasks', [
            'title' => 'Test Task',
            'description' => 'Test Description',
            'status' => 'pending',
            'due_date' => '2025-12-31',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'title',
                    'description',
                    'status',
                    'due_date',
                    'user'
                ]
            ]);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Test Task',
            'user_id' => $user->id,
        ]);
    }

    /**
     * Test task creation validation fails.
     */
    public function test_task_creation_validation_fails(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $response = $this->postJson('/api/tasks', [
            'title' => '', // Empty title should fail
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'errors'
            ]);
    }

    /**
     * Test user can list their tasks.
     */
    public function test_user_can_list_their_tasks(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        Task::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->getJson('/api/tasks');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data',
                    'current_page',
                    'per_page',
                ]
            ]);
    }

    /**
     * Test user can filter tasks by status.
     */
    public function test_user_can_filter_tasks_by_status(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        Task::factory()->create(['user_id' => $user->id, 'status' => 'pending']);
        Task::factory()->create(['user_id' => $user->id, 'status' => 'done']);

        $response = $this->getJson('/api/tasks?status=pending');

        $response->assertStatus(200);
        
        $tasks = $response->json('data.data');
        $this->assertCount(1, $tasks);
        $this->assertEquals('pending', $tasks[0]['status']);
    }

    /**
     * Test user can view a specific task.
     */
    public function test_user_can_view_specific_task(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $task = Task::factory()->create(['user_id' => $user->id]);

        $response = $this->getJson("/api/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $task->id,
                ]
            ]);
    }

    /**
     * Test user can update their task.
     */
    public function test_user_can_update_their_task(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $task = Task::factory()->create(['user_id' => $user->id]);

        $response = $this->putJson("/api/tasks/{$task->id}", [
            'title' => 'Updated Task',
            'status' => 'done',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Task updated successfully'
            ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated Task',
            'status' => 'done',
        ]);
    }

    /**
     * Test user can delete their task.
     */
    public function test_user_can_delete_their_task(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $task = Task::factory()->create(['user_id' => $user->id]);

        $response = $this->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Task deleted successfully'
            ]);

        $this->assertSoftDeleted('tasks', [
            'id' => $task->id,
        ]);
    }

    /**
     * Test user cannot access another user's task.
     */
    public function test_user_cannot_access_another_users_task(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        Passport::actingAs($user1);

        $task = Task::factory()->create(['user_id' => $user2->id]);

        $response = $this->getJson("/api/tasks/{$task->id}");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized access'
            ]);
    }
}
