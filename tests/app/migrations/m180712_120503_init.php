<?php

use yii\db\Migration;
use \yii\db\Schema;
/**
 * Class m180712_120503_init
 */
class m180712_120503_init extends Migration
{
    use \carono\yii2migrate\traits\MigrationTrait;

    public function tableOptions()
    {
        return [
            'mysql' => 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB'
        ];
    }

    public function newTables()
    {
        return [
            '{{%logs}}' => [
                'data' => $this->string(),
                '@tableOptions' => [
                    'mysql' => 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=MyISAM'
                ]
            ],
            '{{%user}}' => [
                'id' => $this->primaryKey(),
                'name' => $this->string(),
                'parents' => $this->pivot('{{%user}}') // Создаём сводную таблицу саму на себя
            ],
            '{{%photo}}' => [
                'id' => $this->primaryKey(),
                'user_id' => $this->integer()
            ],
            '{{%company}}' => [
                'id' => $this->primaryKey(),
                'name' => $this->string(),
                // Создадим сводную таблицу {{%pv_company_directors}}
                'directors' => $this->pivot('{{%user}}', 'director_id')->columns(
                    [
                        'hire_at' => $this->dateTime(),
                        // Внешний ключ с правилом SET NULL при удалении данных из {{%user}}
                        'hired_id' => $this->foreignKey('{{%user}}', null)->onDeleteNull()->unsigned()
                    ]
                ),  // Создаём сводную таблицу pv_company_directors
                '@tableOptions' => [
                    'mysql' => 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB'
                ]
            ],
            '{{%pv_company_user_photo}}' => [
                // Созданём сводную таблицу из нескольких ключей
                'company_id' => $this->foreignKey('{{%company}}', null, Schema::TYPE_PK),
                'user_id' => $this->foreignKey('{{%user}}', null, Schema::TYPE_PK),
                'photo_id' => $this->foreignKey('{{%photo}}', null, Schema::TYPE_PK),
            ]
        ];
    }

    public function newColumns()
    {
        return [
            '{{%company}}' => [
                // Создаём FK на user
                'user_id' => $this->foreignKey('{{%user}}'),
                // Создаём сводную таблицу employees
                'users' => $this->pivot('{{%user}}')->tableName('{{%employees}}')
            ]
        ];
    }

    public function newIndex()
    {
        return [
            '{{%company}}' => [
                $this->index()->columns(['name'])->unique(true)
            ],
        ];
    }

    public function safeUp()
    {
        $this->upNewTables();
        $this->upNewColumns();
        // Добавим FK на существующий столбец
        $this->alterColumn('{{%photo}}', 'user_id', $this->foreignKey('{{%user}}'));
        $this->upNewIndex();
        $this->createIndex(null, '{{%user}}', 'name');
    }

    public function safeDown()
    {
        $this->dropIndexByColumn('{{%user}}', 'name');
        $this->downNewIndex();
        // Удалим FK по имени столбца
        $this->dropForeignKeyByColumn('{{%photo}}', 'user_id');
        $this->downNewColumns();
        $this->downNewTables();
    }
}