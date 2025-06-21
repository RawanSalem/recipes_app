<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\RecipeRequest;
use App\Models\Recipe;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Tag(
 *     name="Recipes",
 *     description="API Endpoints for managing recipes"
 * )
 */
class RecipeController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/recipes",
     *     summary="Get all recipes",
     *     tags={"Recipes"},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term for recipe name or description",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="Filter by category ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="difficulty",
     *         in="query",
     *         description="Filter by difficulty level",
     *         required=false,
     *         @OA\Schema(type="string", enum={"easy", "medium", "hard"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of recipes",
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
     *                 @OA\Property(property="is_favorite", type="boolean", example=false),
     *                 @OA\Property(property="average_rating", type="number", format="float", example=4.5),
     *                 @OA\Property(property="categories", type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Italian"),
     *                         @OA\Property(property="slug", type="string", example="italian")
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $recipes = Recipe::withDetails()
            ->search($request->search)
            ->byCategory($request->category)
            ->byDifficulty($request->difficulty)
            ->byCuisine($request->cuisine)
            ->byMaxCookingTime($request->max_cooking_time)
            ->byDietTags($request->diet_tags)
            ->get();

        // Add favorite status for authenticated users
        if (Auth::check()) {
            $recipes->each(function ($recipe) {
                $recipe->is_favorite = $recipe->favorites()->where('user_id', Auth::id())->exists();
            });
        }

        return response()->json($recipes);
    }

    /**
     * @OA\Post(
     *     path="/api/recipes",
     *     summary="Create a new recipe",
     *     tags={"Recipes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "description", "ingredients", "instructions", "cooking_time", "servings", "difficulty", "categories"},
     *             @OA\Property(property="name", type="string", example="Spaghetti Carbonara"),
     *             @OA\Property(property="description", type="string", example="Classic Italian pasta dish"),
     *             @OA\Property(property="ingredients", type="string", example="Pasta, eggs, cheese, pancetta"),
     *             @OA\Property(property="instructions", type="string", example="1. Cook pasta..."),
     *             @OA\Property(property="cooking_time", type="integer", example=30),
     *             @OA\Property(property="servings", type="integer", example=4),
     *             @OA\Property(property="difficulty", type="string", enum={"easy", "medium", "hard"}, example="medium"),
     *             @OA\Property(property="categories", type="array", @OA\Items(type="integer"), example={1, 2}),
     *             @OA\Property(property="image", type="string", format="binary")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Recipe created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Recipe created successfully"),
     *             @OA\Property(property="recipe", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Spaghetti Carbonara"),
     *                 @OA\Property(property="description", type="string", example="Classic Italian pasta dish"),
     *                 @OA\Property(property="ingredients", type="string", example="Pasta, eggs, cheese, pancetta"),
     *                 @OA\Property(property="instructions", type="string", example="1. Cook pasta..."),
     *                 @OA\Property(property="cooking_time", type="integer", example=30),
     *                 @OA\Property(property="servings", type="integer", example=4),
     *                 @OA\Property(property="difficulty", type="string", example="medium"),
     *                 @OA\Property(property="image_url", type="string", example="http://example.com/image.jpg"),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time"),
     *                 @OA\Property(property="categories", type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Italian"),
     *                         @OA\Property(property="slug", type="string", example="italian")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(RecipeRequest $request)
    {
        $validated = $request->validated();
        $recipe = new Recipe($validated);
        $recipe->user_id = Auth::id();

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('recipes', 'public');
            $recipe->image = Storage::url($path);
        }

        $recipe->save();
        
        if ($request->has('categories')) {
            $recipe->categories()->attach($request->categories);
        }

        return response()->json([
            'message' => 'Recipe created successfully',
            'recipe' => $recipe->load('categories')
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/recipes/{id}",
     *     summary="Get a specific recipe",
     *     tags={"Recipes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Recipe ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Recipe details",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Spaghetti Carbonara"),
     *             @OA\Property(property="description", type="string", example="Classic Italian pasta dish"),
     *             @OA\Property(property="ingredients", type="string", example="Pasta, eggs, cheese, pancetta"),
     *             @OA\Property(property="instructions", type="string", example="1. Cook pasta..."),
     *             @OA\Property(property="cooking_time", type="integer", example=30),
     *             @OA\Property(property="servings", type="integer", example=4),
     *             @OA\Property(property="difficulty", type="string", example="medium"),
     *             @OA\Property(property="category", type="string", example="Italian"),
     *             @OA\Property(property="image_url", type="string", example="http://example.com/image.jpg"),
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="updated_at", type="string", format="date-time"),
     *             @OA\Property(property="is_favorite", type="boolean", example=false),
     *             @OA\Property(property="average_rating", type="number", format="float", example=4.5)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Recipe not found"
     *     )
     * )
     */
    public function show($id)
    {
        $recipe = Recipe::withDetails()->findOrFail($id);

        // Check if the recipe is favorited by the authenticated user
        if (Auth::check()) {
            $recipe->is_favorite = $recipe->favorites()->where('user_id', Auth::id())->exists();
        }

        return response()->json($recipe);
    }

    /**
     * @OA\Put(
     *     path="/api/recipes/{id}",
     *     summary="Update a recipe",
     *     tags={"Recipes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Recipe ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Spaghetti Carbonara"),
     *             @OA\Property(property="description", type="string", example="Classic Italian pasta dish"),
     *             @OA\Property(property="ingredients", type="string", example="Pasta, eggs, cheese, pancetta"),
     *             @OA\Property(property="instructions", type="string", example="1. Cook pasta..."),
     *             @OA\Property(property="cooking_time", type="integer", example=30),
     *             @OA\Property(property="servings", type="integer", example=4),
     *             @OA\Property(property="difficulty", type="string", enum={"easy", "medium", "hard"}, example="medium"),
     *             @OA\Property(property="categories", type="array", @OA\Items(type="integer"), example={1, 2}),
     *             @OA\Property(property="image", type="string", format="binary")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Recipe updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Recipe updated successfully"),
     *             @OA\Property(property="recipe", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Spaghetti Carbonara"),
     *                 @OA\Property(property="description", type="string", example="Classic Italian pasta dish"),
     *                 @OA\Property(property="ingredients", type="string", example="Pasta, eggs, cheese, pancetta"),
     *                 @OA\Property(property="instructions", type="string", example="1. Cook pasta..."),
     *                 @OA\Property(property="cooking_time", type="integer", example=30),
     *                 @OA\Property(property="servings", type="integer", example=4),
     *                 @OA\Property(property="difficulty", type="string", example="medium"),
     *                 @OA\Property(property="image_url", type="string", example="http://example.com/image.jpg"),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time"),
     *                 @OA\Property(property="categories", type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Italian"),
     *                         @OA\Property(property="slug", type="string", example="italian")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Recipe not found"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized to update this recipe"
     *     )
     * )
     */
    public function update(RecipeRequest $request, $id)
    {
        $recipe = Recipe::findOrFail($id);

        if ($recipe->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validated();

        if ($request->hasFile('image')) {
            if ($recipe->image) {
                Storage::delete(str_replace('/storage/', 'public/', $recipe->image));
            }
            $path = $request->file('image')->store('recipes', 'public');
            $validated['image'] = Storage::url($path);
        }

        $recipe->update($validated);

        if ($request->has('categories')) {
            $recipe->categories()->sync($request->categories);
        }

        return response()->json([
            'message' => 'Recipe updated successfully',
            'recipe' => $recipe->load('categories')
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/recipes/{id}",
     *     summary="Delete a recipe",
     *     tags={"Recipes"},
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
     *         description="Recipe deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Recipe deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Recipe not found"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized to delete this recipe"
     *     )
     * )
     */
    public function destroy($id)
    {
        $recipe = Recipe::findOrFail($id);

        if ($recipe->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Delete associated image
        if ($recipe->image) {
            Storage::delete(str_replace('/storage/', 'public/', $recipe->image));
        }

        $recipe->delete();

        return response()->json([
            'message' => 'Recipe deleted successfully'
        ]);
    }
} 