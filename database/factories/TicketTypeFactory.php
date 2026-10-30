<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<TicketType> */
class TicketTypeFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'code' => 'ga-'.fake()->unique()->numerify('##########'),
            'name' => 'General admission',
            'description' => null,
            'display_order' => 0,
            'price_minor' => 1250,
            'currency' => 'GBP',
            'capacity' => 20,
        ];
    }
}
