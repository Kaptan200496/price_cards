<?php 
class PrivatBank {
	// Static properties
	private static $serviceUrl = "https://api.privatbank.ua/p24api";

	function generateQueryStringArgument($name, $value) {
		$encodedValue = urlencode($value);
		$result = "{$name}={$encodedValue}";
		return $result;
	}
	public function __construct() {
		// empty
	}
	public function request($method, $rawArguments = []) {
		$arguments = [];
		foreach ($rawArguments as $argName => $argValue) {
			array_push($arguments, self::generateQueryStringArgument($argName, $argValue));
		}
		$argumentsString = implode("&", $arguments);
		file_put_contents("argumentsString.txt", $argumentsString);
		$serviceUrl = self::$serviceUrl;
		$apiAddress = "{$serviceUrl}/{$method}?{$argumentsString}";

		$requestResult = json_decode(file_get_contents($apiAddress));

		file_put_contents("apiAddress.txt", $apiAddress);
		return $requestResult;
	}

}
