MigrationTrait   
=================
Трейт для помощи в создании базы

|Метод|Описание
|------|---------
|self::foreignKey($table)|установка Foreign Key на поле  
|self::pivot($table)|создание сводной таблицы из 2х primary key
|dropIndexByColumn('{{%user}}', 'name')|Удаление индекса по столбцу
|dropForeignKeyByColumn('{{%photo}}', 'user_id')|Удаление FK по столбцу
|createIndex(null, '{{%user}}', 'name')|Создание индекса с автоматическим именем


```php
<?php

use yii\db\Migration;

/**
 * Class m180712_120503_init
 */
class m180712_120503_init extends Migration
{
    use \carono\yii2migrate\traits\MigrationTrait;

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

    public function newTables()
    {
        return [
            '{{%logs}}' => [
                'data' => $this->string(),
                '@tableOptions' => 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=MyISAM'
            ],
            '{{%user}}' => [
                'id' => $this->primaryKey(),
                'name' => $this->string()
            ],
            '{{%photo}}' => [
                'id' => $this->primaryKey(),
                'user_id' => $this->integer()
            ],
            '{{%company}}' => [
                'id' => $this->primaryKey(),
                'name' => $this->string(),
                'directors' => $this->pivot('{{%user}}', 'director_id')->columns(
                    [
                        'hire_at' => $this->dateTime(),
                        //Внешний ключ с правилом SET NULL при удалении данных из {{%user}}
                        'hired_id' => $this->foreignKey('{{%user}}')->onDeleteNull()
                    ]
                ),  // Создаём сводную таблицу pv_company_directors
                '@tableOptions' => 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB'
            ]
        ];
    }

    public function newIndex()
    {
        return [
            ['{{%company}}', 'name']
        ];
    }

    public function safeUp()
    {
        $this->upNewTables();
        $this->upNewColumns();
        $this->alterColumn('{{%photo}}', 'user_id', $this->foreignKey('{{%user}}'));
        $this->upNewIndex();
        $this->createIndex(null, '{{%user}}', 'name');
    }

    public function safeDown()
    {
        $this->dropIndexByColumn('{{%user}}', 'name');
        $this->downNewIndex();
        $this->dropForeignKeyByColumn('{{%photo}}', 'user_id');
        $this->downNewColumns();
        $this->downNewTables();
    }
}
```
<p align="center">
    <img src="schema.png">
</p>

PivotTrait
==========
Трейт для помощи работы со сводными таблицами  

`$company` - модель таблицы Company (требуется трейт PivotTrait)  
`$user` - модель таблицы User  
`Employee` - сводная таблица двух моделей: компания-юзер  
`Сводная таблица` - таблица, в которой находятся 2 primary key

|Метод|Описание
|------|---------
|$company->addPivot($user, Employee::className(),$attributes = [])|Добавляем в таблицу Employee связку компания-пользователь
|$company->getPivot($model, Employee::className(), $condition = [])|Получаем модель Employee для связки компания-пользователь
|$company->deletePivot($model, Employee::className())|Удаляем связку компания-пользователь
|$company->deletePivots(Employee::className())|Удаляем всех пользователей из Employee для этой компании

