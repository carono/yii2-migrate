<?php

namespace carono\yii2migrate\helpers;

use yii\db\Connection;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;

class SchemaHelper
{
    /**
     * @param Connection $db
     * @param $table
     * @return \yii\db\IndexConstraint[]
     */
    public static function findNonUniqueIndexes($db, $table)
    {
        $indexes = self::findIndexes($db, $table);
        $indexes = array_filter($indexes, function ($index) {
            return !$index->isUnique;
        });
        return ArrayHelper::index($indexes, 'name');
    }

    /**
     * @param Connection $db
     * @param $table
     * @return \yii\db\IndexConstraint[]
     */
    public static function findUniqueIndexes($db, $table)
    {
        $indexes = self::findIndexes($db, $table);
        $indexes = array_filter($indexes, function ($index) {
            return $index->isUnique;
        });
        return ArrayHelper::index($indexes, 'name');
    }

    /**
     * @param Connection $db
     * @param $table
     * @return \yii\db\IndexConstraint[]
     */
    public static function findIndexes($db, $table)
    {
        $reflectionMethod = new \ReflectionMethod(get_class($db->schema), 'loadTableIndexes');
        $reflectionMethod->setAccessible(true);
        $indexes = $reflectionMethod->invoke($db->schema, self::expandTablePrefix($table, $db->tablePrefix));
        return ArrayHelper::index($indexes, 'name');
    }


    /**
     * @param Connection $db
     * @param $table
     * @return \yii\db\ForeignKeyConstraint[]
     */
    public static function findTableForeignKeys($db, $table)
    {
        $reflectionMethod = new \ReflectionMethod(get_class($db->schema), 'loadTableForeignKeys');
        $reflectionMethod->setAccessible(true);
        return ArrayHelper::index($reflectionMethod->invoke($db->schema, self::expandTablePrefix($table, $db->tablePrefix)), 'name');
    }

    /**
     * @param $str
     * @return mixed
     */
    public static function removeSchema($str)
    {
        if (strpos($str, '.') !== false) {
            $arr = explode('.', $str);
            return $arr[1];
        }

        return $str;
    }

    /**
     * {{%table}} to pfx_table
     *
     * @param $name
     * @param $prefix
     * @return null|string|string[]
     */
    public static function expandTablePrefix($name, $prefix)
    {
        return preg_replace('/{{%([\w\-_]+)}}/', addcslashes($prefix, "\\") . '$1', $name);
    }

    /**
     * pfx_table to {{%table}}
     *
     * @param $name
     * @param $prefix
     * @return null|string|string[]
     */
    public static function collapseTablePrefix($name, $prefix)
    {
        $prefix = addcslashes($prefix, "\\/");
        return preg_replace("/^$prefix(.+)$/m", '{{%$1}}', $name);
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
    public static function truncateIndexName($name, $length = 64, $suffix = null)
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