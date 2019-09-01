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
		$this->contact = $this->request("getMe");
	}

	public function request($method, $rawAguments = []) {
		$arguments = [];
		foreach ($rawAguments as $argName => $argValue) {
			array_push($arguments, self::generateQueryStringArgument($argName, $argValue));
		}
		$argumentsString = implode("&", $arguments);

		$serviceUrl = self::$serviceUrl;
		$botToken = $this->token;
		$apiAddress = "{$serviceUrl}{$botToken}/{$method}?{$argumentsString}";
		
		$requestResult = json_decode(file_get_contents($apiAddress));
		return $requestResult->result;
	}
}
