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