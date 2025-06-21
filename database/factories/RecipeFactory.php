<?php

namespace Database\Factories;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RecipeFactory extends Factory
{
    protected $model = Recipe::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $cuisines = ['Italian', 'Mexican', 'Chinese', 'Indian', 'Japanese', 'Mediterranean', 'American', 'Thai'];
        $dietTags = ['Vegetarian', 'Vegan', 'Gluten-Free', 'Dairy-Free', 'Low-Carb', 'Keto', 'Paleo', 'Halal'];

        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(3),
            'ingredients' => [
                ['name' => $this->faker->word, 'amount' => $this->faker->numberBetween(1, 5), 'unit' => 'cups'],
                ['name' => $this->faker->word, 'amount' => $this->faker->numberBetween(1, 10), 'unit' => 'tbsp'],
                ['name' => $this->faker->word, 'amount' => $this->faker->numberBetween(1, 3), 'unit' => 'tsp'],
                ['name' => $this->faker->word, 'amount' => $this->faker->numberBetween(1, 4), 'unit' => 'whole'],
            ],
            'steps' => [
                ['step' => 1, 'instruction' => $this->faker->sentence(10)],
                ['step' => 2, 'instruction' => $this->faker->sentence(10)],
                ['step' => 3, 'instruction' => $this->faker->sentence(10)],
                ['step' => 4, 'instruction' => $this->faker->sentence(10)],
            ],
            'cuisine' => $this->faker->randomElement($cuisines),
            'diet_tags' => $this->faker->randomElements($dietTags, $this->faker->numberBetween(1, 3)),
            'cooking_time' => $this->faker->numberBetween(15, 180),
            'image' => $this->faker->imageUrl(640, 480, 'food'),
        ];
    }
}
