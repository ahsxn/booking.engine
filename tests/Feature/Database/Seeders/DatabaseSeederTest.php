<?php

use App\Models\Event;
use App\Models\TicketType;
use Carbon\CarbonImmutable;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\TicketTypeSeeder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(DatabaseMigrations::class);

afterEach(function () {
    $this->travelBack();
});

/** @return array{events: array, tickets: array} */
function catalogueSnapshot(): array
{
    return [
        'events' => DB::table('events')->orderBy('id')->get()->map(fn (object $row): array => (array) $row)->all(),
        'tickets' => DB::table('ticket_types')->orderBy('id')->get()->map(fn (object $row): array => (array) $row)->all(),
    ];
}

test('seeds the exact original catalogue without accounts or sales', function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-30T12:00:00Z'));
    $this->seed(DatabaseSeeder::class);

    $this->assertDatabaseCount('events', 4);
    $this->assertDatabaseCount('ticket_types', 8);
    $this->assertDatabaseCount('users', 0);
    $this->assertDatabaseCount('jobs', 0);
    foreach (['orders', 'order_items', 'payment_attempts'] as $table) {
        expect(Schema::hasTable($table))->toBeFalse();
    }
    expect(Schema::hasColumn('ticket_types', 'reserved_count'))->toBeFalse();
    expect(Schema::hasColumn('ticket_types', 'confirmed_count'))->toBeFalse();
    expect(Event::query()->distinct()->pluck('organizer_name')->all())->toBe(['Margin & Lantern']);

    $expected = [
        'paper-trails' => ['Paper Trails: An Autumn Reading Circle', 'Bring a short passage about a journey and join a relaxed conversation about the places books carry us.', 'Barbican Centre', 'Silk Street, London EC2Y 8DS, United Kingdom', 'Europe/London', '2026-09-19T13:00:00.000000Z', '2026-09-19T15:00:00.000000Z', '14:00', 1200, 30, 1800, 10, 'Admission to the reading circle.'],
        'city-in-the-margins' => ['The City in the Margins', 'Explore how overlooked streets become memorable settings, then trade reading recommendations over a guided discussion.', 'Manchester Central Library', 'St Peter\'s Square, Manchester M2 5PD, United Kingdom', 'Europe/London', '2026-10-17T13:00:00.000000Z', '2026-10-17T15:00:00.000000Z', '14:00', 1000, 24, 1600, 8, 'Admission to the discussion.'],
        'lantern-pages' => ['Lantern Pages: Stories After Dusk', 'Share an evening of short fiction and discuss how writers build atmosphere with light, silence and surprise.', 'The British Library', '96 Euston Road, London NW1 2DB, United Kingdom', 'Europe/London', '2026-11-14T18:00:00.000000Z', '2026-11-14T20:00:00.000000Z', '18:00', 1500, 40, 2200, 12, 'Admission to the story evening.'],
        'new-year-new-chapter' => ['A New Year, A New Chapter', 'Gather for a fresh reading season, compare discoveries and choose a personal reading challenge for the months ahead.', 'Central Library Oodi', 'Töölönlahdenkatu 4, 00100 Helsinki, Finland', 'Europe/Helsinki', '2027-01-16T12:00:00.000000Z', '2027-01-16T14:00:00.000000Z', '14:00', 1400, 28, 2000, 8, 'Admission to the reading gathering.'],
    ];
    foreach ($expected as $slug => [$title, $description, $venue, $address, $zone, $start, $end, $local, $standardPrice, $standardCapacity, $supporterPrice, $supporterCapacity, $ticketDescription]) {
        $event = Event::query()->where('slug', $slug)->firstOrFail();
        expect([$event->title, $event->description, $event->venue_name, $event->venue_address, $event->timezone])->toBe([$title, $description, $venue, $address, $zone]);
        expect([$event->starts_at->toISOString(), $event->ends_at->toISOString()])->toBe([$start, $end]);
        expect($event->starts_at->setTimezone($zone)->format('H:i'))->toBe($local);
        expect(in_array($zone, DateTimeZone::listIdentifiers(), true))->toBeTrue();
        expect(DB::table('pg_timezone_names')->where('name', $zone)->exists())->toBeTrue();
        expect($event->ticketTypes->map(fn (TicketType $ticket): array => [$ticket->code, $ticket->name, $ticket->description, $ticket->display_order, $ticket->price_minor, $ticket->capacity, $ticket->currency])->all())->toBe([
            ['standard-ga', 'General admission', $ticketDescription, 0, $standardPrice, $standardCapacity, 'GBP'],
            ['supporter-ga', 'Supporter admission', 'The same admission at a voluntary supporter price.', 1, $supporterPrice, $supporterCapacity, 'GBP'],
        ]);
        expect($event->cover_image_url)->toStartWith('data:image/svg+xml;base64,');
        $svg = base64_decode(substr($event->cover_image_url, strlen('data:image/svg+xml;base64,')), true);
        expect($svg)->toContain('<svg', '<path', '<rect')->not->toContain('<script', 'href=', '<image', 'luma');
    }
    expect(Event::query()->distinct()->pluck('cover_image_url'))->toHaveCount(4);
});

test('repeats seeds without changing identities values or audit timestamps', function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-30T12:00:00Z'));
    $this->seed();
    $before = catalogueSnapshot();
    $this->travelTo(CarbonImmutable::parse('2027-02-01T12:00:00Z'));

    $this->seed();

    expect(catalogueSnapshot())->toBe($before);
    $this->assertDatabaseCount('events', 4);
    $this->assertDatabaseCount('ticket_types', 8);
});

test('preserves edits and unrelated records on replay', function () {
    $this->seed();
    Event::query()->where('slug', 'paper-trails')->firstOrFail()->update(['title' => 'Edited title', 'venue_name' => 'Edited venue']);
    $event = Event::query()->where('slug', 'paper-trails')->firstOrFail();
    $event->ticketTypes()->where('code', 'standard-ga')->firstOrFail()->update(['name' => 'Edited admission', 'price_minor' => 1750, 'capacity' => 99]);
    TicketType::factory()->create();
    $before = catalogueSnapshot();

    $this->seed();

    expect(catalogueSnapshot())->toBe($before);
    $this->assertDatabaseCount('events', 5);
    $this->assertDatabaseCount('ticket_types', 9);
});

test('restores only a missing seeded ticket', function () {
    $this->seed();
    $event = Event::query()->where('slug', 'lantern-pages')->firstOrFail();
    $event->ticketTypes()->where('code', 'supporter-ga')->delete();
    $before = catalogueSnapshot();

    $this->seed();

    $after = catalogueSnapshot();
    expect($after['events'])->toBe($before['events']);
    expect(array_slice($after['tickets'], 0, 7))->toBe($before['tickets']);
    $this->assertDatabaseCount('events', 4);
    $this->assertDatabaseCount('ticket_types', 8);
    expect($event->ticketTypes()->where('code', 'supporter-ga')->firstOrFail()->capacity)->toBe(12);
});

test('keeps fixed upcoming and past examples across replay dates', function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-30T12:00:00Z'));
    $this->seed();
    $before = Event::query()->orderBy('id')->get()->map(fn (Event $event): array => [$event->slug, $event->starts_at->toISOString(), $event->ends_at->toISOString()])->all();

    expect(Event::query()->where('starts_at', '>', now())->orderBy('starts_at')->pluck('slug')->all())->toBe(['lantern-pages', 'new-year-new-chapter']);
    expect(Event::query()->where('starts_at', '<=', now())->orderBy('starts_at')->pluck('slug')->all())->toBe(['paper-trails', 'city-in-the-margins']);
    $this->travelTo(CarbonImmutable::parse('2026-11-14T18:00:00Z'));
    expect(Event::query()->where('starts_at', '<=', now())->where('slug', 'lantern-pages')->exists())->toBeTrue();
    expect(Event::query()->where('starts_at', '>', now())->where('slug', 'lantern-pages')->exists())->toBeFalse();
    $this->travelTo(CarbonImmutable::parse('2028-01-01T00:00:00Z'));
    $this->seed();
    expect(Event::query()->where('starts_at', '>', now())->count())->toBe(0);
    expect(Event::query()->where('starts_at', '<=', now())->count())->toBe(4);
    expect(Event::query()->orderBy('id')->get()->map(fn (Event $event): array => [$event->slug, $event->starts_at->toISOString(), $event->ends_at->toISOString()])->all())->toBe($before);
});

test('rolls back all catalogue additions if ticket seeding fails', function () {
    TicketType::factory()->create();
    $before = catalogueSnapshot();
    $this->app->bind(TicketTypeSeeder::class, fn () => new class extends TicketTypeSeeder
    {
        public function run(): void
        {
            parent::run();
            throw new RuntimeException('Injected failure before catalogue commit.');
        }
    });

    expect(fn () => $this->seed(DatabaseSeeder::class))->toThrow(RuntimeException::class, 'Injected failure before catalogue commit.');
    expect(catalogueSnapshot())->toBe($before);
    $this->app->offsetUnset(TicketTypeSeeder::class);
    $this->seed();
    $this->assertDatabaseCount('events', 5);
    $this->assertDatabaseCount('ticket_types', 9);
});

test('fails clearly when a ticket seeders parent is absent', function () {
    expect(fn () => $this->seed(TicketTypeSeeder::class))->toThrow(ModelNotFoundException::class);
    $this->assertDatabaseCount('ticket_types', 0);

    $this->seed();
    $this->assertDatabaseCount('events', 4);
    $this->assertDatabaseCount('ticket_types', 8);
});
