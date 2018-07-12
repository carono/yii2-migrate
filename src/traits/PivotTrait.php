<?php

namespace carono\yii2migrate\traits;

use yii\base\Model;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * Trait PivotTrait
 *
 * @package carono\yii2migrate\traits
 * @mixin ActiveRecord
 */
trait PivotTrait
{
    protected $_storage = [];
    protected $_storageAttributes = [];

    /**
     * @param string|ActiveRecord $pivotClass
     *
     * @return mixed
     */
    public function deletePivots($pivotClass)
    {
        return $pivotClass::deleteAll([$this->getMainPkField($this, $pivotClass) => $this->getMainPk()]);
    }

    /**
     * @param string $pivotClass
     *
     * @return ActiveRecord[]
     */
    public function getStoragePivots($pivotClass)
    {
        if (isset($this->_storage[$pivotClass])) {
            return $this->_storage[$pivotClass];
        } else {
            return [];
        }
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
        $mainPk = $this->getPkField($this, $pivotClass);
        $condition = [$mainPk => $this->getMainPk()];
        if (!is_null($model)) {
            $slavePk = $this->getSlavePkField($model, $pivotClass);
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
     * @param null $modelClass
     */
    public function storagePivots($models, $pivotClass, $modelClass = null)
    {
        if (!is_array($models)) {
            $models = [$models];
        }
        foreach ($models as $model) {
            $this->storagePivot($model, $pivotClass, $modelClass);
        }
    }

    /**
     * @param ActiveRecord $model
     * @param ActiveRecord|string $pivotClass
     * @param ActiveRecord $modelClass
     *
     * @param array $pvAttributes
     * @throws \Exception
     */
    public function storagePivot($model, $pivotClass, $modelClass = null, $pvAttributes = [])
    {
        if (is_numeric($model) && $modelClass) {
            $model = $modelClass::findOne($model);
        } elseif (is_array($model)) {
            $model = \Yii::createObject($model);
        }
        if (!($model instanceof Model)) {
            throw new \Exception('Cannot determine or model not found');
        }
        $this->_storage[$pivotClass][] = $model;
        $this->_storageAttributes[$pivotClass][spl_object_hash($model)] = $pvAttributes;
    }

    public function getStoragePivotAttribute($model, $pivotClass)
    {
        return ArrayHelper::getValue($this->_storageAttributes, $pivotClass . '.' . spl_object_hash($model), []);
    }

    /**
     * @param bool $clear
     */
    public function savePivots($clear = false)
    {
        foreach ($this->getPivotStorage() as $pivotClass => $items) {
            if ($clear) {
                $this->deletePivots($pivotClass);
            }
            foreach ($items as $item) {
                $this->addPivot($item, $pivotClass);
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
        } else {
            $pv->setAttributes(array_merge($condition, $attributes), false);
            $pv->save();
            return $pv;
        }
    }

    /**
     * @param ActiveRecord $model
     * @param string|ActiveRecord $pivotClass
     * @return mixed
     */
    public function deletePivot($model, $pivotClass)
    {
        return $pivotClass::deleteAll([
            $this->getMainPkField($this, $pivotClass) => $this->getMainPk(),
            $this->getSlavePkField($model, $pivotClass) => $model->{$model->primaryKey()[0]}
        ]);
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
     * @param $model
     * @param string|ActiveRecord $pivotClass
     * @return string
     */
    protected function getMainPkField($model, $pivotClass)
    {
        /**
         * @var ActiveRecord $this
         */
        return $this->getPkField($model, $pivotClass);
    }

    /**
     * @param $model
     * @param string|ActiveRecord $pivotClass
     * @return string
     */
    protected function getSlavePkField($model, $pivotClass)
    {
        return $this->getPkField($model, $pivotClass, true);
    }

    /**
     * @param ActiveRecord $model
     * @param ActiveRecord|string $pivotClass
     * @param bool $slave
     * @return int|null|string
     */
    private function getPkField($model, $pivotClass, $slave = false)
    {
        // TODO: на данный момент не могу определить, какое поле является главным в сводной таблице
        // поэтому считаем, что первое по порядку - главное, второе - второстепенное

        if ($filed = $this->getPkFieldByModel($model, $pivotClass)) {
            return $filed;
        } else {
            $pks = self::getDb()->getTableSchema($pivotClass::tableName())->primaryKey;
            return $slave ? $pks[1] : $pks[0];
        }
    }

    /**
     * @param ActiveRecord $model
     * @param ActiveRecord|string $pivotClass
     * @return string
     */
    private function getPkFieldByModel($model, $pivotClass)
    {
        $pks = self::getDb()->getTableSchema($pivotClass::tableName())->primaryKey;
        $fks = self::formFkKeys(self::getDb()->getTableSchema($pivotClass::tableName())->foreignKeys);
        $fks = array_values(array_filter($fks, function ($data) use ($pks) {
            return in_array($data['field'], $pks);
        }));

        $table = preg_replace('#{{%([\w\d\-_]+)}}#', $model::getDb()->tablePrefix . "$1", $model::tableName());

        $field = null;
        foreach ($fks as $fk) {
            if ($fk['table'] == $table) {
                if ($field) {
                    return null;
                } else {
                    $field = $fk['field'];
                }
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