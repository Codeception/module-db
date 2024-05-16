<?php

declare(strict_types=1);

final class PostgreSqlDbTest extends AbstractDbTest
{
    public function getPopulator(): string
    {
        $config = $this->getConfig();

        return sprintf('psql -h $host -d $dbname -U $user < %s', $config['dump']);
    }

    public function getConfig(): array
    {
        if (!function_exists('pg_connect')) {
            $this->markTestSkipped();
        }

        $host = getenv('PG_HOST') ?: 'localhost';
        $user = getenv('PG_USER') ?: 'postgres';
        $password = getenv('PG_PASSWORD') ?: null;
        $database = getenv('PG_DB') ?: 'codeception_test';
        $dsn = getenv('PG_DSN') ?: 'pgsql:host=' . $host . ';dbname=' . $database;

        return [
            'dsn' => $dsn,
            'user' => $user,
            'password' => $password,
            'dump' => 'tests/data/dumps/postgres.sql',
            'reconnect' => true,
            'cleanup' => true,
            'populate' => true
        ];
    }
}
