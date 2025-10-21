<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MiddlewareTest extends TestCase
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

    protected function createUserWithRole($role)
    {
        $user = User::create([
            'name' => ucfirst($role) . ' User',
            'email' => $role . '@example.com',
            'password' => Hash::make('password'),
        ]);
        $user->assignRole($role);
        return $user;
    }

    public function test_sanctum_middleware_blocks_unauthenticated_requests()
    {
        $response = $this->getJson('/api/user');
        $response->assertStatus(401);

        $response = $this->getJson('/api/users');
        $response->assertStatus(401);

        $response = $this->getJson('/api/tasks');
        $response->assertStatus(401);

        $response = $this->postJson('/api/tasks', []);
        $response->assertStatus(401);

        $response = $this->putJson('/api/tasks/1', []);
        $response->assertStatus(401);

        $response = $this->deleteJson('/api/tasks/1');
        $response->assertStatus(401);
    }

    public function test_sanctum_middleware_allows_authenticated_requests()
    {
        $user = $this->createUserWithRole('admin');
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/user');

        $response->assertStatus(200);
    }

    public function test_sanctum_middleware_blocks_invalid_tokens()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid-token',
        ])->getJson('/api/user');

        $response->assertStatus(401);
    }

    public function test_sanctum_middleware_blocks_expired_tokens()
    {
        $user = $this->createUserWithRole('admin');
        $token = $user->createToken('test-token');
        
        // Manually expire the token by deleting it
        $token->accessToken->delete();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token->plainTextToken,
        ])->getJson('/api/user');

        $response->assertStatus(401);
    }

    public function test_sanctum_middleware_blocks_malformed_authorization_header()
    {
        $response = $this->withHeaders([
            'Authorization' => 'InvalidFormat token',
        ])->getJson('/api/user');

        $response->assertStatus(401);
    }

    public function test_sanctum_middleware_blocks_missing_bearer_prefix()
    {
        $user = $this->createUserWithRole('admin');
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => $token, // Missing 'Bearer ' prefix
        ])->getJson('/api/user');

        $response->assertStatus(401);
    }

    public function test_cors_middleware_allows_origin_requests()
    {
        $response = $this->withHeaders([
            'Origin' => 'http://localhost:3000',
        ])->getJson('/api/user');

        // Should return 401 (unauthorized) but with CORS headers
        $response->assertStatus(401);
        $response->assertHeader('Access-Control-Allow-Origin', 'http://localhost:3000');
    }

    public function test_cors_middleware_handles_preflight_requests()
    {
        $response = $this->withHeaders([
            'Origin' => 'http://localhost:3000',
            'Access-Control-Request-Method' => 'POST',
            'Access-Control-Request-Headers' => 'Content-Type, Authorization',
        ])->options('/api/tasks');

        $response->assertStatus(200);
        $response->assertHeader('Access-Control-Allow-Origin', 'http://localhost:3000');
        $response->assertHeader('Access-Control-Allow-Methods');
        $response->assertHeader('Access-Control-Allow-Headers');
    }

    public function test_api_routes_require_authentication()
    {
        $protectedRoutes = [
            'GET' => [
                '/api/user',
                '/api/users',
                '/api/tasks',
            ],
            'POST' => [
                '/api/logout',
                '/api/tasks',
            ],
            'PUT' => [
                '/api/users/1',
                '/api/tasks/1',
            ],
            'DELETE' => [
                '/api/users/1',
                '/api/tasks/1',
            ],
        ];

        foreach ($protectedRoutes as $method => $routes) {
            foreach ($routes as $route) {
                $response = $this->json($method, $route);
                $response->assertStatus(401);
            }
        }
    }

    public function test_public_routes_do_not_require_authentication()
    {
        $publicRoutes = [
            'POST' => [
                '/api/login',
                '/api/register',
            ],
        ];

        foreach ($publicRoutes as $method => $routes) {
            foreach ($routes as $route) {
                $response = $this->json($method, $route, []);
                // These should return validation errors, not 401
                $response->assertStatus(422);
            }
        }
    }

    public function test_middleware_handles_missing_authorization_header()
    {
        $response = $this->getJson('/api/user');
        $response->assertStatus(401);
    }

    public function test_middleware_handles_empty_authorization_header()
    {
        $response = $this->withHeaders([
            'Authorization' => '',
        ])->getJson('/api/user');

        $response->assertStatus(401);
    }

    public function test_middleware_handles_whitespace_authorization_header()
    {
        $response = $this->withHeaders([
            'Authorization' => '   ',
        ])->getJson('/api/user');

        $response->assertStatus(401);
    }

    public function test_middleware_handles_multiple_authorization_headers()
    {
        $user = $this->createUserWithRole('admin');
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => ['Bearer ' . $token, 'Bearer invalid-token'],
        ])->getJson('/api/user');

        // Should still work with valid token
        $response->assertStatus(200);
    }

    public function test_middleware_handles_case_insensitive_bearer()
    {
        $user = $this->createUserWithRole('admin');
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'bearer ' . $token, // lowercase 'bearer'
        ])->getJson('/api/user');

        $response->assertStatus(200);
    }

    public function test_middleware_handles_mixed_case_bearer()
    {
        $user = $this->createUserWithRole('admin');
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'BeArEr ' . $token, // mixed case
        ])->getJson('/api/user');

        $response->assertStatus(200);
    }

    public function test_middleware_handles_extra_spaces_in_authorization()
    {
        $user = $this->createUserWithRole('admin');
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => '  Bearer  ' . $token . '  ', // extra spaces
        ])->getJson('/api/user');

        $response->assertStatus(200);
    }

    public function test_middleware_handles_requests_without_content_type()
    {
        $user = $this->createUserWithRole('admin');
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->get('/api/user'); // Using get() instead of getJson()

        $response->assertStatus(200);
    }

    public function test_middleware_handles_requests_with_different_content_types()
    {
        $user = $this->createUserWithRole('admin');
        $token = $user->createToken('test-token')->plainTextToken;

        $contentTypes = [
            'application/json',
            'application/x-www-form-urlencoded',
            'multipart/form-data',
            'text/plain',
        ];

        foreach ($contentTypes as $contentType) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => $contentType,
            ])->getJson('/api/user');

            $response->assertStatus(200);
        }
    }

    public function test_middleware_handles_large_authorization_header()
    {
        $user = $this->createUserWithRole('admin');
        $token = $user->createToken('test-token')->plainTextToken;
        
        // Create a very long authorization header
        $longToken = str_repeat($token, 100);
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $longToken,
        ])->getJson('/api/user');

        $response->assertStatus(401); // Should fail due to invalid token
    }

    public function test_middleware_handles_special_characters_in_token()
    {
        $user = $this->createUserWithRole('admin');
        $token = $user->createToken('test-token')->plainTextToken;
        
        // Add special characters to token
        $specialToken = $token . '!@#$%^&*()';
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $specialToken,
        ])->getJson('/api/user');

        $response->assertStatus(401); // Should fail due to invalid token
    }
}
