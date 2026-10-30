<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Seeder;

class TicketTypeSeeder extends Seeder
{
    public function run(): void
    {
        $tickets = [
            'paper-trails' => ['description' => 'Admission to the reading circle.', 'standard_price' => 1200, 'standard_capacity' => 30, 'supporter_price' => 1800, 'supporter_capacity' => 10],
            'city-in-the-margins' => ['description' => 'Admission to the discussion.', 'standard_price' => 1000, 'standard_capacity' => 24, 'supporter_price' => 1600, 'supporter_capacity' => 8],
            'lantern-pages' => ['description' => 'Admission to the story evening.', 'standard_price' => 1500, 'standard_capacity' => 40, 'supporter_price' => 2200, 'supporter_capacity' => 12],
            'new-year-new-chapter' => ['description' => 'Admission to the reading gathering.', 'standard_price' => 1400, 'standard_capacity' => 28, 'supporter_price' => 2000, 'supporter_capacity' => 8],
        ];

        ksort($tickets);
        foreach ($tickets as $slug => $attributes) {
            $event = Event::query()->where('slug', $slug)->firstOrFail();
            $event->ticketTypes()->firstOrCreate(['code' => 'standard-ga'], [
                'name' => 'General admission',
                'description' => $attributes['description'],
                'display_order' => 0,
                'price_minor' => $attributes['standard_price'],
                'currency' => 'GBP',
                'capacity' => $attributes['standard_capacity'],
            ]);
            $event->ticketTypes()->firstOrCreate(['code' => 'supporter-ga'], [
                'name' => 'Supporter admission',
                'description' => 'The same admission at a voluntary supporter price.',
                'display_order' => 1,
                'price_minor' => $attributes['supporter_price'],
                'currency' => 'GBP',
                'capacity' => $attributes['supporter_capacity'],
            ]);
        }
    }
}
