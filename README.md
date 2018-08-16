[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/carono/yii2-migrate/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/carono/yii2-migrate/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/carono/yii2-migrate/v/stable)](https://packagist.org/packages/carono/yii2-migrate)
[![Total Downloads](https://poser.pugx.org/carono/yii2-migrate/downloads)](https://packagist.org/packages/carono/yii2-migrate)
[![License](https://poser.pugx.org/carono/yii2-migrate/license)](https://packagist.org/packages/carono/yii2-migrate)
[![Build Status](https://travis-ci.org/carono/yii2-migrate.svg?branch=master)](https://travis-ci.org/carono/yii2-migrate)
[![Code Coverage](https://scrutinizer-ci.com/g/carono/yii2-migrate/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/carono/yii2-migrate/?branch=master)

MigrationTrait   
=================
Для расширения возможностей миграции, необходимо добавить трейт **\carono\yii2migrate\traits\MigrationTrait** или наследовать класс миграции от **\carono\yii2migrate\Migration**

Работа с внешними ключами
=========================

Создание таблицы, с указанием внешнего ключа, только по имени таблицы
```php
$this->createTable('{{%user}}', [
    'id' => $this->primaryKey(),
    'company_id' => $this->foreignKey('{{%company}}')
]);
```        
        
Добавление колонки с внешним ключем
```php
$this->addColumn('{{%user}}', 'company_id', $this->foreignKey('{{%company}}'));
```        

Добавление внешнего ключа на существующую колонку
```php
$this->alterColumn('{{%user}}', 'company_id', $this->foreignKey('{{%company}}'));
```

Удаление внешнего ключа по имени колонки
```php
$this->dropForeignKeyByColumn('{{%user}}', 'company_id');
```

Полная миграция
```php
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

