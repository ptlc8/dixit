<?php
session_start();
if (!isset($_SESSION["dixitGameId"], $_SESSION["dixitPlayerId"])) exit("notingame");

include("init.php");

$result = sendRequest("SELECT * FROM DIXIT WHERE id = '", $_SESSION["dixitGameId"], "'");
if ($result->num_rows === 0) exit("nogame");

$game = json_decode($result->fetch_assoc()["data"]);

if ($game->state != "room")
    exit("playing");

foreach ($game->players as $index => $player)
    if ($player->id == $_SESSION["dixitPlayerId"])
        $playerIndex = $index;

if ($playerIndex !== 0)
	exit("notowner");

if (count($game->players) < 3) {
    exit("needmoreplayers");
}
/*if (count($game->players) > 9) {
    
}*/

$theme = isset($_POST["theme"]) ? $_POST["theme"] : null;

$cards=[];
/*for ($i = 0; count($cards) < count($game->players)*7; $i++) {
	$card = json_decode(file_get_contents("https://api.waifu.pics/sfw/waifu", false))->url;
	if (in_array($card, $cards)) continue;
	array_push($cards, $card);
	/*foreach (json_decode($response)->files as $card)
		array_push($cards, $card);*/
/*}*/
if ($theme == "waifu") {
	$url = "https://api.waifu.pics/many/sfw/waifu";
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			"Content-Type: application/json",
		));
	while (count($cards) < count($game->players)*7*2) {
		curl_setopt($curl, CURLOPT_POSTFIELDS, "{}"); // TODO!!! : remove already getted files
		$cards = array_merge($cards, json_decode(curl_exec($curl))->files);
	}
	curl_close($curl);
} else if ($theme == "ac") {
	$villagers = (array)json_decode(file_get_contents("https://acnhapi.com/v1/Villagers"));
	while (count($cards) < count($game->players)*7*2) { // TODO: replace with shuffle ?
		$image = $villagers[array_rand($villagers, 1)]->image_uri;
		if (!in_array($image, $cards))
			array_push($cards, $image);
	}
} else if($theme = "pictures") {
	for ($page = 1; count($cards) < count($game->players)*7*2*3; $page++) { // *3 pour faire de la diversité
		$cards = array_merge($cards, array_map(function($r) {return $r->largeImageURL;}, json_decode(file_get_contents("https://pixabay.com/api/?key=15601609-98b6a08239d3ab0b6000e1f4f&image_type=photo&per_page=63&page=".$page))->hits));
	}
	shuffle($cards);
} else {
	for ($page = 1; count($cards) < count($game->players)*7*2*3; $page++) { // *3 pour faire de la diversité
		$cards = array_merge($cards, array_map(function($r) {return $r->largeImageURL;}, json_decode(file_get_contents("https://pixabay.com/api/?key=15601609-98b6a08239d3ab0b6000e1f4f&per_page=63&page=".$page))->hits));
	}
	shuffle($cards);
}

$winCondition = isset($_POST["winCondition"]) ? $_POST["winCondition"] : null;
switch ($winCondition) {
    case "score":
        $game->winCondition = "score";
        $game->winScore = isset($_POST["winScore"]) ? $_POST["winScore"] : 20;
        break;
    case "turn":
    default:
        $game->winCondition = "turn";
        $game->winTurn = isset($_POST["winTurn"]) ? $_POST["winTurn"] : 2;
}

$game->hands = [];
foreach ($game->players as $i=>$player) {
	$game->hands[$i] = [];
	$player->score = 0;
}
for ($i = 0; $i < count($game->players)*7; $i++) {
	array_push($game->hands[$i%count($game->players)], array_splice($cards, $i, 1)[0]);
}
$game->draw = $cards;

$game->dealer = 0;
$game->state = "game";
$game->phase = 0;
$game->turn = 0;
array_push($game->events, array("name" => "start", "dealer" => $game->dealer));

sendRequest("UPDATE DIXIT SET data = '", json_encode($game), "' WHERE id = '", $_SESSION["dixitGameId"], "'");
echo "started";
?>