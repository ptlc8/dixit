<?php
session_start();
if (!isset($_SESSION["dixitGameId"], $_SESSION["dixitPlayerId"])) exit("notingame");

include("init.php");

$result = sendRequest("SELECT * FROM DIXIT WHERE id = '", $_SESSION["dixitGameId"], "'");
if ($result->num_rows === 0) exit("nogame");

$game = json_decode($result->fetch_assoc()["data"]);

foreach ($game->players as $index => $player)
    if ($player->id == $_SESSION["dixitPlayerId"])
        array_splice($game->players, $index, 1);

if (count($game->players) == 0) {
    sendRequest("DELETE FROM `DIXIT` WHERE `id` = '", $_SESSION["dixitGameId"], "'");
} else {
    sendRequest("UPDATE DIXIT SET data = '", json_encode($game), "' WHERE id = '", $_SESSION["dixitGameId"], "'");
}

unset($_SESSION['dixitGameId']);
unset($_SESSION['dixitPlayerId']);

echo "leaved";
?>