<?php

namespace Database\Seeders;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Seeder;

class RecipeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create some users first
        $users = User::factory()->count(5)->create();

        // Create recipes for each user
        foreach ($users as $user) {
            Recipe::factory()
                ->count(3)
                ->create(['user_id' => $user->id]);
        }
    }
}
