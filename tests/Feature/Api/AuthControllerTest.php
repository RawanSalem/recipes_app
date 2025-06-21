<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Test user registration with valid data
     */
    public function test_user_can_register_with_valid_data()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at'
                ],
                'token'
            ])
            ->assertJson([
                'user' => [
                    'name' => 'John Doe',
                    'email' => 'john@example.com'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);

        $this->assertDatabaseHas('personal_access_tokens', [
            'name' => 'auth_token'
        ]);
    }

    /**
     * Test user registration validation - missing name
     */
    public function test_user_registration_requires_name()
    {
        $userData = [
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /**
     * Test user registration validation - missing email
     */
    public function test_user_registration_requires_email()
    {
        $userData = [
            'name' => 'John Doe',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test user registration validation - invalid email format
     */
    public function test_user_registration_requires_valid_email()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test user registration validation - duplicate email
     */
    public function test_user_registration_prevents_duplicate_email()
    {
        // Create existing user
        User::factory()->create(['email' => 'john@example.com']);

        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test user registration validation - missing password
     */
    public function test_user_registration_requires_password()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password_confirmation' => 'password123'
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * Test user registration validation - password too short
     */
    public function test_user_registration_requires_password_min_length()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => '123',
            'password_confirmation' => '123'
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * Test user registration validation - password confirmation mismatch
     */
    public function test_user_registration_requires_password_confirmation()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'differentpassword'
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * Test user login with valid credentials
     */
    public function test_user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('password123')
        ]);

        $loginData = [
            'email' => 'john@example.com',
            'password' => 'password123'
        ];

        $response = $this->postJson('/api/auth/login', $loginData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'name',
                    'email'
                ],
                'token'
            ])
            ->assertJson([
                'user' => [
                    'email' => 'john@example.com'
                ]
            ]);

        $this->assertDatabaseHas('personal_access_tokens', [
            'name' => 'auth_token'
        ]);
    }

    /**
     * Test user login validation - missing email
     */
    public function test_user_login_requires_email()
    {
        $loginData = [
            'password' => 'password123'
        ];

        $response = $this->postJson('/api/auth/login', $loginData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test user login validation - missing password
     */
    public function test_user_login_requires_password()
    {
        $loginData = [
            'email' => 'john@example.com'
        ];

        $response = $this->postJson('/api/auth/login', $loginData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * Test user login validation - invalid email format
     */
    public function test_user_login_requires_valid_email()
    {
        $loginData = [
            'email' => 'invalid-email',
            'password' => 'password123'
        ];

        $response = $this->postJson('/api/auth/login', $loginData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test user login with invalid credentials
     */
    public function test_user_cannot_login_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('password123')
        ]);

        $loginData = [
            'email' => 'john@example.com',
            'password' => 'wrongpassword'
        ];

        $response = $this->postJson('/api/auth/login', $loginData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test user login with non-existent email
     */
    public function test_user_cannot_login_with_nonexistent_email()
    {
        $loginData = [
            'email' => 'nonexistent@example.com',
            'password' => 'password123'
        ];

        $response = $this->postJson('/api/auth/login', $loginData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test user logout when authenticated
     */
    public function test_user_can_logout_when_authenticated()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Successfully logged out'
            ]);

        // Verify token was deleted
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class
        ]);
    }

    /**
     * Test user logout when not authenticated
     */
    public function test_user_cannot_logout_when_not_authenticated()
    {
        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(401);
    }

    /**
     * Test get user info when authenticated
     */
    public function test_user_can_get_info_when_authenticated()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/auth/user');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'email',
                'created_at',
                'updated_at'
            ])
            ->assertJson([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email
            ]);
    }

    /**
     * Test get user info when not authenticated
     */
    public function test_user_cannot_get_info_when_not_authenticated()
    {
        $response = $this->getJson('/api/auth/user');

        $response->assertStatus(401);
    }

    /**
     * Test token refresh when authenticated
     */
    public function test_user_can_refresh_token_when_authenticated()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Get initial token count
        $initialTokenCount = $user->tokens()->count();

        $response = $this->postJson('/api/auth/refresh');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'token'
            ])
            ->assertJson([
                'message' => 'Token refreshed successfully'
            ]);

        // Verify old tokens were deleted and new one created
        $this->assertEquals(1, $user->fresh()->tokens()->count());
        $this->assertNotEquals($initialTokenCount, $user->fresh()->tokens()->count());
    }

    /**
     * Test token refresh when not authenticated
     */
    public function test_user_cannot_refresh_token_when_not_authenticated()
    {
        $response = $this->postJson('/api/auth/refresh');

        $response->assertStatus(401);
    }

    /**
     * Test multiple token refresh calls
     */
    public function test_multiple_token_refresh_calls_work_correctly()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // First refresh
        $response1 = $this->postJson('/api/auth/refresh');
        $response1->assertStatus(200);
        $token1 = $response1->json('token');

        // Second refresh
        $response2 = $this->postJson('/api/auth/refresh');
        $response2->assertStatus(200);
        $token2 = $response2->json('token');

        // Tokens should be different
        $this->assertNotEquals($token1, $token2);

        // Should only have one token at a time
        $this->assertEquals(1, $user->fresh()->tokens()->count());
    }

    /**
     * Test registration with maximum field lengths
     */
    public function test_user_registration_with_maximum_field_lengths()
    {
        $userData = [
            'name' => str_repeat('a', 255), // Maximum length
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(201);
    }

    /**
     * Test registration with field exceeding maximum length
     */
    public function test_user_registration_fails_with_excessive_field_lengths()
    {
        $userData = [
            'name' => str_repeat('a', 256), // Exceeds maximum length
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /**
     * Test registration with minimum password length
     */
    public function test_user_registration_with_minimum_password_length()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => '12345678', // Exactly 8 characters
            'password_confirmation' => '12345678'
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(201);
    }

    /**
     * Test that password is properly hashed during registration
     */
    public function test_password_is_hashed_during_registration()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $this->postJson('/api/auth/register', $userData);

        $user = User::where('email', 'john@example.com')->first();
        
        $this->assertNotEquals('password123', $user->password);
        $this->assertTrue(password_verify('password123', $user->password));
    }

    /**
     * Test that user can login after registration
     */
    public function test_user_can_login_after_registration()
    {
        // Register user
        $registerData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $this->postJson('/api/auth/register', $registerData);

        // Login with same credentials
        $loginData = [
            'email' => 'john@example.com',
            'password' => 'password123'
        ];

        $response = $this->postJson('/api/auth/login', $loginData);

        $response->assertStatus(200);
    }

    /**
     * Test registration with special characters in name
     */
    public function test_user_registration_with_special_characters_in_name()
    {
        $userData = [
            'name' => 'José María O\'Connor-Smith',
            'email' => 'jose@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(201)
            ->assertJson([
                'user' => [
                    'name' => 'José María O\'Connor-Smith'
                ]
            ]);
    }

    /**
     * Test registration with different email formats
     */
    public function test_user_registration_with_different_email_formats()
    {
        $validEmails = [
            'test@example.com',
            'test.name@example.com',
            'test+name@example.com',
            'test@subdomain.example.com',
            'test@example.co.uk'
        ];

        foreach ($validEmails as $email) {
            $userData = [
                'name' => 'Test User',
                'email' => $email,
                'password' => 'password123',
                'password_confirmation' => 'password123'
            ];

            $response = $this->postJson('/api/auth/register', $userData);
            $response->assertStatus(201);
        }
    }

    /**
     * Test registration with invalid email formats
     */
    public function test_user_registration_rejects_invalid_email_formats()
    {
        $invalidEmails = [
            'invalid-email',
            '@example.com',
            'test@',
            'test..test@example.com',
            'test@.com',
            'test@example.',
            'test example@example.com'
        ];

        foreach ($invalidEmails as $email) {
            $userData = [
                'name' => 'Test User',
                'email' => $email,
                'password' => 'password123',
                'password_confirmation' => 'password123'
            ];

            $response = $this->postJson('/api/auth/register', $userData);
            $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
        }
    }

    /**
     * Test that tokens are unique for each user
     */
    public function test_tokens_are_unique_for_each_user()
    {
        // Register first user
        $user1Data = [
            'name' => 'User One',
            'email' => 'user1@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $response1 = $this->postJson('/api/auth/register', $user1Data);
        $token1 = $response1->json('token');

        // Register second user
        $user2Data = [
            'name' => 'User Two',
            'email' => 'user2@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $response2 = $this->postJson('/api/auth/register', $user2Data);
        $token2 = $response2->json('token');

        // Tokens should be different
        $this->assertNotEquals($token1, $token2);
    }

    /**
     * Test that user can access protected routes with valid token
     */
    public function test_user_can_access_protected_routes_with_valid_token()
    {
        // Register user
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $response = $this->postJson('/api/auth/register', $userData);
        $token = $response->json('token');

        // Access protected route with token
        $userResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ])->getJson('/api/auth/user');

        $userResponse->assertStatus(200)
            ->assertJson([
                'name' => 'John Doe',
                'email' => 'john@example.com'
            ]);
    }

    /**
     * Test that user cannot access protected routes with invalid token
     */
    public function test_user_cannot_access_protected_routes_with_invalid_token()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid-token',
            'Accept' => 'application/json'
        ])->getJson('/api/auth/user');

        $response->assertStatus(401);
    }

    /**
     * Test that user cannot access protected routes without token
     */
    public function test_user_cannot_access_protected_routes_without_token()
    {
        $response = $this->withHeaders([
            'Accept' => 'application/json'
        ])->getJson('/api/auth/user');

        $response->assertStatus(401);
    }

    /**
     * Test logout removes current user token
     */
    public function test_logout_removes_current_user_token()
    {
        $user = User::factory()->create();
        // Create multiple tokens
        $token1 = $user->createToken('token1')->plainTextToken;
        $token2 = $user->createToken('token2')->plainTextToken;

        // Verify tokens exist
        $this->assertEquals(2, $user->tokens()->count());

        // Logout using token1 (should only remove token1)
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token1,
            'Accept' => 'application/json'
        ])->postJson('/api/auth/logout');

        $response->assertStatus(200);

        // Only token2 should remain
        $this->assertEquals(1, $user->fresh()->tokens()->count());
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'token1'
        ]);
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'token2'
        ]);
    }

    /**
     * Test that refresh token creates new token and removes old ones
     */
    public function test_refresh_token_creates_new_token_and_removes_old_ones()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Create initial token
        $initialToken = $user->createToken('initial')->plainTextToken;

        // Refresh token
        $response = $this->postJson('/api/auth/refresh');

        $response->assertStatus(200);
        $newToken = $response->json('token');

        // Tokens should be different
        $this->assertNotEquals($initialToken, $newToken);

        // Should only have one token
        $this->assertEquals(1, $user->fresh()->tokens()->count());
    }

    /**
     * Test registration with empty strings
     */
    public function test_user_registration_rejects_empty_strings()
    {
        $userData = [
            'name' => '',
            'email' => '',
            'password' => '',
            'password_confirmation' => ''
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    /**
     * Test registration with whitespace-only strings
     */
    public function test_user_registration_rejects_whitespace_only_strings()
    {
        $userData = [
            'name' => '   ',
            'email' => '   ',
            'password' => '   ',
            'password_confirmation' => '   '
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    /**
     * Test logout from all devices removes all user tokens
     */
    public function test_logout_all_removes_all_user_tokens()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Create multiple tokens
        $token1 = $user->createToken('token1')->plainTextToken;
        $token2 = $user->createToken('token2')->plainTextToken;

        // Verify tokens exist
        $this->assertEquals(2, $user->tokens()->count());

        // Logout from all devices
        $response = $this->postJson('/api/auth/logout-all');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Successfully logged out from all devices'
            ]);

        // Verify all tokens were removed
        $this->assertEquals(0, $user->fresh()->tokens()->count());
    }
}
