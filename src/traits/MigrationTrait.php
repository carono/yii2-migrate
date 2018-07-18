<?php


namespace carono\yii2migrate\traits;

use carono\yii2migrate\ForeignKeyColumn;
use carono\yii2migrate\IndexColumn;
use carono\yii2migrate\PivotColumn;
use yii\db\ColumnSchema;
use yii\db\ColumnSchemaBuilder;
use yii\db\Migration;
use yii\db\Schema;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;

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
     * @param string $name
     * @param string $table
     * @param array|string $columns
     * @param bool $unique
     */
    public function createIndex($name, $table, $columns, $unique = false)
    {
        $suffix = $unique ? 'unq' : 'idx';
        if (is_null($name)) {
            $name = self::formIndexName($table, $columns, $suffix);
            $name = $this->expandTablePrefix($name);
        }
        $name = self::truncateName($name, 64, '_' . $suffix);
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
        return self::setTablePrefix($name, $this->db->tablePrefix);
    }

    /**
     * @param $name
     * @param $prefix
     * @return mixed
     * @internal param $prefix
     */
    public static function setTablePrefix($name, $prefix)
    {
        return preg_replace('#{{%([\w\-_]+)}}#', $prefix . '$1', $name);
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
        foreach ($columns as $column => &$type) {
            if ($column === self::$tableOptions && is_string($type)) {
                $options = $type;
                unset($columns[$column]);
                continue;
            }
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
                $type->setName($column)->sourceTable($table);
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
        foreach ($fks as $fk) {
            $fk->apply();
        }
        if (count($pks) > 1) {
            $this->addPrimaryKey(null, $table, $pks);
        }
        foreach ($pvs as $pv) {
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
        if (is_null($name)) {
            $name = self::formFkName($table, $columns, $refTable, $refColumns);
            $name = $this->expandTablePrefix($name);
        }
        $name = self::truncateName($name, 64, '_fk');
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
        } else {
            parent::addColumn($table, $column, $type);
        }
    }

    /**
     * @inheritdoc
     */
    public function addPrimaryKey($name, $table, $columns)
    {
        if (is_null($name)) {
            $name = self::formIndexName($table, $columns, 'pk');
            $name = $this->expandTablePrefix($name);
        }
        $name = self::truncateName($name, 64, '_pk');
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
                $column[2]->setName($column[1])->sourceTable($column[0]);
            }
            if ($revert) {
                if ($column[2] instanceof PivotColumn) {
                    $column[2]->remove();
                    continue;
                }
                $this->dropColumn($column[0], $column[1], $column[2]);
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
    public function dropColumn($table, $column, $type = null)
    {
        if ($type instanceof ForeignKeyColumn) {
            $type->sourceTable($table);
            $type->sourceColumn($column);
            $type->remove();
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

            if (!$data instanceof IndexColumn) {
                $unq = isset($data[2]) && $data[2];
                $columns = is_array($data[1]) ? $data[1] : explode(',', $data[1]);
                $index = $this->index($columns, $unq)->table($data[0]);
            } else {
                $index = $data;
            }

            if ($revert) {
                $index->remove();
            } else {
                $index->apply();
            }
        }
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
                        $type->setName($column)->sourceTable($table);
                        $type->remove();
                    }
                }
                $this->dropTable($table);
            } else {
                $tableOptions = ArrayHelper::remove($columns, 'tableOptions', $tableOptions);
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
    public static function formFkName($table, $columns, $refTable, $refColumns)
    {
        if (is_array($columns)) {
            $column = implode(',', $columns);
        } else {
            $column = $columns;
        }
        if (is_array($refColumns)) {
            $refColumn = implode(',', $refColumns);
        } else {
            $refColumn = $refColumns;
        }
        $table = count($t = explode('.', $table)) > 1 ? $t[1] : $t[0];
        $refTable = count($t = explode('.', $refTable)) > 1 ? $t[1] : $t[0];
        return "{$table}[{$column}]_{$refTable}[{$refColumn}]_fk";
    }

    /**
     * @param $table
     * @param $columns
     * @param string $suffix
     * @return string
     */
    public static function formPkIndexName($table, $columns, $suffix = 'pk')
    {
        return self::formIndexName($table, $columns, $suffix);
    }

    /**
     * @param $table
     * @param $columns
     * @param string $suffix
     * @return string
     */
    public static function formIndexName($table, $columns, $suffix = 'idx')
    {
        $table = self::removeSchema($table);
        $column = implode(':', array_map('trim', (array)$columns));
        return "{$table}:{$column}_$suffix";
    }

    /**
     * @param $table
     * @param $rows
     * @param int $idStart
     * @param string $updateSeq
     * @deprecated
     */
    public function insertTo($table, $rows, $idStart = 1, $updateSeq = 'id')
    {
        $c = $idStart;
        foreach ($rows as $row) {
            if (!isset($row['id']) && !is_null($idStart)) {
                $row += ['id' => $c++];
            }
            $this->insert($table, $row);
        }
        if ($updateSeq) {
            $c = (int)\Yii::$app->db->createCommand("SELECT count(*) FROM {{$table}}")->queryScalar() + 1;
            $this->execute("ALTER SEQUENCE {$table}_{$updateSeq}_seq RESTART WITH $c;");
        }
    }

    public static function removeSchema($str)
    {
        if (strpos($str, '.') !== false) {
            $arr = explode('.', $str);
            return $arr[1];
        }

        return $str;
    }

    /**
     * @param $table
     * @param $column
     * @return false|null|string
     * @throws \yii\db\Exception
     */
    protected function getForeignKey($table, $column)
    {
        $condition = [':t' => $this->expandTablePrefix($table), ':c' => $column];
        $sql = 'SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_NAME=:t AND COLUMN_NAME=:c';
        if ($this->db->driverName === 'mysql') {
            $sql .= ' AND CONSTRAINT_SCHEMA=DATABASE()';
        }
        return $this->db->createCommand($sql, $condition)->queryScalar();
    }

    /**
     * @TODO https://stackoverflow.com/questions/6777456/list-all-index-names-column-names-and-its-table-name-of-a-postgresql-database
     * @unstable
     *
     * @param $table
     * @param $column
     * @return false|null|string
     */
    protected function getIndexName($table, $column)
    {
        $condition = [':t' => $this->expandTablePrefix($table), ':c' => $column];

        if ($this->db->driverName === 'pgsql') {
            $sql = <<<SQL
SELECT
	i.relname
FROM
	pg_class T,
	pg_class i,
	pg_index ix,
	pg_attribute A
WHERE
	T .oid = ix.indrelid
AND i.oid = ix.indexrelid
AND A .attrelid = T .oid
AND A .attnum = ANY (ix.indkey)
AND T .relname = :t
AND A .attname = :c
SQL;
        } else {
            $sql = 'SELECT DISTINCT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_NAME=:t AND COLUMN_NAME=:c AND CONSTRAINT_SCHEMA=DATABASE()';
        }
        return $this->db->createCommand($sql, $condition)->queryScalar();
    }

    /**
     * @param $table
     * @param $column
     */
    public function dropIndexByColumn($table, $column)
    {
        if ($key = $this->getIndexName($table, $column)) {
            $this->dropIndex($key, $table);
        }
    }

    /**
     * @param $table
     * @param $column
     * @throws \yii\db\Exception
     */
    public function dropForeignKeyByColumn($table, $column)
    {
        if ($key = $this->getForeignKey($table, $column)) {
            $this->dropForeignKey($key, $table);
        }
    }

    /**
     * Принудительно обрезаем названия ключей, если они получаются больше чем $length, т.к. базы могут вылететь с ошибкой
     *
     * @see https://dev.mysql.com/doc/refman/5.7/en/identifiers.html
     *
     * @param $name
     * @param int $length
     * @param null $suffix
     * @return string
     */
    public static function truncateName($name, $length = 64, $suffix = null)
    {
        if (strlen($name) > $length) {
            if (StringHelper::endsWith($name, $suffix)) {
                $name = substr($name, 0, strlen($suffix) * -1);
            }
            return dechex(crc32($name)) . $suffix;
        }

        return $name;
    }
}