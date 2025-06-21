<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'ingredients',
        'steps',
        'cuisine',
        'diet_tags',
        'cooking_time',
        'image'
    ];

    protected $casts = [
        'ingredients' => 'array',
        'steps' => 'array',
        'diet_tags' => 'array',
        'cooking_time' => 'integer'
    ];

    protected $appends = ['favorites_count', 'average_rating', 'ratings_count', 'comments_count'];

    /**
     * Get the user that created the recipe.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites')
            ->withTimestamps();
    }

    /**
     * Alias for favoritedBy relationship
     */
    public function favorites()
    {
        return $this->belongsToMany(User::class, 'favorites')
            ->withTimestamps();
    }

    /**
     * Get the ratings for the recipe.
     */
    public function ratings()
    {
        return $this->hasMany(RecipeRating::class);
    }

    /**
     * Get the comments for the recipe.
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get the number of users who favorited this recipe.
     */
    public function getFavoritesCountAttribute()
    {
        return $this->favoritedBy()->count();
    }

    /**
     * Get the average rating for the recipe.
     */
    public function getAverageRatingAttribute()
    {
        return $this->ratings()->avg('rating') ?? 0;
    }

    /**
     * Get the number of ratings for the recipe.
     */
    public function getRatingsCountAttribute()
    {
        return $this->ratings()->count();
    }

    /**
     * Get the number of comments for the recipe.
     */
    public function getCommentsCountAttribute()
    {
        return $this->comments()->count();
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * Scope for searching recipes by name or description
     */
    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        return $query;
    }

    /**
     * Scope for filtering by category
     */
    public function scopeByCategory($query, $categoryId)
    {
        if ($categoryId) {
            return $query->whereHas('categories', function($q) use ($categoryId) {
                $q->where('categories.id', $categoryId);
            });
        }
        return $query;
    }

    /**
     * Scope for filtering by difficulty
     */
    public function scopeByDifficulty($query, $difficulty)
    {
        if ($difficulty) {
            return $query->where('difficulty', $difficulty);
        }
        return $query;
    }

    /**
     * Scope for filtering by cuisine
     */
    public function scopeByCuisine($query, $cuisine)
    {
        if ($cuisine) {
            return $query->where('cuisine', $cuisine);
        }
        return $query;
    }

    /**
     * Scope for filtering by cooking time (max time)
     */
    public function scopeByMaxCookingTime($query, $maxTime)
    {
        if ($maxTime) {
            return $query->where('cooking_time', '<=', $maxTime);
        }
        return $query;
    }

    /**
     * Scope for filtering by diet tags
     */
    public function scopeByDietTags($query, $dietTags)
    {
        if ($dietTags) {
            $tags = is_array($dietTags) ? $dietTags : explode(',', $dietTags);
            return $query->whereJsonContains('diet_tags', $tags);
        }
        return $query;
    }

    /**
     * Scope for getting recipes with all relationships and calculated fields
     */
    public function scopeWithDetails($query)
    {
        return $query->with(['categories', 'user'])
            ->withAvg('ratings', 'rating')
            ->withCount('ratings');
    }

    /**
     * Scope for getting user's favorite recipes
     */
    public function scopeFavoritedByUser($query, $userId)
    {
        return $query->whereHas('favorites', function($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }
}
