<?php

use \carono\yii2migrate\helpers\SchemaHelper;

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
 */
class UnitTester extends \Codeception\Actor
{
    use _generated\UnitTesterActions;

    /**
     * Define custom actions here
     */

    public function assertUniqueIndexByColumnsExist($table, $column)
    {
        $index = SchemaHelper::findUniqueIndexes(\Yii::$app->db, $table);
        $result = array_filter($index, function ($index) use ($column) {
            return $index->columnNames === (array)$column;
        });
        $this->assertNotEmpty($result);
    }


    public function assertTableExist($table)
    {
        $this->assertNotNull(\Yii::$app->db->getTableSchema($table));
    }

    public function assertTableNotExist($table)
    {
        $this->assertNull(\Yii::$app->db->getTableSchema($table));
    }

    public function assertForeignKeyExist($table, $column)
    {
        $foreignKey = SchemaHelper::findTableForeignKeys(\Yii::$app->db, $table);
        $result = array_filter($foreignKey, function ($index) use ($column) {
            return $index->columnNames === (array)$column;
        });
        $this->assertNotEmpty($result);
    }

    public function assertForeignKeyNotExist($table, $column)
    {
        $foreignKey = SchemaHelper::findTableForeignKeys(\Yii::$app->db, $table);
        $result = array_filter($foreignKey, function ($index) use ($column) {
            return $index->columnNames === (array)$column;
        });
        $this->assertEmpty($result);
    }

    public function assertColumnNotExist($table, $column)
    {
        $this->assertNull(\Yii::$app->db->getTableSchema($table)->getColumn($column));
    }

    public function assertColumnExist($table, $column)
    {
        $this->assertNotNull(\Yii::$app->db->getTableSchema($table)->getColumn($column));
    }

    public function assertIndexByColumnsNotExist($table, $column)
    {
        $index = SchemaHelper::findNonUniqueIndexes(\Yii::$app->db, $table);
        $result = array_filter($index, function ($index) use ($column) {
            return $index->columnNames === (array)$column;
        });
        $this->assertEmpty($result);
    }

    public function assertIndexByColumnsExist($table, $column)
    {
        $index = SchemaHelper::findNonUniqueIndexes(\Yii::$app->db, $table);
        $result = array_filter($index, function ($index) use ($column) {
            return $index->columnNames === (array)$column;
        });
        $this->assertNotEmpty($result);
    }

    public function assertUniqueIndexByColumnsNotExist($table, $column)
    {
        $index = SchemaHelper::findUniqueIndexes(\Yii::$app->db, $table);
        $result = array_filter($index, function ($index) use ($column) {
            return $index->columnNames === (array)$column;
        });
        $this->assertEmpty($result);
    }

    public function assertIndexByNameNotExist($table, $name)
    {
        $index = SchemaHelper::findNonUniqueIndexes(\Yii::$app->db, $table);
        $this->assertArrayNotHasKey($name, $index);
    }

    public function assertIndexByNameExist($table, $name)
    {
        $index = SchemaHelper::findNonUniqueIndexes(\Yii::$app->db, $table);
        $this->assertArrayHasKey($name, $index);
    }

    public function assertForeignKeyExistByName($table, $name)
    {
        $index = SchemaHelper::findTableForeignKeys(\Yii::$app->db, $table);
        $this->assertArrayHasKey($name, $index);
    }

    public function assertForeignKeyNotExistByName($table, $name)
    {
        $index = SchemaHelper::findTableForeignKeys(\Yii::$app->db, $table);
        $this->assertArrayNotHasKey($name, $index);
    }
}