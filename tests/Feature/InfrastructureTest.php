<?php

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Tests\Fixtures\FoundationProbeJob;

uses(DatabaseMigrations::class);

beforeEach(function () {
    config([
        'cache.default' => 'database',
        'queue.default' => 'database',
        'queue.connections.database.connection' => 'pgsql',
        'queue.failed.database' => 'pgsql',
    ]);
});

test('infrastructure uses isolated real PostgreSQL with no seeded account', function () {
    $identity = DB::selectOne("SELECT current_database() AS database, current_user AS role, current_setting('TimeZone') AS timezone");

    expect(DB::connection()->getDriverName())->toBe('pgsql');
    expect($identity)->toMatchObject(['database' => 'laravel_test', 'role' => 'laravel_test', 'timezone' => 'UTC']);

    foreach (['users', 'password_reset_tokens', 'sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs'] as $table) {
        expect(Schema::hasTable($table))->toBeTrue();
    }

    $this->seed();
    $this->assertDatabaseCount('users', 0);
});

test('separate PostgreSQL connections see only committed writes', function () {
    config(['database.connections.observer' => config('database.connections.pgsql')]);
    $writer = DB::connection('pgsql');
    $observer = DB::connection('observer');
    $key = 'visibility:'.Str::uuid();

    expect($writer->selectOne('SELECT pg_backend_pid() AS pid')->pid)
        ->not->toBe($observer->selectOne('SELECT pg_backend_pid() AS pid')->pid);

    try {
        $writer->beginTransaction();
        $writer->table('cache')->insert(['key' => $key, 'value' => 'committed', 'expiration' => 0]);
        expect($observer->table('cache')->where('key', $key)->exists())->toBeFalse();
        $writer->commit();
        expect($observer->table('cache')->where('key', $key)->value('value'))->toBe('committed');

        $writer->beginTransaction();
        $writer->table('cache')->where('key', $key)->update(['value' => 'rolled-back']);
        $writer->rollBack();
        expect($observer->table('cache')->where('key', $key)->value('value'))->toBe('committed');
    } finally {
        if ($writer->transactionLevel() > 0) {
            $writer->rollBack();
        }

        $writer->table('cache')->where('key', $key)->delete();
        DB::purge('observer');
    }
});

test('guest HTTP sessions persist without a user', function () {
    config(['session.driver' => 'database']);

    $this->get(route('home'))->assertOk();

    $this->assertDatabaseCount('sessions', 1);
    $this->assertDatabaseHas('sessions', ['user_id' => null]);
    $this->assertDatabaseCount('users', 0);
});

test('basic rate limiting uses PostgreSQL cache', function () {
    $key = 'limiter:'.Str::uuid();

    try {
        expect(RateLimiter::attempt($key, 1, fn (): string => 'allowed'))->toBe('allowed');
        expect(RateLimiter::attempt($key, 1, fn (): string => 'unexpected'))->toBeFalse();
        expect(DB::table('cache')->count())->toBeGreaterThan(0);
    } finally {
        RateLimiter::clear($key);
    }

    expect(RateLimiter::tooManyAttempts($key, 1))->toBeFalse();
});

/**
 * @return array<string, string|false>
 */
function foundationWorkerEnvironment(): array
{
    $connection = DB::connection('pgsql')->getConfig();

    return [
        'APP_ENV' => 'testing',
        'APP_KEY' => config('app.key'),
        'APP_CONFIG_CACHE' => false,
        'CI' => getenv('CI') ?: false,
        'DB_CONNECTION' => 'pgsql',
        'DB_URL' => '',
        'DB_HOST' => $connection['host'],
        'DB_PORT' => (string) $connection['port'],
        'DB_DATABASE' => $connection['database'],
        'DB_USERNAME' => $connection['username'],
        'DB_PASSWORD' => $connection['password'],
        'DB_QUEUE_CONNECTION' => 'pgsql',
        'QUEUE_CONNECTION' => 'database',
        'CACHE_STORE' => 'database',
        'SESSION_DRIVER' => 'array',
        'MAIL_MAILER' => 'array',
    ];
}

test('an independent worker consumes only the committed test queue', function (bool $shouldFail) {
    $marker = 'queue:'.Str::uuid();
    Cache::store('database')->put($marker.':attempts', 0, 60);
    Queue::connection('database')->push(new FoundationProbeJob($marker, $shouldFail));
    $this->assertDatabaseCount('jobs', 1);
    expect(Cache::store('database')->get($marker))->toBeNull();

    $worker = new Process([
        PHP_BINARY, 'artisan', 'queue:work', 'database', '--stop-when-empty',
        '--sleep=0', '--tries=5', '--timeout=10', '--backoff=0', '--no-interaction',
    ], base_path(), foundationWorkerEnvironment(), timeout: 30);
    $worker->mustRun();

    $this->assertDatabaseCount('jobs', 0);
    $this->assertDatabaseCount('failed_jobs', $shouldFail ? 1 : 0);
    expect(Cache::store('database')->get($marker.':attempts'))->toBe($shouldFail ? 5 : 1);

    if ($shouldFail) {
        expect(Cache::store('database')->get($marker))->toBeNull();
        $failure = DB::table('failed_jobs')->first();
        expect($failure->exception)->toContain('Intentional foundation probe failure.');
        expect($failure->uuid)->toBeUuid();
    } else {
        expect(Cache::store('database')->get($marker))->toBe('processed');
    }
})->with(['successful execution' => false, 'five failed attempts' => true]);

test('database connection and enqueue failures surface without sync fallback', function () {
    $unreachable = config('database.connections.pgsql');
    $unreachable['port'] = '1';
    config([
        'database.connections.unreachable' => $unreachable,
        'queue.connections.unreachable' => [
            ...config('queue.connections.database'),
            'connection' => 'unreachable',
        ],
    ]);
    $marker = 'unreachable:'.Str::uuid();

    try {
        expect(fn () => DB::connection('unreachable')->selectOne('SELECT 1'))->toThrow(QueryException::class);
        expect(fn () => Queue::connection('unreachable')->push(new FoundationProbeJob($marker)))->toThrow(QueryException::class);

        $this->assertDatabaseCount('jobs', 0);
        expect(Cache::store('database')->get($marker))->toBeNull();
        expect(DB::selectOne('SELECT current_database() AS database')->database)->toBe('laravel_test');
    } finally {
        DB::purge('unreachable');
    }
});

test('unsafe cached test configuration is rejected before migration', function (string $key, mixed $value) {
    $configuration = config()->all();
    data_set($configuration, $key, $value);
    $cachePath = tempnam(sys_get_temp_dir(), 'foundation-config-');
    file_put_contents($cachePath, '<?php return '.var_export($configuration, true).';');
    $migrationCount = DB::table('migrations')->count();

    try {
        $process = new Process([
            PHP_BINARY, '-r',
            'require "vendor/autoload.php"; (new class("probe") extends \\Tests\\TestCase {})->createApplication();',
        ], base_path(), [...foundationWorkerEnvironment(), 'APP_CONFIG_CACHE' => $cachePath], timeout: 10);
        $process->run();

        expect($process->getExitCode())->not->toBe(0);
        expect($process->getOutput().$process->getErrorOutput())->toContain('Unsafe test database configuration');
        expect(DB::table('migrations')->count())->toBe($migrationCount);
    } finally {
        unlink($cachePath);
    }
})->with([
    ['database.connections.pgsql.database', 'wrong_database'],
    ['database.connections.pgsql.username', 'wrong_role'],
    ['database.connections.pgsql.url', 'pgsql://invalid/unused'],
    ['database.connections.pgsql.host', 'postgres'],
    ['database.connections.pgsql.port', '1'],
    ['database.default', 'sqlite'],
    ['app.env', 'local'],
]);
