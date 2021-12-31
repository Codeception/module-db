<?php

declare(strict_types=1);

use Codeception\Lib\Driver\Db;
use Codeception\Lib\Driver\PostgreSql;
use Codeception\Test\Unit;

/**
 * @group appveyor
 * @group db
 */
final class PostgresTest extends Unit
{
    protected static array $config = [
        'dsn' => 'pgsql:host=localhost;dbname=codeception_test',
        'user' => 'postgres',
        'password' => null,
    ];

    protected static $sql;

    protected ?PostgreSql $postgres = null;

    public static function _setUpBeforeClass()
    {
        if (!function_exists('pg_connect')) {
            return;
        }
        self::$config['password'] = getenv('PGPASSWORD') ? getenv('PGPASSWORD') : null;
        $sql = file_get_contents(codecept_data_dir('dumps/postgres.sql'));
        $sql = preg_replace('#/\*(?:(?!\*/).)*\*/#s', '', $sql);
        self::$sql = explode("\n", $sql);
    }

    public function _setUp()
    {
        try {
            $this->postgres = Db::create(self::$config['dsn'], self::$config['user'], self::$config['password']);
        } catch (Exception $e) {
            $this->markTestSkipped("Coudn't establish connection to database: " . $e->getMessage());
        }
        $this->postgres->load(self::$sql);
    }

    public function _tearDown()
    {
        if ($this->postgres !== null) {
            $this->postgres->cleanup();
        }
    }

    public function testCleanupDatabase()
    {
        $this->assertNotEmpty(
            $this->postgres->getDbh()->query("SELECT * FROM pg_tables where schemaname = 'public'")->fetchAll()
        );
        $this->postgres->cleanup();
        $this->assertEmpty(
            $this->postgres->getDbh()->query("SELECT * FROM pg_tables where schemaname = 'public'")->fetchAll()
        );
    }

    public function testCleanupDatabaseDeletesTypes()
    {
        $customTypes = ['composite_type', 'enum_type', 'range_type', 'base_type'];
        foreach ($customTypes as $customType) {
            $this->assertNotEmpty(
                $this->postgres->getDbh()
                    ->query("SELECT 1 FROM pg_type WHERE typname = '" . $customType . "';")
                    ->fetchAll()
            );
        }
        $this->postgres->cleanup();
        foreach ($customTypes as $customType) {
            $this->assertEmpty(
                $this->postgres->getDbh()
                    ->query("SELECT 1 FROM pg_type WHERE typname = '" . $customType . "';")
                    ->fetchAll()
            );
        }
    }

    public function testLoadDump()
    {
        $res = $this->postgres->getDbh()->query("select * from users where name = 'davert'");
        $this->assertNotSame(false, $res);
        $this->assertGreaterThan(0, $res->rowCount());

        $res = $this->postgres->getDbh()->query("select * from groups where name = 'coders'");
        $this->assertNotSame(false, $res);
        $this->assertGreaterThan(0, $res->rowCount());

        $res = $this->postgres->getDbh()->query("select * from users where email = 'user2@example.org'");
        $this->assertNotSame(false, $res);
        $this->assertGreaterThan(0, $res->rowCount());

        $res = $this->postgres->getDbh()
            ->query("select * from anotherschema.users where email = 'schemauser@example.org'");
        $this->assertSame(1, $res->rowCount());
    }

    public function testSelectWithEmptyCriteria()
    {
        $emptyCriteria = [];
        $generatedSql = $this->postgres->select('test_column', 'test_table', $emptyCriteria);

        $this->assertStringNotContainsString('where', $generatedSql);
    }

    public function testGetSingleColumnPrimaryKey()
    {
        $this->assertSame(['id'], $this->postgres->getPrimaryKey('order'));
    }

    public function testGetCompositePrimaryKey()
    {
        $this->assertSame(['group_id', 'id'], $this->postgres->getPrimaryKey('composite_pk'));
    }

    public function testGetEmptyArrayIfTableHasNoPrimaryKey()
    {
        $this->assertSame([], $this->postgres->getPrimaryKey('no_pk'));
    }

    public function testLastInsertIdReturnsSequenceValueWhenNonStandardSequenceNameIsUsed()
    {
        $this->postgres->executeQuery('INSERT INTO seqnames(name) VALUES(?)',['test']);
        $this->assertSame('1', $this->postgres->lastInsertId('seqnames'));
    }

    /**
     * @issue https://github.com/Codeception/Codeception/issues/4059
     */
    public function testLoadDumpEndingWithoutDelimiter()
    {
        $newDriver = new PostgreSql(self::$config['dsn'], self::$config['user'], self::$config['password']);
        $newDriver->load(["INSERT INTO empty_table VALUES(1, 'test')"]);

        $res = $newDriver->getDbh()->query("select * from empty_table where field = 'test'");
        $this->assertNotSame(false, $res);
        $this->assertNotEmpty($res->fetchAll());
    }
}
