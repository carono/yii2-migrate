<?php


namespace carono\yii2migrate\traits;

use carono\yii2migrate\ForeignKeyColumn;
use carono\yii2migrate\helpers\SchemaHelper;
use carono\yii2migrate\IndexColumn;
use carono\yii2migrate\PivotColumn;
use yii\db\ColumnSchema;
use yii\db\ColumnSchemaBuilder;
use yii\db\Migration;
use yii\db\Schema;
use yii\helpers\ArrayHelper;

/**
 * Trait MigrationTrait
 *
 * @package carono\yii2migrate\traits
 * @mixin Migration
 */
trait MigrationTrait
{
    private static $tableOptions = '@tableOptions';

    /**
     * @param      $refTable
     * @param null $refColumn
     *
     * @param string $type
     * @param null $length
     * @return ForeignKeyColumn
     */
    public function foreignKey($refTable = null, $refColumn = null, $type = Schema::TYPE_INTEGER, $length = null)
    {
        return (new ForeignKeyColumn($type, $length))->refTable($refTable)->refColumn($refColumn)->setMigrate($this);
    }

    /**
     * @param array $columns
     * @param bool $isUnique
     * @return IndexColumn
     */
    public function index($columns = [], $isUnique = false)
    {
        return (new IndexColumn())->setMigrate($this)->columns($columns)->unique($isUnique);
    }

    /**
     * @param null $refTable
     * @param null $refColumn
     *
     * @return PivotColumn
     */
    public function pivot($refTable = null, $refColumn = null)
    {
        return (new PivotColumn())->refTable($refTable)->refColumn($refColumn)->setMigrate($this);
    }

    /**
     * @return array
     */
    public function tableOptions()
    {
        return [];
    }

    /**
     * @param string $name
     * @param string $table
     * @param array|string $columns
     * @param bool $unique
     */
    public function createIndex($name, $table, $columns, $unique = false)
    {
        $suffix = $unique ? '_unq' : '_idx';
        if ($name === null) {
            $name = self::formIndexName($table, $columns, $suffix, $this->db->tablePrefix);
            $name = $this->expandTablePrefix($name);
        }
        $name = SchemaHelper::truncateIndexName($name, 64, $suffix);
        parent::createIndex($name, $table, $columns, $unique);
    }

    /**
     * @param ColumnSchema $column
     * @return $this|ColumnSchemaBuilder
     * @throws \Exception
     */
    public function columnSchemaToBuilder(ColumnSchema $column)
    {
        $size = $column->size;
        $precision = $column->precision;
        $default = $column->defaultValue;
        $scale = $column->scale;
        if ($column->isPrimaryKey && $column->autoIncrement) {
            return $this->primaryKey();
        }
        switch ($column->type) {
            case 'string':
                $builder = $this->string($size);
                break;
            case 'integer':
                $builder = $this->integer($size);
                break;
            case 'datetime':
                $builder = $this->dateTime($precision);
                break;
            case 'text':
                $builder = $this->text();
                break;
            case 'smallint':
                if ($size === 1) {
                    $default = (boolean)$default;
                    $builder = $this->boolean();
                } else {
                    $builder = $this->smallInteger($size);
                }
                break;
            case 'binary':
                $builder = $this->binary()->defaultValue($default);
                break;
            case 'decimal':
                $builder = $this->decimal($precision, $scale);
                break;
            case 'double':
                $builder = $this->double($precision)->defaultValue($default);
                break;
            default:
                throw new \Exception("Column ($column->name) type '$column->type' not recognized");
        }
        $builder->defaultValue($default);
        if (!$column->allowNull) {
            $builder->notNull();
        }
        $builder->comment($column->comment);
        return $builder;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function expandTablePrefix($name)
    {
        return SchemaHelper::expandTablePrefix($name, $this->db->tablePrefix);
    }

    /**
     * @param string $table
     * @param array $columns
     * @param null $options
     * @throws \Exception
     */
    public function createTable($table, $columns, $options = null)
    {
        /**
         * @var PivotColumn[] $pvs
         * @var ForeignKeyColumn[] $fks
         */
        echo "    > create table $table ...";
        $time = microtime(true);
        $pvs = [];
        $fks = [];
        $pks = [];

        $options = $this->getTableOptionsFromArray(ArrayHelper::remove($columns, self::$tableOptions, []), $options);

        foreach ($columns as $column => &$type) {

            if ($type instanceof ColumnSchema) {
                $column = is_numeric($column) ? $type->name : $column;
                $type = $this->columnSchemaToBuilder($type);
            }
            if ((string)$type === (string)$this->primaryKey()) {
                $pks[] = $column;
            }
            if ($type instanceof ForeignKeyColumn) {
                $type->sourceTable($table)->sourceColumn($column);
                $fks[] = $type;
            }

            if ($type instanceof PivotColumn) {
                $type->setSuffix($column)->sourceTable($table);
                $pvs[] = $type;
                unset($columns[$column]);
            }
        }
        if (count($pks) > 1) {
            foreach ($columns as $column => &$type) {
                $type = $this->integer();
            }
        }
        $this->db->createCommand()->createTable($table, $columns, $options)->execute();
        foreach ($columns as $column => $type) {
            if ($type instanceof ColumnSchemaBuilder && $type->comment !== null) {
                $this->db->createCommand()->addCommentOnColumn($table, $column, $type->comment)->execute();
            }
        }
        if ($fks) {
            echo "\n";
        }
        foreach ($fks as $fk) {
            echo '  ';
            $fk->apply();
        }
        if ($fks) {
            echo "\n";
        }
        if (count($pks) > 1) {
            echo '  ';
            $this->addPrimaryKey(null, $table, $pks);
        }
        if ($pvs) {
            echo "\n";
        }
        foreach ($pvs as $pv) {
            echo '  ';
            $pv->apply();
        }
        echo ' done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";
    }

    /**
     * @param string $name
     * @param string $table
     * @param array|string $columns
     * @param string $refTable
     * @param array|string $refColumns
     * @param null $delete
     * @param null $update
     */
    public function addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete = null, $update = null)
    {
        if ($name === null) {
            $name = $this->formFkName($table, $columns, $refTable, $refColumns);
            $name = $this->expandTablePrefix($name);
        }
        $name = SchemaHelper::truncateIndexName($name, 64, '_fk');
        parent::addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete, $update);
    }

    /**
     * @inheritdoc
     */
    public function alterColumn($table, $column, $type)
    {
        if ($type instanceof ForeignKeyColumn) {
            $type->sourceTable($table);
            $type->sourceColumn($column);
            $type->apply();
        } else {
            parent::alterColumn($table, $column, $type);
        }
    }

    /**
     * @inheritdoc
     */
    public function addColumn($table, $column, $type)
    {
        if ($type instanceof ForeignKeyColumn) {
            parent::addColumn($table, $column, $type);
            $type->sourceTable($table);
            $type->sourceColumn($column);
            $type->apply();
        } elseif ($type instanceof PivotColumn) {
            $type->sourceTable($table);
            $type->setSuffix($column);
            $type->apply();
        } else {
            parent::addColumn($table, $column, $type);
        }
    }

    /**
     * @inheritdoc
     */
    public function addPrimaryKey($name, $table, $columns)
    {
        if ($name === null) {
            $name = self::formIndexName($table, $columns, '_pk', $this->db->tablePrefix);
            $name = $this->expandTablePrefix($name);
        }
        $name = SchemaHelper::truncateIndexName($name, 64, '_pk');
        parent::addPrimaryKey($name, $table, $columns);
    }

    /**
     * @return array
     */
    public function newColumns()
    {
        return [];
    }

    /**
     * @param array $array
     */
    public function downNewColumns($array = [])
    {
        $this->_applyNewColumns($array ?: $this->newColumns(), true);
    }

    /**
     * @param array $array
     */
    public function upNewColumns($array = [])
    {
        $this->_applyNewColumns($array ?: $this->newColumns(), false);
    }

    /**
     * @param array $columns
     * @param bool $revert
     */
    protected function _applyNewColumns($columns = [], $revert = false)
    {
        $columns = $revert ? array_reverse($columns) : $columns;

        $result = [];
        foreach ($columns as $key => $column) {
            if (is_numeric($key)) {
                $result[] = $column;
            } else {
                foreach ($column as $columnName => $value) {
                    $result[] = [$key, $columnName, $value];
                }
            }
        }
        foreach ($result as $column) {
            if ($column[2] instanceof PivotColumn) {
                $column[2]->setSuffix($column[1])->sourceTable($column[0]);
            }
            if ($revert) {
                if ($column[2] instanceof PivotColumn) {
                    $column[2]->remove();
                    continue;
                }
                $this->dropColumn($column[0], $column[1]);
            } else {
                if ($column[2] instanceof PivotColumn) {
                    $column[2]->apply();
                    continue;
                }
                $this->addColumn($column[0], $column[1], $column[2]);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function dropColumn($table, $column)
    {
        $foreignKeys = SchemaHelper::findTableForeignKeys($this->db, $table);
        foreach ($foreignKeys as $key => $foreignKey) {
            if ($foreignKey->columnNames === [$column]) {
                $this->dropForeignKey($key, $table);
            }
        }
        return parent::dropColumn($table, $column);
    }

    /**
     * @return array
     */
    public function newTables()
    {
        return [];
    }

    /**
     * @param array $array
     * @param null $tableOptions
     */
    public function upNewTables($array = [], $tableOptions = null)
    {
        $this->_applyNewTables($array ?: $this->newTables(), false, $tableOptions);
    }

    /**
     * @param array $array
     */
    public function upNewIndex($array = [])
    {
        $this->_applyNewIndex($array ?: $this->newIndex());
    }

    /**
     * @param array $array
     */
    public function downNewIndex($array = [])
    {
        $this->_applyNewIndex($array ?: $this->newIndex(), true);
    }

    /**
     * @return array
     */
    public function newIndex()
    {
        return [];
    }

    /**
     * @param array $array
     */
    public function downNewTables($array = [])
    {
        $this->_applyNewTables($array ?: $this->newTables(), true);
    }

    /**
     * @param $indexes
     * @param bool $revert
     */
    protected function _applyNewIndex($indexes, $revert = false)
    {
        /**
         * @var IndexColumn $index
         */
        $indexes = $revert ? array_reverse($indexes) : $indexes;
        foreach ($indexes as $key => $data) {
            if (!is_numeric($key)) {
                foreach ($data as $index) {
                    if ($index instanceof IndexColumn) {
                        $index->table($key);
                        if ($revert) {
                            $index->remove();
                        } else {
                            $index->apply();
                        }
                    }
                }
                continue;
            }

            // Old style
            $unq = isset($data[2]) && $data[2];
            $columns = is_array($data[1]) ? $data[1] : explode(',', $data[1]);
            $index = $this->index($columns, $unq)->table($data[0]);

            if ($revert) {
                $index->remove();
            } else {
                $index->apply();
            }
        }
    }

    /**
     * @param array|string $items
     * @param string|array $default
     * @return array|mixed|string
     */
    private function getTableOptionsFromArray($items, $default = '')
    {
        if (is_array($default)) {
            $default = ArrayHelper::getValue($default, $this->db->driverName, '');
        }

        if (!$default) {
            $default = ArrayHelper::getValue($this->tableOptions(), $this->db->driverName, '');
        }

        if (is_array($items)) {
            return ArrayHelper::getValue($items, $this->db->driverName, $default);
        }

        if ($items && is_string($items)) {
            return $items;
        }

        return $default;
    }

    /**
     * @param $tables
     * @param bool $revert
     * @param null $tableOptions
     */
    protected function _applyNewTables($tables, $revert = false, $tableOptions = null)
    {
        $tables = $revert ? array_reverse($tables) : $tables;
        foreach ($tables as $table => $columns) {
            if ($revert) {
                foreach ($columns as $column => $type) {
                    if ($type instanceof PivotColumn) {
                        $type->setSuffix($column)->sourceTable($table);
                        $type->remove();
                    }
                }
                $this->dropTable($table);
            } else {
                $this->createTable($table, $columns, $tableOptions);
            }
        }
    }

    /**
     * @param $table
     * @param $columns
     * @param $refTable
     * @param $refColumns
     * @return string
     */
    public function formFkName($table, $columns, $refTable, $refColumns)
    {
        return $this->foreignKey($refTable, $refColumns)->sourceTable($table)->sourceColumn($columns)->formIndexName();
    }

    /**
     * @param $table
     * @param $columns
     * @param string $suffix
     * @param string $tablePrefix
     * @return string
     */
    public static function formPkIndexName($table, $columns, $suffix = '_pk', $tablePrefix = '')
    {
        return self::formIndexName($table, $columns, $suffix, $tablePrefix);
    }

    /**
     * @param $table
     * @param $columns
     * @param string $suffix
     * @param string $tablePrefix
     * @return string
     */
    public static function formIndexName($table, $columns, $suffix = '_idx', $tablePrefix = '')
    {
        $table = SchemaHelper::expandTablePrefix($table, $tablePrefix);
        $table = SchemaHelper::removeSchema($table);
        $column = implode(':', array_map('trim', (array)$columns));
        return "{$table}:{$column}$suffix";
    }

    /**
     * @param $table
     * @param $column
     */
    public function dropIndexByColumn($table, $column)
    {
        /**
         * @var \yii\db\IndexConstraint $index
         */
        foreach (SchemaHelper::findNonUniqueIndexes($this->db, $this->expandTablePrefix($table)) as $index) {
            if ($index->columnNames === (array)$column) {
                $this->dropIndex($index->name, $table);
            }
        }
    }

    /**
     * @param $table
     * @param $column
     * @throws \yii\db\Exception
     */
    public function dropForeignKeyByColumn($table, $column)
    {
        foreach (SchemaHelper::findTableForeignKeys($this->db, $table) as $key => $index) {
            if ($index->columnNames === (array)$column) {
                $this->dropForeignKey($key, $table);
            }
        }
    }
}