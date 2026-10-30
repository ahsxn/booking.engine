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

test('serves Past separately and preserves the explicit Upcoming default', function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-30T12:00:00Z'));
    $this->seed();
    $this->get(route('home', ['view' => 'upcoming']))->assertOk()->assertInertia(fn (Assert $page) => $page
        ->where('view', 'upcoming')->has('events.data', 2)->where('events.data.0.slug', 'lantern-pages'));
    $this->get(route('home', ['view' => 'past']))->assertOk()->assertInertia(fn (Assert $page) => $page
        ->where('view', 'past')->has('events.data', 2)
        ->where('events.data.0.slug', 'city-in-the-margins')->where('events.data.1.slug', 'paper-trails'));
});

test('round trips native numeric page metadata through partial append and reset requests', function (string $view) {
    $this->travelTo(CarbonImmutable::parse('2026-10-30T12:00:00Z'));
    $events = Event::factory()->count(45)->{$view}()->create();
    $ids = $view === 'past' ? array_reverse($events->modelKeys()) : $events->modelKeys();
    $initial = $this->get(route('home', ['view' => $view]))->assertOk()->viewData('page');
    expect($initial['mergeProps'])->toContain('events.data');
    expect($initial['matchPropsOn'])->toContain('events.data.id');
    expect($initial['scrollProps']['events'])->toMatchArray([
        'pageName' => 'page', 'currentPage' => 1,
        'nextPage' => 2, 'previousPage' => null,
    ]);
    $headers = [
        'X-Inertia' => 'true', 'X-Inertia-Version' => $initial['version'],
        'X-Requested-With' => 'XMLHttpRequest', 'X-Inertia-Partial-Component' => 'events/index',
        'X-Inertia-Partial-Data' => 'events', 'X-Inertia-Infinite-Scroll-Merge-Intent' => 'append',
    ];
    $next = $this->get($initial['props']['events']['next_page_url'], $headers)->assertOk()->assertHeader('X-Inertia', 'true');
    $next->assertJsonMissingPath('props.view')->assertJsonMissingPath('props.browse')
        ->assertJsonPath('scrollProps.events.currentPage', 2)->assertJsonPath('scrollProps.events.nextPage', 3)
        ->assertJsonMissingPath('props.events.asOf')->assertJsonMissingPath('props.events.next_cursor');
    expect(array_column($next->json('props.events.data'), 'id'))->toBe(array_slice($ids, 20, 20));
    $previous = $this->get($next->json('props.events.prev_page_url'), $headers)->assertOk();
    expect(array_column($previous->json('props.events.data'), 'id'))->toBe(array_slice($ids, 0, 20));
    $last = $this->get($next->json('props.events.next_page_url'), $headers)->assertOk()->assertJsonPath('props.events.next_page_url', null);
    expect(array_column($last->json('props.events.data'), 'id'))->toBe(array_slice($ids, 40));
    $this->get(route('home', ['view' => $view]), [...$headers, 'X-Inertia-Partial-Data' => 'view,events', 'X-Inertia-Reset' => 'events'])
        ->assertOk()->assertJsonPath('props.view', $view)
        ->assertJsonPath('props.events.current_page', 1)->assertJsonPath('scrollProps.events.reset', true);
})->with(['upcoming', 'past']);

test('rejects invalid views with standard JSON validation before reading the catalogue', function (array $query) {
    DB::connection()->enableQueryLog();
    DB::connection()->flushQueryLog();
    try {
        $this->getJson(route('home', $query))->assertUnprocessable()->assertJsonValidationErrors('view');
        $queries = DB::connection()->getQueryLog();
    } finally {
        DB::connection()->disableQueryLog();
    }
    expect(collect($queries)->filter(fn (array $entry): bool => str_contains($entry['query'], '"events"')))->toHaveCount(0);
})->with(['array' => [['view' => ['past']]], 'blank' => [['view' => '']], 'case' => [['view' => 'Past']], 'unknown' => [['view' => 'all']]]);

test('recovers invalid document and Inertia views at canonical home without a redirect loop', function (bool $inertia) {
    $initial = $this->get(route('home'))->assertOk()->viewData('page');
    $headers = $inertia ? ['X-Inertia' => 'true', 'X-Inertia-Version' => $initial['version'], 'X-Requested-With' => 'XMLHttpRequest'] : [];
    $this->from(route('home', ['view' => 'bad']))->get(route('home', ['view' => 'bad']), $headers)
        ->assertRedirect(route('home'))->assertSessionHasErrors('view');
    $this->get(route('home'), $headers)->assertOk();
})->with([false, true]);

test('uses native page one fallback for invalid page inputs', function (array $query) {
    $this->travelTo(CarbonImmutable::parse('2026-10-30T12:00:00Z'));
    $events = Event::factory()->count(21)->upcoming()->create();
    $this->get(route('home', $query))->assertOk()->assertInertia(fn (Assert $page) => $page
        ->where('view', 'upcoming')->where('events.current_page', 1)
        ->where('events.data.0.id', $events[0]->id)->has('events.data', 20));
})->with(['missing' => [[]], 'zero' => [['page' => 0]], 'negative' => [['page' => -1]], 'fraction' => [['page' => '1.5']], 'text' => [['page' => 'bad']], 'array' => [['page' => [2]]], 'overflow' => [['page' => '9223372036854775808']]]);

test('returns a successful empty page beyond the catalogue', function () {
    Event::factory()->upcoming()->create();
    $this->get(route('home', ['page' => 900]))->assertOk()->assertInertia(fn (Assert $page) => $page
        ->where('events.current_page', 900)->where('events.data', [])->where('events.next_page_url', null));
});

test('ignores obsolete cursor reference and sort controls and GET body view overrides', function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-30T12:00:00Z'));
    $events = Event::factory()->count(21)->upcoming()->create();
    Event::factory()->past()->create();
    $response = $this->call('GET', route('home', ['view' => 'upcoming', 'cursor' => ['invalid'], 'as_of' => 'future', 'per_page' => 1000, 'sort' => 'id desc; drop table events']), [], [], [], ['CONTENT_TYPE' => 'application/json'], '{"view":"past"}')->assertOk();
    $page = $response->viewData('page')['props']['events'];
    expect(array_column($page['data'], 'id'))->toBe(array_slice($events->modelKeys(), 0, 20));
    parse_str(parse_url($page['next_page_url'], PHP_URL_QUERY), $query);
    expect($query)->toEqual(['view' => 'upcoming', 'page' => '2']);
    $this->assertDatabaseCount('events', 22);
});

test('reclassifies on a later HTTP request rather than preserving a reference', function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-30T12:00:00Z'));
    $event = Event::factory()->create(['starts_at' => now()->addSecond(), 'ends_at' => now()->addHour()]);
    $this->get(route('home'))->assertOk()->assertInertia(fn (Assert $page) => $page->where('events.data.0.id', $event->id));
    $this->travel(2)->seconds();
    $this->get(route('home'))->assertOk()->assertInertia(fn (Assert $page) => $page->where('events.data', []));
    $this->get(route('home', ['view' => 'past']))->assertOk()->assertInertia(fn (Assert $page) => $page->where('events.data.0.id', $event->id));
});

test('guests receive the seeded catalogue through the public home route', function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-30T12:00:00Z'));
    $this->seed();
    $helsinki = Event::query()->where('slug', 'new-year-new-chapter')->firstOrFail();
    $ticketsBefore = DB::table('ticket_types')->orderBy('id')->get()->all();

    $response = $this->get(route('home'))->assertOk();

    $response->assertInertia(fn (Assert $page) => $page
        ->component('events/index')
        ->where('view', 'upcoming')->has('events.data', 2)->missing('upcoming')->missing('past')
        ->where('events.data.0.slug', 'lantern-pages')
        ->where('events.data.1.title', 'A New Year, A New Chapter')
        ->where('events.data.1.slug', $helsinki->slug)
        ->where('events.data.1.coverImageUrl', $helsinki->cover_image_url)
        ->where('events.data.1.localStartDate', '2027-01-16')
        ->where('events.data.1.startsAtLabel', '16 January 2027, 14:00 (+02:00)')
        ->where('events.data.1.timezone', 'Europe/Helsinki')
        ->where('events.data.1.venueName', 'Central Library Oodi')
        ->where('events.data.1.ticketTypes.0.name', 'General admission')
        ->where('events.data.1.ticketTypes.0.priceMinor', 1400)
        ->where('events.data.1.ticketTypes.0.priceLabel', '£14.00')
        ->where('events.data.1.ticketTypes.1.name', 'Supporter admission')
        ->where('events.data.1.ticketTypes.1.description', 'The same admission at a voluntary supporter price.')
        ->where('events.data.1.ticketTypes.1.priceLabel', '£20.00')
        ->missing('auth')->missing('events.data.1.ticketTypes.0.capacity')
        ->missing('events.data.1.ticketTypes.0.available')->missing('events.data.1.ticketTypes.0.soldOut'));
    $this->seed();
    $this->assertDatabaseCount('events', 4);
    expect(DB::table('ticket_types')->orderBy('id')->get()->all())->toEqual($ticketsBefore);
});

test('exposes persisted slug and cover with the event local start date across UTC year boundaries', function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-30T12:00:00Z'));
    $event = Event::factory()->create(['slug' => 'new-year-reading', 'cover_image_url' => '/original-cover.svg', 'timezone' => 'Europe/Helsinki', 'starts_at' => '2026-12-31T23:30:00Z', 'ends_at' => '2027-01-01T01:30:00Z']);

    $this->get(route('home'))->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('events/index')
        ->where('events.data.0.id', $event->id)
        ->where('events.data.0.slug', 'new-year-reading')
        ->where('events.data.0.coverImageUrl', '/original-cover.svg')
        ->where('events.data.0.localStartDate', '2027-01-01'));
});

test('returns successful empty lists after a successful empty catalogue read', function () {
    $this->get(route('home'))->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('events/index')->where('events.data', [])->where('events.next_page_url', null));
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
