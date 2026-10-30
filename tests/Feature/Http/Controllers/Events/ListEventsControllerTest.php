<?php

use App\Models\Event;
use Carbon\CarbonImmutable;
use Carbon\Exceptions\InvalidTimeZoneException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Exceptions;
use Inertia\Testing\AssertableInertia as Assert;

uses(DatabaseMigrations::class);

afterEach(function () {
    $this->travelBack();
});

test('guests receive the seeded catalogue through the public home route', function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-30T12:00:00Z'));
    $this->seed();
    $helsinki = Event::query()->where('slug', 'new-year-new-chapter')->firstOrFail();
    $ticketsBefore = DB::table('ticket_types')->orderBy('id')->get()->all();

    $response = $this->get(route('home'))->assertOk();

    $response->assertInertia(fn (Assert $page) => $page
        ->component('events/index')
        ->has('upcoming', 2)->has('past', 2)
        ->where('upcoming.0.slug', 'lantern-pages')
        ->where('upcoming.1.title', 'A New Year, A New Chapter')
        ->where('upcoming.1.slug', $helsinki->slug)
        ->where('upcoming.1.coverImageUrl', $helsinki->cover_image_url)
        ->where('upcoming.1.localStartDate', '2027-01-16')
        ->where('upcoming.1.startsAtLabel', '16 January 2027, 14:00 (+02:00)')
        ->where('upcoming.1.timezone', 'Europe/Helsinki')
        ->where('upcoming.1.venueName', 'Central Library Oodi')
        ->where('upcoming.1.ticketTypes.0.name', 'General admission')
        ->where('upcoming.1.ticketTypes.0.priceMinor', 1400)
        ->where('upcoming.1.ticketTypes.0.priceLabel', '£14.00')
        ->where('upcoming.1.ticketTypes.1.name', 'Supporter admission')
        ->where('upcoming.1.ticketTypes.1.description', 'The same admission at a voluntary supporter price.')
        ->where('upcoming.1.ticketTypes.1.priceLabel', '£20.00')
        ->where('past.0.slug', 'city-in-the-margins')
        ->where('past.1.slug', 'paper-trails')
        ->missing('auth')->missing('upcoming.1.ticketTypes.0.capacity')
        ->missing('upcoming.1.ticketTypes.0.available')->missing('upcoming.1.ticketTypes.0.soldOut'));
    $this->seed();
    $this->assertDatabaseCount('events', 4);
    expect(DB::table('ticket_types')->orderBy('id')->get()->all())->toEqual($ticketsBefore);
});

test('exposes persisted slug and cover with the event local start date across UTC year boundaries', function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-30T12:00:00Z'));
    $event = Event::factory()->create(['slug' => 'new-year-reading', 'cover_image_url' => '/original-cover.svg', 'timezone' => 'Europe/Helsinki', 'starts_at' => '2026-12-31T23:30:00Z', 'ends_at' => '2027-01-01T01:30:00Z']);

    $this->get(route('home'))->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('events/index')
        ->where('upcoming.0.id', $event->id)
        ->where('upcoming.0.slug', 'new-year-reading')
        ->where('upcoming.0.coverImageUrl', '/original-cover.svg')
        ->where('upcoming.0.localStartDate', '2027-01-01'));
});

test('returns successful empty lists after a successful empty catalogue read', function () {
    $this->get(route('home'))->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('events/index')->where('upcoming', [])->where('past', []));
});

test('reports catalogue query failures instead of returning a successful empty listing', function () {
    Exceptions::fake();
    config(['app.debug' => false]);
    $event = Event::factory()->create();
    DB::connection()->beforeExecuting(function (string $query, array $bindings): void {
        if (str_contains($query, '"events"')) {
            throw new QueryException('pgsql', $query, $bindings, new PDOException('Injected catalogue read failure.'));
        }
    });

    try {
        $response = $this->get(route('home'))->assertServerError();
        expect($response->getContent())->not->toContain('"component":"events/index"');
        Exceptions::assertReported(QueryException::class);
    } finally {
        DB::purge('pgsql');
    }

    $this->assertModelExists($event);
    $this->assertDatabaseCount('events', 1);
});

test('reports corrupted timezone data without silently substituting UTC', function () {
    Exceptions::fake();
    config(['app.debug' => false]);
    $event = Event::factory()->create();
    DB::table('events')->where('id', $event->id)->update(['timezone' => 'Mars/Olympus']);

    $this->get(route('home'))->assertServerError();

    Exceptions::assertReported(InvalidTimeZoneException::class);
    $this->assertDatabaseHas('events', ['id' => $event->id, 'timezone' => 'Mars/Olympus']);
});

test('throttles public reads and permits reads after the window without changing business data', function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-30T12:00:00Z'));
    $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.61']);

    for ($request = 0; $request < 60; $request++) {
        $this->get(route('home'))->assertOk();
    }
    $this->get(route('home'))->assertTooManyRequests()->assertHeader('Retry-After');
    $this->travel(61)->seconds();
    $this->get(route('home'))->assertOk();

    $this->assertDatabaseCount('events', 0);
    $this->assertDatabaseCount('ticket_types', 0);
});
