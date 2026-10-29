<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'assigned_to' => null,
            'category_id' => Category::factory(),
            'subject' => fake()->sentence(6),
            'description' => fake()->paragraph(),
            'status' => 'open',
            'priority' => fake()->randomElement(['low', 'medium', 'high']),
        ];
    }
}
