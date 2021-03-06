<?php

namespace src;

use carono\yii2migrate\helpers\SchemaHelper;
use carono\yii2migrate\Migration;

class PivotColumnBaseTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    protected $_migrate;

    protected function _before()
    {
        $migrate = $this->getMigrate();
        $migrate->createTable('{{%test_user}}', ['id' => $migrate->primaryKey()]);
        $migrate->createTable('{{%test_photo}}', ['id' => $migrate->primaryKey()]);
        $this->getPivot()->apply();
    }

    protected function _after()
    {
        $migrate = $this->getMigrate();
        $this->getPivot()->remove();
        $migrate->dropTable('{{%test_user}}');
        $migrate->dropTable('{{%test_photo}}');
    }

    protected function getPivot()
    {
        return $this->getMigrate()
            ->pivot('{{%test_photo}}')
            ->sourceTable('{{%test_user}}')
            ->columns(['value' => 'string'])
            ->setSuffix('photos');
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

    // tests
    public function testApply()
    {
        $this->assertNotNull(\Yii::$app->db->getTableSchema('{{%pv_test_user_photos}}'));
    }

    public function testForeignKeys()
    {
        $schema = $this->getMigrate()->db->getTableSchema('{{%pv_test_user_photos}}');
        $this->assertArrayHasKey('pfx_pv_test_user_photos[test_photo_id]_pfx_test_photo[id]_fk', $schema->foreignKeys);
        $this->assertArrayHasKey('pfx_pv_test_user_photos[test_user_id]_pfx_test_user[id]_fk', $schema->foreignKeys);
    }

    public function testPrimaryKeys()
    {
        $expect = [
            'test_user_id',
            'test_photo_id'
        ];
        $primaryKeys = \Yii::$app->db->getTableSchema('{{%pv_test_user_photos}}')->primaryKey;
        $this->assertSame($expect, $primaryKeys);
    }

    public function testColumns()
    {
        $expect = [
            'test_user_id',
            'test_photo_id',
            'value'
        ];
        $columns = \Yii::$app->db->getTableSchema('{{%pv_test_user_photos}}')->columnNames;
        $this->assertSame($expect, $columns);
    }

    public function testColumnNames()
    {
        $pv = $this->getMigrate()->pivot('{{%test_photo}}')->sourceTable('{{%test_user}}')->setSuffix('photos2');
        $pv->refColumn('ref_id');
        $pv->sourceColumn('source_id');
        $pv->apply();

        $this->tester->assertColumnExist('{{%pv_test_user_photos2}}', 'ref_id');
        $this->tester->assertColumnExist('{{%pv_test_user_photos2}}', 'source_id');

        $pv->remove();
    }
}