[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/carono/yii2-migrate/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/carono/yii2-migrate/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/carono/yii2-migrate/v/stable)](https://packagist.org/packages/carono/yii2-migrate)
[![Total Downloads](https://poser.pugx.org/carono/yii2-migrate/downloads)](https://packagist.org/packages/carono/yii2-migrate)
[![License](https://poser.pugx.org/carono/yii2-migrate/license)](https://packagist.org/packages/carono/yii2-migrate)
[![Build Status](https://travis-ci.org/carono/yii2-migrate.svg?branch=master)](https://travis-ci.org/carono/yii2-migrate)
[![Code Coverage](https://scrutinizer-ci.com/g/carono/yii2-migrate/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/carono/yii2-migrate/?branch=master)

MigrationTrait   
=================
Для расширения возможностей миграции, необходимо добавить трейт **\carono\yii2migrate\traits\MigrationTrait** или наследовать класс миграции от **\carono\yii2migrate\Migration**

public function tableOptions()
-
Возвращаем массив с настройками для создания таблиц, где ключ - название драйвера.  
При создании таблиц через createTable(), если не указаны свойства, то они будут подхватываться из этой функции
```php
    public function tableOptions()
    {
        return [
            'mysql' => 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB'
        ];
    }
```    

public function newTables()
-
Возвращаем массив, где ключ это название таблицы, а значения это колонки с типами.  
Если вызвать функцию **$this->upNewTables()**, все указанные таблицы будут созданы через createTable()  
Если вызвать функцию **$this->downNewTables()**, все указанные таблицы будут удалены через dropTable()  
```php
    public function newTables()
    {
        return [
            '{{%logs}}' => [
                'data' => $this->string(),
                '@tableOptions' => [
                    'mysql' => 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=MyISAM'
                ]
            ]
        ];
    }
    
    public function safeUp()
    {
        $this->upNewTables();
    }
    
    public function safeUp()
    {
        $this->downNewTables();
    }
```            

pubic function newColumns()
-
Возвращаем массив, где ключ это название существующей таблицы, а значения это колонки с типами.  
Если вызвать функцию **$this->upNewColumns()**, все указанные столбцы будут созданы через addColumn()  
Если вызвать функцию **$this->downNewColumns()**, все указанные столбцы будут удалены через dropColumn()  
```php
    public function newColumns()
    {
        return [
            '{{%company}}' => [
                'address' => $this->string(),
                'is_active' => $this->boolean()
            ]
        ];
    }
    
    public function safeUp()
    {
        $this->upNewColumns();
    }
    
    public function safeUp()
    {
        $this->downNewColumns();
    }    
```

public function newIndex()
-
Возвращаем массив, где ключ это название существующей таблицы, а значения это параметры индекса через $this->index()    
Если вызвать функцию **$this->upNewIndex()**, все указанные индексы будут созданы через createIndex()  
Если вызвать функцию **$this->downNewIndex()**, все указанные столбцы будут удалены через dropIndex() 
```php
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
        $this->upNewIndex();
    }
    
    public function safeUp()
    {
        $this->downNewIndex();
    }        
```    
    
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

Добавление внешнего ключа с автоматическим именем
```php
$this->addForeignKey(null, '{{%user}}', 'photo_id', '{{%photo}}', 'id');
```

Удаление внешнего ключа по имени колонки
```php
$this->dropForeignKeyByColumn('{{%user}}', 'company_id');
```

Работа с индексами
==================
Создание индекса с автоматическим именем
```php
$this->createIndex(null, '{{%user}}', 'name');
```

Удаление индекса по имени колонки
```php
$this->dropIndexByColumn('{{%user}}', 'name');
```

**(!)** Необходимо обратить внимение, если на индексе находится несколько колонок, то нужно указать их в нужной последовательности.
Если с таким набором и последовательностью несколько индексов - все они будут удалены.
**(!)** Не корректно работает с postgreSQL (https://github.com/yiisoft/yii2/issues/16639)
```php
$this->createIndex(null, '{{%user}}', ['name', 'surname']);
$this->dropIndexByColumn('{{%user}}', ['name', 'surname']);
```

Сводные таблицы
===============
Для реализации таблиц многие-ко-многим можно использовать функцию $this->pivot(), будет создана таблица с 2мя ключами.
Имена ключей в сводной таблице формируются автоматически, так их можно задать через refColumn() и sourceColumn()

Создание сводной таблицы через создание таблицы.
В итоге получаем таблицы {{%user}}[id] и {{%pv_user_photos}}[user_id, photo_id]
```php
$this->createTable('{{%user}}', ['id' => $this->primaryKey(), 'photos' => $this->pivot('{{%photo}}')]);
```

Создание сводной таблицы через добавление колонки.
```php
$this->addColumn('{{%user}}', 'photos', $this->pivot('{{%photo}}'));
```        

Указание названия сводной таблицы
```php
$this->addColumn('{{%user}}', 'photos', $this->pivot('{{%photo}}')->tableName('{{%album}}'));
```   

PivotTrait
==========
Трейт для помощи работы со сводными таблицами.

`$company` - модель таблицы Company (требуется трейт PivotTrait)  
`$user` - модель таблицы User  
`PvCompanyDirector` - сводная таблица двух моделей: компания-пользователь  
`Сводная таблица` - таблица, в которой находятся 2 primary key


Добавляем в таблицу PvCompanyDirector связку компания-пользователь
```php
$company->addPivot($user, PvCompanyDirector::class,$attributes = []);
```

Получаем модель PvCompanyDirector для связки компания-пользователь
```php
$company->getPivot($model, PvCompanyDirector::class, $condition = []);
```

Удаляем связку компания-пользователь
```php
$company->deletePivot($model, PvCompanyDirector::class);
```

Удаляем всех пользователей из PvCompanyDirector для этой компании
```php
$company->deletePivots(PvCompanyDirector::class);
```

Сохраняем во временную переменную связи, чтобы можно было использовать их потом
```php
$company->storagePivot($user, PvCompanyDirector::class, ['hire_at' => '2010-01-01 00:00:00']);
$users = $company->getStoragePivots(PvCompanyDirector::class)); // Список моделей, что добавили ранее
```

Сохранение связей из временной переменной.  
$clear - Полностью очищать все связи перед добавлением
```php
$company->savePivots($clear); // Сохраним все связи, что добавили через storagePivot
```

Изменение в поведении класса миграции
=====================================
public function createIndex($name, $table, $columns, $unique = false)  
-
* $name можно указать null, чтобы сформировать имя индекса автоматически

public function createTable($table, $columns, $options = null)
-
* $columns поддерживает типы $this->foreignKey() и $this->pivot()
* если не указан $options, опции подтягиваются из $this->tableOptions, если там нет опций, то из **@tableOptions** в $columns

public function alterColumn($table, $column, $type)
-
* $type поддерживает тип $this->foreignKey()

public function addColumn($table, $column, $type)
-
* $type поддерживает тип $this->foreignKey() и $this->pivot()

public function addPrimaryKey($name, $table, $columns)
-
* $name можно указать null, чтобы сформировать имя индекса автоматически

public function dropColumn($table, $column)
-
* Перед удалением таблицы, очищаются внешний ключи

Пример полной миграции
======================
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
                ),
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
                $this->index()->columns(['name'])->unique(true)->length(10)
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
Результирующая схема базы
=========================
<p align="center">
    <img src="https://raw.github.com/carono/yii2-migrate/HEAD/schema.png">
</p>

