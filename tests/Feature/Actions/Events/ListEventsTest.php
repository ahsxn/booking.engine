<?php

use App\Actions\Events\ListEvents;
use App\Data\Events\EventListingData;
use App\Models\Event;
use App\Models\TicketType;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;

uses(DatabaseMigrations::class);

afterEach(function () {
    $this->travelBack();
});

test('traverses bounded pages without gaps or duplicates in each chronological view', function (string $view, int $count, array $sizes) {
    $reference = CarbonImmutable::parse('2026-10-30T12:00:00Z');
    $this->travelTo($reference);
    $events = Event::factory()->count($count)->sequence(fn ($sequence) => [
        'starts_at' => $reference->addSeconds(($view === 'upcoming' ? 1 : -1) * ($sequence->index + 1)),
        'ends_at' => $reference->addDays(2),
    ])->has(TicketType::factory()->count(2))->create();
    Event::factory()->{$view === 'upcoming' ? 'past' : 'upcoming'}()->create();
    $expected = $events->modelKeys();
    $ids = [];
    $observedSizes = [];
    $pageNumber = 1;

    do {
        request()->query->set('page', $pageNumber);
        $page = (new ListEvents)->handle($view);
        $observedSizes[] = count($page->items());
        $ids = array_merge($ids, array_column($page->items(), 'id'));
        $pageNumber++;
        expect($page)->toBeInstanceOf(Paginator::class);
        expect($page->items()[0] ?? null)->toBeInstanceOf(EventListingData::class);
        expect($page->toArray()['data'])->toHaveCount(count($page->items()));
    } while ($page->hasMorePages());

    expect($ids)->toBe($expected);
    expect(array_unique($ids))->toHaveCount($count);
    expect($observedSizes)->toBe($sizes);
})->with(['upcoming' => ['upcoming', 53, [20, 20, 13]], 'past' => ['past', 73, [20, 20, 20, 13]]]);

test('traverses equal starts with a unique ID tie breaker across three pages', function (string $view) {
    $this->travelTo(CarbonImmutable::parse('2026-10-30T12:00:00Z'));
    $events = Event::factory()->count(45)->{$view}()->create();
    $expected = $events->modelKeys();
    if ($view === 'past') {
        $expected = array_reverse($expected);
    }
    $ids = [];
    $pageNumber = 1;
    do {
        request()->query->set('page', $pageNumber);
        $page = (new ListEvents)->handle($view);
        $ids = array_merge($ids, array_column($page->items(), 'id'));
        $pageNumber++;
    } while ($page->hasMorePages());
    expect($ids)->toBe($expected);
})->with(['upcoming', 'past']);

test('detects exact page boundaries without an extra sentinel page', function (int $count) {
    $this->travelTo(CarbonImmutable::parse('2026-10-30T12:00:00Z'));
    Event::factory()->count($count)->upcoming()->create();
    $pageNumber = 1;
    $sizes = [];
    do {
        request()->query->set('page', $pageNumber);
        $page = (new ListEvents)->handle('upcoming');
        $sizes[] = count($page->items());
        $pageNumber++;
    } while ($page->hasMorePages());
    expect(array_sum($sizes))->toBe($count);
    expect(count($sizes))->toBe(max(1, (int) ceil($count / 20)));
})->with([0, 1, 19, 20, 21, 40, 41]);

test('classifies using live server time on every request', function () {
    $reference = CarbonImmutable::parse('2026-10-30T12:00:00Z');
    $this->travelTo($reference);
    $event = Event::factory()->create(['starts_at' => $reference->addSecond(), 'ends_at' => $reference->addHour()]);
    expect(array_column((new ListEvents)->handle('upcoming')->items(), 'id'))->toBe([$event->id]);
    $this->travel(2)->seconds();
    expect((new ListEvents)->handle('upcoming')->items())->toBe([]);
    expect(array_column((new ListEvents)->handle('past')->items(), 'id'))->toBe([$event->id]);
});

test('lists every event once in stable upcoming and past order with its own ordered tickets', function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-30T12:00:00Z'));
    $later = Event::factory()->create(['starts_at' => '2026-11-15T12:00:00Z', 'ends_at' => '2026-11-15T14:00:00Z']);
    $pastFirst = Event::factory()->past()->create();
    $upcomingFirst = Event::factory()->upcoming()->create();
    $pastSecond = Event::factory()->past()->create();
    $upcomingSecond = Event::factory()->upcoming()->create();
    $lastTicket = TicketType::factory()->for($upcomingFirst)->create(['display_order' => 3]);
    $firstTicket = TicketType::factory()->for($upcomingFirst)->create(['display_order' => 0]);
    $tiedTicket = TicketType::factory()->for($upcomingFirst)->create(['display_order' => 0]);
    $otherTicket = TicketType::factory()->for($later)->create();

    $result = (new ListEvents)->handle('upcoming');

    expect(array_column($result->items(), 'id'))->toBe([$upcomingFirst->id, $upcomingSecond->id, $later->id]);
    expect(array_column((new ListEvents)->handle('past')->items(), 'id'))->toBe([$pastSecond->id, $pastFirst->id]);
    expect(array_column($result->items()[0]->ticketTypes, 'id'))->toBe([$firstTicket->id, $tiedTicket->id, $lastTicket->id]);
    expect(array_column($result->items()[2]->ticketTypes, 'id'))->toBe([$otherTicket->id]);
});

test('classifies an event at its start instant even while it is ongoing', function (int $seconds, string $group) {
    $reference = CarbonImmutable::parse('2026-10-30T12:00:00Z');
    $this->travelTo($reference);
    $event = Event::factory()->create(['starts_at' => $reference->addSeconds($seconds), 'ends_at' => $reference->addHours(2)]);

    expect(array_column((new ListEvents)->handle($group)->items(), 'id'))->toBe([$event->id]);
    expect((new ListEvents)->handle($group === 'past' ? 'upcoming' : 'past')->items())->toBe([]);
})->with(['before start' => [1, 'upcoming'], 'exact start' => [0, 'past'], 'ongoing' => [-1, 'past']]);

test('projects event local dates and both endpoint offsets without changing instants', function (string $start, string $end, string $zone, string $date, string $startLabel, string $endLabel) {
    $this->travelTo(CarbonImmutable::parse('2026-01-01T00:00:00Z'));
    Event::factory()->count(20)->create(['starts_at' => '2026-01-02T12:00:00Z', 'ends_at' => '2026-01-02T14:00:00Z']);
    $event = Event::factory()->create(['starts_at' => $start, 'ends_at' => $end, 'timezone' => $zone]);

    request()->query->set('page', 2);
    $data = (new ListEvents)->handle('upcoming')->items()[0];

    expect($data->localStartDate)->toBe($date);
    expect($data->startsAtLabel)->toBe($startLabel);
    expect($data->endsAtLabel)->toBe($endLabel);
    expect($data->timezone)->toBe($zone);
    expect($data->startsAt)->toBe(CarbonImmutable::parse($start)->toISOString());
    expect($data->endsAt)->toBe(CarbonImmutable::parse($end)->toISOString());
    expect($event->fresh()->starts_at->toISOString())->toBe($data->startsAt);
    expect($event->fresh()->ends_at->toISOString())->toBe($data->endsAt);
})->with([
    'London summer' => ['2026-07-18T13:00:00Z', '2026-07-18T15:00:00Z', 'Europe/London', '2026-07-18', '18 July 2026, 14:00 (+01:00)', '18 July 2026, 16:00 (+01:00)'],
    'London winter' => ['2026-11-14T18:00:00Z', '2026-11-14T20:00:00Z', 'Europe/London', '2026-11-14', '14 November 2026, 18:00 (+00:00)', '14 November 2026, 20:00 (+00:00)'],
    'Helsinki winter' => ['2027-01-16T12:00:00Z', '2027-01-16T14:00:00Z', 'Europe/Helsinki', '2027-01-16', '16 January 2027, 14:00 (+02:00)', '16 January 2027, 16:00 (+02:00)'],
    'local next year' => ['2026-12-31T23:30:00Z', '2027-01-01T01:30:00Z', 'Europe/Helsinki', '2027-01-01', '1 January 2027, 01:30 (+02:00)', '1 January 2027, 03:30 (+02:00)'],
    'local previous year' => ['2027-01-01T00:30:00Z', '2027-01-01T02:30:00Z', 'America/New_York', '2026-12-31', '31 December 2026, 19:30 (-05:00)', '31 December 2026, 21:30 (-05:00)'],
    'month and overnight boundary' => ['2026-07-31T22:30:00Z', '2026-08-01T00:30:00Z', 'Europe/London', '2026-07-31', '31 July 2026, 23:30 (+01:00)', '1 August 2026, 01:30 (+01:00)'],
    'autumn repeated hour' => ['2026-10-25T00:30:00Z', '2026-10-25T01:30:00Z', 'Europe/London', '2026-10-25', '25 October 2026, 01:30 (+01:00)', '25 October 2026, 01:30 (+00:00)'],
    'spring skipped hour' => ['2026-03-29T00:30:00Z', '2026-03-29T01:30:00Z', 'Europe/London', '2026-03-29', '29 March 2026, 00:30 (+00:00)', '29 March 2026, 02:30 (+01:00)'],
]);

test('serializes exactly the public typed boundary and integer GBP prices', function (int $price, string $label) {
    $this->travelTo(CarbonImmutable::parse('2026-10-30T12:00:00Z'));
    $event = Event::factory()->create();
    $ticket = TicketType::factory()->for($event)->create(['price_minor' => $price]);

    $data = (new ListEvents)->handle('upcoming')->items()[0]->toArray();

    expect($data)->toBe([
        'id' => $event->id,
        'slug' => $event->slug,
        'title' => $event->title,
        'coverImageUrl' => $event->cover_image_url,
        'localStartDate' => '2026-11-14',
        'organizerName' => $event->organizer_name,
        'venueName' => $event->venue_name,
        'venueAddress' => $event->venue_address,
        'timezone' => 'Europe/London',
        'startsAt' => '2026-11-14T18:00:00.000000Z',
        'endsAt' => '2026-11-14T20:00:00.000000Z',
        'startsAtLabel' => '14 November 2026, 18:00 (+00:00)',
        'endsAtLabel' => '14 November 2026, 20:00 (+00:00)',
        'ticketTypes' => [[
            'id' => $ticket->id,
            'name' => 'General admission',
            'description' => null,
            'priceMinor' => $price,
            'currency' => 'GBP',
            'priceLabel' => $label,
        ]],
    ]);
})->with(['penny' => [1, '£0.01'], 'pounds and pence' => [1250, '£12.50'], 'maximum stored price' => [2147483647, '£21474836.47']]);

test('returns empty arrays when there are no events', function () {
    expect((new ListEvents)->handle('upcoming')->items())->toBe([]);
});

test('retains an event without ticket definitions', function () {
    $event = Event::factory()->upcoming()->create();

    $result = (new ListEvents)->handle('upcoming');

    expect($result->items()[0]->id)->toBe($event->id);
    expect($result->items()[0]->ticketTypes)->toBe([]);
    expect($result->items()[0]->toArray()['ticketTypes'])->toBe([]);
});

test('reads the catalogue in two selects including serialization and leaves all rows unchanged', function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-30T12:00:00Z'));
    Event::factory()->count(6)->has(TicketType::factory()->count(2))->create();
    $before = [DB::table('events')->orderBy('id')->get()->all(), DB::table('ticket_types')->orderBy('id')->get()->all()];
    DB::connection()->enableQueryLog();
    DB::connection()->flushQueryLog();

    try {
        $groups = (new ListEvents)->handle('upcoming');
        $groups->toArray();
        $queries = DB::connection()->getQueryLog();
    } finally {
        DB::connection()->disableQueryLog();
    }

    expect($queries)->toHaveCount(2);
    foreach ($queries as $query) {
        expect(strtolower($query['query']))->toStartWith('select ')->not->toContain('for update');
    }
    expect([DB::table('events')->orderBy('id')->get()->all(), DB::table('ticket_types')->orderBy('id')->get()->all()])->toEqual($before);
});

test('bounds first and deep queries in a large catalogue and records real query plans', function () {
    $reference = CarbonImmutable::parse('2026-10-30T12:00:00Z');
    $this->travelTo($reference);
    Event::factory()->count(1000)->sequence(fn ($sequence) => [
        'starts_at' => $reference->subMinutes($sequence->index + 1),
        'ends_at' => $reference->addHour(),
    ])->has(TicketType::factory()->count(2))->create();
    Event::factory()->count(53)->sequence(fn ($sequence) => [
        'starts_at' => $reference->addMinutes($sequence->index + 1),
        'ends_at' => $reference->addDays(2),
    ])->has(TicketType::factory()->count(2))->create();
    $before = [DB::table('events')->orderBy('id')->get()->all(), DB::table('ticket_types')->orderBy('id')->get()->all()];
    $action = new ListEvents;

    foreach (['upcoming', 'past'] as $view) {
        foreach ([1, $view === 'past' ? 46 : 3] as $pageNumber) {
            DB::connection()->enableQueryLog();
            DB::connection()->flushQueryLog();
            try {
                request()->query->set('page', $pageNumber);
                $page = $action->handle($view);
                $page->toArray();
                $queries = DB::connection()->getQueryLog();
            } finally {
                DB::connection()->disableQueryLog();
            }
            expect($queries)->toHaveCount(2);
            expect(count($page->items()))->toBeLessThanOrEqual(20);
            expect(strtolower($queries[0]['query']))->toContain('limit 21')->toContain('offset '.(($pageNumber - 1) * 20))->not->toContain('count(', 'for update');
            preg_match('/in \(([^)]+)\)/', $queries[1]['query'], $matches);
            expect(count(explode(',', $matches[1])))->toBeLessThanOrEqual(21);
            $plan = DB::select('EXPLAIN (ANALYZE, BUFFERS, FORMAT JSON) '.$queries[0]['query'], $queries[0]['bindings']);
            $details = json_decode($plan[0]->{'QUERY PLAN'}, true, flags: JSON_THROW_ON_ERROR)[0];
            expect($details['Plan']['Node Type'])->toBe('Limit');
            expect($details['Plan']['Actual Rows'])->toBeLessThanOrEqual(21);
            if (getenv('EVENT_PAGINATION_EXPLAIN') === '1') {
                fwrite(STDERR, $view.(' page '.$pageNumber.' ').json_encode($details, JSON_THROW_ON_ERROR).PHP_EOL);
            }
        }
    }
    expect([DB::table('events')->orderBy('id')->get()->all(), DB::table('ticket_types')->orderBy('id')->get()->all()])->toEqual($before);
});

test('performs one bounded select for an empty page', function () {
    DB::connection()->enableQueryLog();
    DB::connection()->flushQueryLog();
    try {
        $page = (new ListEvents)->handle('past');
        $page->toArray();
        $queries = DB::connection()->getQueryLog();
    } finally {
        DB::connection()->disableQueryLog();
    }
    expect($page->items())->toBe([]);
    expect($queries)->toHaveCount(1);
    expect($queries[0]['query'])->toContain('limit 21');
});
