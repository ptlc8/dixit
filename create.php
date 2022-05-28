<?php
if (!isset($_POST["name"])) exit("need name");
session_start();

include("init.php");

$gameId = "";
for ($i = 0; $i < 4; $i++)
    $gameId .= "0123456789azertyuiopqsdfghjklmwxcvbnAZERTYUIOPQSDFGHJKLMWXCVBN_-"[random_int(0,63)];
$game = new stdClass();
$game->state = "room";
$game->phase = 0;
$game->events = array();
$game->kId = 0;
$owner = new stdClass();
$owner->id = 0;
$owner->name = $_POST["name"];
$game->players = array($owner);
$game->hands = [];
$game->cards = [];
$game->owners = [];
$game->votes = [];

sendRequest("INSERT INTO DIXIT (id,data) VALUES ('", $gameId, "', '", json_encode($game), "')");
$_SESSION["dixitGameId"] = $gameId;
$_SESSION["dixitPlayerId"] = 0;

echo "created";
?>