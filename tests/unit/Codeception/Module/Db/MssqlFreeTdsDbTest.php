<?php

declare(strict_types=1);

namespace unit\Codeception\Module\Db;

use AbstractDbTest;
use Codeception\Configuration;

require_once Configuration::testsDir() . 'unit/Codeception/Module/Db/AbstractDbTest.php';

/**
 * @group db
 */
final class MssqlFreeTdsDbTest extends AbstractDbTest
{
    public function getPopulator(): string
    {
        return '/opt/mssql-tools/bin/sqlcmd -S localhost -U sa -P yourStrong(!)Password -d codeception_test -i tests/data/dumps/mssql.sql';
    }

    public function getConfig(): array
    {
        return [
            'dsn'       => 'dblib:host=localhost;dbname=codeception_test',
            'user'      => 'sa',
            'password'  => 'yourStrong(!)Password',
            'dump'      => 'tests/data/dumps/mssql.sql',
            'reconnect' => true,
            'cleanup'   => true,
            'populate'  => true,
        ];
    }
}
