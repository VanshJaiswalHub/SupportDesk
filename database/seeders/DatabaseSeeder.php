<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Reply;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Known accounts, so you can log in and test each role
        $agent = User::factory()->create([
            'name' => 'Alex Agent',
            'email' => 'agent@example.com',
            'role' => 'agent',
        ]);

        $customer = User::factory()->create([
            'name' => 'Casey Customer',
            'email' => 'customer@example.com',
            'role' => 'customer',
        ]);

        // More agents and customers for variety
        $otherAgents = User::factory()->count(2)->create(['role' => 'agent']);
        $customers = User::factory()->count(6)->create(['role' => 'customer']);

        // Real category names instead of random words
        $categories = collect(['Billing', 'Technical', 'Account', 'Feature Request'])
            ->map(fn ($name) => Category::create(['name' => $name]));

        $agents = $otherAgents->push($agent);
        $allCustomers = $customers->push($customer);

        // 25 tickets, each owned by a random customer
        Ticket::factory()
            ->count(25)
            ->create([
                'user_id' => fn () => $allCustomers->random()->id,
                'category_id' => fn () => $categories->random()->id,
            ])
            ->each(function (Ticket $ticket) use ($agents) {
                // About 70% of tickets get an agent and a non-open status
                if (fake()->boolean(70)) {
                    $ticket->update([
                        'assigned_to' => $agents->random()->id,
                        'status' => fake()->randomElement(['in_progress', 'resolved', 'closed']),
                    ]);
                }

                // 0-4 replies per ticket, written by the owner or an agent
                $count = fake()->numberBetween(0, 4);
                for ($i = 0; $i < $count; $i++) {
                    Reply::factory()->create([
                        'ticket_id' => $ticket->id,
                        'user_id' => fake()->boolean()
                            ? $ticket->user_id
                            : $agents->random()->id,
                    ]);
                }
            });
    }
}
