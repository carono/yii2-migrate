<?php

/**
 * This class is generated using the package carono/codegen.
 */

namespace tests\yii2migrate\models\base;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the base-model class for table "user".
 *
 * @property int                                            $id
 * @property string                                         $name
 * @property \tests\yii2migrate\models\Company[]            $companies
 * @property \tests\yii2migrate\models\Employee[]           $employees
 * @property \tests\yii2migrate\models\Company[]            $companies0
 * @property \tests\yii2migrate\models\Photo[]              $photos
 * @property \tests\yii2migrate\models\PvCompanyDirector[]  $pvCompanyDirectors
 * @property \tests\yii2migrate\models\PvCompanyDirector[]  $pvCompanyDirectors0
 * @property \tests\yii2migrate\models\Company[]            $companies1
 * @property \tests\yii2migrate\models\PvCompanyUserPhoto[] $pvCompanyUserPhotos
 * @property \tests\yii2migrate\models\PvUserParent[]       $pvUserParents
 * @property \tests\yii2migrate\models\PvUserParent[]       $pvUserParents0
 * @property \tests\yii2migrate\models\User[]               $users
 * @property \tests\yii2migrate\models\User[]               $parents
 */
class User extends ActiveRecord
{
    protected $_relationClasses = [];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     *
     * @return \tests\yii2migrate\models\User|\yii\db\ActiveRecord
     */
    public static function findOne($condition, $raise = false)
    {
        $model = parent::findOne($condition);
        if (!$model && $raise) {
            throw new \yii\web\HttpException(404, Yii::t('errors', 'Model tests\\yii2migrate\\models\\User not found'));
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
            'id'   => Yii::t('models', 'ID'),
            'name' => Yii::t('models', 'Name'),
        ];
    }

    /**
     * @inheritdoc
     *
     * @return \tests\yii2migrate\models\query\UserQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \tests\yii2migrate\models\query\UserQuery(get_called_class());
    }

    /**
     * @return \tests\yii2migrate\models\query\CompanyQuery|\yii\db\ActiveQuery
     */
    public function getCompanies()
    {
        return $this->hasMany(\tests\yii2migrate\models\Company::className(), ['user_id' => 'id']);
    }

    /**
     * @return \tests\yii2migrate\models\query\EmployeeQuery|\yii\db\ActiveQuery
     */
    public function getEmployees()
    {
        return $this->hasMany(\tests\yii2migrate\models\Employee::className(), ['user_id' => 'id']);
    }

    /**
     * @return \tests\yii2migrate\models\query\CompanyQuery|\yii\db\ActiveQuery
     */
    public function getCompanies0()
    {
        return $this->hasMany(\tests\yii2migrate\models\Company::className(), ['id' => 'company_id'])->viaTable('{{%employees}}', ['user_id' => 'id']);
    }

    /**
     * @return \tests\yii2migrate\models\query\PhotoQuery|\yii\db\ActiveQuery
     */
    public function getPhotos()
    {
        return $this->hasMany(\tests\yii2migrate\models\Photo::className(), ['user_id' => 'id']);
    }

    /**
     * @return \tests\yii2migrate\models\query\PvCompanyDirectorQuery|\yii\db\ActiveQuery
     */
    public function getPvCompanyDirectors()
    {
        return $this->hasMany(\tests\yii2migrate\models\PvCompanyDirector::className(), ['director_id' => 'id']);
    }

    /**
     * @return \tests\yii2migrate\models\query\PvCompanyDirectorQuery|\yii\db\ActiveQuery
     */
    public function getPvCompanyDirectors0()
    {
        return $this->hasMany(\tests\yii2migrate\models\PvCompanyDirector::className(), ['hired_id' => 'id']);
    }

    /**
     * @return \tests\yii2migrate\models\query\CompanyQuery|\yii\db\ActiveQuery
     */
    public function getCompanies1()
    {
        return $this->hasMany(\tests\yii2migrate\models\Company::className(), ['id' => 'company_id'])->viaTable('{{%pv_company_directors}}', ['director_id' => 'id']);
    }

    /**
     * @return \tests\yii2migrate\models\query\PvCompanyUserPhotoQuery|\yii\db\ActiveQuery
     */
    public function getPvCompanyUserPhotos()
    {
        return $this->hasMany(\tests\yii2migrate\models\PvCompanyUserPhoto::className(), ['user_id' => 'id']);
    }

    /**
     * @return \tests\yii2migrate\models\query\PvUserParentQuery|\yii\db\ActiveQuery
     */
    public function getPvUserParents()
    {
        return $this->hasMany(\tests\yii2migrate\models\PvUserParent::className(), ['parent_id' => 'id']);
    }

    /**
     * @return \tests\yii2migrate\models\query\PvUserParentQuery|\yii\db\ActiveQuery
     */
    public function getPvUserParents0()
    {
        return $this->hasMany(\tests\yii2migrate\models\PvUserParent::className(), ['user_id' => 'id']);
    }

    /**
     * @return \tests\yii2migrate\models\query\UserQuery|\yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(\tests\yii2migrate\models\User::className(), ['id' => 'user_id'])->viaTable('{{%pv_user_parents}}', ['parent_id' => 'id']);
    }

    /**
     * @return \tests\yii2migrate\models\query\UserQuery|\yii\db\ActiveQuery
     */
    public function getParents()
    {
        return $this->hasMany(\tests\yii2migrate\models\User::className(), ['id' => 'parent_id'])->viaTable('{{%pv_user_parents}}', ['user_id' => 'id']);
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
