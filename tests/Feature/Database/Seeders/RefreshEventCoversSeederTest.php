<?php

use App\Models\Event;
use Carbon\CarbonImmutable;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\RefreshEventCoversSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

uses(DatabaseMigrations::class);

afterEach(function () {
    $this->travelBack();
});

/** @return array<string, string> */
function legacyCoverReferences(): array
{
    return [
        'paper-trails' => 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA4MDAgNTAwIj48cmVjdCB3aWR0aD0iODAwIiBoZWlnaHQ9IjUwMCIgZmlsbD0iIzI4NGQ0MiIvPjxjaXJjbGUgY3g9IjY0MCIgY3k9IjkwIiByPSIxMzAiIGZpbGw9IiNjNTgyNDUiLz48cGF0aCBkPSJNMTYwIDE3MHExMjAtNDAgMjQwIDIwIDEyMC02MCAyNDAtMjB2MjEwcS0xMjAtNDAtMjQwIDIwLTEyMC02MC0yNDAtMjB6IiBmaWxsPSIjZjJkZmI4Ii8+PHBhdGggZD0iTTQwMCAxOTB2MjEwTTIwMCAyMjBsMTQwIDI1bS0xNDAgMzAgMTQwIDI1bTEyMC01NSAxNDAtMjVtLTE0MCA4MCAxNDAtMjUiIGZpbGw9Im5vbmUiIHN0cm9rZT0iIzI4NGQ0MiIgc3Ryb2tlLXdpZHRoPSI4Ii8+PC9zdmc+',
        'city-in-the-margins' => 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA4MDAgNTAwIj48cmVjdCB3aWR0aD0iODAwIiBoZWlnaHQ9IjUwMCIgZmlsbD0iIzM1M2M2NCIvPjxwYXRoIGQ9Ik04MCA0MDBWMjEwaDgwVjEzMGgxMDB2MjcwbTUwIDBWOTBoMTMwdjMxMG01MCAwVjE4MGg4MFYxMTBoMTAwdjI5MCIgZmlsbD0iI2IyYTFjYiIvPjxwYXRoIGQ9Ik0xNzAgMzIwcTExMC0zNSAyMzAgMjAgMTIwLTU1IDIzMC0yMHYxMTBxLTExMC0zNS0yMzAgMjAtMTIwLTU1LTIzMC0yMHoiIGZpbGw9IiNmM2RiYTYiLz48cGF0aCBkPSJNNDAwIDM0MHYxMTAiIHN0cm9rZT0iIzM1M2M2NCIgc3Ryb2tlLXdpZHRoPSI4Ii8+PC9zdmc+',
        'lantern-pages' => 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA4MDAgNTAwIj48cmVjdCB3aWR0aD0iODAwIiBoZWlnaHQ9IjUwMCIgZmlsbD0iIzE3MmQ0MCIvPjxjaXJjbGUgY3g9IjQwMCIgY3k9IjIyMCIgcj0iMTcwIiBmaWxsPSIjNjU1MDM1Ii8+PHJlY3QgeD0iMzIwIiB5PSIxMzAiIHdpZHRoPSIxNjAiIGhlaWdodD0iMjIwIiByeD0iMjUiIGZpbGw9IiNmMmM1NmYiLz48cGF0aCBkPSJNMzUwIDEzMHYtMzVxNTAtNTUgMTAwIDB2MzVtLTUwIDMwdjE2ME0yMTAgMzkwcTkwLTMwIDE5MCAyMCAxMDAtNTAgMTkwLTIwIiBmaWxsPSJub25lIiBzdHJva2U9IiNmNmU2YmYiIHN0cm9rZS13aWR0aD0iMTIiLz48L3N2Zz4=',
        'new-year-new-chapter' => 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA4MDAgNTAwIj48cmVjdCB3aWR0aD0iODAwIiBoZWlnaHQ9IjUwMCIgZmlsbD0iI2Q5ZThlMCIvPjxjaXJjbGUgY3g9IjYxMCIgY3k9IjEyMCIgcj0iNjUiIGZpbGw9IiNkYWE0NWIiLz48cGF0aCBkPSJNMCAzNDBxMjAwLTEwMCA0MDAgMCAyMDAgMTAwIDQwMCAwdjE2MEgweiIgZmlsbD0iIzZkOTg5MCIvPjxwYXRoIGQ9Ik0xODAgMTgwcTExMC00MCAyMjAgMjAgMTEwLTYwIDIyMC0yMHYyMDBxLTExMC00MC0yMjAgMjAtMTEwLTYwLTIyMC0yMHoiIGZpbGw9IiNmZmY1ZGYiLz48cGF0aCBkPSJNNDAwIDIwMHYyMDBtLTE1MC0xNDBoOTBtMTIwIDBoOTAiIGZpbGw9Im5vbmUiIHN0cm9rZT0iIzMxNTc1YiIgc3Ryb2tlLXdpZHRoPSI4Ii8+PC9zdmc+',
    ];
}

/** @return array<string, string> */
function photoCoverReferences(): array
{
    return [
        'paper-trails' => '/images/events/paper-trails-photo-v1.png',
        'city-in-the-margins' => '/images/events/city-in-the-margins-photo-v1.png',
        'lantern-pages' => '/images/events/lantern-pages-photo-v1.png',
        'new-year-new-chapter' => '/images/events/new-year-new-chapter-photo-v1.png',
    ];
}

function restoreLegacyCoverReferences(): void
{
    foreach (legacyCoverReferences() as $slug => $cover) {
        DB::table('events')->where('slug', $slug)->update(['cover_image_url' => $cover]);
    }
}

/** @return array{events: array, tickets: array} */
function coverRefreshSnapshot(): array
{
    return [
        'events' => DB::table('events')->orderBy('id')->get()->map(fn (object $row): array => (array) $row)->all(),
        'tickets' => DB::table('ticket_types')->orderBy('id')->get()->map(fn (object $row): array => (array) $row)->all(),
    ];
}

test('upgrades only exact legacy covers while preserving all other values and timestamps', function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-30T12:00:00Z'));
    $this->seed(DatabaseSeeder::class);
    restoreLegacyCoverReferences();
    Event::query()->where('slug', 'paper-trails')->firstOrFail()->update(['title' => 'Edited title', 'venue_name' => 'Edited venue']);
    DB::table('ticket_types')->update(['price_minor' => 1750, 'capacity' => 99]);
    $expected = coverRefreshSnapshot();
    foreach ($expected['events'] as &$row) {
        $row['cover_image_url'] = photoCoverReferences()[$row['slug']];
    }
    unset($row);
    $this->travelTo(CarbonImmutable::parse('2027-02-01T12:00:00Z'));

    $this->artisan('db:seed', ['--class' => RefreshEventCoversSeeder::class, '--no-interaction' => true])
        ->expectsOutput('Event covers: 4 updated, 0 already-new, 0 custom/skipped, 0 missing.')
        ->assertSuccessful();

    expect(coverRefreshSnapshot())->toBe($expected);
});

test('ordinary seeding leaves untouched legacy covers unchanged', function () {
    $this->seed(DatabaseSeeder::class);
    restoreLegacyCoverReferences();
    $before = coverRefreshSnapshot();

    $this->seed(DatabaseSeeder::class);

    expect(coverRefreshSnapshot())->toBe($before);
});

test('replays the opt-in refresh and ordinary seed without changing the refreshed catalogue', function () {
    $this->seed(DatabaseSeeder::class);
    restoreLegacyCoverReferences();
    $this->seed(RefreshEventCoversSeeder::class);
    $before = coverRefreshSnapshot();
    $this->travelTo(CarbonImmutable::parse('2027-02-01T12:00:00Z'));

    $this->seed(RefreshEventCoversSeeder::class);
    $this->seed(DatabaseSeeder::class);

    expect(coverRefreshSnapshot())->toBe($before);
});

test('skips custom covers unknown slugs and missing events', function (string $customCover) {
    $this->seed(DatabaseSeeder::class);
    DB::table('events')->where('slug', 'city-in-the-margins')->update(['cover_image_url' => $customCover]);
    Event::factory()->create(['slug' => 'unrelated-reading', 'cover_image_url' => legacyCoverReferences()['city-in-the-margins']]);
    $missingEvent = Event::query()->where('slug', 'paper-trails')->firstOrFail();
    $missingEvent->ticketTypes()->delete();
    $missingEvent->delete();
    $before = coverRefreshSnapshot();

    $this->artisan('db:seed', ['--class' => RefreshEventCoversSeeder::class, '--no-interaction' => true])
        ->expectsOutput('Event covers: 0 updated, 2 already-new, 1 custom/skipped, 1 missing.')
        ->assertSuccessful();

    expect(coverRefreshSnapshot())->toBe($before);
})->with([
    'custom local cover' => '/images/edited-cover.png',
    'custom external cover' => 'https://example.org/edited-cover.png',
    'custom embedded svg' => 'data:image/svg+xml;base64,'.base64_encode('<svg xmlns="http://www.w3.org/2000/svg"/>'),
    'similar legacy reference' => legacyCoverReferences()['city-in-the-margins'].'custom',
]);

test('aborts before database writes when a required cover is missing or invalid', function (string $problem) {
    $this->seed(DatabaseSeeder::class);
    restoreLegacyCoverReferences();
    $before = coverRefreshSnapshot();
    $originalPublicPath = public_path();
    $temporaryPublicPath = tempnam(sys_get_temp_dir(), 'slice2b-covers-');
    if ($temporaryPublicPath === false) {
        throw new RuntimeException('Unable to create isolated asset fixture.');
    }
    unlink($temporaryPublicPath);
    File::ensureDirectoryExists($temporaryPublicPath.'/images/events');
    File::copyDirectory($originalPublicPath.'/images/events', $temporaryPublicPath.'/images/events');
    $target = $temporaryPublicPath.'/images/events/paper-trails-photo-v1.png';
    match ($problem) {
        'missing' => unlink($target),
        'invalid' => file_put_contents($target, 'not an image'),
        'dimensions' => file_put_contents($target, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVQIHWP4z8DwHwAFgAI/ScLbtAAAAABJRU5ErkJggg==')),
        'oversize' => file_put_contents($target, str_repeat("\0", 2097153), FILE_APPEND),
    };
    $this->app->usePublicPath($temporaryPublicPath);
    DB::enableQueryLog();
    DB::flushQueryLog();

    try {
        expect(fn () => $this->seed(RefreshEventCoversSeeder::class))
            ->toThrow(RuntimeException::class, 'Missing or invalid event cover: /images/events/paper-trails-photo-v1.png');
        $updates = array_filter(DB::getQueryLog(), fn (array $query): bool => str_starts_with($query['query'], 'update "events"'));
        expect($updates)->toBeEmpty();
        expect(coverRefreshSnapshot())->toBe($before);
    } finally {
        DB::disableQueryLog();
        $this->app->usePublicPath($originalPublicPath);
        File::deleteDirectory($temporaryPublicPath);
    }
})->with(['missing', 'invalid', 'dimensions', 'oversize']);

test('rolls back all cover updates on database failure and permits a safe retry', function () {
    $this->seed(DatabaseSeeder::class);
    restoreLegacyCoverReferences();
    $before = coverRefreshSnapshot();
    $updates = 0;
    $failed = false;
    DB::connection()->beforeExecuting(function (string $query) use (&$updates, &$failed): void {
        if (! $failed && str_starts_with($query, 'update "events"') && ++$updates === 2) {
            $failed = true;
            throw new RuntimeException('Injected cover update failure.');
        }
    });

    expect(fn () => $this->seed(RefreshEventCoversSeeder::class))
        ->toThrow(RuntimeException::class, 'Injected cover update failure.');
    expect($failed)->toBeTrue();
    expect(coverRefreshSnapshot())->toBe($before);

    $this->seed(RefreshEventCoversSeeder::class);

    expect(DB::table('events')->pluck('cover_image_url', 'slug')->all())->toEqual(photoCoverReferences());
    expect(DB::table('ticket_types')->orderBy('id')->get()->map(fn (object $row): array => (array) $row)->all())->toBe($before['tickets']);
});

test('preserves a concurrent cover edit made on a separate PostgreSQL connection before the guarded update', function () {
    $this->seed(DatabaseSeeder::class);
    restoreLegacyCoverReferences();
    config(['database.connections.cover_refresh_competitor' => DB::connection()->getConfig()]);
    $competitor = DB::connection('cover_refresh_competitor');
    expect($competitor->selectOne('SELECT pg_backend_pid() AS pid')->pid)
        ->not->toBe(DB::selectOne('SELECT pg_backend_pid() AS pid')->pid);
    $changed = false;
    DB::connection()->beforeExecuting(function (string $query) use ($competitor, &$changed): void {
        if (! $changed && str_starts_with($query, 'update "events"')) {
            $changed = true;
            $competitor->table('events')->where('slug', 'city-in-the-margins')->update([
                'cover_image_url' => '/images/concurrent-edit.png',
                'title' => 'Concurrent edited title',
            ]);
        }
    });

    try {
        $this->seed(RefreshEventCoversSeeder::class);

        expect($changed)->toBeTrue();
        $this->assertDatabaseHas('events', [
            'slug' => 'city-in-the-margins',
            'cover_image_url' => '/images/concurrent-edit.png',
            'title' => 'Concurrent edited title',
        ]);
        foreach (array_diff_key(photoCoverReferences(), ['city-in-the-margins' => true]) as $slug => $cover) {
            $this->assertDatabaseHas('events', ['slug' => $slug, 'cover_image_url' => $cover]);
        }
    } finally {
        DB::purge('cover_refresh_competitor');
    }
});
