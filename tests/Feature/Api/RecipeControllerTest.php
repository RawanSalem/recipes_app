<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Recipe;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RecipeControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test listing all recipes (public)
     */
    public function test_can_list_all_recipes()
    {
        Recipe::factory()->count(3)->create();
        $response = $this->getJson('/api/recipes');
        $response->assertStatus(200)->assertJsonCount(3);
    }

    /**
     * Test filtering recipes by category
     */
    public function test_can_filter_recipes_by_category()
    {
        $category = Category::factory()->create();
        $recipeIn = Recipe::factory()->create();
        $recipeIn->categories()->attach($category->id);
        $recipeOut = Recipe::factory()->create();
        $response = $this->getJson('/api/recipes?category=' . $category->id);
        $response->assertStatus(200);
        $ids = collect($response->json())->pluck('id');
        $this->assertTrue($ids->contains($recipeIn->id));
        $this->assertFalse($ids->contains($recipeOut->id));
    }

    /**
     * Test showing a single recipe
     */
    public function test_can_show_single_recipe()
    {
        $recipe = Recipe::factory()->create();
        $response = $this->getJson('/api/recipes/' . $recipe->id);
        $response->assertStatus(200)->assertJson(['id' => $recipe->id]);
    }

    /**
     * Test showing a non-existent recipe returns 404
     */
    public function test_show_nonexistent_recipe_returns_404()
    {
        $response = $this->getJson('/api/recipes/99999');
        $response->assertStatus(404);
    }

    /**
     * Test creating a recipe (authenticated)
     */
    public function test_authenticated_user_can_create_recipe()
    {
        Sanctum::actingAs(User::factory()->create());
        $category = Category::factory()->create();
        $data = [
            'title' => 'Test Recipe',
            'description' => 'Yummy',
            'ingredients' => [
                ['name' => 'Eggs', 'amount' => 2, 'unit' => 'pcs'],
                ['name' => 'Flour', 'amount' => 1, 'unit' => 'cup']
            ],
            'steps' => [
                ['step' => 1, 'instruction' => 'Mix ingredients'],
                ['step' => 2, 'instruction' => 'Bake for 30 minutes']
            ],
            'cuisine' => 'Italian',
            'categories' => [$category->id],
            'diet_tags' => ['vegetarian'],
            'cooking_time' => 30
        ];
        $response = $this->postJson('/api/recipes', $data);
        $response->assertStatus(201)->assertJsonFragment(['title' => 'Test Recipe']);
        $this->assertDatabaseHas('recipes', ['title' => 'Test Recipe']);
    }

    /**
     * Test creating a recipe requires authentication
     */
    public function test_guest_cannot_create_recipe()
    {
        $response = $this->postJson('/api/recipes', []);
        $response->assertStatus(401);
    }

    /**
     * Test creating a recipe with invalid data returns validation errors
     */
    public function test_create_recipe_validation_errors()
    {
        Sanctum::actingAs(User::factory()->create());
        $response = $this->postJson('/api/recipes', []);
        $response->assertStatus(422)->assertJsonValidationErrors([
            'title', 'description', 'ingredients', 'steps', 'cuisine', 'cooking_time', 'categories'
        ]);
    }

    /**
     * Test updating a recipe (owner only)
     */
    public function test_owner_can_update_recipe()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $category = Category::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $data = [
            'title' => 'Updated Title',
            'description' => 'Updated',
            'ingredients' => [
                ['name' => 'A', 'amount' => 1, 'unit' => 'pcs']
            ],
            'steps' => [
                ['step' => 1, 'instruction' => 'Do something']
            ],
            'cuisine' => 'French',
            'categories' => [$category->id],
            'cooking_time' => 10
        ];
        $response = $this->putJson('/api/recipes/' . $recipe->id, $data);
        $response->assertStatus(200)->assertJsonFragment(['title' => 'Updated Title']);
        $this->assertDatabaseHas('recipes', ['id' => $recipe->id, 'title' => 'Updated Title']);
    }

    /**
     * Test non-owner cannot update recipe
     */
    public function test_non_owner_cannot_update_recipe()
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $category = Category::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $owner->id]);
        Sanctum::actingAs($other);
        $data = [
            'title' => 'Hacked',
            'description' => 'Hacked',
            'ingredients' => [
                ['name' => 'A', 'amount' => 1, 'unit' => 'pcs']
            ],
            'steps' => [
                ['step' => 1, 'instruction' => 'Do something']
            ],
            'cuisine' => 'French',
            'categories' => [$category->id],
            'cooking_time' => 10
        ];
        $response = $this->putJson('/api/recipes/' . $recipe->id, $data);
        $response->assertStatus(403);
    }

    /**
     * Test deleting a recipe (owner only)
     */
    public function test_owner_can_delete_recipe()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $response = $this->deleteJson('/api/recipes/' . $recipe->id);
        $response->assertStatus(200);
        $this->assertDatabaseMissing('recipes', ['id' => $recipe->id]);
    }

    /**
     * Test non-owner cannot delete recipe
     */
    public function test_non_owner_cannot_delete_recipe()
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $owner->id]);
        Sanctum::actingAs($other);
        $response = $this->deleteJson('/api/recipes/' . $recipe->id);
        $response->assertStatus(403);
    }

    /**
     * Test deleting a non-existent recipe returns 404
     */
    public function test_delete_nonexistent_recipe_returns_404()
    {
        Sanctum::actingAs(User::factory()->create());
        $response = $this->deleteJson('/api/recipes/99999');
        $response->assertStatus(404);
    }
} 