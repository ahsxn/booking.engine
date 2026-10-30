<?php

use App\Models\Event;
use App\Models\TicketType;
use Carbon\CarbonImmutable;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\EventSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;

uses(DatabaseMigrations::class);

afterEach(function () {
    $this->travelBack();
});

test('returns only an events tickets in stable display order', function () {
    $event = Event::factory()->create();
    $last = TicketType::factory()->for($event)->create(['display_order' => 3]);
    $first = TicketType::factory()->for($event)->create(['display_order' => 0]);
    $tied = TicketType::factory()->for($event)->create(['display_order' => 0]);
    TicketType::factory()->create();

    expect($event->ticketTypes->modelKeys())->toBe([$first->id, $tied->id, $last->id]);
});

test('accepts exact IANA timezone identifiers', function (string $timezone) {
    $event = Event::factory()->create(['timezone' => $timezone]);

    expect($event->fresh()->timezone)->toBe($timezone);
    $this->assertModelExists($event);
})->with(['Europe/London', 'Europe/Helsinki', 'America/New_York', 'UTC']);

test('rejects invalid timezones on create and update before persistence', function (string $timezone) {
    $event = Event::factory()->create(['timezone' => 'Europe/London']);

    expect(fn () => Event::factory()->create(['timezone' => $timezone]))->toThrow(InvalidArgumentException::class);
    expect(fn () => $event->update(['timezone' => $timezone]))->toThrow(InvalidArgumentException::class);
    $this->assertDatabaseCount('events', 1);
    expect($event->fresh()->timezone)->toBe('Europe/London');
})->with(['abbreviation' => 'BST', 'offset' => '+01:00', 'invented' => 'Mars/Olympus', 'leading space' => ' Europe/London', 'trailing space' => 'Europe/London ', 'blank' => '']);

test('validates timezone assignments even when seeding mutes model events', function () {
    $this->app->bind(EventSeeder::class, fn () => new class extends EventSeeder
    {
        public function run(): void
        {
            Event::factory()->create(['timezone' => 'BST']);
        }
    });

    expect(fn () => $this->seed(DatabaseSeeder::class))->toThrow(InvalidArgumentException::class);
    $this->assertDatabaseCount('events', 0);
    $this->assertDatabaseCount('ticket_types', 0);
});

test('preserves UTC instants while reading event local dates', function (string $utc, string $timezone, string $expected) {
    $start = CarbonImmutable::parse($utc);
    $event = Event::factory()->create(['timezone' => $timezone, 'starts_at' => $start, 'ends_at' => $start->addHours(2)])->fresh();

    expect($event->starts_at)->toBeInstanceOf(CarbonImmutable::class);
    expect($event->starts_at->setTimezone($timezone)->format('Y-m-d H:i:s P'))->toBe($expected);
    expect($event->starts_at->toIso8601String())->toBe($start->toIso8601String());
    expect($event->toArray()['starts_at'])->toBe($start->toISOString());
    expect($event->fresh()->starts_at->toISOString())->toBe($start->toISOString());
})->with([
    'London summer' => ['2026-07-18T13:00:00Z', 'Europe/London', '2026-07-18 14:00:00 +01:00'],
    'London winter' => ['2026-11-14T18:00:00Z', 'Europe/London', '2026-11-14 18:00:00 +00:00'],
    'Helsinki winter' => ['2027-01-16T12:00:00Z', 'Europe/Helsinki', '2027-01-16 14:00:00 +02:00'],
]);

test('normalizes offset bearing SQL timestamps to the same UTC instant', function () {
    $event = Event::factory()->create();
    DB::table('events')->where('id', $event->id)->update(['starts_at' => '2026-07-18 14:00:00+01', 'ends_at' => '2026-07-18 16:00:00+01']);

    expect($event->fresh()->starts_at->toISOString())->toBe('2026-07-18T13:00:00.000000Z');
});

test('keeps distinct instants across the repeated autumn hour', function () {
    $event = Event::factory()->create([
        'timezone' => 'Europe/London',
        'starts_at' => CarbonImmutable::parse('2026-10-25T00:30:00Z'),
        'ends_at' => CarbonImmutable::parse('2026-10-25T01:30:00Z'),
    ])->fresh();

    expect($event->starts_at->setTimezone($event->timezone)->format('H:i P'))->toBe('01:30 +01:00');
    expect($event->ends_at->setTimezone($event->timezone)->format('H:i P'))->toBe('01:30 +00:00');
    expect($event->starts_at->lessThan($event->ends_at))->toBeTrue();
    expect(Event::query()->whereColumn('ends_at', '>', 'starts_at')->count())->toBe(1);
});

test('creates useful factory states relative to the controlled clock', function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-30T12:00:00Z'));
    $upcoming = Event::factory()->upcoming()->create();
    $past = Event::factory()->past()->create();

    expect($upcoming->starts_at->isFuture())->toBeTrue();
    expect($past->ends_at->isPast())->toBeTrue();
});
