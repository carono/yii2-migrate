<?php

namespace src;

use carono\yii2migrate\helpers\SchemaHelper;
use carono\yii2migrate\IndexColumn;
use carono\yii2migrate\Migration;

class IndexColumnBaseTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    protected $_migrate;

    protected function _before()
    {
        $migrate = $this->getMigrate();
        $migrate->createTable('{{%test_user}}', [
            'id' => $migrate->primaryKey(),
            'name' => $migrate->string(),
            'login' => $migrate->string()
        ]);
    }

    protected function _after()
    {
        $this->getMigrate()->dropTable('{{%test_user}}');
    }

    /**
     * @param string $db
     * @return Migration
     */
    protected function getMigrate()
    {
        if ($this->_migrate) {
            return $this->_migrate;
        }
        return $this->_migrate = new \carono\yii2migrate\Migration(['db' => \Yii::$app->db]);
    }

    public function testRemove()
    {
        $columns = ['name', 'login'];
        $index = $this->getMigrate()->index()->table('{{%test_user}}')->columns($columns);
        $this->assertEmpty(SchemaHelper::findNonUniqueIndexes(\Yii::$app->db, '{{%test_user}}'));
        $index->apply();
        $this->assertNotEmpty(SchemaHelper::findNonUniqueIndexes(\Yii::$app->db, '{{%test_user}}'));
        $index->remove();
        $this->assertEmpty(SchemaHelper::findNonUniqueIndexes(\Yii::$app->db, '{{%test_user}}'));
    }

    public function testApplyNonUnique()
    {
        $columns = ['name', 'login'];
        $this->getMigrate()->index()->table('{{%test_user}}')->columns($columns)->apply();
        $index = current(SchemaHelper::findNonUniqueIndexes(\Yii::$app->db, '{{%test_user}}'));
        $this->assertSame('pfx_test_user:name:login_idx', $index->name);
        $this->assertSame($columns, $index->columnNames);
    }

    public function testApplyUnique()
    {
        $columns = ['name', 'login'];
        $this->getMigrate()->index()->table('{{%test_user}}')->columns($columns)->unique(true)->apply();
        $table = \Yii::$app->db->getTableSchema('{{%test_user}}');
        $index = [
            'pfx_test_user:name:login_unq' => $columns
        ];
        $this->assertSame($index, \Yii::$app->db->getSchema()->findUniqueIndexes($table));
    }
}