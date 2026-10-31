<?php

use App\Models\Event;
use App\Models\TicketType;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

uses(DatabaseMigrations::class);

afterEach(function () {
    $this->travelBack();
});

test('a guest sees the event description and ordered GBP ticket prices without inventory claims', function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-31T12:00:00Z'));
    $event = Event::factory()->create(['description' => "First paragraph.\n\nSecond paragraph."]);
    $supporter = TicketType::factory()->for($event)->create(['display_order' => 2, 'price_minor' => 2200]);
    $standard = TicketType::factory()->for($event)->create(['display_order' => 0, 'price_minor' => 1500]);

    $response = $this->get(route('events.show', ['event' => $event->slug]))->assertOk();

    $response->assertInertia(fn (Assert $page) => $page
        ->component('events/show')
        ->where('event.slug', $event->slug)
        ->where('event.description', "First paragraph.\n\nSecond paragraph.")
        ->where('event.coverImageUrl', $event->cover_image_url)
        ->where('event.venueAddress', $event->venue_address)
        ->where('event.selectionOpen', true)
        ->has('event.ticketTypes', 2)
        ->where('event.ticketTypes.0.id', $standard->id)
        ->where('event.ticketTypes.0.priceMinor', 1500)
        ->where('event.ticketTypes.0.priceLabel', '£15.00')
        ->where('event.ticketTypes.0.currency', 'GBP')
        ->where('event.ticketTypes.1.id', $supporter->id)
        ->missing('event.ticketTypes.0.capacity')
        ->missing('event.ticketTypes.0.availability')
        ->missing('event.ticketTypes.0.reserved_count')
        ->missing('event.ticketTypes.0.confirmed_count'));
});

test('unknown event slugs return 404', function () {
    $this->get(route('events.show', ['event' => 'missing-event']))->assertNotFound();
});

test('events without ticket information still have a public detail page', function () {
    $event = Event::factory()->create();

    $this->get(route('events.show', ['event' => $event->slug]))->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('event.ticketTypes', []));
});

test('registration presentation closes exactly at the start while the detail remains visible', function (string $reference, bool $selectionOpen) {
    $event = Event::factory()->create(['starts_at' => '2026-11-14T18:00:00Z', 'ends_at' => '2026-11-14T20:00:00Z']);
    $this->travelTo(CarbonImmutable::parse($reference));

    $this->get(route('events.show', ['event' => $event->slug]))->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('event.selectionOpen', $selectionOpen));
})->with([
    'before' => ['2026-11-14T17:59:59Z', true],
    'at start' => ['2026-11-14T18:00:00Z', false],
    'past' => ['2026-11-15T18:00:00Z', false],
]);

test('detail dates retain the event local day and offset across a UTC date boundary', function () {
    $event = Event::factory()->create([
        'timezone' => 'Europe/Helsinki',
        'starts_at' => '2027-01-16T23:00:00Z',
        'ends_at' => '2027-01-17T01:00:00Z',
    ]);

    $this->get(route('events.show', ['event' => $event->slug]))->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('event.localStartDate', '2027-01-17')
            ->where('event.timezone', 'Europe/Helsinki')
            ->where('event.startsAtLabel', '17 January 2027, 01:00 (+02:00)')
            ->where('event.endsAtLabel', '17 January 2027, 03:00 (+02:00)'));
});

test('detail reads only the requested event and its tickets without query growth', function () {
    $event = Event::factory()->create();
    TicketType::factory()->count(12)->for($event)->create();
    TicketType::factory()->create();
    DB::connection()->enableQueryLog();
    DB::connection()->flushQueryLog();

    try {
        $this->get(route('events.show', ['event' => $event->slug]))->assertOk()
            ->assertInertia(fn (Assert $page) => $page->has('event.ticketTypes', 12));
        $queries = collect(DB::connection()->getQueryLog())->pluck('query');
    } finally {
        DB::connection()->disableQueryLog();
    }

    expect($queries->filter(fn (string $query): bool => str_contains($query, 'from "events"')))->toHaveCount(1);
    expect($queries->filter(fn (string $query): bool => str_contains($query, 'from "ticket_types"')))->toHaveCount(1);
});
