<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FavoriteControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test listing user's favorite recipes (authenticated)
     */
    public function test_can_list_favorite_recipes()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $recipes = Recipe::factory()->count(2)->create();
        $user->favorites()->attach($recipes->pluck('id')->toArray());
        $response = $this->getJson('/api/favorites');
        $response->assertStatus(200)->assertJsonCount(2);
    }

    /**
     * Test listing favorites requires authentication
     */
    public function test_guest_cannot_list_favorites()
    {
        $response = $this->getJson('/api/favorites');
        $response->assertStatus(401);
    }

    /**
     * Test adding a recipe to favorites
     */
    public function test_can_add_recipe_to_favorites()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $recipe = Recipe::factory()->create();
        $response = $this->postJson('/api/recipes/' . $recipe->id . '/favorite');
        $response->assertStatus(200)->assertJson(['message' => 'Recipe added to favorites']);
        $this->assertTrue($user->fresh()->favorites->contains($recipe->id));
    }

    /**
     * Test adding a non-existent recipe to favorites returns 404
     */
    public function test_add_nonexistent_recipe_to_favorites_returns_404()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $response = $this->postJson('/api/recipes/99999/favorite');
        $response->assertStatus(404);
    }

    /**
     * Test guest cannot add to favorites
     */
    public function test_guest_cannot_add_to_favorites()
    {
        $recipe = Recipe::factory()->create();
        $response = $this->postJson('/api/recipes/' . $recipe->id . '/favorite');
        $response->assertStatus(401);
    }
} 