<?php

namespace Database\Factories;

use App\Models\Event;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Event> */
class EventFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'slug' => 'reading-'.fake()->unique()->numerify('##########'),
            'title' => 'A Gathering of Readers',
            'description' => 'An original discussion of stories and the places they take us.',
            'organizer_name' => 'Margin & Lantern',
            'cover_image_url' => 'data:image/svg+xml;base64,'.base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect width="100" height="100" fill="#183c42"/><path d="M20 30h60v40H20z" fill="#efd7a4"/></svg>'),
            'venue_name' => 'Reading Room',
            'venue_address' => '1 Library Street, London, United Kingdom',
            'timezone' => 'Europe/London',
            'starts_at' => CarbonImmutable::parse('2026-11-14T18:00:00Z'),
            'ends_at' => CarbonImmutable::parse('2026-11-14T20:00:00Z'),
        ];
    }

    public function upcoming(): static
    {
        return $this->state(function (): array {
            $start = CarbonImmutable::now('UTC')->addWeek();

            return ['starts_at' => $start, 'ends_at' => $start->addHours(2)];
        });
    }

    public function past(): static
    {
        return $this->state(function (): array {
            $start = CarbonImmutable::now('UTC')->subWeek();

            return ['starts_at' => $start, 'ends_at' => $start->addHours(2)];
        });
    }
}
