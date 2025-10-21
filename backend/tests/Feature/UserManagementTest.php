<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserManagementTest extends TestCase
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

    public function test_admin_can_list_all_users()
    {
        $admin = $this->createAdminUser();
        $editor = $this->createEditorUser();
        $viewer = $this->createViewerUser();

        $token = $admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonCount(3)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'name',
                    'email',
                    'roles',
                    'created_at',
                ],
            ]);
    }

    public function test_non_admin_cannot_list_users()
    {
        $editor = $this->createEditorUser();
        $token = $editor->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/users');

        $response->assertStatus(403)
            ->assertJson(['message' => 'No autorizado']);
    }

    public function test_admin_can_view_specific_user()
    {
        $admin = $this->createAdminUser();
        $targetUser = $this->createEditorUser();

        $token = $admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/users/{$targetUser->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'email',
                'roles',
                'created_at',
                'updated_at',
            ])
            ->assertJson([
                'id' => $targetUser->id,
                'name' => $targetUser->name,
                'email' => $targetUser->email,
            ]);
    }

    public function test_non_admin_cannot_view_specific_user()
    {
        $editor = $this->createEditorUser();
        $targetUser = $this->createViewerUser();

        $token = $editor->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/users/{$targetUser->id}");

        $response->assertStatus(403)
            ->assertJson(['message' => 'No autorizado']);
    }

    public function test_admin_can_update_user()
    {
        $admin = $this->createAdminUser();
        $targetUser = $this->createEditorUser();

        $token = $admin->createToken('test-token')->plainTextToken;

        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => 'viewer',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/users/{$targetUser->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
                'roles' => ['viewer'],
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

        $targetUser->refresh();
        $this->assertTrue($targetUser->hasRole('viewer'));
    }

    public function test_admin_can_update_user_partially()
    {
        $admin = $this->createAdminUser();
        $targetUser = $this->createEditorUser();

        $token = $admin->createToken('test-token')->plainTextToken;

        $updateData = [
            'name' => 'Updated Name Only',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/users/{$targetUser->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'name' => 'Updated Name Only',
                'email' => $targetUser->email, // Should remain unchanged
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'name' => 'Updated Name Only',
            'email' => $targetUser->email,
        ]);
    }

    public function test_non_admin_cannot_update_user()
    {
        $editor = $this->createEditorUser();
        $targetUser = $this->createViewerUser();

        $token = $editor->createToken('test-token')->plainTextToken;

        $updateData = [
            'name' => 'Updated Name',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/users/{$targetUser->id}", $updateData);

        $response->assertStatus(403)
            ->assertJson(['message' => 'No autorizado']);
    }

    public function test_admin_can_delete_user()
    {
        $admin = $this->createAdminUser();
        $targetUser = $this->createEditorUser();

        $token = $admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/users/{$targetUser->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Usuario eliminado correctamente']);

        $this->assertDatabaseMissing('users', ['id' => $targetUser->id]);
    }

    public function test_admin_cannot_delete_self()
    {
        $admin = $this->createAdminUser();
        $token = $admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/users/{$admin->id}");

        $response->assertStatus(403)
            ->assertJson(['message' => 'No puedes eliminar tu propia cuenta']);

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_non_admin_cannot_delete_user()
    {
        $editor = $this->createEditorUser();
        $targetUser = $this->createViewerUser();

        $token = $editor->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/users/{$targetUser->id}");

        $response->assertStatus(403)
            ->assertJson(['message' => 'No autorizado']);
    }

    public function test_user_update_validation()
    {
        $admin = $this->createAdminUser();
        $targetUser = $this->createEditorUser();

        $token = $admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/users/{$targetUser->id}", [
            'email' => 'invalid-email',
            'role' => 'invalid-role',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'role']);
    }

    public function test_cannot_update_user_with_existing_email()
    {
        $admin = $this->createAdminUser();
        $targetUser = $this->createEditorUser();
        $anotherUser = $this->createViewerUser();

        $token = $admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/users/{$targetUser->id}", [
            'email' => $anotherUser->email,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_can_update_user_with_same_email()
    {
        $admin = $this->createAdminUser();
        $targetUser = $this->createEditorUser();

        $token = $admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/users/{$targetUser->id}", [
            'email' => $targetUser->email, // Same email
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'name' => 'Updated Name',
                'email' => $targetUser->email,
            ]);
    }
}
