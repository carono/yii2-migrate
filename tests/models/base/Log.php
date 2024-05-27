<?php

/**
 * This class is generated using the package carono/codegen.
 */

namespace tests\yii2migrate\models\base;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the base-model class for table "logs".
 *
 * @property string $data
 */
class Log extends ActiveRecord
{
    protected $_relationClasses = [];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%logs}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['data'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     *
     * @return \tests\yii2migrate\models\Log|\yii\db\ActiveRecord
     */
    public static function findOne($condition, $raise = false)
    {
        $model = parent::findOne($condition);
        if (!$model && $raise) {
            throw new \yii\web\HttpException(404, Yii::t('errors', 'Model tests\\yii2migrate\\models\\Log not found'));
        } else {
            return $model;
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'data' => Yii::t('models', 'Data'),
        ];
    }

    /**
     * @inheritdoc
     *
     * @return \tests\yii2migrate\models\query\LogQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \tests\yii2migrate\models\query\LogQuery(get_called_class());
    }

    /**
     * @param string $attribute
     *
     * @return string|null
     */
    public function getRelationClass($attribute)
    {
        return ArrayHelper::getValue($this->_relationClasses, $attribute);
    }
}
