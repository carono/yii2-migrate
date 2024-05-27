<?php

namespace helpers;

use carono\yii2migrate\helpers\SchemaHelper;
use yii\db\Migration;

class SchemaHelperTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    public $prefixes = ['pfx_', 'pfx_', 'pfx\\', 'pfx/', '#', '@', '%'];

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    public function testFindIndexes()
    {
        $migrate = new Migration(['db' => \Yii::$app->db]);
        $migrate->createTable('{{%test}}', [
            'id'   => $migrate->primaryKey(),
            'name' => $migrate->integer()->defaultValue(0),
        ]);
        $migrate->createIndex('pfx_test:name_idx', '{{%test}}', 'name');
        $this->assertSame('pfx_test:name_idx', current(SchemaHelper::findNonUniqueIndexes(\Yii::$app->db, 'pfx_test'))->name);
        $this->assertSame('pfx_test:name_idx', current(SchemaHelper::findNonUniqueIndexes(\Yii::$app->db, '{{%test}}'))->name);
        $migrate->dropTable('{{%test}}');
    }

    // tests
    public function testExpandTablePrefix()
    {
        foreach ($this->prefixes as $prefix) {
            $this->assertSame($prefix.'table', SchemaHelper::expandTablePrefix('{{%table}}', $prefix));
        }
        $this->assertSame('pfx_table', SchemaHelper::expandTablePrefix('pfx_table', 'pfx_'));
        $this->assertSame('pfx_table', SchemaHelper::expandTablePrefix('pfx_table', 'pfx_wrong'));
    }

    public function testCollapseTablePrefix()
    {
        foreach ($this->prefixes as $prefix) {
            $this->assertSame('{{%table}}', SchemaHelper::collapseTablePrefix($prefix.'table', $prefix));
        }
    }

    public function testRemoveSchema()
    {
        $this->assertSame('table', SchemaHelper::removeSchema('schema.table'));
    }
}
