[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/carono/yii2-migrate/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/carono/yii2-migrate/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/carono/yii2-migrate/v/stable)](https://packagist.org/packages/carono/yii2-migrate)
[![Total Downloads](https://poser.pugx.org/carono/yii2-migrate/downloads)](https://packagist.org/packages/carono/yii2-migrate)
[![License](https://poser.pugx.org/carono/yii2-migrate/license)](https://packagist.org/packages/carono/yii2-migrate)
[![Build Status](https://travis-ci.org/carono/yii2-migrate.svg?branch=master)](https://travis-ci.org/carono/yii2-migrate)
[![Code Coverage](https://scrutinizer-ci.com/g/carono/yii2-migrate/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/carono/yii2-migrate/?branch=master)

[[ENG](README.md)] [[RUS](README_RUS.md)]

MigrationTrait   
=================
To expand the migration capabilities, you must add a trait **\carono\yii2migrate\traits\MigrationTrait** or extend the migration class from **\carono\yii2migrate\Migration**

public function tableOptions()
-
Return the array with the settings for creating tables, where the key is the name of the db driver.  
When creating tables through createTable(), if no properties are specified, they will be picked up from this function
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
Return an array where the key is the name of the table, and the values are columns with types.  
If you call the **$this->upNewTables()** function, all specified tables will be created via createTable()  
If you call the function **$this->downNewTables()**, all specified table will be deleted using dropTable()
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
Return an array where the key is the name of an existing table and the values are columns with types.  
If you call the function **$this->upNewColumns()**, all specified columns will be created using addColumn()  
If you call the function **$this->downNewColumns()**, all specified columns will be deleted after dropColumn()
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
Return an array where the key is the name of an existing table and the values are the index parameters via $this->index()    
If you call the **$this->upNewIndex()** function, all specified indexes will be created via createIndex()  
If you call the function **$this->downNewIndex()**, all specified columns will be deleted using the dropIndex()
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
    
Working with foreign keys
=========================

Create a table, specifying a foreign key, by table name only
```php
$this->createTable('{{%user}}', [
    'id' => $this->primaryKey(),
    'company_id' => $this->foreignKey('{{%company}}')
]);
```        
        
Adding a foreign key column
```php
$this->addColumn('{{%user}}', 'company_id', $this->foreignKey('{{%company}}'));
```        

Adding a foreign key to an existing column
```php
$this->alterColumn('{{%user}}', 'company_id', $this->foreignKey('{{%company}}'));
```

Adding foreign key with auto name
```php
$this->addForeignKey(null, '{{%user}}', 'photo_id', '{{%photo}}', 'id');
```

Delete foreign key by column name
```php
$this->dropForeignKeyByColumn('{{%user}}', 'company_id');
```

Working with indexes
==================
Create an index with an automatic name
```php
$this->createIndex(null, '{{%user}}', 'name');
```

Deleting an index by column name
```php
$this->dropIndexByColumn('{{%user}}', 'name');
```

**(!)** It is necessary to pay attention, if there are several columns on the index, then it is necessary to specify them in the necessary sequence.
If there are several indexes with such a set and sequence, all of them will be deleted.
**(!)** Does not work correctly with postgreSQL (https://github.com/yiisoft/yii2/issues/16639)
```php
$this->createIndex(null, '{{%user}}', ['name', 'surname']);
$this->dropIndexByColumn('{{%user}}', ['name', 'surname']);
```

Pivot tables
===============
To implement many-to-many tables, you can use the $this->pivot() function, a table with 2 keys will be created.
The names of the keys in the PivotTable are generated automatically, so they can be set via refColumn() and sourceColumn()

Create a PivotTable by creating a table.
The result is the table {{%user}}[id] {{%pv_user_photos}}[user_id, photo_id]
```php
$this->createTable('{{%user}}', ['id' => $this->primaryKey(), 'photos' => $this->pivot('{{%photo}}')]);
```

Create a PivotTable by adding a column.
```php
$this->addColumn('{{%user}}', 'photos', $this->pivot('{{%photo}}'));
```        

Specify the name of the PivotTable
```php
$this->addColumn('{{%user}}', 'photos', $this->pivot('{{%photo}}')->tableName('{{%album}}'));
```   

PivotTrait
==========
Trait to help work with pivot tables.

`$company` - table model Company (requires trait PivotTrait)  
`$user` - model of table User  
`PvCompanyDirector` - a pivot table of the two models: company and user  
`Pivot table` - a table which contains 2 primary key


Added to the table PvCompanyDirector a bunch of the end user company
```php
$company->addPivot($user, PvCompanyDirector::class, $attributes = []);
```

Get the PvCompanyDirector model for the company-user bundle
```php
$company->getPivot($model, PvCompanyDirector::class, $condition = []);
```

Removed a bunch of the user-company
```php
$company->deletePivot($model, PvCompanyDirector::class);
```

Remove all users from PvCompanyDirector for this company
```php
$company->deletePivots(PvCompanyDirector::class);
```

Save to a temporary link variable so that you can use them later
```php
$company->storagePivot($user, PvCompanyDirector::class, ['hire_at' => '2010-01-01 00:00:00']);
$users = $company->getStoragePivots(PvCompanyDirector::class)); // The list of models that have been added earlier
```

The preservation of the ties of a temporary variable.  
$clear - completely clears all links before adding
```php
$company->savePivots($clear); // Save all links added via storagePivot
```

The change in behavior of the migration class
=====================================
public function createIndex($name, $table, $columns, $unique = false)  
-
* $name you can specify null to generate the index name automatically

public function createTable($table, $columns, $options = null)
-
* $columns supports the $this->foreignKey() and $this->pivot()
* if $options is not specified, options are pulled from $this->tableOptions, if there are no options, then from **@tableOptions** to $columns

public function alterColumn($table, $column, $type)
-
* $type supports type $this->foreignKey()

public function addColumn($table, $column, $type)
-
* $type supports type $this->foreignKey() and $this->pivot()

public function addPrimaryKey($name, $table, $columns)
-
* $name you can specify null to generate the index name automatically

public function dropColumn($table, $column)
-
* Before deleting the table, foreign keys are cleared

An example of a complete migration
==================================
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
                'parents' => $this->pivot('{{%user}}') // Create a pivot table on itself
            ],
            '{{%photo}}' => [
                'id' => $this->primaryKey(),
                'user_id' => $this->integer()
            ],
            '{{%company}}' => [
                'id' => $this->primaryKey(),
                'name' => $this->string(),
                // Create a pivot table {{%pv_company_directors}}
                'directors' => $this->pivot('{{%user}}', 'director_id')->columns(
                    [
                        'hire_at' => $this->dateTime(),
                        // A foreign key with SET NULL rule is when you remove data from {{%user}}
                        'hired_id' => $this->foreignKey('{{%user}}', null)->onDeleteNull()->unsigned()
                    ]
                ),
                '@tableOptions' => [
                    'mysql' => 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB'
                ]
            ],
            '{{%pv_company_user_photo}}' => [
                // Create a PivotTable of several keys
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
                // Create a FK to user
                'user_id' => $this->foreignKey('{{%user}}'),
                // Create a pivot table employees
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
        // Add a FK to an existing column
        $this->alterColumn('{{%photo}}', 'user_id', $this->foreignKey('{{%user}}'));
        $this->upNewIndex();
        $this->createIndex(null, '{{%user}}', 'name');
    }

    public function safeDown()
    {
        $this->dropIndexByColumn('{{%user}}', 'name');
        $this->downNewIndex();
        // Remove the FK on the column name
        $this->dropForeignKeyByColumn('{{%photo}}', 'user_id');
        $this->downNewColumns();
        $this->downNewTables();
    }
}
```
The resulting database schema
=========================
<p align="center">
    <img src="https://raw.github.com/carono/yii2-migrate/HEAD/schema.png">
</p>

