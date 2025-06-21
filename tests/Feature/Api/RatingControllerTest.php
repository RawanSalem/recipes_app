<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Recipe;
use App\Models\RecipeRating;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RatingControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test authenticated user can rate a recipe
     */
    public function test_authenticated_user_can_rate_recipe()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $recipe = Recipe::factory()->create();
        $data = ['rating' => 4];
        $response = $this->postJson('/api/recipes/' . $recipe->id . '/rate', $data);
        $response->assertStatus(200)->assertJson(['message' => 'Recipe rated successfully']);
        $this->assertDatabaseHas('recipe_ratings', ['user_id' => $user->id, 'recipe_id' => $recipe->id, 'rating' => 4]);
    }

    /**
     * Test guest cannot rate a recipe
     */
    public function test_guest_cannot_rate_recipe()
    {
        $recipe = Recipe::factory()->create();
        $response = $this->postJson('/api/recipes/' . $recipe->id . '/rate', ['rating' => 5]);
        $response->assertStatus(401);
    }

    /**
     * Test rating validation errors
     */
    public function test_rating_validation_errors()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $recipe = Recipe::factory()->create();
        $response = $this->postJson('/api/recipes/' . $recipe->id . '/rate', ['rating' => 10]);
        $response->assertStatus(422)->assertJsonValidationErrors(['rating']);
    }

    /**
     * Test getting user's rating for a recipe
     */
    public function test_can_get_user_rating_for_recipe()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $recipe = Recipe::factory()->create();
        $user->recipeRatings()->create(['recipe_id' => $recipe->id, 'rating' => 3]);
        $response = $this->getJson('/api/recipes/' . $recipe->id . '/rate');
        $response->assertStatus(200)->assertJson(['rating' => 3]);
    }

    /**
     * Test getting rating for a recipe with no rating returns null
     */
    public function test_get_rating_for_recipe_with_no_rating_returns_null()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $recipe = Recipe::factory()->create();
        $response = $this->getJson('/api/recipes/' . $recipe->id . '/rate');
        $response->assertStatus(200)->assertJson(['rating' => null]);
    }

    /**
     * Test updating a rating
     */
    public function test_can_update_rating()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $recipe = Recipe::factory()->create();
        $user->recipeRatings()->create(['recipe_id' => $recipe->id, 'rating' => 2]);
        $response = $this->postJson('/api/recipes/' . $recipe->id . '/rate', ['rating' => 5]);
        $response->assertStatus(200)->assertJson(['message' => 'Recipe rated successfully']);
        $this->assertDatabaseHas('recipe_ratings', ['user_id' => $user->id, 'recipe_id' => $recipe->id, 'rating' => 5]);
    }

    /**
     * Test deleting a rating
     */
    public function test_can_delete_rating()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $recipe = Recipe::factory()->create();
        $user->recipeRatings()->create(['recipe_id' => $recipe->id, 'rating' => 2]);
        $response = $this->deleteJson('/api/recipes/' . $recipe->id . '/rate');
        $response->assertStatus(200)->assertJson(['message' => 'Recipe rating deleted successfully']);
        $this->assertDatabaseMissing('recipe_ratings', ['user_id' => $user->id, 'recipe_id' => $recipe->id]);
    }

    /**
     * Test deleting a rating for a non-existent recipe returns 404
     */
    public function test_delete_rating_for_nonexistent_recipe_returns_404()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $response = $this->deleteJson('/api/recipes/99999/rate');
        $response->assertStatus(404);
    }

} 