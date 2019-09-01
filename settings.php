<?php 
// settings.php
// Файл настроек

// Функция initializeSettings
// Функция для задания первоначальных настроек
// Нужна, чтобы изолировать задание настроек от остального кода
// (void) - не имеет параметров
function initializeSettings() {
	// Первоначальные настройки
	// Ключ - категория/название ("db/host" значит категория db и название host)
	// Значение - значение настройки
	$initialSettings = array(
		// Настройки отладки
		"debug/enabled" => true,

		// Настройки базы данных
		"db/host" => "database hostname here",
		"db/user" => "database username here",
		"db/password" => "database password here",
		"db/database" => "database name here"
	);

	// Перебираем весь массив изначальных настроек и передаём их SettingsProvider
	foreach ($initialSettings as $settingName => $settingValue) {
		SettingsProvider::setSetting($settingName, $settingValue);
	}
}

initializeSettings();

?>
