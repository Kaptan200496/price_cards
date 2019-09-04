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
$sf = new Scryfall();
$pb = new PrivatBank();
$bot = new TelegramBot("bottoken");

// STEP 1: Получить данные в json и перевести их в объект
$requestText = file_get_contents("php://input");
$requestObject = json_decode($requestText);
$cardName = $requestObject->message->text;

// STEP 2: Проверить есть ли уже данные о карте в базе данных
$validationEx = "SELECT * FROM cards WHERE Name = '{$cardName}'";
$dbResponse = Database::query($validationEx);
$cardInDatabase = ($dbResponse->num_rows > 0);

// STEP 3: Если есть, то проверить их возраст
$olderThan12Hours = false;
$card = new stdClass();
if($cardInDatabase) {
	$dbRow = $dbResponse->fetch_assoc();
	$card->date = intval($dbRow["Date"]);
	$card->name = $dbRow["Name"];
	$card->price = floatval($dbRow["Price"]);
	$card->address = $dbRow["Address"];
	$olderThan12Hours = ($card->date < (time() - 3600 * 12));
}

// STEP 4: Если они старше 12 часов, то запросить новые данные
if((!$cardInDatabase) || $olderThan12Hours) {
	$cardData = $sf->request("named", ["exact" => $cardName]);
	file_put_contents("cardData.txt", json_encode($cardData));
	$card->name = $cardData->name;
	$card->price = $cardData->prices->usd;
	$card->address = $cardData->image_uris->large;
}
if(!$cardInDatabase) {
	$insertEx = "INSERT INTO cards (
		Name, 
		Address, 
		Price
	) VALUES (
		'{$card->name}', 
		'{$card->address}', 
		'{$card->price}'
	)";
	Database::query($insertEx);
}
else if($olderThan12Hours) {
	$updateEx = "UPDATE cards 
	SET  
		Address = '{$card->address}', 
		Price = '{$card->price}'
	WHERE 
		Name = '{$card->name}'
	";
	Database::query($updateEx);
}

// STEP 6: Проверить данные о курсе в базе данных
$selectEx = "SELECT * FROM price";
$response = Database::query($selectEx);
$rateInDB = ($response->num_rows > 0);

$rate = new stdClass();
if($rateInDB) {
	$rateRow = $response->fetch_assoc();
	$rate->name = $rateRow["Name_currency"];
	$rate->rate = $rateRow["RateExchangeUAH"];
	$rate->date = $rateRow["Date"];
	$olderThan12Hours = ($rate->date < (time() - 3600 * 12));
}
if(!$rateInDB || $olderThan12Hours) {
	$rawArguments = [
		"json" => 1,
		"exchange" => 1,
		"coursid" => 11 
	];
	$rateData = $pb->request("pubinfo", $rawArguments);
	$rate->name = $rateData[0]->ccy;
	$rate->rate = $rateData[0]->sale;
}
if(!$rateInDB) {
	$insertExchange = "INSERT INTO price (
		Name_currency, 
		RateExchangeUAH
	) VALUES (
		'{$rate->name}', 
		'{$rate->rate}'
	)";
	Database::query($insertExchange);
}
else if($olderThan12Hours) {
	$updateExchange = "UPDATE price 
		SET 
			RateExchangeUAH = '{$rate->rate}'
		WHERE
			Name_currency = 'USD'
	";
	Database::query($updateExchange);
}
// STEP 10: Вычислить цену в гривнах
$priceInUAH = $rate->rate * $card->price;
// STEP 11: Вывести фото
$methodPhoto = "sendPhoto";
$rawArgumentsPhoto = [
	"chat_id" => $requestObject->message->chat->id,
	"photo" => $card->address
];
$responsePhoto = $bot->request($methodPhoto, $rawArgumentsPhoto);
// STEP 12: Вывести название карты и цену в гривнах. Рядом в скобках цену в долларах.
$priceRow = $cardName . " " . "-" . " " . $priceInUAH . " " . "(" . $card->price . "$)";
$methodText = "sendMessage";
$rawArgumentsText = [
	"chat_id" => $requestObject->message->chat->id,
	"text" => $priceRow
];
$responseText = $bot->request($methodText, $rawArgumentsText);
?>

