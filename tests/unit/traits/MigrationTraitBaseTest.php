<?php

namespace traits;

use carono\yii2migrate\helpers\SchemaHelper;
use carono\yii2migrate\Migration;

class MigrationTraitBaseTest extends \Codeception\Test\Unit
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
        $this->migration = $migrate = new Migration(['db' => \Yii::$app->db]);
        $migrate->createTable('{{%photo}}', [
            'id' => $migrate->integer(),
            'name' => $migrate->string(),
        ]);
        $migrate->createTable('{{%department}}', [
            'id' => $migrate->primaryKey(),
            'name' => $migrate->string(),
        ]);
        $migrate->createTable('{{%user}}', [
            'id' => $migrate->primaryKey(),
            'name' => $migrate->string(),
            'login' => $migrate->string(),
            'company_id' => $migrate->integer(),
            'department_id' => $migrate->foreignKey('{{%department}}')
        ]);
        $migrate->createTable('{{%company}}', [
            'id' => $migrate->primaryKey(),
            'name' => $migrate->string(),
        ]);
        $migrate->createTable('{{%pv_department}}', [
            'company_id' => $migrate->primaryKey(),
            'department_id' => $migrate->primaryKey(),
        ]);
    }

    protected function _after()
    {
        $migrate = $this->migration;
        $migrate->dropTable('{{%user}}');
        $migrate->dropTable('{{%company}}');
        $migrate->dropTable('{{%photo}}');
        $migrate->dropTable('{{%department}}');
        $migrate->dropTable('{{%pv_department}}');
    }

    /**
     * @param $name
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

    public function assertUniqueIndexByColumnsExist($table, $column)
    {
        $index = SchemaHelper::findUniqueIndexes(\Yii::$app->db, $table);
        $result = array_filter($index, function ($index) use ($column) {
            return $index->columnNames === (array)$column;
        });
        $this->assertNotEmpty($result);
    }

    /**
     * @param $params
     * @return Migration|object
     */
    public function createMigration($params)
    {
        return $this->make(Migration::class, ['db' => \Yii::$app->db] + $params);
    }

//testing
    public function testCreateIndexDefault()
    {
        $this->migration->createIndex('index-name', '{{%user}}', ['name', 'login']);
        $index = SchemaHelper::findIndexes(\Yii::$app->db, '{{%user}}');
        $this->assertArrayHasKey('index-name', $index);
    }

    public function testCreateIndexAutoName()
    {
        $this->migration->createIndex(null, '{{%user}}', ['name', 'login']);
        $index = SchemaHelper::findIndexes(\Yii::$app->db, '{{%user}}');
        $this->assertArrayHasKey('pfx_user:name:login_idx', $index);
    }

    public function testAddForeignKeyDefault()
    {
        $this->migration->addForeignKey('fk-name', '{{%user}}', 'company_id', '{{%company}}', 'id');
        $foreignKeys = \Yii::$app->db->getTableSchema('{{%user}}')->foreignKeys;
        $this->assertArrayHasKey('fk-name', $foreignKeys);
    }

    public function testAddForeignKeyAutoName()
    {
        $this->migration->addForeignKey(null, '{{%user}}', 'company_id', '{{%company}}', 'id');
        $foreignKeys = \Yii::$app->db->getTableSchema('{{%user}}')->foreignKeys;
        $this->assertArrayHasKey('pfx_user[company_id]_pfx_company[id]_fk', $foreignKeys);
    }

    public function testAlterColumnDefault()
    {
        $this->migration->alterColumn('{{%user}}', 'name', $this->migration->text());
        $column = \Yii::$app->db->getTableSchema('{{%user}}')->getColumn('name');
        $this->assertSame($column->type, 'text');
    }

    public function testAlterColumnForeignKey()
    {
        $this->migration->alterColumn('{{%user}}', 'company_id', $this->migration->foreignKey('{{%company}}'));
        $foreignKeys = \Yii::$app->db->getTableSchema('{{%user}}')->foreignKeys;
        $this->assertArrayHasKey('pfx_user[company_id]_pfx_company[id]_fk', $foreignKeys);
    }

    public function testAddColumnDefault()
    {
        $this->migration->addColumn('{{%user}}', 'description', $this->migration->text());
        $column = \Yii::$app->db->getTableSchema('{{%user}}')->getColumn('description');
        $this->assertSame($column->type, 'text');
    }

    public function testAddColumnForeignKey()
    {
        $this->migration->addColumn('{{%user}}', 'company2_id', $this->migration->foreignKey('{{%company}}'));
        $foreignKeys = \Yii::$app->db->getTableSchema('{{%user}}')->foreignKeys;
        $this->assertArrayHasKey('pfx_user[company2_id]_pfx_company[id]_fk', $foreignKeys);
    }

    public function testAddPrimaryKey()
    {
        $this->migration->addPrimaryKey('id_pk', '{{%photo}}', 'id');
        $primaryKey = \Yii::$app->db->getTableSchema('{{%photo}}')->primaryKey;
        $this->assertArraySubset(['id'], $primaryKey);
    }

    public function testAddPrimaryKeyAutoName()
    {
        $this->migration->addPrimaryKey(null, '{{%photo}}', 'id');
        $primaryKey = \Yii::$app->db->getTableSchema('{{%photo}}')->primaryKey;
        $this->assertArraySubset(['id'], $primaryKey);
    }

    public function testDropColumnDefault()
    {
        $this->migration->dropColumn('{{%photo}}', 'name');
        $columns = \Yii::$app->db->getTableSchema('{{%photo}}')->columnNames;
        $this->assertSame(['id'], $columns);
    }

    public function testDropColumnForeignKey()
    {
        $this->migration->dropColumn('{{%user}}', 'department_id');
        $foreignKeys = \Yii::$app->db->getTableSchema('{{%user}}')->foreignKeys;
        $this->assertArrayNotHasKey('pfx_user[department_id]_pfx_department[id]_fk', $foreignKeys);

        $columns = \Yii::$app->db->getTableSchema('{{%user}}')->getColumnNames();

        $this->assertArrayNotHasKey('department_id', array_combine($columns, $columns));
    }

    public function testDropIndexByColumn()
    {
        $this->migration->createIndex(null, '{{%user}}', ['name', 'login']);
        $this->migration->createIndex(null, '{{%user}}', ['login', 'name']);
        $this->migration->createIndex(null, '{{%user}}', ['company_id', 'name']);

        $index = SchemaHelper::findIndexes($this->migration->db, '{{%user}}');
        $this->assertArrayHasKey('pfx_user:name:login_idx', $index);
        $this->assertArrayHasKey('pfx_user:login:name_idx', $index);
        $this->assertArrayHasKey('pfx_user:company_id:name_idx', $index);

        $this->migration->dropIndexByColumn('{{%user}}', 'name');

        $index = SchemaHelper::findIndexes($this->migration->db, '{{%user}}');
        $this->assertArrayHasKey('pfx_user:name:login_idx', $index);
        $this->assertArrayHasKey('pfx_user:login:name_idx', $index);
        $this->assertArrayHasKey('pfx_user:company_id:name_idx', $index);

        $this->migration->dropIndexByColumn('{{%user}}', ['login', 'name']);

        $index = SchemaHelper::findIndexes($this->migration->db, '{{%user}}');
        $this->assertArrayHasKey('pfx_user:name:login_idx', $index);
        $this->assertArrayNotHasKey('pfx_user:login:name_idx', $index);
        $this->assertArrayHasKey('pfx_user:company_id:name_idx', $index);
    }

    public function testDropForeignKeyByColumn()
    {
        $this->migration->addForeignKey(null, '{{%user}}', 'company_id, department_id', '{{%pv_department}}', 'company_id, department_id');
        $foreignKey = SchemaHelper::findTableForeignKeys($this->migration->db, '{{%user}}');
        $this->assertArrayHasKey('pfx_user[department_id]_pfx_department[id]_fk', $foreignKey);
        $this->assertArrayHasKey('aa241947_fk', $foreignKey);

        $this->migration->dropForeignKeyByColumn('{{%user}}', 'department_id');

        $foreignKey = SchemaHelper::findTableForeignKeys($this->migration->db, '{{%user}}');

        $this->assertArrayNotHasKey('pfx_user[department_id]_pfx_department[id]_fk', $foreignKey);
        $this->assertArrayHasKey('aa241947_fk', $foreignKey);
    }

    public function testNewTableDefault()
    {
        /**
         * @var Migration $object
         */
        $newTables = [
            '{{%test_table1}}' => [
                'id' => $this->migration->primaryKey(),
                'name' => $this->migration->string()
            ],
        ];
        $object = $this->createMigration([
            'newTables' => function () use ($newTables) {
                return $newTables;
            }
        ]);
        $this->assertNull(\Yii::$app->db->getTableSchema('{{%test_table1}}'));
        $object->upNewTables();
        $this->assertNotNull(\Yii::$app->db->getTableSchema('{{%test_table1}}'));

        $object->downNewTables();
        $this->assertNull(\Yii::$app->db->getTableSchema('{{%test_table1}}'));
    }

    public function testNewTableForeignKey()
    {
        $newTables = [
            '{{%test_table1}}' => [
                'id' => $this->migration->primaryKey(),
                'name' => $this->migration->string()
            ],
            '{{%test_table2}}' => [
                'id' => $this->migration->primaryKey(),
                'table1_id' => $this->migration->foreignKey('{{%test_table1}}')
            ],
        ];
        $object = $this->createMigration([
            'newTables' => function () use ($newTables) {
                return $newTables;
            }
        ]);
        $this->assertTableNotExist('{{%test_table1}}');
        $this->assertTableNotExist('{{%test_table2}}');

        $object->upNewTables();
        $this->assertTableExist('{{%test_table1}}');
        $this->assertTableExist('{{%test_table2}}');

        $this->assertForeignKeyExist('{{%test_table2}}', 'table1_id');

        $object->downNewTables();
    }

    public function testNewTableWithOptionsInnoDb()
    {
        if (\Yii::$app->db->driverName !== 'mysql') {
            $this->markTestSkipped('Only for mysql');
            return;
        }
        $newTables = [
            '{{%test_table1}}' => [
                'id' => $this->migration->primaryKey(),
                'name' => $this->migration->string(),
                '@tableOptions' => 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB'
            ],
        ];
        $object = $this->createMigration([
            'newTables' => function () use ($newTables) {
                return $newTables;
            }
        ]);
        $this->assertTableNotExist('{{%test_table1}}');

        $object->upNewTables();
        $this->assertTableExist('{{%test_table1}}');

        $sql = "SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pfx_test_table1'";
        $this->assertSame('InnoDB', \Yii::$app->db->createCommand($sql)->queryScalar());

        $object->downNewTables();
    }

    public function testNewTableWithOptionsMyISAM()
    {
        if (\Yii::$app->db->driverName !== 'mysql') {
            $this->markTestSkipped('Only for mysql');
            return;
        }
        $newTables = [
            '{{%test_table1}}' => [
                'id' => $this->migration->primaryKey(),
                'name' => $this->migration->string(),
                '@tableOptions' => 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=MyISAM'
            ],
        ];
        $object = $this->createMigration([
            'newTables' => function () use ($newTables) {
                return $newTables;
            }
        ]);
        $this->assertTableNotExist('{{%test_table1}}');

        $object->upNewTables();
        $this->assertTableExist('{{%test_table1}}');

        $sql = "SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pfx_test_table1'";
        $this->assertSame('MyISAM', \Yii::$app->db->createCommand($sql)->queryScalar());

        $object->downNewTables();
    }

    public function testNewTablePivot()
    {
        $newTables = [
            '{{%test_table1}}' => [
                'id' => $this->migration->primaryKey(),
                'name' => $this->migration->string()
            ],
            '{{%test_table2}}' => [
                'id' => $this->migration->primaryKey(),
                'pivots' => $this->migration->pivot('{{%test_table1}}')->tableName('{{%pivots}}')
            ],
        ];
        $object = $this->createMigration([
            'newTables' => function () use ($newTables) {
                return $newTables;
            }
        ]);
        $this->assertTableNotExist('{{%test_table1}}');
        $this->assertTableNotExist('{{%test_table2}}');
        $this->assertTableNotExist('{{%pivots}}');

        $object->upNewTables();

        $this->assertTableExist('{{%test_table1}}');
        $this->assertTableExist('{{%test_table2}}');
        $this->assertTableExist('{{%pivots}}');

        $object->downNewTables();
    }

    public function testNewColumns()
    {
        $newColumns = [
            '{{%user}}' => [
                'surname' => $this->migration->string()
            ],
            '{{%company}}' => [
                'address' => $this->migration->string(),
            ],
        ];
        $object = $this->createMigration([
            'newColumns' => function () use ($newColumns) {
                return $newColumns;
            }
        ]);
        $this->assertColumnNotExist('{{%user}}', 'surname');
        $this->assertColumnNotExist('{{%company}}', 'address');

        $object->upNewColumns();

        $this->assertColumnExist('{{%user}}', 'surname');
        $this->assertColumnExist('{{%company}}', 'address');

        $object->downNewColumns();
    }

    public function testNewColumnsForeignKey()
    {
        $newColumns = [
            '{{%user}}' => [
                'sub_company_id' => $this->migration->foreignKey('{{%company}}')
            ],
        ];
        $object = $this->createMigration([
            'newColumns' => function () use ($newColumns) {
                return $newColumns;
            }
        ]);
        $this->assertColumnNotExist('{{%user}}', 'sub_company_id');
        $this->assertForeignKeyNotExist('{{%user}}', 'sub_company_id');
        $object->upNewColumns();

        $this->assertColumnExist('{{%user}}', 'sub_company_id');
        $this->assertForeignKeyExist('{{%user}}', 'sub_company_id');

        $object->downNewColumns();
    }

    public function testNewColumnsPivot()
    {
        $newColumns = [
            '{{%user}}' => [
                'pivots' => $this->migration->pivot('{{%company}}')->tableName('{{%pivots}}')
            ],
        ];
        $object = $this->createMigration([
            'newColumns' => function () use ($newColumns) {
                return $newColumns;
            }
        ]);
        $this->assertTableNotExist('{{%pivots}}');
        $object->upNewColumns();
        $this->assertTableExist('{{%pivots}}');
        $object->downNewColumns();
        $this->assertTableNotExist('{{%pivots}}');
    }

    public function testNewIndex()
    {
        $newIndex = [
            '{{%user}}' => [
                $this->migration->index(['name']),
                $this->migration->index(['login'])->unique(true)
            ],
        ];
        $object = $this->createMigration([
            'newIndex' => function () use ($newIndex) {
                return $newIndex;
            }
        ]);
        $this->assertIndexByColumnsNotExist('{{%user}}', ['name']);
        $this->assertUniqueIndexByColumnsNotExist('{{%user}}', ['login']);
        $object->upNewIndex();
        $this->assertIndexByColumnsExist('{{%user}}', ['name']);
        $this->assertUniqueIndexByColumnsExist('{{%user}}', ['login']);
        $object->downNewIndex();
        $this->assertIndexByColumnsNotExist('{{%user}}', ['name']);
        $this->assertUniqueIndexByColumnsNotExist('{{%user}}', ['login']);
    }

    public function testNewIndexOldStyle()
    {
        $newIndex = [
            ['{{%user}}', 'name'],
            ['{{%user}}', 'login', true],
        ];
        $object = $this->createMigration([
            'newIndex' => function () use ($newIndex) {
                return $newIndex;
            }
        ]);
        $this->assertIndexByColumnsNotExist('{{%user}}', ['name']);
        $this->assertUniqueIndexByColumnsNotExist('{{%user}}', ['login']);
        $object->upNewIndex();
        $this->assertIndexByColumnsExist('{{%user}}', ['name']);
        $this->assertUniqueIndexByColumnsExist('{{%user}}', ['login']);
        $object->downNewIndex();
        $this->assertIndexByColumnsNotExist('{{%user}}', ['name']);
        $this->assertUniqueIndexByColumnsNotExist('{{%user}}', ['login']);
    }
}