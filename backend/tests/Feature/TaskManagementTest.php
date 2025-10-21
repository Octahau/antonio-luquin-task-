<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TaskManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'editor']);
        Role::create(['name' => 'viewer']);
    }

    protected function createAdminUser()
    {
        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);
        $user->assignRole('admin');
        return $user;
    }

    protected function createEditorUser()
    {
        $user = User::create([
            'name' => 'Editor User',
            'email' => 'editor@example.com',
            'password' => Hash::make('password'),
        ]);
        $user->assignRole('editor');
        return $user;
    }

    protected function createViewerUser()
    {
        $user = User::create([
            'name' => 'Viewer User',
            'email' => 'viewer@example.com',
            'password' => Hash::make('password'),
        ]);
        $user->assignRole('viewer');
        return $user;
    }

    protected function createTaskForUser($user, $data = [])
    {
        $defaultData = [
            'title' => 'Test Task',
            'description' => 'Test Description',
            'status' => 'pending',
            'due_date' => now()->addDays(7),
            'user_id' => $user->id,
        ];

        return Task::create(array_merge($defaultData, $data));
    }

    public function test_admin_can_list_all_tasks()
    {
        $admin = $this->createAdminUser();
        $editor = $this->createEditorUser();
        $viewer = $this->createViewerUser();

        $adminTask = $this->createTaskForUser($admin, ['title' => 'Admin Task']);
        $editorTask = $this->createTaskForUser($editor, ['title' => 'Editor Task']);
        $viewerTask = $this->createTaskForUser($viewer, ['title' => 'Viewer Task']);

        $token = $admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/tasks');

        $response->assertStatus(200)
            ->assertJsonCount(3)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'title',
                    'description',
                    'status',
                    'due_date',
                    'user_id',
                    'user' => [
                        'id',
                        'name',
                        'email',
                    ],
                ],
            ]);
    }

    public function test_editor_can_list_own_tasks()
    {
        $editor = $this->createEditorUser();
        $otherUser = $this->createViewerUser();

        $editorTask = $this->createTaskForUser($editor, ['title' => 'Editor Task']);
        $otherTask = $this->createTaskForUser($otherUser, ['title' => 'Other Task']);

        $token = $editor->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/tasks');

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJson([
                [
                    'id' => $editorTask->id,
                    'title' => 'Editor Task',
                ],
            ]);
    }

    public function test_viewer_can_list_own_tasks()
    {
        $viewer = $this->createViewerUser();
        $otherUser = $this->createEditorUser();

        $viewerTask = $this->createTaskForUser($viewer, ['title' => 'Viewer Task']);
        $otherTask = $this->createTaskForUser($otherUser, ['title' => 'Other Task']);

        $token = $viewer->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/tasks');

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJson([
                [
                    'id' => $viewerTask->id,
                    'title' => 'Viewer Task',
                ],
            ]);
    }

    public function test_tasks_can_be_filtered_by_status()
    {
        $admin = $this->createAdminUser();
        $editor = $this->createEditorUser();

        $pendingTask = $this->createTaskForUser($editor, ['title' => 'Pending Task', 'status' => 'pending']);
        $completedTask = $this->createTaskForUser($editor, ['title' => 'Completed Task', 'status' => 'completed']);

        $token = $admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/tasks?status=pending');

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJson([
                [
                    'id' => $pendingTask->id,
                    'title' => 'Pending Task',
                    'status' => 'pending',
                ],
            ]);
    }

    public function test_admin_can_create_task()
    {
        $admin = $this->createAdminUser();
        $targetUser = $this->createEditorUser();

        $token = $admin->createToken('test-token')->plainTextToken;

        $taskData = [
            'title' => 'New Task',
            'description' => 'New Description',
            'status' => 'pending',
            'due_date' => now()->addDays(5)->format('Y-m-d'),
            'user_id' => $targetUser->id,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/tasks', $taskData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'title',
                'description',
                'status',
                'due_date',
                'user_id',
                'user' => [
                    'id',
                    'name',
                    'email',
                ],
            ])
            ->assertJson([
                'title' => 'New Task',
                'description' => 'New Description',
                'status' => 'pending',
                'user_id' => $targetUser->id,
            ]);

        $this->assertDatabaseHas('tasks', [
            'title' => 'New Task',
            'user_id' => $targetUser->id,
        ]);
    }

    public function test_editor_can_create_task_for_self()
    {
        $editor = $this->createEditorUser();
        $token = $editor->createToken('test-token')->plainTextToken;

        $taskData = [
            'title' => 'Editor Task',
            'description' => 'Editor Description',
            'status' => 'in_progress',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/tasks', $taskData);

        $response->assertStatus(201)
            ->assertJson([
                'title' => 'Editor Task',
                'user_id' => $editor->id,
            ]);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Editor Task',
            'user_id' => $editor->id,
        ]);
    }

    public function test_editor_cannot_assign_task_to_others()
    {
        $editor = $this->createEditorUser();
        $targetUser = $this->createViewerUser();
        $token = $editor->createToken('test-token')->plainTextToken;

        $taskData = [
            'title' => 'Editor Task',
            'user_id' => $targetUser->id,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/tasks', $taskData);

        $response->assertStatus(403)
            ->assertJson(['message' => 'Solo los administradores pueden asignar tareas a otros usuarios']);
    }

    public function test_viewer_cannot_create_task()
    {
        $viewer = $this->createViewerUser();
        $token = $viewer->createToken('test-token')->plainTextToken;

        $taskData = [
            'title' => 'Viewer Task',
            'description' => 'Viewer Description',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/tasks', $taskData);

        $response->assertStatus(403)
            ->assertJson(['message' => 'No autorizado']);
    }

    public function test_admin_can_view_any_task()
    {
        $admin = $this->createAdminUser();
        $editor = $this->createEditorUser();
        $task = $this->createTaskForUser($editor, ['title' => 'Editor Task']);

        $token = $admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $task->id,
                'title' => 'Editor Task',
            ]);
    }

    public function test_user_can_view_own_task()
    {
        $editor = $this->createEditorUser();
        $task = $this->createTaskForUser($editor, ['title' => 'Editor Task']);

        $token = $editor->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $task->id,
                'title' => 'Editor Task',
            ]);
    }

    public function test_user_cannot_view_others_task()
    {
        $editor = $this->createEditorUser();
        $viewer = $this->createViewerUser();
        $task = $this->createTaskForUser($viewer, ['title' => 'Viewer Task']);

        $token = $editor->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/tasks/{$task->id}");

        $response->assertStatus(403)
            ->assertJson(['message' => 'No autorizado']);
    }

    public function test_admin_can_update_any_task()
    {
        $admin = $this->createAdminUser();
        $editor = $this->createEditorUser();
        $task = $this->createTaskForUser($editor, ['title' => 'Original Title']);

        $token = $admin->createToken('test-token')->plainTextToken;

        $updateData = [
            'title' => 'Updated Title',
            'status' => 'completed',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/tasks/{$task->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'title' => 'Updated Title',
                'status' => 'completed',
            ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated Title',
            'status' => 'completed',
        ]);
    }

    public function test_editor_can_update_own_task()
    {
        $editor = $this->createEditorUser();
        $task = $this->createTaskForUser($editor, ['title' => 'Original Title']);

        $token = $editor->createToken('test-token')->plainTextToken;

        $updateData = [
            'title' => 'Updated Title',
            'status' => 'completed',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/tasks/{$task->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'title' => 'Updated Title',
                'status' => 'completed',
            ]);
    }

    public function test_editor_cannot_update_others_task()
    {
        $editor = $this->createEditorUser();
        $viewer = $this->createViewerUser();
        $task = $this->createTaskForUser($viewer, ['title' => 'Viewer Task']);

        $token = $editor->createToken('test-token')->plainTextToken;

        $updateData = [
            'title' => 'Updated Title',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/tasks/{$task->id}", $updateData);

        $response->assertStatus(403)
            ->assertJson(['message' => 'No autorizado']);
    }

    public function test_viewer_cannot_update_task()
    {
        $viewer = $this->createViewerUser();
        $task = $this->createTaskForUser($viewer, ['title' => 'Viewer Task']);

        $token = $viewer->createToken('test-token')->plainTextToken;

        $updateData = [
            'title' => 'Updated Title',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/tasks/{$task->id}", $updateData);

        $response->assertStatus(403)
            ->assertJson(['message' => 'No autorizado']);
    }

    public function test_admin_can_reassign_task()
    {
        $admin = $this->createAdminUser();
        $editor = $this->createEditorUser();
        $viewer = $this->createViewerUser();
        $task = $this->createTaskForUser($editor, ['title' => 'Editor Task']);

        $token = $admin->createToken('test-token')->plainTextToken;

        $updateData = [
            'user_id' => $viewer->id,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/tasks/{$task->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'user_id' => $viewer->id,
            ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'user_id' => $viewer->id,
        ]);
    }

    public function test_editor_cannot_reassign_task()
    {
        $editor = $this->createEditorUser();
        $viewer = $this->createViewerUser();
        $task = $this->createTaskForUser($editor, ['title' => 'Editor Task']);

        $token = $editor->createToken('test-token')->plainTextToken;

        $updateData = [
            'user_id' => $viewer->id,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/tasks/{$task->id}", $updateData);

        $response->assertStatus(403)
            ->assertJson(['message' => 'Solo los administradores pueden cambiar la asignación de tareas']);
    }

    public function test_admin_can_delete_any_task()
    {
        $admin = $this->createAdminUser();
        $editor = $this->createEditorUser();
        $task = $this->createTaskForUser($editor, ['title' => 'Editor Task']);

        $token = $admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Tarea eliminada correctamente']);

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_editor_can_delete_own_task()
    {
        $editor = $this->createEditorUser();
        $task = $this->createTaskForUser($editor, ['title' => 'Editor Task']);

        $token = $editor->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Tarea eliminada correctamente']);

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_editor_cannot_delete_others_task()
    {
        $editor = $this->createEditorUser();
        $viewer = $this->createViewerUser();
        $task = $this->createTaskForUser($viewer, ['title' => 'Viewer Task']);

        $token = $editor->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(403)
            ->assertJson(['message' => 'No autorizado']);

        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }

    public function test_viewer_cannot_delete_task()
    {
        $viewer = $this->createViewerUser();
        $task = $this->createTaskForUser($viewer, ['title' => 'Viewer Task']);

        $token = $viewer->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(403)
            ->assertJson(['message' => 'No autorizado']);

        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }

    public function test_task_creation_validation()
    {
        $admin = $this->createAdminUser();
        $token = $admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/tasks', [
            'title' => '', // Required field empty
            'status' => 'invalid_status', // Invalid status
            'due_date' => 'invalid_date', // Invalid date
            'user_id' => 999, // Non-existent user
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'status', 'due_date', 'user_id']);
    }

    public function test_task_update_validation()
    {
        $admin = $this->createAdminUser();
        $task = $this->createTaskForUser($admin, ['title' => 'Original Task']);

        $token = $admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/tasks/{$task->id}", [
            'title' => '', // Required field empty
            'status' => 'invalid_status', // Invalid status
            'due_date' => 'invalid_date', // Invalid date
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'status', 'due_date']);
    }

    public function test_task_creation_with_default_status()
    {
        $editor = $this->createEditorUser();
        $token = $editor->createToken('test-token')->plainTextToken;

        $taskData = [
            'title' => 'Task Without Status',
            'description' => 'Description',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/tasks', $taskData);

        $response->assertStatus(201)
            ->assertJson([
                'title' => 'Task Without Status',
                'status' => 'pending',
            ]);
    }
}
