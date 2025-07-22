<?php

namespace Database\Factories;

use App\Models\Todo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Todo>
 */
class TodoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'title' => $this->faker->sentence(3),
            'position' => 0,
        ];
    }

    public function run()
    {
        Todo::factory()->count(5)->create()->each(function ($todo, $index) {
            $todo->position = $index;
            $todo->save();
        });
    }


}
