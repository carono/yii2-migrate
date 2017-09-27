Трейт для помощи в создании базы  

`self::foreignKey($table)` - установка Foreign Key на поле
`self::pivot($table)` - создание сводной таблицы из 2х primary key

```php
<?php

class m170812_175803_test extends \yii\db\Migration
{
    use \carono\yii2migrate\traits\MigrationTrait;

    public function newColumns()
    {
        return [
            'company' => [
                'user_id' => self::foreignKey('user')->notNull()->onDeleteNull(), // Создаём FK на user, с правилом SET NULL при удалении
                'users' => self::pivot('user')->tableName('employees') // Создаём сводную таблицу employees
            ]
        ];
    }

    public function newTables()
    {
        return [
            'user' => [
                'id' => self::primaryKey()
            ],
            'photo' => [
                'id' => self::primaryKey(),
                'user_id' => self::integer()
            ],
            'company' => [
                'id' => self::primaryKey(),
                'name' => self::string(),
                'directors' => self::pivot('user', 'director_id')  // Создаём сводную таблицу pv_company_directors
            ]
        ];
    }

    public function newIndex()
    {
        return [
            ['company', 'name']
        ];
    }

    public function safeUp()
    {
        $this->upNewTables();
        $this->upNewColumns();
        $this->alterColumn('photo', 'user_id', self::foreignKey('user'));
        $this->upNewIndex();
    }

    public function safeDown()
    {
        $this->downNewIndex();
        $this->dropForeignKeyByColumn('photo', 'user_id');
        $this->downNewColumns();
        $this->downNewTables();
    }
}
```