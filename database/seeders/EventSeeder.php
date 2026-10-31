<?php

namespace Database\Seeders;

use App\Models\Event;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $events = [
            'paper-trails' => [
                'title' => 'Paper Trails: An Autumn Reading Circle',
                'description' => 'Bring a short passage about a journey and join a relaxed conversation about the places books carry us.',
                'venue_name' => 'Barbican Centre',
                'venue_address' => 'Silk Street, London EC2Y 8DS, United Kingdom',
                'timezone' => 'Europe/London',
                'starts_at' => CarbonImmutable::parse('2026-09-19T13:00:00Z'),
                'ends_at' => CarbonImmutable::parse('2026-09-19T15:00:00Z'),
                'cover_image_url' => '/images/events/paper-trails-photo-v1.png',
            ],
            'city-in-the-margins' => [
                'title' => 'The City in the Margins',
                'description' => 'Explore how overlooked streets become memorable settings, then trade reading recommendations over a guided discussion.',
                'venue_name' => 'Manchester Central Library',
                'venue_address' => 'St Peter\'s Square, Manchester M2 5PD, United Kingdom',
                'timezone' => 'Europe/London',
                'starts_at' => CarbonImmutable::parse('2026-10-17T13:00:00Z'),
                'ends_at' => CarbonImmutable::parse('2026-10-17T15:00:00Z'),
                'cover_image_url' => '/images/events/city-in-the-margins-photo-v1.png',
            ],
            'lantern-pages' => [
                'title' => 'Lantern Pages: Stories After Dusk',
                'description' => 'Share an evening of short fiction and discuss how writers build atmosphere with light, silence and surprise.',
                'venue_name' => 'The British Library',
                'venue_address' => '96 Euston Road, London NW1 2DB, United Kingdom',
                'timezone' => 'Europe/London',
                'starts_at' => CarbonImmutable::parse('2026-11-14T18:00:00Z'),
                'ends_at' => CarbonImmutable::parse('2026-11-14T20:00:00Z'),
                'cover_image_url' => '/images/events/lantern-pages-photo-v1.png',
            ],
            'new-year-new-chapter' => [
                'title' => 'A New Year, A New Chapter',
                'description' => 'Gather for a fresh reading season, compare discoveries and choose a personal reading challenge for the months ahead.',
                'venue_name' => 'Central Library Oodi',
                'venue_address' => 'Töölönlahdenkatu 4, 00100 Helsinki, Finland',
                'timezone' => 'Europe/Helsinki',
                'starts_at' => CarbonImmutable::parse('2027-01-16T12:00:00Z'),
                'ends_at' => CarbonImmutable::parse('2027-01-16T14:00:00Z'),
                'cover_image_url' => '/images/events/new-year-new-chapter-photo-v1.png',
            ],
        ];

        ksort($events);
        foreach ($events as $slug => $attributes) {
            Event::query()->firstOrCreate(['slug' => $slug], [...$attributes, 'organizer_name' => 'Margin & Lantern']);
        }
    }
}
