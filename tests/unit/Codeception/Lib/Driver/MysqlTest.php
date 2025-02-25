<?php

declare(strict_types=1);

use Codeception\Exception\ModuleException;
use Codeception\Lib\Driver\Db;
use Codeception\Lib\Driver\MySql;
use Codeception\Test\Unit;

final class MysqlTest extends Unit
{
    protected static array $config = [
        'dsn' => 'mysql:host=localhost;dbname=codeception_test',
        'user' => 'root',
        'password' => ''
    ];

    protected static $sql;

    protected ?MySql $mysql = null;

    public static function _setUpBeforeClass()
    {
        $host = getenv('MYSQL_HOST') ?: 'localhost';
        $user = getenv('MYSQL_USER') ?: 'root';
        $password = getenv('MYSQL_PASSWORD') ?: '';
        $database = getenv('MYSQL_DB') ?: 'codeception_test';
        $dsn = getenv('MYSQL_DSN') ?: 'mysql:host=' . $host . ';dbname=' . $database;

        self::$config['dsn'] = $dsn;
        self::$config['user'] = $user;
        self::$config['password'] = $password;

        $sql = file_get_contents(\Codeception\Configuration::dataDir() . '/dumps/mysql.sql');
        $sql = preg_replace('#/\*(?:(?!\*/).)*\*/#s', "", $sql);
        self::$sql = explode("\n", $sql);
        try {
            $mysql = Db::create(self::$config['dsn'], self::$config['user'], self::$config['password']);
            $mysql->cleanup();
        } catch (Exception $e) {
        }
    }

    public function _setUp()
    {
        try {
            $this->mysql = Db::create(self::$config['dsn'], self::$config['user'], self::$config['password']);
        } catch (Exception $e) {
            $this->markTestSkipped("Couldn't establish connection to database: " . $e->getMessage());
        }
        $this->mysql->load(self::$sql);
    }

    public function _tearDown()
    {
        if ($this->mysql !== null) {
            $this->mysql->cleanup();
        }
    }

    public function testCleanupDatabase()
    {
        $this->assertNotEmpty($this->mysql->getDbh()->query("SHOW TABLES")->fetchAll());
        $this->mysql->cleanup();
        $this->assertEmpty($this->mysql->getDbh()->query("SHOW TABLES")->fetchAll());
    }

    public function testLoadDump()
    {
        $res = $this->mysql->getDbh()->query("select * from users where name = 'davert'");
        $this->assertNotSame(false, $res);
        $this->assertGreaterThan(0, $res->rowCount());

        $res = $this->mysql->getDbh()->query("select * from groups where name = 'coders'");
        $this->assertNotSame(false, $res);
        $this->assertGreaterThan(0, $res->rowCount());
    }

    public function testGetSingleColumnPrimaryKey()
    {
        $this->assertSame(['id'], $this->mysql->getPrimaryKey('order'));
    }

    public function testGetCompositePrimaryKey()
    {
        $this->assertSame(['group_id', 'id'], $this->mysql->getPrimaryKey('composite_pk'));
    }

    public function testGetEmptyArrayIfTableHasNoPrimaryKey()
    {
        $this->assertSame([], $this->mysql->getPrimaryKey('no_pk'));
    }

    public function testSelectWithBooleanParam()
    {
        $res = $this->mysql->executeQuery("select `id` from `users` where `is_active` = ?", [false]);
        $this->assertSame(1, $res->rowCount());
    }

    public function testInsertIntoBitField()
    {
        $res = $this->mysql->executeQuery(
            "insert into `users`(`id`,`name`,`email`,`is_active`,`created_at`) values (?,?,?,?,?)",
            [5, 'insert.test', 'insert.test@mail.ua', false, '2012-02-01 21:17:47']
        );
        $this->assertSame(1, $res->rowCount());
    }

    /**
     * THis will fail if MariaDb is used
     */
    public function testLoadThrowsExceptionWhenDumpFileContainsSyntaxError()
    {
        $sql = "INSERT INTO `users` (`name`) VALS('')";
        $expectedMessage = 'You have an error in your SQL syntax; ' .
            'check the manual that corresponds to your MySQL server version for the right syntax to use near ' .
            "'VALS('')' at line 1\nSQL query being executed: \n" . $sql;
        $this->expectException(ModuleException::class);
        $this->expectExceptionMessage($expectedMessage);
        $this->mysql->load([$sql]);
    }
}
