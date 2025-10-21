<?php

namespace Tests\Unit;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_can_be_created()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $task = Task::create([
            'title' => 'Test Task',
            'description' => 'Test Description',
            'status' => 'pending',
            'due_date' => now()->addDays(7),
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Test Task',
            'description' => 'Test Description',
            'status' => 'pending',
            'user_id' => $user->id,
        ]);
    }

    public function test_task_belongs_to_user()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $task = Task::create([
            'title' => 'Test Task',
            'description' => 'Test Description',
            'status' => 'pending',
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $task->user);
        $this->assertEquals($user->id, $task->user->id);
    }

    public function test_task_fillable_attributes()
    {
        $task = new Task();
        $fillable = $task->getFillable();

        $this->assertContains('title', $fillable);
        $this->assertContains('description', $fillable);
        $this->assertContains('status', $fillable);
        $this->assertContains('due_date', $fillable);
        $this->assertContains('user_id', $fillable);
    }

    public function test_task_due_date_casting()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $task = Task::create([
            'title' => 'Test Task',
            'description' => 'Test Description',
            'status' => 'pending',
            'due_date' => '2024-12-31',
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $task->due_date);
        $this->assertEquals('2024-12-31', $task->due_date->format('Y-m-d'));
    }

    public function test_task_can_have_different_statuses()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $statuses = ['pending', 'in_progress', 'completed'];

        foreach ($statuses as $status) {
            $task = Task::create([
                'title' => "Test Task {$status}",
                'description' => 'Test Description',
                'status' => $status,
                'user_id' => $user->id,
            ]);

            $this->assertEquals($status, $task->status);
        }
    }

    public function test_task_can_be_updated()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $task = Task::create([
            'title' => 'Original Title',
            'description' => 'Original Description',
            'status' => 'pending',
            'user_id' => $user->id,
        ]);

        $task->update([
            'title' => 'Updated Title',
            'status' => 'completed',
        ]);

        $this->assertEquals('Updated Title', $task->title);
        $this->assertEquals('completed', $task->status);
        $this->assertEquals('Original Description', $task->description);
    }

    public function test_task_can_be_deleted()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $task = Task::create([
            'title' => 'Test Task',
            'description' => 'Test Description',
            'status' => 'pending',
            'user_id' => $user->id,
        ]);

        $taskId = $task->id;
        $task->delete();

        $this->assertDatabaseMissing('tasks', ['id' => $taskId]);
    }

    public function test_task_has_default_status()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $task = Task::create([
            'title' => 'Test Task',
            'user_id' => $user->id,
        ]);

        $this->assertEquals('pending', $task->status);
    }
}
