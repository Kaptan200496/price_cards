<?php 
// TODO:
/*

	+ Хранение настроек
	+ Запись настроек
	+ Получение настроек

*/

class SettingsProvider {
	// Статическое свойство для хранения настроек
	private static $settings = array();

	// Задание настройки
	// $name (string) - название настройки
	// $value (mixed) - значение настройки
	public static function setSetting($name, $value) {

		self::$settings[$name] = $value;
	}

	// Получение настройки
	// $name (string) - название настройки
	public static function getSetting($name) {
		if(isset(self::$settings[$name])) {
			return self::$settings[$name];
		}
		else {
			return NULL;
		}
	}
}

?>
