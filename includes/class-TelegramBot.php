<?php
class TelegramBot {
	// Static properties
	private static $serviceUrl = "https://api.telegram.org/bot";

	// Object properties
	private $token = "";
	public $contact;

	// TODO: ubrat' nafig
	// Function to generate urlencoded arguments
	private static function generateQueryStringArgument($name, $value) {
		$encodedValue = urlencode($value);
		$result = "{$name}={$encodedValue}";
		return $result;
	}

	public function __construct($botToken) {
		$this->token = $botToken;
		
	}

	public function request($method, $fileToSend) {
		$serviceUrl = self::$serviceUrl;
		$botToken = $this->token;
		$curl = "{$serviceUrl}{$botToken}/{$method}";
		$curlRequest = curl_init($curl);
		$headers = [
			"Content-Type: application/json"
		];
		curl_setopt($curlRequest, CURLOPT_HTTPHEADER, $headers);
		// Указываем, что это POST запрос
		curl_setopt($curlRequest, CURLOPT_POST, true);
		// Указываем, что нам нужно сохранить вывод сервера
		curl_setopt($curlRequest, CURLOPT_RETURNTRANSFER, true);
		// Указываем, что мы его отправляем
		curl_setopt($curlRequest, CURLOPT_POSTFIELDS, $fileToSend);
		// Отправляем запрос, получаем ответ
		$serverOutput = curl_exec($curlRequest);
		// Закрываем запрос
		curl_close($curlRequest);
		return json_encode($serverOutput);
	}
}
