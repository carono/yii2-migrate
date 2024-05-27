<?php

namespace traits;

use carono\yii2migrate\ForeignKeyColumn;
use carono\yii2migrate\IndexColumn;
use carono\yii2migrate\Migration;
use carono\yii2migrate\PivotColumn;

class MigrationTraitTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    /**
     * @var \carono\yii2migrate\Migration
     */
    protected $migration;

    protected function _before()
    {
        $this->migration = new \carono\yii2migrate\Migration(['db' => \Yii::$app->db]);
    }

    protected function _after()
    {
    }

    /**
     * @param $name
     *
     * @return \ReflectionMethod
     */
    public function getProtectedMethod($name)
    {
        $class = new \ReflectionClass(Migration::class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }

    public function invokeArgs($name, $args)
    {
        return $this->getProtectedMethod($name)->invokeArgs($this->migration, $args);
    }

    public function testForeignKeyColumn()
    {
        $this->assertSame(ForeignKeyColumn::class, get_class($this->migration->foreignKey()));
    }

    public function testIndexColumn()
    {
        $this->assertSame(IndexColumn::class, get_class($this->migration->index()));
    }

    public function testPivotColumn()
    {
        $this->assertSame(PivotColumn::class, get_class($this->migration->pivot()));
    }

    public function testTableOptions()
    {
        $this->assertSame([], $this->migration->tableOptions());
    }

    public function testNewColumns()
    {
        $this->assertSame([], $this->migration->newColumns());
    }

    public function testNewTables()
    {
        $this->assertSame([], $this->migration->newTables());
    }

    public function testNewIndex()
    {
        $this->assertSame([], $this->migration->newIndex());
    }

    public function testExpandTablePrefix()
    {
        $this->assertSame('pfx_table', $this->migration->expandTablePrefix('{{%table}}'));
    }

    public function testGetTableOptionsFromArray()
    {
        $method = 'getTableOptionsFromArray';
        $array = [
            \Yii::$app->db->driverName => 'EXPECT TABLE OPTIONS',
            'other-driver'             => 'NON-EXPECT TABLE OPTIONS',
        ];
        $this->assertSame('EXPECT TABLE OPTIONS', $this->invokeArgs($method, [$array, '']));
    }

    public function testGetTableOptionsFromArrayDefault()
    {
        $method = 'getTableOptionsFromArray';
        $this->assertSame('EXPECT TABLE OPTIONS', $this->invokeArgs($method, ['', 'EXPECT TABLE OPTIONS']));
    }

    public function testGetTableOptionsFromArrayDefaultArray()
    {
        $method = 'getTableOptionsFromArray';
        $array = ['other-driver' => 'NON-EXPECT TABLE OPTIONS'];
        $default = [\Yii::$app->db->driverName => 'EXPECT TABLE OPTIONS'];
        $this->assertSame('EXPECT TABLE OPTIONS', $this->invokeArgs($method, [$array, $default]));
    }

    public function testGetTableOptionsFromArrayAsString()
    {
        $method = 'getTableOptionsFromArray';
        $array = 'EXPECT TABLE OPTIONS';
        $default = [\Yii::$app->db->driverName => 'NON EXPECT TABLE OPTIONS'];
        $this->assertSame('EXPECT TABLE OPTIONS', $this->invokeArgs($method, [$array, $default]));
    }

    public function testFormPkIndexName()
    {
        $this->assertSame('pfx_user:id_pk', Migration::formPkIndexName('pfx_user', ['id']));
        $this->assertSame('pfx_user:id_pk', Migration::formPkIndexName('{{%user}}', ['id'], '_pk', 'pfx_'));
    }

    public function testFormFkName()
    {
        $index = $this->migration->formFkName('{{%user}}', 'photo_id', '{{%photo}}', 'id');
        $this->assertSame('pfx_user[photo_id]_pfx_photo[id]_fk', $index);
    }
}
