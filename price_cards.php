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
// STEP 3: Если есть, то проверить их возраст
if($dbResponse->num_rows == 1) {
	$responseRow = $dbResponse->fetch_assoc();
	$date = intval($responseRow["Date"]);
}
	//file_put_contents("Date.txt", json_encode($Date));
// STEP 4: Если они старше 12 часов, то запросить новые данные
$timeNow = time();
$twelveHours = 60*60*12;
$sf = new Scryfall();
if(($timeNow - $date) > $twelveHours) {
	// запрашиваем новые данные
	$rawArguments = [
		"exact" => $cardName
	];
	$method = "named";
	$sfResponse = $sf->request($method, $rawArguments);
}


// STEP 5: При получении записать их в базу данных или обновить если уже были.
// STEP 6: Проверить данные о курсе в базе данных
// STEP 7: Если данные есть, то проверить их возраст
// STEP 8: Если возраст больше 12 часов, то запросить новые
// STEP 9: При получении записать данные о курсе в базу данных или обновить есдли уже были
// STEP 10: Вычислить цену в гривнах
// STEP 11: Вывести фото
// STEP 12: Вывести название карты и цену в гривнах. Рядом в скобках цену в долларах.

?>
