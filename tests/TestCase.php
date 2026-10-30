<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    public function createApplication(): Application
    {
        $app = parent::createApplication();
        $configuration = $app['config'];
        $connection = $configuration->get('database.connections.pgsql');
        $allowedEndpoints = [['127.0.0.1', '5433'], ['postgres-test', '5432']];

        if (getenv('CI') === 'true') {
            $allowedEndpoints[] = ['127.0.0.1', '5432'];
        }

        if (! $app->environment('testing')
            || $configuration->get('database.default') !== 'pgsql'
            || ! empty($connection['url'])
            || $connection['database'] !== 'laravel_test'
            || $connection['username'] !== 'laravel_test'
            || ! in_array([$connection['host'], (string) $connection['port']], $allowedEndpoints, true)) {
            throw new RuntimeException('Unsafe test database configuration: require the approved isolated PostgreSQL test connection.');
        }

        $app['db']->purge('pgsql');
        $identity = $app['db']->connection('pgsql')->selectOne('SELECT current_database() AS database, current_user AS role');

        if ($identity->database !== 'laravel_test' || $identity->role !== 'laravel_test') {
            throw new RuntimeException('Unsafe test database identity: require laravel_test database and role.');
        }

        return $app;
    }
}
