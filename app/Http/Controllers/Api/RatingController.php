<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\RatingRequest;
use App\Models\Recipe;
use App\Models\RecipeRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Ratings",
 *     description="API Endpoints for managing recipe ratings"
 * )
 */
class RatingController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/recipes/{id}/rate",
     *     summary="Rate a recipe",
     *     tags={"Ratings"},
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
     *             required={"rating"},
     *             @OA\Property(property="rating", type="integer", minimum=1, maximum=5, example=4)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Recipe rated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Recipe rated successfully"),
     *             @OA\Property(property="rating", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="recipe_id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="rating", type="integer", example=4),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Recipe not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(RatingRequest $request, $id)
    {
        $recipe = Recipe::findOrFail($id);
        
        $rating = RecipeRating::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'recipe_id' => $recipe->id
            ],
            [
                'rating' => $request->rating
            ]
        );

        return response()->json([
            'message' => 'Recipe rated successfully',
            'rating' => $rating
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/recipes/{id}/rate",
     *     summary="Get user's rating for a recipe",
     *     tags={"Ratings"},
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
     *         description="User's rating",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="rating", type="integer", nullable=true, example=4)
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
        $recipe = Recipe::findOrFail($id);
        
        $rating = $recipe->ratings()
            ->where('user_id', Auth::id())
            ->first();

        return response()->json([
            'rating' => $rating ? $rating->rating : null
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/recipes/{id}/rate",
     *     summary="Update recipe rating",
     *     tags={"Ratings"},
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
     *             required={"rating"},
     *             @OA\Property(property="rating", type="integer", minimum=1, maximum=5, example=5)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Recipe rating updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Recipe rating updated successfully"),
     *             @OA\Property(property="rating", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="recipe_id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="rating", type="integer", example=5),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Recipe not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(RatingRequest $request, $id)
    {
        $recipe = Recipe::findOrFail($id);
        
        $rating = RecipeRating::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'recipe_id' => $recipe->id
            ],
            [
                'rating' => $request->rating
            ]
        );

        return response()->json([
            'message' => 'Recipe rating updated successfully',
            'rating' => $rating
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/recipes/{id}/rate",
     *     summary="Delete recipe rating",
     *     tags={"Ratings"},
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
     *         description="Recipe rating deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Recipe rating deleted successfully")
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
        
        $recipe->ratings()
            ->where('user_id', Auth::id())
            ->delete();

        return response()->json([
            'message' => 'Recipe rating deleted successfully'
        ]);
    }
} 