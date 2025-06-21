<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Favorites",
 *     description="API Endpoints for managing favorite recipes"
 * )
 */
class FavoriteController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/favorites",
     *     summary="Get user's favorite recipes",
     *     tags={"Favorites"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of favorite recipes",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Spaghetti Carbonara"),
     *                 @OA\Property(property="description", type="string", example="Classic Italian pasta dish"),
     *                 @OA\Property(property="ingredients", type="string", example="Pasta, eggs, cheese, pancetta"),
     *                 @OA\Property(property="instructions", type="string", example="1. Cook pasta..."),
     *                 @OA\Property(property="cooking_time", type="integer", example=30),
     *                 @OA\Property(property="servings", type="integer", example=4),
     *                 @OA\Property(property="difficulty", type="string", example="medium"),
     *                 @OA\Property(property="category", type="string", example="Italian"),
     *                 @OA\Property(property="image_url", type="string", example="http://example.com/image.jpg"),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time"),
     *                 @OA\Property(property="is_favorite", type="boolean", example=true),
     *                 @OA\Property(property="average_rating", type="number", format="float", example=4.5)
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $favorites = Recipe::withDetails()
            ->favoritedByUser(Auth::id())
            ->get();

        // Mark all as favorites
        $favorites->each(function ($recipe) {
            $recipe->is_favorite = true;
        });

        return response()->json($favorites);
    }

    /**
     * @OA\Post(
     *     path="/api/recipes/{id}/favorite",
     *     summary="Add a recipe to favorites",
     *     tags={"Favorites"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Recipe ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Recipe added to favorites",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Recipe added to favorites")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Recipe not found"
     *     )
     * )
     */
    public function store($id)
    {
        $recipe = Recipe::findOrFail($id);
        
        Auth::user()->favorites()->attach($recipe->id);

        return response()->json([
            'message' => 'Recipe added to favorites'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/recipes/{id}/favorite",
     *     summary="Check if recipe is favorited",
     *     tags={"Favorites"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Recipe ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Favorite status",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="is_favorite", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Recipe not found"
     *     )
     * )
     */
    public function check($id)
    {
        $recipe = Recipe::findOrFail($id);
        $isFavorite = Auth::user()->favorites()->where('recipe_id', $recipe->id)->exists();

        return response()->json([
            'is_favorite' => $isFavorite
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/recipes/{id}/favorite",
     *     summary="Remove a recipe from favorites",
     *     tags={"Favorites"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Recipe ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Recipe removed from favorites",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Recipe removed from favorites")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Recipe not found"
     *     )
     * )
     */
    public function destroy($id)
    {
        $recipe = Recipe::findOrFail($id);
        
        Auth::user()->favorites()->detach($recipe->id);

        return response()->json([
            'message' => 'Recipe removed from favorites'
        ]);
    }
} 