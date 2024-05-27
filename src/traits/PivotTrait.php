<?php

namespace carono\yii2migrate\traits;

use carono\yii2migrate\helpers\SchemaHelper;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * Trait PivotTrait
 *
 * @package carono\yii2migrate\traits
 */
trait PivotTrait
{
    protected $_storage = [];

    /**
     * @param string|ActiveRecord $pivotClass
     *
     * @return mixed
     */
    public function deletePivots($pivotClass, $condition = [])
    {
        return $pivotClass::deleteAll(array_merge([$this->getPivotMainPkField($this, $pivotClass) => $this->getMainPk()], $condition));
    }

    /**
     * @param string $pivotClass
     *
     * @return ActiveRecord[]
     */
    public function getStoragePivots($pivotClass)
    {
        if (isset($this->_storage[$pivotClass])) {
            return array_values($this->_storage[$pivotClass]);
        }

        return [];
    }

    /**
     * @return array
     */
    public function getPivotStorage()
    {
        return $this->_storage;
    }

    /**
     * @param ActiveRecord|null $model
     * @param string|ActiveRecord $pivotClass
     * @return array
     */
    private function getPivotCondition($model, $pivotClass)
    {
        $mainPk = $this->getPivotMainPkField($this, $pivotClass);
        $condition = [$mainPk => $this->getMainPk()];
        if ($model !== null) {
            $slavePk = $this->getPivotSlavePkField($model, $pivotClass);
            $condition[$slavePk] = $model->getAttribute($model->primaryKey()[0]);
        }
        return $condition;
    }

    /**
     * @param ActiveRecord $model
     * @param string|ActiveRecord $pivotClass
     * @param array $condition
     * @return ActiveRecord|null
     */
    public function getPivot($model, $pivotClass, $condition = [])
    {
        return $this->findPivot($model, $pivotClass)->andWhere($condition)->one();
    }

    /**
     * @param string|ActiveRecord $pivotClass
     * @param array $condition
     * @return ActiveRecord[]
     */
    public function getPivots($pivotClass, $condition = [])
    {
        return $this->findPivots($pivotClass)->andWhere($condition)->all();
    }

    /**
     * @param ActiveRecord $model
     * @param string|ActiveRecord $pivotClass
     * @return ActiveQuery
     */
    public function findPivot($model, $pivotClass)
    {
        return $pivotClass::find()->andWhere($this->getPivotCondition($model, $pivotClass));
    }

    /**
     * @param string|ActiveRecord $pivotClass
     * @return ActiveQuery
     */
    public function findPivots($pivotClass)
    {
        return $this->findPivot(null, $pivotClass);
    }

    /**
     * @param string $pivotClass
     */
    public function clearStorage($pivotClass)
    {
        unset($this->_storage[$pivotClass]);
    }

    /**
     * @param ActiveRecord[] $models
     * @param string $pivotClass
     * @param array $attributes
     */
    public function storagePivots($models, $pivotClass, $attributes = [])
    {
        foreach ((array)$models as $model) {
            $this->storagePivot($model, $pivotClass, $attributes);
        }
    }

    /**
     * @param ActiveRecord $model
     * @param string $pivotClass
     * @param array $attributes
     */
    public function storagePivot($model, $pivotClass, $attributes = [])
    {
        $this->_storage[$pivotClass][spl_object_hash($model)] = ['model' => $model, 'attributes' => $attributes];
    }

    public function getStoragePivotAttribute($model, $pivotClass)
    {
        return ArrayHelper::getValue($this->_storage, $pivotClass . '.' . spl_object_hash($model) . '.attributes', []);
    }

    /**
     * @param bool $clear
     */
    public function savePivots($clear = false, $condition = [])
    {
        foreach ($this->getPivotStorage() as $pivotClass => $items) {
            if ($clear) {
                $this->deletePivots($pivotClass, $condition);
            }
            foreach ($items as $item) {
                $this->addPivot($item['model'], $pivotClass, $item['attributes']);
            }
        }
    }

    /**
     * @param $model
     * @param $pivotClass
     * @param array $attributes
     * @return array|null|ActiveRecord
     * @throws \Exception
     */
    public function addPivot($model, $pivotClass, $attributes = [])
    {
        /**
         * @var ActiveRecord $pv
         */
        $pv = new $pivotClass;
        $attributes = $attributes ? $attributes : $this->getStoragePivotAttribute($model, $pivotClass);
        $condition = $this->getPivotCondition($model, $pivotClass);
        if ($find = (new ActiveQuery($pivotClass))->andWhere($condition)->one()) {
            if ($attributes) {
                $find->setAttributes($attributes, false);
                $find->save();
            }
            return $find;
        }

        $pv->setAttributes(array_merge($condition, $attributes), false);
        $pv->save();
        return $pv;
    }

    /**
     * @param ActiveRecord $model
     * @param string|ActiveRecord $pivotClass
     * @return mixed
     */
    public function deletePivot($model, $pivotClass, $condition = [])
    {
        return $pivotClass::deleteAll(array_merge([
            $this->getPivotMainPkField($this, $pivotClass) => $this->getMainPk(),
            $this->getPivotSlavePkField($model, $pivotClass) => $model->{$model->primaryKey()[0]}
        ], $condition));
    }


    /**
     * @return mixed
     */
    protected function getMainPk()
    {
        /**
         * @var ActiveRecord $this
         */
        return $this->{$this->primaryKey()[0]};
    }

    /**
     * @param ActiveRecord $model
     * @param string|ActiveRecord $pivotClass
     * @return string
     */
    protected function getPivotMainPkField($model, $pivotClass)
    {
        return $this->getPivotPkField($model, $pivotClass, false);
    }

    /**
     * @param $model
     * @param string|ActiveRecord $pivotClass
     * @return string
     */
    protected function getPivotSlavePkField($model, $pivotClass)
    {
        return $this->getPivotPkField($model, $pivotClass, true);
    }

    /**
     * @param ActiveRecord $model
     * @param ActiveRecord|string $pivotClass
     * @param bool $slave
     * @return int|null|string
     */
    private function getPivotPkField($model, $pivotClass, $slave = false)
    {
        if ($field = $this->getPkFieldByModel($model, $pivotClass)) {
            return $field;
        }

        $pks = $pivotClass::getDb()->getTableSchema($pivotClass::tableName())->primaryKey;
        return $slave ? $pks[1] : $pks[0];
    }

    /**
     * @param ActiveRecord $model
     * @param ActiveRecord|string $pivotClass
     * @return string
     */
    private function getPkFieldByModel($model, $pivotClass)
    {
        $pks = $pivotClass::getDb()->getTableSchema($pivotClass::tableName())->primaryKey;
        $fks = static::formFkKeys($pivotClass::getDb()->getTableSchema($pivotClass::tableName())->foreignKeys);
        $fks = array_values(array_filter($fks, function ($data) use ($pks) {
            return in_array($data['field'], $pks, true);
        }));
        $table = SchemaHelper::expandTablePrefix($model::tableName(), $model::getDb()->tablePrefix);
        $field = null;
        foreach ($fks as $fk) {
            if ($fk['table'] === $table) {
                if ($field) {
                    return null;
                }

                $field = $fk['field'];
            }
        }
        return $field;
    }

    /**
     * @param $array
     * @return array
     */
    private static function formFkKeys($array)
    {
        $result = [];
        foreach ($array as $key => $data) {
            $result[$key] = [
                'table' => ArrayHelper::remove($data, 0),
                'field' => key($data),
                'reference' => current($data),
            ];
        }
        return $result;
    }
}