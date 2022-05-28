<?php
session_start();
if (!isset($_SESSION["dixitGameId"], $_SESSION["dixitPlayerId"]))
    exit('{"state":"join"}');

include("init.php");

$result = sendRequest("SELECT data FROM DIXIT WHERE id = '", $_SESSION["dixitGameId"], "'");
if ($result->num_rows === 0)
    exit('{"state":"join"}'); //exit("unknow id");

$game = json_decode($result->fetch_assoc()["data"]);

// TODO: amputer les données de la game
$gameForUser = new stdClass();
$gameForUser->id = $_SESSION["dixitGameId"];
$gameForUser->state = $game->state;
$gameForUser->phase = $game->phase;
$gameForUser->events = $game->events;
if (isset($game->dealer)) $gameForUser->dealer = $game->dealer;
$gameForUser->players = $game->players;
$gameForUser->cards = $game->cards;
$gameForUser->sentence = isset($game->sentence) ? $game->sentence : "";
$gameForUser->selfId = $_SESSION["dixitPlayerId"];
foreach ($game->players as $index => $player)
    if ($player->id == $_SESSION["dixitPlayerId"]) {
        $gameForUser->selfIndex = $index;
        if (isset($game->hands, $game->hands[$index]))
            $gameForUser->hand = $game->hands[$index];
    }

if ($game->phase != 2) {
    for ($i = 0; $i < count($gameForUser->cards); $i++)
        $gameForUser->cards[$i] = "assets/back.png";
}

echo json_encode($gameForUser);

?>