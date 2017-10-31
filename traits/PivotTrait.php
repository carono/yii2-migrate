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

//    /**
//     * @param string|ActiveRecord $pivotClass
//     * @param string $column
//     * @param array $condition
//     *
//     * @return mixed
//     */
//    public function getPivotAttribute($pivotClass, $column, $condition = [])
//    {
//        /**
//         * @var ActiveRecord $pv
//         */
//        if (is_numeric($condition)) {
//            $pv = new $pivotClass;
//            $mainPk = $this->getMainPkField($this, $pivotClass);
//            $pk = $pv->primaryKey();
//            $slavePk = current(array_diff($pk, [$mainPk]));
//            $condition = [$slavePk => $condition];
//        }
//        $condition = array_merge($condition, [$this->getMainPkField($this, $pivotClass) => $this->getMainPk()]);
//        return $pivotClass::find()->andWhere($condition)->select([$column])->scalar();
//    }

    /**
     * @param ActiveRecord $model
     * @param string|ActiveRecord $pivotClass
     * @return array
     */
    private function getPivotCondition($model, $pivotClass)
    {
        $mainPk = $this->getPkField($this, $pivotClass);
        $slavePk = $this->getSlavePkField($this, $pivotClass);
        $condition = [$mainPk => $this->getMainPk()];
        if (!is_null($model)) {
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
        return $pivotClass::find()->andWhere($this->getPivotCondition($model, $pivotClass))->andWhere($condition)->one();
    }

    /**
     * @param string|ActiveRecord $pivotClass
     * @param array $condition
     * @return ActiveRecord[]
     */
    public function getPivots($pivotClass, $condition = [])
    {
        return $pivotClass::find()->andWhere($this->getPivotCondition(null, $pivotClass))->andWhere($condition)->all();
    }

//    /**
//     * @param string|ActiveRecord $pivotClass
//     * @param              $value
//     * @param null $column
//     * @param array $condition
//     */
//    public function updatePivotAttribute($pivotClass, $model, $value, $column = null, $condition = [])
//    {
//        /**
//         * @var ActiveRecord $pv
//         */
//        if (is_numeric($condition)) {
//            $pv = new $pivotClass;
//            $mainPk = $this->getMainPkField($pivotClass);
//            $pk = $pv->primaryKey();
//            $slavePk = current(array_diff($pk, [$mainPk]));
//            $condition = [$slavePk => $condition];
//        }
//        $condition = array_merge($condition, [$this->getMainPkField($pivotClass) => $this->getMainPk()]);
//        $pivotClass::updateAll([$column => $value], $condition);
//    }

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

//    /**
//     * @param $pivotClass
//     * @return mixed
//     * @throws \Exception
//     */
//    private function getSlavePk($pivotClass)
//    {
//        /**
//         * @var ActiveRecord $pv
//         */
//        $mainPk = $this->getMainPkField($pivotClass);
//        $pv = new $pivotClass;
//        $pk = $pv->primaryKey();
//        if (!in_array($mainPk, $pk)) {
//            throw  new \Exception("Fail found pk $mainPk in " . $pivotClass);
//        }
//        return current(array_diff($pk, [$mainPk]));
//    }

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
        $mainPk = $this->getMainPkField($this, $pivotClass);
        $pk = $pv->primaryKey();
        if (!in_array($mainPk, $pk)) {
            throw  new \Exception("Fail found pk $mainPk in " . $pivotClass);
        }
        $slavePk = current(array_diff($pk, [$mainPk]));
        $attributes = $attributes ? $attributes : $this->getStoragePivotAttribute($model, $pivotClass);
        $condition = [];
        $condition[$mainPk] = $this->getMainPk();
        $condition[$slavePk] = $model->id;
        if ($find = (new ActiveQuery($pivotClass))->andWhere($condition)->one()) {
            if ($attributes) {
                $find->updateAttributes($attributes);
            }
            return $find;
        } else {
            $pv->setAttributes(array_merge($condition, $attributes));
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
            $this->getSlavePkField($this, $pivotClass) => $model->{$model->primaryKey()[0]}
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
     * @param $model
     * @param $pivotClass
     * @param bool $slave
     * @return int|null|string
     */
    private function getPkField($model, $pivotClass, $slave = false)
    {
        $modelTableName = self::getDb()->getTableSchema($model::tableName())->name;
        $pk = self::getDb()->getTableSchema($pivotClass::tableName())->primaryKey;
        foreach (self::getDb()->getTableSchema($pivotClass::tableName())->foreignKeys as $fk) {
            if (!in_array(current(array_filter(array_keys($fk))), $pk)) {
                continue;
            }
            if ($slave) {
                if ($fk[0] != $modelTableName) {
                    unset($fk[0]);
                    return key($fk);
                }
            } else {
                if ($fk[0] == $modelTableName) {
                    unset($fk[0]);
                    return key($fk);
                }
            }
        }
        return null;
    }
}