# DAO
Реализация Yii DAO без лишних зависимостей

### 1. Соединение с БД
```php
$db = new \ova777\DAO\Connection(
    'mysql:host=localhost;dbname=test', //DSN 
    'user', //Login
    'pwd', //Password
    $options //PDO - параметры подключения (опционально)
);
```

### 2. Выполнение SQL-запросов
```php
$cmd = $db->createCommand($sql); //Создаем новую команду
```

Варианты выполнения SQL-запроса:
```php
$result = $cmd->execute(); //Выполнение любого запроса (возвращает bool)
$reader = $cmd->query(); //Выполнение запроса SELECT 
$rows = $cmd->queryAll(); //Возвращает все строки результата запроса
$row = $cmd->queryRow(); //Возвращает первую строку результата запроса
$column = $cmd->queryColumn(); //Возвращает первый столбец результата запроса
$value = $cmd->queryScalar(); //Возвращает значение первого поля первой строки результата запроса
```

### 3. Обработка результатов запроса
Метод $cmd->query(); возвращает экземпляр класса Reader, результат можно получить следующими способами:
```php
$reader = $cmd->query();
//Вариан 1
while(($row = $reader->read())!==false) { ... }
//Вариант 2
foreach($reader as $row) { ... }
//Вариант 3 - получение всех строк сразу
$rows = $reader->readAll();
```

### 4. Использование транзакций
```php
$db->beginTransaction();
try {
	$db->createCommand($sql1)->execute();
	$db->createCommand($sql2)->execute();
	//...
	$db->commit();
} catch (Exception $e) {
	$db->rollback();
	echo 'Error: '.$e->getMessage();
}
```

### 5. Привязка параметров
```php
//Создаем команду
$cmd = $db->createCommand('INSERT INTO table SET col=:col');
//Провязываем переменную $value к параметру :col
$cmd->bindParam(':col', $value, PDO::PARAM_STR);
//Вставляем строку
$value = 'foo';
$cmd->execute();
//Вставляем следующую строку с новыми значениями
$value = 'bar';
$cmd->execute();
```

### 6. Привязка значений
Вместо привязки переменных к запросу, можно напрямую привязать значения
```php
$cmd = $db->createCommand('SELECT * FROM table WHERE a=:a AND b=:b AND c=:c');
//Привязка одного значения
$cmd->bindValue(':a', 'A_VALUE', PDO::PARAM_STR);
//Привязка нескольких значений
$cmd->bindValues(array(':b'=>'B_VALUE', ':c'=>'C_VALUE'));
//Получаем результат
$rows = $cmd->queryAll();
```

### 7. Использование префиксов таблиц
Для использования префиксов таблиц необходимо установить свойство Connection::tablePrefix.
Затем в SQL-выражениях можно использовать {{tableName}} для имен таблиц.
```php
$db->tablePrefix = "tbl_";
$rows = $db->createCommand('SELECT * FROM {{table}}')->queryAll();
//Название таблицы {{table}} будет преобразовано в tbl_table
```
### 8. Конструктор запросов
Конструктор запросов предоставляет объектно-ориентированный способ написания SQL-запросов (аналогично Yii).  
В данной библиотеки не поддерживаются запросы на изменение структуры БД

#### 8.1 Получение данных
```php
//Примеры
$rows = $db->createCommand()
    ->select('id, col1, col2 as cl2')
    ->from('{{table1}} t1')
    ->leftJoin('{{table2}} t2', 't1.id=t2.t1_id')
    ->where('id=:id', array(':id'=>id))
    ->order('id ASC')
    ->limit(5)
    ->offset(10)
    ->queryAll();
    
//union
$rows = $db->createCommand()
    ->select()
    ->from('{{one}}')
    ->union()
    ->select()
    ->from('{{two}}')
    ->queryAll();
    
//group, having
$rows = $db->createCommand()
	->select('*,COUNT(*) as cnt')
	->from('{{one}}')
	->group('col1')
	->having('cnt=2')
	->queryAll();
```

#### 8.2 Удаление данных
```php
//Без привязки значений
$db->createCommand()->delete('table_name', 'id=10');
//С привязкой
$db->createCommand()->delete('table_name', 'id=:id', array(':id'=>10));
```

#### 8.3 Обновление данных
```php
//Без условий
$db->createCommand()->update(
    'table_name', 
    array('column1'=>'value1', 'column2'=>'valu2')
);
//С условиями
$db->createCommand()->update(
    'table_name', 
    array('column1'=>'value1', 'column2'=>'valu2'),
    'id=:id',
    array(':id' => 1)
);
```

#### 8.4 Добавление данных
```php
$db->createCommand()->insert('table_name', array('column1'=>'value1', 'column2'=>'value2'));
```

#### 9. Получение SQL
```php
$db->createCommand()
    ->select('foo')
    ->from('tbl_bar')
    ->text;
//или
$db->createCommand()
    ->select('foo')
    ->from('tbl_bar')
    ->sql;
```