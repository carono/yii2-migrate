<?php

/**
 * This class is generated using the package carono/codegen.
 */

namespace tests\yii2migrate\models\base;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the base-model class for table "company".
 *
 * @property int                                            $id
 * @property string                                         $name
 * @property int                                            $user_id
 * @property \tests\yii2migrate\models\User                 $user
 * @property \tests\yii2migrate\models\Employee[]           $employees
 * @property \tests\yii2migrate\models\User[]               $users
 * @property \tests\yii2migrate\models\PvCompanyDirector[]  $pvCompanyDirectors
 * @property \tests\yii2migrate\models\User[]               $directors
 * @property \tests\yii2migrate\models\PvCompanyUserPhoto[] $pvCompanyUserPhotos
 */
class Company extends ActiveRecord
{
    protected $_relationClasses = ['user_id' => 'tests\yii2migrate\models\User'];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%company}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['name'], 'unique'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => \tests\yii2migrate\models\User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     *
     * @return \tests\yii2migrate\models\Company|\yii\db\ActiveRecord
     */
    public static function findOne($condition, $raise = false)
    {
        $model = parent::findOne($condition);
        if (!$model && $raise) {
            throw new \yii\web\HttpException(404, Yii::t('errors', 'Model tests\\yii2migrate\\models\\Company not found'));
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
            'id'      => Yii::t('models', 'ID'),
            'name'    => Yii::t('models', 'Name'),
            'user_id' => Yii::t('models', 'User ID'),
        ];
    }

    /**
     * @inheritdoc
     *
     * @return \tests\yii2migrate\models\query\CompanyQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \tests\yii2migrate\models\query\CompanyQuery(get_called_class());
    }

    /**
     * @return \tests\yii2migrate\models\query\UserQuery|\yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(\tests\yii2migrate\models\User::className(), ['id' => 'user_id']);
    }

    /**
     * @return \tests\yii2migrate\models\query\EmployeeQuery|\yii\db\ActiveQuery
     */
    public function getEmployees()
    {
        return $this->hasMany(\tests\yii2migrate\models\Employee::className(), ['company_id' => 'id']);
    }

    /**
     * @return \tests\yii2migrate\models\query\UserQuery|\yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(\tests\yii2migrate\models\User::className(), ['id' => 'user_id'])->viaTable('{{%employees}}', ['company_id' => 'id']);
    }

    /**
     * @return \tests\yii2migrate\models\query\PvCompanyDirectorQuery|\yii\db\ActiveQuery
     */
    public function getPvCompanyDirectors()
    {
        return $this->hasMany(\tests\yii2migrate\models\PvCompanyDirector::className(), ['company_id' => 'id']);
    }

    /**
     * @return \tests\yii2migrate\models\query\UserQuery|\yii\db\ActiveQuery
     */
    public function getDirectors()
    {
        return $this->hasMany(\tests\yii2migrate\models\User::className(), ['id' => 'director_id'])->viaTable('{{%pv_company_directors}}', ['company_id' => 'id']);
    }

    /**
     * @return \tests\yii2migrate\models\query\PvCompanyUserPhotoQuery|\yii\db\ActiveQuery
     */
    public function getPvCompanyUserPhotos()
    {
        return $this->hasMany(\tests\yii2migrate\models\PvCompanyUserPhoto::className(), ['company_id' => 'id']);
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
