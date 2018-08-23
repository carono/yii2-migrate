1.1.1
=====
* В addColumn() добавлена возможность добавлять сводную таблицу через pivot() 
```
$this->addColumn('{{%user}}', 'photos', $this->pivot('{{%photo}}'));
```
* \carono\yii2migrate\PivotColumn - функции getName и setName заменены на setSuffix и getSuffix

1.1.0
=====
* \carono\yii2migrate\traits\MigrationTrait::formFkName - изменена сигнатура static -> nonstatic
* \carono\yii2migrate\ForeignKeyColumn::formName - изменена сигнатура static -> nonstatic 

1.0.16
======
* Добавлена возможность указывать опции для каждой отдельной таблицы. Для этого указывать дополнительный столбец с зарезервированным именем @tableOptions

```php
    public function newTables()
    {
        return [
            '{{%logs}}' => [
                'data' => $this->string(),
                '@tableOptions' => 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=MyISAM'
            ],
        ];
    }
```