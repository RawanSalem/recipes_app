<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test listing all categories (public)
     */
    public function test_can_list_all_categories()
    {
        Category::factory()->count(3)->create();
        $response = $this->getJson('/api/categories');
        $response->assertStatus(200)->assertJsonCount(3);
    }

    /**
     * Test showing a single category
     */
    public function test_can_show_single_category()
    {
        $category = Category::factory()->create();
        $response = $this->getJson('/api/categories/' . $category->id);
        $response->assertStatus(200)->assertJson(['id' => $category->id]);
    }

    /**
     * Test showing a non-existent category returns 404
     */
    public function test_show_nonexistent_category_returns_404()
    {
        $response = $this->getJson('/api/categories/99999');
        $response->assertStatus(404);
    }

    /**
     * Test creating a category (authenticated)
     */
    public function test_authenticated_user_can_create_category()
    {
        Sanctum::actingAs(User::factory()->create());
        $data = ['name' => 'New Category'];
        $response = $this->postJson('/api/categories', $data);
        $response->assertStatus(201)->assertJsonFragment(['name' => 'New Category']);
        $this->assertDatabaseHas('categories', ['name' => 'New Category']);
    }

    /**
     * Test creating a category requires authentication
     */
    public function test_guest_cannot_create_category()
    {
        $response = $this->postJson('/api/categories', ['name' => 'Test']);
        $response->assertStatus(401);
    }

    /**
     * Test creating a category with invalid data returns validation errors
     */
    public function test_create_category_validation_errors()
    {
        Sanctum::actingAs(User::factory()->create());
        $response = $this->postJson('/api/categories', []);
        $response->assertStatus(422)->assertJsonValidationErrors(['name']);
    }

    /**
     * Test updating a category (authenticated)
     */
    public function test_authenticated_user_can_update_category()
    {
        Sanctum::actingAs(User::factory()->create());
        $category = Category::factory()->create();
        $data = ['name' => 'Updated Category'];
        $response = $this->putJson('/api/categories/' . $category->id, $data);
        $response->assertStatus(200)->assertJsonFragment(['name' => 'Updated Category']);
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Updated Category']);
    }

    /**
     * Test guest cannot update category
     */
    public function test_guest_cannot_update_category()
    {
        $category = Category::factory()->create();
        $response = $this->putJson('/api/categories/' . $category->id, ['name' => 'Hacked']);
        $response->assertStatus(401);
    }

    /**
     * Test updating a category with invalid data returns validation errors
     */
    public function test_update_category_validation_errors()
    {
        Sanctum::actingAs(User::factory()->create());
        $category = Category::factory()->create();
        $response = $this->putJson('/api/categories/' . $category->id, ['name' => '']);
        $response->assertStatus(422)->assertJsonValidationErrors(['name']);
    }

    /**
     * Test deleting a category (authenticated)
     */
    public function test_authenticated_user_can_delete_category()
    {
        Sanctum::actingAs(User::factory()->create());
        $category = Category::factory()->create();
        $response = $this->deleteJson('/api/categories/' . $category->id);
        $response->assertStatus(200);
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    /**
     * Test guest cannot delete category
     */
    public function test_guest_cannot_delete_category()
    {
        $category = Category::factory()->create();
        $response = $this->deleteJson('/api/categories/' . $category->id);
        $response->assertStatus(401);
    }

    /**
     * Test deleting a non-existent category returns 404
     */
    public function test_delete_nonexistent_category_returns_404()
    {
        Sanctum::actingAs(User::factory()->create());
        $response = $this->deleteJson('/api/categories/99999');
        $response->assertStatus(404);
    }
} 