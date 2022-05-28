<?php
if (!isset($_POST["name"], $_POST["id"])) exit("need id and name");
session_start();

include("init.php");

$result = sendRequest("SELECT * FROM DIXIT WHERE id = '", $_POST["id"], "'");
if ($result->num_rows === 0) exit("nogame");

$game = json_decode($result->fetch_assoc()["data"]);

if ($game->state != "room")
    exit("playing");

foreach($game->players as $player)
    if($player->name == $_POST["name"])
        exit("nametaken");

$game->kId++;
$player = new stdClass();
$player->id = $game->kId;
$player->name = $_POST["name"];
array_push($game->players, $player);

sendRequest("UPDATE DIXIT SET data = '", json_encode($game), "' WHERE id = '", $_POST["id"], "'");
$_SESSION["dixitGameId"] = $_POST["id"];
$_SESSION["dixitPlayerId"] = $player->id;

echo "joined";
?>