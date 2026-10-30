<?php

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(DatabaseMigrations::class);

/** @return array<string, mixed> */
function validCatalogueEvent(): array
{
    return [
        'slug' => 'schema-reading', 'title' => 'Schema Reading', 'description' => 'An original reading gathering.',
        'organizer_name' => 'Margin & Lantern', 'cover_image_url' => 'data:image/svg+xml;base64,PHN2Zy8+',
        'venue_name' => 'Public Library', 'venue_address' => '1 Library Street, London',
        'timezone' => 'Europe/London', 'starts_at' => '2026-11-14 18:00:00+00', 'ends_at' => '2026-11-14 20:00:00+00',
    ];
}

/** @return array<string, mixed> */
function validCatalogueTicket(int $eventId): array
{
    return ['event_id' => $eventId, 'code' => 'standard-ga', 'name' => 'General admission', 'price_minor' => 1250, 'capacity' => 20];
}

function assertCatalogueConstraint(Closure $write, string $state, ?string $constraint = null): void
{
    try {
        DB::transaction($write);
        test()->fail('Expected PostgreSQL to reject the invalid catalogue write.');
    } catch (QueryException $exception) {
        expect($exception->errorInfo[0])->toBe($state);
        if ($constraint !== null) {
            expect($exception->getMessage())->toContain($constraint);
        }
    }
}

test('creates the exact event catalogue schema on isolated PostgreSQL', function () {
    expect(DB::selectOne("SELECT current_database() AS database, current_user AS role, current_setting('TimeZone') AS timezone"))
        ->toMatchObject(['database' => 'laravel_test', 'role' => 'laravel_test', 'timezone' => 'UTC']);

    $expected = [
        'events' => [
            'id' => ['bigint', 'NO', null], 'slug' => ['character varying', 'NO', 160],
            'title' => ['character varying', 'NO', 255], 'description' => ['text', 'NO', null],
            'organizer_name' => ['character varying', 'NO', 120], 'cover_image_url' => ['text', 'NO', null],
            'venue_name' => ['character varying', 'NO', 255], 'venue_address' => ['text', 'NO', null],
            'timezone' => ['character varying', 'NO', 64], 'starts_at' => ['timestamp with time zone', 'NO', null],
            'ends_at' => ['timestamp with time zone', 'NO', null],
            'created_at' => ['timestamp with time zone', 'YES', null], 'updated_at' => ['timestamp with time zone', 'YES', null],
        ],
        'ticket_types' => [
            'id' => ['bigint', 'NO', null], 'event_id' => ['bigint', 'NO', null],
            'code' => ['character varying', 'NO', 64], 'name' => ['character varying', 'NO', 120],
            'description' => ['text', 'YES', null], 'display_order' => ['integer', 'NO', null],
            'price_minor' => ['integer', 'NO', null], 'currency' => ['character varying', 'NO', 3],
            'capacity' => ['integer', 'NO', null], 'created_at' => ['timestamp with time zone', 'YES', null],
            'updated_at' => ['timestamp with time zone', 'YES', null],
        ],
    ];

    foreach ($expected as $table => $definitions) {
        $columns = collect(DB::select('SELECT column_name, data_type, is_nullable, character_maximum_length, datetime_precision, column_default FROM information_schema.columns WHERE table_schema = ? AND table_name = ?', ['public', $table]))->keyBy('column_name');
        expect($columns->keys()->sort()->values()->all())->toBe(collect(array_keys($definitions))->sort()->values()->all());
        foreach ($definitions as $name => [$type, $nullable, $length]) {
            $column = $columns[$name];
            expect([$column->data_type, $column->is_nullable, $column->character_maximum_length])->toBe([$type, $nullable, $length]);
            if ($type === 'timestamp with time zone') {
                expect($column->datetime_precision)->toBe(0);
            }
            if ($name === 'id') {
                expect($column->column_default)->toContain('nextval(');
            } elseif ($name === 'currency') {
                expect($column->column_default)->toContain('GBP');
            } elseif ($name === 'display_order') {
                expect($column->column_default)->toBe('0');
            } else {
                expect($column->column_default)->toBeNull();
            }
        }
    }

    $indexes = collect(DB::select("SELECT indexname, indexdef FROM pg_indexes WHERE schemaname = 'public' AND tablename IN ('events', 'ticket_types')"))->pluck('indexdef', 'indexname');
    expect($indexes->keys()->sort()->values()->all())->toBe([
        'events_pkey', 'events_slug_unique', 'events_starts_at_id_index', 'ticket_types_event_id_code_unique',
        'ticket_types_event_id_display_order_id_index', 'ticket_types_pkey',
    ]);
    foreach ([
        'events_pkey' => '(id)', 'events_slug_unique' => '(slug)', 'events_starts_at_id_index' => '(starts_at, id)',
        'ticket_types_pkey' => '(id)', 'ticket_types_event_id_code_unique' => '(event_id, code)',
        'ticket_types_event_id_display_order_id_index' => '(event_id, display_order, id)',
    ] as $name => $columns) {
        expect($indexes[$name])->toContain('USING btree', $columns);
        if (str_ends_with($name, '_unique') || str_ends_with($name, '_pkey')) {
            expect($indexes[$name])->toContain('UNIQUE INDEX');
        }
    }
    $checks = collect(DB::select("SELECT conname FROM pg_constraint WHERE conrelid IN ('events'::regclass, 'ticket_types'::regclass) AND contype = 'c'"))->pluck('conname')->sort()->values()->all();
    expect($checks)->toBe([
        'events_required_text_check', 'events_schedule_check', 'events_slug_format_check',
        'ticket_types_capacity_check', 'ticket_types_code_format_check', 'ticket_types_currency_check',
        'ticket_types_display_order_check', 'ticket_types_name_check', 'ticket_types_price_minor_check',
    ]);
    expect(DB::selectOne("SELECT confdeltype, confupdtype, condeferrable FROM pg_constraint WHERE conname = 'ticket_types_event_id_foreign'"))
        ->toMatchObject(['confdeltype' => 'r', 'confupdtype' => 'r', 'condeferrable' => false]);

    $eventId = DB::table('events')->insertGetId(validCatalogueEvent());
    DB::table('ticket_types')->insert(validCatalogueTicket($eventId));
    $this->assertDatabaseHas('ticket_types', ['event_id' => $eventId, 'currency' => 'GBP', 'display_order' => 0, 'description' => null, 'created_at' => null, 'updated_at' => null]);
});

test('reverses and reapplies only event domain migrations', function () {
    $events = require database_path('migrations/2026_10_30_000100_create_events_table.php');
    $tickets = require database_path('migrations/2026_10_30_000200_create_ticket_types_table.php');
    $tickets->down();
    $events->down();
    expect(Schema::hasTable('events'))->toBeFalse();
    expect(Schema::hasTable('ticket_types'))->toBeFalse();
    expect(Schema::hasTable('users'))->toBeTrue();
    expect(Schema::hasTable('jobs'))->toBeTrue();

    $events->up();
    $tickets->up();
    $eventId = DB::table('events')->insertGetId(validCatalogueEvent());
    DB::table('ticket_types')->insert(validCatalogueTicket($eventId));
    assertCatalogueConstraint(fn () => DB::table('ticket_types')->where('event_id', $eventId)->update(['capacity' => 0]), '23514', 'ticket_types_capacity_check');
    $this->assertDatabaseHas('ticket_types', ['event_id' => $eventId, 'capacity' => 20]);
});

test('rejects null in each required catalogue field', function (string $table, string $column) {
    $eventId = DB::table('events')->insertGetId(validCatalogueEvent());
    $attributes = $table === 'events' ? [...validCatalogueEvent(), 'slug' => 'second-event'] : validCatalogueTicket($eventId);
    assertCatalogueConstraint(fn () => DB::table($table)->insert([...$attributes, $column => null]), '23502');
    $this->assertDatabaseCount('events', 1);
    $this->assertDatabaseCount('ticket_types', 0);
})->with([
    ...array_map(fn (string $column): array => ['events', $column], ['slug', 'title', 'description', 'organizer_name', 'cover_image_url', 'venue_name', 'venue_address', 'timezone', 'starts_at', 'ends_at']),
    ...array_map(fn (string $column): array => ['ticket_types', $column], ['event_id', 'code', 'name', 'display_order', 'price_minor', 'currency', 'capacity']),
]);

test('rejects malformed event slugs', function (string $slug) {
    assertCatalogueConstraint(fn () => DB::table('events')->insert([...validCatalogueEvent(), 'slug' => $slug]), '23514', 'events_slug_format_check');
    $this->assertDatabaseCount('events', 0);
})->with(['empty' => '', 'uppercase' => 'Reading', 'spaces' => 'reading circle']);

test('rejects blank required event content', function (string $column) {
    assertCatalogueConstraint(fn () => DB::table('events')->insert([...validCatalogueEvent(), $column => '   ']), '23514', 'events_required_text_check');
    $this->assertDatabaseCount('events', 0);
})->with(['title', 'description', 'organizer_name', 'cover_image_url', 'venue_name', 'venue_address', 'timezone']);

test('keeps event slugs unique', function () {
    DB::table('events')->insert(validCatalogueEvent());
    assertCatalogueConstraint(fn () => DB::table('events')->insert(validCatalogueEvent()), '23505', 'events_slug_unique');
    DB::table('events')->insert([...validCatalogueEvent(), 'slug' => 'another-reading']);
    $this->assertDatabaseCount('events', 2);
});

test('requires finite events ending after their start', function (string $start, string $end) {
    assertCatalogueConstraint(fn () => DB::table('events')->insert([...validCatalogueEvent(), 'starts_at' => $start, 'ends_at' => $end]), '23514', 'events_schedule_check');
    $this->assertDatabaseCount('events', 0);
})->with([
    'equal' => ['2026-11-14 18:00:00Z', '2026-11-14 18:00:00Z'],
    'reversed' => ['2026-11-14 20:00:00Z', '2026-11-14 18:00:00Z'],
    'infinite end' => ['2026-11-14 18:00:00Z', 'infinity'],
    'infinite start' => ['-infinity', '2026-11-14 18:00:00Z'],
]);

test('accepts short and overnight events without requiring future dates', function (string $start, string $end) {
    DB::table('events')->insert([...validCatalogueEvent(), 'starts_at' => $start, 'ends_at' => $end]);
    $this->assertDatabaseCount('events', 1);
})->with([
    'one second' => ['2020-01-01 00:00:00Z', '2020-01-01 00:00:01Z'],
    'overnight' => ['2020-01-01 23:00:00Z', '2020-01-02 01:00:00Z'],
]);

test('requires ticket ownership and preserves populated events', function () {
    assertCatalogueConstraint(fn () => DB::table('ticket_types')->insert(validCatalogueTicket(999)), '23503', 'ticket_types_event_id_foreign');
    $eventId = DB::table('events')->insertGetId(validCatalogueEvent());
    DB::table('ticket_types')->insert(validCatalogueTicket($eventId));
    assertCatalogueConstraint(fn () => DB::table('events')->where('id', $eventId)->delete(), '23503', 'ticket_types_event_id_foreign');
    assertCatalogueConstraint(fn () => DB::table('events')->where('id', $eventId)->update(['id' => $eventId + 100]), '23503', 'ticket_types_event_id_foreign');
    $this->assertDatabaseHas('events', ['id' => $eventId]);
    $this->assertDatabaseHas('ticket_types', ['event_id' => $eventId]);
});

test('keeps ticket codes unique only within their event', function () {
    $eventId = DB::table('events')->insertGetId(validCatalogueEvent());
    DB::table('ticket_types')->insert(validCatalogueTicket($eventId));
    assertCatalogueConstraint(fn () => DB::table('ticket_types')->insert(validCatalogueTicket($eventId)), '23505', 'ticket_types_event_id_code_unique');
    $otherId = DB::table('events')->insertGetId([...validCatalogueEvent(), 'slug' => 'other-reading']);
    DB::table('ticket_types')->insert(validCatalogueTicket($otherId));
    DB::table('ticket_types')->insert([...validCatalogueTicket($eventId), 'code' => 'supporter-ga']);
    $this->assertDatabaseCount('ticket_types', 3);
});

test('rejects invalid ticket values at the database boundary', function (string $column, mixed $value, string $state, ?string $constraint) {
    $eventId = DB::table('events')->insertGetId(validCatalogueEvent());
    assertCatalogueConstraint(fn () => DB::table('ticket_types')->insert([...validCatalogueTicket($eventId), $column => $value]), $state, $constraint);
    $this->assertDatabaseCount('ticket_types', 0);
})->with([
    'empty code' => ['code', '', '23514', 'ticket_types_code_format_check'],
    'uppercase code' => ['code', 'GA', '23514', 'ticket_types_code_format_check'],
    'code spaces' => ['code', 'general admission', '23514', 'ticket_types_code_format_check'],
    'blank name' => ['name', '   ', '23514', 'ticket_types_name_check'],
    'negative order' => ['display_order', -1, '23514', 'ticket_types_display_order_check'],
    'zero capacity' => ['capacity', 0, '23514', 'ticket_types_capacity_check'],
    'negative capacity' => ['capacity', -1, '23514', 'ticket_types_capacity_check'],
    'overflow capacity' => ['capacity', 2147483648, '22003', null],
    'zero price' => ['price_minor', 0, '23514', 'ticket_types_price_minor_check'],
    'negative price' => ['price_minor', -1, '23514', 'ticket_types_price_minor_check'],
    'overflow price' => ['price_minor', 2147483648, '22003', null],
    'euro' => ['currency', 'EUR', '23514', 'ticket_types_currency_check'],
    'dollar' => ['currency', 'USD', '23514', 'ticket_types_currency_check'],
    'lowercase currency' => ['currency', 'gbp', '23514', 'ticket_types_currency_check'],
]);

test('preserves capacity after an invalid update', function (int $capacity) {
    $eventId = DB::table('events')->insertGetId(validCatalogueEvent());
    DB::table('ticket_types')->insert(validCatalogueTicket($eventId));
    assertCatalogueConstraint(fn () => DB::table('ticket_types')->where('event_id', $eventId)->update(['capacity' => $capacity]), '23514', 'ticket_types_capacity_check');
    $this->assertDatabaseHas('ticket_types', ['event_id' => $eventId, 'capacity' => 20]);
})->with(['zero' => 0, 'negative' => -1]);

test('accepts positive integer boundaries and tied display positions', function (int $value) {
    $eventId = DB::table('events')->insertGetId(validCatalogueEvent());
    DB::table('ticket_types')->insert([...validCatalogueTicket($eventId), 'capacity' => $value, 'price_minor' => $value]);
    DB::table('ticket_types')->insert([...validCatalogueTicket($eventId), 'code' => 'supporter-ga', 'display_order' => 0]);
    $this->assertDatabaseHas('ticket_types', ['capacity' => $value, 'price_minor' => $value, 'display_order' => 0]);
    $this->assertDatabaseCount('ticket_types', 2);
})->with(['minimum' => 1, 'pence' => 1250, 'maximum' => 2147483647]);
