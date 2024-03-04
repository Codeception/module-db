<?php

declare(strict_types=1);

use Codeception\Configuration;

require_once Configuration::testsDir() . 'unit/Codeception/Module/Db/AbstractDbTest.php';

/**
 * @group db
 */
final class MssqlSqlSrvDbTest extends AbstractDbTest
{
    public function getPopulator(): string
    {
        $config = $this->getConfig();

        return sprintf('/opt/mssql-tools/bin/sqlcmd -S $Server -U $user -P $password -d $Database -i %s', $config['dump']);
    }

    public function getConfig(): array
    {
        $host = getenv('MSSQL_HOST') ?: 'localhost';
        $user = getenv('MSSQL_USER') ?: 'sa';
        $password = getenv('MSSQL_PASSWORD') ?: '';
        $database = getenv('MSSQL_DB') ?: 'codeception_test';
        $dsn = getenv('MSSQL_DSN') ?: 'sqlsrv:Server=' . $host . ';Database=' . $database . ';Encrypt=no;TrustServerCertificate=yes';

        return [
            'dsn' => $dsn,
            'user' => $user,
            'password' => $password,
            'dump' => 'tests/data/dumps/mssql.sql',
            'reconnect' => true,
            'cleanup' => true,
            'populate' => true,
        ];
    }
}
