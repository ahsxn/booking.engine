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
                'cover_image_url' => 'data:image/svg+xml;base64,'.base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 500"><rect width="800" height="500" fill="#284d42"/><circle cx="640" cy="90" r="130" fill="#c58245"/><path d="M160 170q120-40 240 20 120-60 240-20v210q-120-40-240 20-120-60-240-20z" fill="#f2dfb8"/><path d="M400 190v210M200 220l140 25m-140 30 140 25m120-55 140-25m-140 80 140-25" fill="none" stroke="#284d42" stroke-width="8"/></svg>'),
            ],
            'city-in-the-margins' => [
                'title' => 'The City in the Margins',
                'description' => 'Explore how overlooked streets become memorable settings, then trade reading recommendations over a guided discussion.',
                'venue_name' => 'Manchester Central Library',
                'venue_address' => 'St Peter\'s Square, Manchester M2 5PD, United Kingdom',
                'timezone' => 'Europe/London',
                'starts_at' => CarbonImmutable::parse('2026-10-17T13:00:00Z'),
                'ends_at' => CarbonImmutable::parse('2026-10-17T15:00:00Z'),
                'cover_image_url' => 'data:image/svg+xml;base64,'.base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 500"><rect width="800" height="500" fill="#353c64"/><path d="M80 400V210h80V130h100v270m50 0V90h130v310m50 0V180h80V110h100v290" fill="#b2a1cb"/><path d="M170 320q110-35 230 20 120-55 230-20v110q-110-35-230 20-120-55-230-20z" fill="#f3dba6"/><path d="M400 340v110" stroke="#353c64" stroke-width="8"/></svg>'),
            ],
            'lantern-pages' => [
                'title' => 'Lantern Pages: Stories After Dusk',
                'description' => 'Share an evening of short fiction and discuss how writers build atmosphere with light, silence and surprise.',
                'venue_name' => 'The British Library',
                'venue_address' => '96 Euston Road, London NW1 2DB, United Kingdom',
                'timezone' => 'Europe/London',
                'starts_at' => CarbonImmutable::parse('2026-11-14T18:00:00Z'),
                'ends_at' => CarbonImmutable::parse('2026-11-14T20:00:00Z'),
                'cover_image_url' => 'data:image/svg+xml;base64,'.base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 500"><rect width="800" height="500" fill="#172d40"/><circle cx="400" cy="220" r="170" fill="#655035"/><rect x="320" y="130" width="160" height="220" rx="25" fill="#f2c56f"/><path d="M350 130v-35q50-55 100 0v35m-50 30v160M210 390q90-30 190 20 100-50 190-20" fill="none" stroke="#f6e6bf" stroke-width="12"/></svg>'),
            ],
            'new-year-new-chapter' => [
                'title' => 'A New Year, A New Chapter',
                'description' => 'Gather for a fresh reading season, compare discoveries and choose a personal reading challenge for the months ahead.',
                'venue_name' => 'Central Library Oodi',
                'venue_address' => 'Töölönlahdenkatu 4, 00100 Helsinki, Finland',
                'timezone' => 'Europe/Helsinki',
                'starts_at' => CarbonImmutable::parse('2027-01-16T12:00:00Z'),
                'ends_at' => CarbonImmutable::parse('2027-01-16T14:00:00Z'),
                'cover_image_url' => 'data:image/svg+xml;base64,'.base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 500"><rect width="800" height="500" fill="#d9e8e0"/><circle cx="610" cy="120" r="65" fill="#daa45b"/><path d="M0 340q200-100 400 0 200 100 400 0v160H0z" fill="#6d9890"/><path d="M180 180q110-40 220 20 110-60 220-20v200q-110-40-220 20-110-60-220-20z" fill="#fff5df"/><path d="M400 200v200m-150-140h90m120 0h90" fill="none" stroke="#31575b" stroke-width="8"/></svg>'),
            ],
        ];

        ksort($events);
        foreach ($events as $slug => $attributes) {
            Event::query()->firstOrCreate(['slug' => $slug], [...$attributes, 'organizer_name' => 'Margin & Lantern']);
        }
    }
}
