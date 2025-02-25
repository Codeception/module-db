<?php

declare(strict_types=1);

use Codeception\Exception\ModuleException;
use Codeception\Lib\Driver\Db;
use Codeception\Lib\Driver\Sqlite;
use Codeception\Test\Unit;

final class SqliteTest extends Unit
{
    /**
     * @var array<string, string>
     */
    protected static array $config = [
        'dsn' => 'sqlite:tests/data/sqlite.db',
        'user' => 'root',
        'password' => ''
    ];

    protected static ?Sqlite $sqlite = null;

    protected static $sql;

    public static function _setUpBeforeClass()
    {
        $dumpFile = '/dumps/sqlite.sql';
        $sql = file_get_contents(\Codeception\Configuration::dataDir() . $dumpFile);
        $sql = preg_replace('#/\*(?:(?!\*/).)*\*/#s', "", $sql);
        self::$sql = explode("\n", $sql);
    }

    public function _setUp()
    {
        self::$sqlite = Db::create(self::$config['dsn'], self::$config['user'], self::$config['password']);
        self::$sqlite->load(self::$sql);
    }

    public function _tearDown()
    {
        if (isset(self::$sqlite)) {
            self::$sqlite->cleanup();
        }
    }

    public function testLoadDump()
    {
        $res = self::$sqlite->getDbh()->query("select * from users where name = 'davert'");
        $this->assertNotSame(false, $res);
        $this->assertNotEmpty($res->fetchAll());

        $res = self::$sqlite->getDbh()->query("select * from groups where name = 'coders'");
        $this->assertNotSame(false, $res);
        $this->assertNotEmpty($res->fetchAll());
    }

    public function testGetPrimaryKeyReturnsRowIdIfTableHasIt()
    {
        $this->assertSame(['_ROWID_'], self::$sqlite->getPrimaryKey('groups'));
    }

    public function testGetPrimaryKeyReturnsRowIdIfTableHasNoPrimaryKey()
    {
        $this->assertSame(['_ROWID_'], self::$sqlite->getPrimaryKey('no_pk'));
    }

    public function testGetSingleColumnPrimaryKeyWhenTableHasNoRowId()
    {
        $this->assertSame(['id'], self::$sqlite->getPrimaryKey('order'));
    }

    public function testGetCompositePrimaryKeyWhenTableHasNoRowId()
    {
        $this->assertSame(['group_id', 'id'], self::$sqlite->getPrimaryKey('composite_pk'));
    }

    public function testThrowsExceptionIfInMemoryDatabaseIsUsed()
    {
        $this->expectException(ModuleException::class);
        $this->expectExceptionMessage(':memory: database is not supported');

        Db::create('sqlite::memory:', '', '');
    }

    /**
     * @issue https://github.com/Codeception/Codeception/issues/4059
     */
    public function testLoadDumpEndingWithoutDelimiter()
    {
        $newDriver = new Sqlite(self::$config['dsn'], '', '');
        $newDriver->load(['INSERT INTO empty_table VALUES(1, "test")']);

        $res = $newDriver->getDbh()->query("select * from empty_table where field = 'test'");
        $this->assertNotSame(false, $res);
        $this->assertNotEmpty($res->fetchAll());
    }
}
