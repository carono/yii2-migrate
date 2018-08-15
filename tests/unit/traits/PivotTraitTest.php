<?php

namespace traits;

use tests\yii2migrate\fixtures\CompanyFixture;
use tests\yii2migrate\fixtures\UserFixture;
use tests\yii2migrate\models\Company;
use tests\yii2migrate\models\PvCompanyDirector;
use tests\yii2migrate\models\PvUserParent;
use tests\yii2migrate\models\User;

class PivotTraitTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    /**
     * @var User
     */
    public $user;
    /**
     * @var Company
     */
    public $company;

    public static function setUpBeforeClass()
    {
        $dir = dirname(dirname(__DIR__));
        $cmd = "php $dir/app/yii migrate/fresh --interactive=0";
        shell_exec($cmd);
    }

    public static function tearDownAfterClass()
    {
        $dir = dirname(dirname(__DIR__));
        $cmd = "php $dir/app/yii migrate/down --interactive=0";
        $x = shell_exec($cmd);
    }

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    /**
     * @param $name
     * @param $className
     * @return \ReflectionMethod
     */
    public function getProtectedMethod($name, $className)
    {
        $class = new \ReflectionClass($className);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    public function invokeArgs($model, $name, $args)
    {
        return $this->getProtectedMethod($name, get_class($model))->invokeArgs($model, $args);
    }

    public function _fixtures()
    {
        return [
            'user' => UserFixture::class,
            'company' => CompanyFixture::class
        ];
    }

    public function addCompanyPivots()
    {
        $user2 = $this->tester->grabUser('user2');
        $user3 = $this->tester->grabUser('user3');
        $user4 = $this->tester->grabUser('user4');
        $user5 = $this->tester->grabUser('user5');
        $company = $this->tester->grabCompany('company1');


        $company->addPivot($user2, PvCompanyDirector::class);
        $company->addPivot($user3, PvCompanyDirector::class);
        $company->addPivot($user4, PvCompanyDirector::class);
        $company->addPivot($user5, PvCompanyDirector::class);
    }

    public function addUserPivots()
    {
        $user1 = $this->tester->grabUser('user1');
        $user2 = $this->tester->grabUser('user2');
        $user3 = $this->tester->grabUser('user3');
        $user4 = $this->tester->grabUser('user4');
        $user5 = $this->tester->grabUser('user5');

        $this->assertFalse($user1->addPivot($user2, PvUserParent::class)->hasErrors());
        $this->assertFalse($user1->addPivot($user3, PvUserParent::class)->hasErrors());

        $this->assertFalse($user2->addPivot($user4, PvUserParent::class)->hasErrors());
        $this->assertFalse($user2->addPivot($user5, PvUserParent::class)->hasErrors());
    }

    public function testAddPivots()
    {
        /**
         * @var PvCompanyDirector $pv
         * @var \tests\yii2migrate\fixtures\UserFixture $user1
         */
        $this->addCompanyPivots();

        $user1 = $this->tester->grabUser('user1');
        $company = $this->tester->grabCompany('company1');
        $pv = $company->addPivot($user1, PvCompanyDirector::class, ['hire_at' => '2000-01-01 00:00:00']);
        $this->assertFalse($pv->hasErrors());
        $pv = PvCompanyDirector::find()->andWhere(['director_id' => $user1->id, 'company_id' => $company->id])->one();
        $this->assertNotNull($pv);
        $this->assertSame($pv->director_id, 201);
        $this->assertSame($pv->company_id, 101);
        $this->assertSame($pv->hire_at, '2000-01-01 00:00:00');
    }

    public function testAddPivotWithAttributes()
    {
        /**
         * @var PvCompanyDirector $pv
         */
        $user = $this->tester->grabUser('user1');
        $company = $this->tester->grabCompany('company1');

        $pv = $company->addPivot($user, PvCompanyDirector::class, ['hire_at' => '2000-01-01 00:00:00']);

        $this->assertFalse($pv->hasErrors());

        $pv = PvCompanyDirector::find()->one();
        $this->assertSame($pv->director_id, 201);
        $this->assertSame($pv->company_id, 101);
        $this->assertSame($pv->hire_at, '2000-01-01 00:00:00');
    }


    public function testAddExistedPivot()
    {
        /**
         * @var PvCompanyDirector $pv
         */

        $user = $this->tester->grabUser('user1');
        $company = $this->tester->grabCompany('company1');
        $company->addPivot($user, PvCompanyDirector::class, ['hire_at' => '2000-01-01 00:00:00']);

        $pv = $company->getPivot($user, PvCompanyDirector::class);
        $this->assertSame($pv->hire_at, '2000-01-01 00:00:00');

        $company->addPivot($user, PvCompanyDirector::class, ['hire_at' => '2000-01-01 00:00:01']);

        $pv = $company->getPivot($user, PvCompanyDirector::class);
        $this->assertSame($pv->hire_at, '2000-01-01 00:00:01');
    }

    public function testDeletePivot()
    {
        $this->addUserPivots();
        $user = $this->tester->grabUser('user1');
        $company = $this->tester->grabCompany('company1');
        $company->deletePivot($user, PvCompanyDirector::class);
        $this->assertNull(PvCompanyDirector::find()->one());
    }

    public function testDeletePivots()
    {
        $this->addUserPivots();
        $company = $this->tester->grabCompany('company1');
        $company->deletePivots(PvCompanyDirector::class);
        $this->assertNull(PvCompanyDirector::find()->one());
    }

    public function testFindPivot()
    {
        /**
         * @var PvCompanyDirector $pv
         */
        $this->addCompanyPivots();
        $user = $this->tester->grabUser('user2');
        $company = $this->tester->grabCompany('company1');
        $pv = $company->findPivot($user, PvCompanyDirector::class)->one();
        $this->assertNotNull($pv);
        $this->assertSame($pv->director_id, $user->id);
        $this->assertSame($pv->company_id, $company->id);
    }

    public function testGetPivot()
    {
        /**
         * @var PvCompanyDirector $pv
         */
        $this->addCompanyPivots();
        $user = $this->tester->grabUser('user2');
        $company = $this->tester->grabCompany('company1');
        $pv = $company->getPivot($user, PvCompanyDirector::class);

        $this->assertNotNull($pv);
        $this->assertSame($pv->director_id, $user->id);
        $this->assertSame($pv->company_id, $company->id);
    }

    public function testGetPivots()
    {
        /**
         * @var PvCompanyDirector $pv
         */
        $user = $this->tester->grabUser('user1');
        $company = $this->tester->grabCompany('company1');
        $company->addPivot($user, PvCompanyDirector::class);
        $pvs = $company->getPivots(PvCompanyDirector::class);

        $this->assertCount(1, $pvs);
        $this->assertNotNull($pvs);
        $this->assertSame($pvs[0]->director_id, $user->id);
        $this->assertSame($pvs[0]->company_id, $company->id);
    }

    public function testAddPivotsSelfSlave()
    {
        /**
         * @var PvCompanyDirector $pv
         */
        $this->addUserPivots();
        $user1 = $this->tester->grabUser('user1');
        $user1Parents = PvUserParent::find()->andWhere(['user_id' => $user1->id])->asArray()->all();
        $this->assertCount(2, $user1Parents);
        $this->assertArraySubset([0 => ['user_id' => 201, 'parent_id' => 202]], $user1Parents);
        $this->assertArraySubset([1 => ['user_id' => 201, 'parent_id' => 203]], $user1Parents);
    }

    public function testAddPivotSelfMaster()
    {
        /**
         * @var PvCompanyDirector $pv
         */
        $this->addUserPivots();
        $user2 = $this->tester->grabUser('user2');

        $user2Children = PvUserParent::find()->andWhere(['parent_id' => $user2->id])->asArray()->all();
        $this->assertCount(1, $user2Children);
        $this->assertArraySubset([0 => ['user_id' => 201, 'parent_id' => 202]], $user2Children);
    }

    public function testDeletePivotSelfSlave()
    {
        $this->addUserPivots();

        $user1 = $this->tester->grabUser('user1');
        $user2 = $this->tester->grabUser('user2');

        $user1->deletePivot($user2, PvUserParent::class);

        $user1Parents = PvUserParent::find()->andWhere(['user_id' => $user1->id])->asArray()->all();

        $this->assertCount(1, $user1Parents);
    }

    public function testGetStoragePivots()
    {
        $user1 = $this->tester->grabUser('user1');
        $user2 = $this->tester->grabUser('user2');
        $user3 = $this->tester->grabUser('user3');
        $company1 = $this->tester->grabCompany('company1');
        $user1->storagePivot($user2, PvUserParent::class);
        $user1->storagePivot($user3, PvUserParent::class);
        $user1->storagePivot($company1, PvCompanyDirector::class);
        $pvs = $user1->getStoragePivots(PvUserParent::class);

        $this->assertSame([['model' => $user2, 'attributes' => []], ['model' => $user3, 'attributes' => []]], $pvs);
    }

    public function testGetStoragePivots2()
    {
        $user1 = $this->tester->grabUser('user1');
        $user2 = $this->tester->grabUser('user2');
        $user3 = $this->tester->grabUser('user3');
        $company1 = $this->tester->grabCompany('company1');
        $user1->storagePivots([$user2, $user3], PvUserParent::class);
        $user1->storagePivots([$company1], PvCompanyDirector::class);
        $pvs = $user1->getStoragePivots(PvUserParent::class);

        $this->assertSame([['model' => $user2, 'attributes' => []], ['model' => $user3, 'attributes' => []]], $pvs);
    }

    public function testGetPivotStorage()
    {
        $user1 = $this->tester->grabUser('user1');
        $user2 = $this->tester->grabUser('user2');
        $user3 = $this->tester->grabUser('user3');
        $company1 = $this->tester->grabCompany('company1');
        $user1->storagePivot($company1, PvCompanyDirector::class);
        $user1->storagePivot($user2, PvUserParent::class);
        $user1->storagePivot($user3, PvUserParent::class);
        $pvs = $user1->getStoragePivots(PvUserParent::class);
        $this->assertSame([['model' => $user2, 'attributes' => []], ['model' => $user3, 'attributes' => []]], $pvs);
    }

    public function testGetPivotConditionSlave()
    {
        $user1 = $this->tester->grabUser('user1');
        $company1 = $this->tester->grabCompany('company1');
        $condition = $this->invokeArgs($user1, 'getPivotCondition', [$company1, PvCompanyDirector::class]);
        $expect = ['director_id' => $user1->id, 'company_id' => $company1->id];
        sort($expect);
        sort($condition);
        $this->assertSame($expect, $condition);
    }

    public function testGetPivotConditionMaster()
    {
        $user1 = $this->tester->grabUser('user1');
        $company1 = $this->tester->grabCompany('company1');
        $condition = $this->invokeArgs($company1, 'getPivotCondition', [$user1, PvCompanyDirector::class]);
        $expect = ['director_id' => $user1->id, 'company_id' => $company1->id];
        sort($expect);
        sort($condition);
        $this->assertSame($expect, $condition);
    }

    public function testGetPivotConditionSelfSlave()
    {
        $user1 = $this->tester->grabUser('user1');
        $user2 = $this->tester->grabUser('user2');
        $condition = $this->invokeArgs($user1, 'getPivotCondition', [$user2, PvUserParent::class]);
        $expect = ['user_id' => $user1->id, 'parent_id' => $user2->id];
        sort($expect);
        sort($condition);
        $this->assertSame($expect, $condition);
    }

    public function testGetPivotConditionSelfMaster()
    {
        $user1 = $this->tester->grabUser('user1');
        $user2 = $this->tester->grabUser('user2');
        $condition = $this->invokeArgs($user2, 'getPivotCondition', [$user1, PvUserParent::class]);
        $expect = ['user_id' => $user2->id, 'parent_id' => $user1->id];
        sort($expect);
        sort($condition);
        $this->assertSame($expect, $condition);
    }

    public function testFindPivots()
    {
        $this->addCompanyPivots();
        $company1 = $this->tester->grabCompany('company1');
        $pvs = $company1->findPivots(PvCompanyDirector::class)->asArray()->all();

        $this->assertCount(4, $pvs);
    }

    public function testClearStorage()
    {
        $user1 = $this->tester->grabUser('user1');
        $user2 = $this->tester->grabUser('user2');
        $company1 = $this->tester->grabCompany('company1');

        $this->assertEmpty($user1->getStoragePivots(PvUserParent::class));
        $this->assertEmpty($user1->getStoragePivots(PvCompanyDirector::class));

        $user1->storagePivot($user2, PvUserParent::class);
        $user1->storagePivot($company1, PvCompanyDirector::class);

        $this->assertNotEmpty($user1->getStoragePivots(PvUserParent::class));
        $this->assertNotEmpty($user1->getStoragePivots(PvCompanyDirector::class));

        $user1->clearStorage(PvUserParent::class);

        $this->assertEmpty($user1->getStoragePivots(PvUserParent::class));
        $this->assertNotEmpty($user1->getStoragePivots(PvCompanyDirector::class));
    }

    public function testStoragePivots()
    {
        $user1 = $this->tester->grabUser('user1');
        $user2 = $this->tester->grabUser('user2');
        $company1 = $this->tester->grabCompany('company1');
        $user1->storagePivots([$user2], PvUserParent::class);
        $user1->storagePivots([$company1], PvCompanyDirector::class);

        $this->assertSame([['model' => $user2, 'attributes' => []]], $user1->getStoragePivots(PvUserParent::class));
        $this->assertSame([
            [
                'model' => $company1,
                'attributes' => []
            ]
        ], $user1->getStoragePivots(PvCompanyDirector::class));
    }

    public function testStoragePivot()
    {
        $user1 = $this->tester->grabUser('user1');
        $user2 = $this->tester->grabUser('user2');
        $company1 = $this->tester->grabCompany('company1');
        $user1->storagePivot($user2, PvUserParent::class);
        $user1->storagePivot($company1, PvCompanyDirector::class);

        $this->assertSame([['model' => $user2, 'attributes' => []]], $user1->getStoragePivots(PvUserParent::class));
        $this->assertSame([
            [
                'model' => $company1,
                'attributes' => []
            ]
        ], $user1->getStoragePivots(PvCompanyDirector::class));
    }

    public function testGetStoragePivotAttribute()
    {
        $user1 = $this->tester->grabUser('user1');
        $company1 = $this->tester->grabCompany('company1');
        $attributes = ['hire_at' => '2010-01-01 00:00:00'];
        $user1->storagePivot($company1, PvCompanyDirector::class, $attributes);
        $this->assertSame($attributes, $user1->getStoragePivotAttribute($company1, PvCompanyDirector::class));
    }

    public function testSavePivotsWithoutClear()
    {
        $user1 = $this->tester->grabUser('user1');
        $company1 = $this->tester->grabCompany('company1');
        $user1->storagePivot($company1, PvCompanyDirector::class, ['hire_at' => '2010-01-01 00:00:00']);
        $user1->savePivots();
        $this->assertCount(1, PvCompanyDirector::find()->andWhere(['director_id' => $user1->id])->all());
        $this->assertSame('2010-01-01 00:00:00', PvCompanyDirector::find()->andWhere(['director_id' => $user1->id])->one()->hire_at);
    }

    public function testSavePivotsWithClear()
    {
        $user1 = $this->tester->grabUser('user1');
        $company1 = $this->tester->grabCompany('company1');
        $company2 = $this->tester->grabCompany('company2');

        $user1->addPivot($company2, PvCompanyDirector::class);
        $this->assertCount(1, PvCompanyDirector::find()->andWhere(['director_id' => $user1->id])->all());

        $user1->storagePivot($company1, PvCompanyDirector::class, ['hire_at' => '2010-01-01 00:00:00']);
        $user1->savePivots(true);
        $this->assertCount(1, PvCompanyDirector::find()->andWhere(['director_id' => $user1->id])->all());
        $this->assertSame('2010-01-01 00:00:00', PvCompanyDirector::find()->andWhere(['director_id' => $user1->id])->one()->hire_at);
    }

    public function testAddPivot()
    {
        $user1 = $this->tester->grabUser('user1');
        $company1 = $this->tester->grabCompany('company1');
        $attributes = ['hire_at' => '2010-01-01 00:00:00'];
        $user1->addPivot($company1, PvCompanyDirector::class, $attributes);
        $this->assertCount(1, PvCompanyDirector::find()->andWhere(['director_id' => $user1->id])->all());
        $this->assertSame('2010-01-01 00:00:00', PvCompanyDirector::find()->andWhere(['company_id' => $company1->id])->one()->hire_at);
    }

    public function testGetMainPk()
    {
        $user1 = $this->tester->grabUser('user1');
        $pk = $this->invokeArgs($user1, 'getMainPk', []);
        $this->assertSame($pk, $user1->id);
    }

    public function testGetPivotMainPkField()
    {
        $user1 = $this->tester->grabUser('user1');
        $pk = $this->invokeArgs($user1, 'getPivotMainPkField', [$user1, PvUserParent::class]);
        $this->assertSame($pk, 'user_id');
    }

    public function testGetPivotSlavePkField()
    {
        $user1 = $this->tester->grabUser('user1');
        $pk = $this->invokeArgs($user1, 'getPivotSlavePkField', [$user1, PvUserParent::class]);
        $this->assertSame($pk, 'parent_id');
    }

    public function testGetPivotPkFieldByModel()
    {
        $user1 = $this->tester->grabUser('user1');
        $company1 = $this->tester->grabCompany('company1');

        $field = $this->invokeArgs($user1, 'getPivotPkField', [$company1, PvCompanyDirector::class]);
        $this->assertSame('company_id', $field);

        $field = $this->invokeArgs($company1, 'getPivotPkField', [$user1, PvCompanyDirector::class]);
        $this->assertSame('director_id', $field);
    }

    public function testGetPivotPkFieldSlave()
    {
        $user1 = $this->tester->grabUser('user1');
        $user2 = $this->tester->grabUser('user2');

        $field = $this->invokeArgs($user1, 'getPivotPkField', [$user2, PvUserParent::class, false]);
        $this->assertSame('user_id', $field);

        $field = $this->invokeArgs($user1, 'getPivotPkField', [$user2, PvUserParent::class, true]);
        $this->assertSame('parent_id', $field);
    }
}