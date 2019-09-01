<?php 
// class-database.php
// Класс для взаимодействия с базой данных

class Database {
	// Объект соединения с базой данных (mysqli)
	private static $connection;

	// Метод для отправки запроса
	// $sqlExpression (string) - SQL запрос
	public static function query($sqlExpression) {
		$queryResult = self::$connection->query($sqlExpression);

		if(self::$connection->errno) {
			// Формируем ассоциативный массив с полным описанием ошибки
			// Какой запрос подали и что при этом произошло
			$errorDetails = array(
				"expression" => $sqlExpression,
				"error" => self::$connection->error
			);
			
		}

		return $queryResult;
	}

	// Метод для подключения к базе данных
	public static function connect() {
		$host = SettingsProvider::getSetting("db/host");
		$user = SettingsProvider::getSetting("db/user");
		$password = SettingsProvider::getSetting("db/password");
		$database = SettingsProvider::getSetting("db/database");
		self::$connection = new mysqli($host, $user, $password, $database);
		// Если произошла ошибка подключения
		if(self::$connection->connect_error) {
			$errorDetails = array(
				"host" => $host,
				"user" => $user,
				"password" => $password,
				"database" => $database,
				"code" => self::$connection->connect_errno,
				"error" => self::$connection->connect_error
			);
		}
	}

	// Метод для дезинфекции строк
	public static function sanitizeString($stringToSanitize) {
		$result = self::$connection->real_escape_string($stringToSanitize);
		return $result;
	}

	// Метод для выполнения INSERT с объектом какого-то класса
	// $data (object:mixed) - объект, который будем вставлять
	// $tableName (string) - таблица, куда будет производиться вставка
	// $schema (array) - схема таблицы с указанием типов
	public static function insert($data, $tableName, $schema) {
		// Ассоциативный массив для подготовки данных ко вставке
		// Ключи — названия полей в таблице
		// Значения - значения для вставки в базу данных
		$preparedValues = array();

		// Перебираем свойства в схеме базы данных
		foreach ($schema as $propertyName => $fieldDescription) {
			// Берём значение из текущего свойства. 
			// Если оно не задано, то берём следующее свойство
			// Если задано — обрабатываем согласно схеме
			$valueExists = isset($data->{$propertyName});
			if(!$valueExists) {
				// coninue - следующая итерация цикла
				continue;
			}
			// Переменная для значения свойства
			$propertyValue = $data->{$propertyName};
			// Переменная для хранения целевого типа
			$targetType = $fieldDescription["type"];
			// Проверяем, соответствует ли значение типу
			$typeMatches = gettype($propertyValue) === $targetType;
			if(!$typeMatches) {
				continue;
			}

			// Подготавливаем данные к вставке в базу данных
			// Переменная для хранения значения
			$targetValue;
			if($targetType === "integer") {
				$targetValue = "{$propertyValue}";
			}
			else if($targetType === "string") {
				$propertyValue = self::sanitizeString($propertyValue);
				$targetValue = "'{$propertyValue}'";
			}
			else if($targetType === "boolean") {
				$propertyValue = intval($propertyValue);
				$targetValue = "{$propertyValue}";
			}
			// Переменная для хранения названия поля
			$targetField = $fieldDescription["field"];
			// Заносим поле и значение в массив заготовленных значений
			$preparedValues[$targetField] = $targetValue;
		}
		// Собираем SQL выражение из кусков
		$openingExpressionPart = "INSERT INTO {$tableName}";
		$fieldsExpressionPart = "(\n\t" . implode(",\n\t", array_keys($preparedValues)) . "\n)";
		$valuesExpressionPart = "(\n\t" . implode(",\n\t", array_values($preparedValues)) . "\n)";

		$sqlExpression = "{$openingExpressionPart} {$fieldsExpressionPart}VALUES {$valuesExpressionPart};";
		// Выполняем запрос
		self::query($sqlExpression);
		// Возваращаем id полседней вставленной записи
		return self::$connection->insert_id;
	}
}

?>
