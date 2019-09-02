<?php 
header('Content-Type: application/json');
$includes = array(
	"class-database.php",
	"class-PrivatBank.php",
	"class-TelegramBot.php",
	"class-scryfall.php",
	"class-settings-provider.php"
);
foreach ($includes as $fileToInclude) {
	require_once("includes/" . $fileToInclude);
}
require_once("settings.php");
Database::connect();
// STEP 1: Получить данные в json и перевести их в объект
$requestText = file_get_contents("php://input");
$requestObject = json_decode($requestText);
	//file_put_contents("requestoject.txt", json_encode($requestObject));
$cardName = $requestObject->message->text;
	//file_put_contents("cardname.txt", $cardName);
// STEP 2: Проверить есть ли уже данные о карте в базе данных
$validationEx = "SELECT * FROM cards WHERE Name = '{$cardName}'";
$dbResponse = Database::query($validationEx);
$date = "";
$address = $sfResponse->image_uris->large;
$price = $sfResponse->price->usd;
$sf = new Scryfall();
$pb = new PrivatBank();
$bot = new TelegramBot("686794783:AAFvJ6_yvAt2Zt0jilrZss26atxYyuEkWao");
$updateEx = "UPDATE cards SET Name = '{$cardName}', Address = '{$address}', Price = '{$price}' WHERE Name = '{$cardName}'";
$insertEx = "INSERT INTO cards (Name, Address, Price) VALUES ('{$cardName}', '{$address}', '{$price}')";
// STEP 3: Если есть, то проверить их возраст
if($dbResponse->num_rows == 1) {
	$responseRow = $dbResponse->fetch_assoc();
	$date = intval($responseRow["Date"]);
}
	//file_put_contents("Date.txt", json_encode($Date));
else {
	$rawArguments = [
		"exact" => $cardName
	];
	$method = "named";
	$sfResponse = $sf->request($method, $rawArguments);
	Datavase::query($insertEx);
}
// STEP 4: Если они старше 12 часов, то запросить новые данные
$timeNow = time();
$twelveHours = 60;
if(($timeNow - $date) > $twelveHours) {
	// запрашиваем новые данные
	$rawArguments = [
		"exact" => $cardName
	];
	$method = "named";
	$sfResponse = $sf->request($method, $rawArguments);
// STEP 5: При получении записать их в базу данных или обновить если уже были.
	Database::query($updateEx);

}
	//file_put_contents("sfresponse.txt", json_encode($sfResponse));

// STEP 6: Проверить данные о курсе в базе данных
$selectEx = "SELECT RateExchangeUAH FROM price";
$response = Database::query($selectEx);
$rateExchangeUAH = $pbResponse[0]->sale;
$insertExchange = "INSERT INTO price (Name_currency, RateExchangeUAH) VALUES('USD', '{$rateExchangeUAH}')";
$updateExchange = "UPDATE price SET RateExchangeUAH = '{$rateExchangeUAH}' WHERE Name_currency = 'USD'";
if($response->num_rows == 1) {
	$row = $response->fetch_assoc();
	$dateRow = intval($row["Date"]);
}
else {
	$rawArguments = [
		"json" => 1,
		"exchange" => 1,
		"coursid" => 11 
	];
	$method = "pubinfo";
	$pbResponse = $pb->request($method, $rawArguments);
	Database::query($insertExchange);
}
// STEP 7: Если данные есть, то проверить их возраст
if(($timeNow - $dateRow) > $twelveHours) {
// STEP 8: Если возраст больше 12 часов, то запросить новые
	$rawArguments = [
		"json" => 1,
		"exchange" => 1,
		"coursid" => 11 
	];
	$method = "pubinfo";
	$pbResponse = $pb->request($method, $rawArguments);
// STEP 9: При получении записать данные о курсе в базу данных или обновить есдли уже были
	Database::query($updateExchange);
}
// STEP 10: Вычислить цену в гривнах
$priceInUAH = $rateExchangeUAH * $price;
// STEP 11: Вывести фото
$methodPhoto = "sendPhoto";
$rawArgumentsPhoto = [
	"chat_id" => $requestObject->chat_id,
	"photo" => $address
];
$responsePhoto = $bot->request($methodPhoto, $rawArgumentsPhoto);
// STEP 12: Вывести название карты и цену в гривнах. Рядом в скобках цену в долларах.
$methodText = "sendMessage";
$rawArgumentsText = [
	"chat_id" => $requestObject->chat_id,
	"text" => $priceInUAH
];
$responseText = $bot->request($methodText, $rawArgumentsText);
?>
