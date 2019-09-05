<?php 
class Scryfall {
	private static $serviceUrl = "https://api.scryfall.com/cards";

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
		$serviceUrl = self::$serviceUrl;
		$apiAddress = "{$serviceUrl}/{$method}?{$argumentsString}";

		$requestResult = json_decode(file_get_contents($apiAddress));
		return $requestResult;
	}
	
}
?>
