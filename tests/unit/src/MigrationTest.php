<?php

namespace src;

class MigrationTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    public function testApplyMigration()
    {
        require_once dirname(dirname(__DIR__)) . '/migrations/m180712_120503_init.php';
        $migration = new \m180712_120503_init();
        $migration->db = \Yii::$app->db;
        $migration->safeUp();
        $migration->safeDown();
    }
}