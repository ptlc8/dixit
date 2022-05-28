<?php
session_start();
if (!isset($_SESSION["dixitGameId"], $_SESSION["dixitPlayerId"])) exit("notingame");

include("init.php");

$result = sendRequest("SELECT * FROM DIXIT WHERE id = '", $_SESSION["dixitGameId"], "'");
if ($result->num_rows === 0) exit("nogame");

$game = json_decode($result->fetch_assoc()["data"]);
$game->votes = (array)$game->votes;

foreach ($game->players as $index => $player)
    if ($player->id == $_SESSION["dixitPlayerId"])
        $playerIndex = $index;

if ($game->phase == 0) { // dealer choisit sa carte et la phrase
    if($game->dealer != $playerIndex)
        exit("notdealer");
    if (!isset($_POST['card'], $_POST['sentence']))
        exit("need card and sentence");
    if (0 > intval($_POST['card']) || intval($_POST['card']) >= count($game->hands[$playerIndex]))
        exit("bad card");
    $game->sentence = $_POST['sentence'];
    $game->cards = array(array_splice($game->hands[$playerIndex], intval($_POST['card']), 1)[0]);
    $game->owners = array($playerIndex);
    $game->phase = 1;
    array_push($game->events, array("name" => "choosesentence", "sentence" => $game->sentence, "player" => $playerIndex));
}

else if ($game->phase == 1) {
    if($game->dealer == $playerIndex)
        exit("dealer");
    if (!isset($_POST['card']))
        exit("need card");
    if (in_array($playerIndex, $game->owners))
        exit("already choose");
    if (0 > intval($_POST['card']) || intval($_POST['card']) >= count($game->hands[$playerIndex]))
        exit("bad card");
    array_push($game->cards, array_splice($game->hands[$playerIndex], intval($_POST['card']), 1)[0]);
    array_push($game->owners, $playerIndex);
    array_push($game->events, array("name" => "choosecard", "player" => $playerIndex));
    if (count($game->owners) == count($game->players)) {
        for ($i = 0; $i < count($game->cards); $i++) {
            $j = random_int(0, count($game->cards)-1);
            $tmpC = $game->cards[$i];
            $tmpO = $game->owners[$i];
            $game->cards[$i] = $game->cards[$j];
            $game->owners[$i] = $game->owners[$j];
            $game->cards[$j] = $tmpC;
            $game->owners[$j] = $tmpO;
        }
        $game->phase = 2;
        $game->votes = array();
        array_push($game->events, array("name" => "startvote", "cards" => $game->cards));
    }
    
}

else if ($game->phase == 2) {
    if($game->dealer == $playerIndex)
        exit("dealer");
    if (!isset($_POST['vote']))
        exit("need vote");
    if (in_array($playerIndex, array_keys((array)$game->votes)))
        exit("already vote");
    if (0 > intval($_POST['vote']) || intval($_POST['vote']) >= count($game->cards))
        exit("bad vote");
    $game->votes[$playerIndex] = intval($_POST["vote"]);
    array_push($game->events, array("name" => "vote", "player" => $playerIndex, "vote" => intval($_POST["vote"])));
    if (count((array)$game->votes) == count((array)$game->players)-1) {
        $dealerIndex = array_search($game->dealer, $game->owners);
        $founders = 0;
        foreach ($game->votes as $vote) {
            if ($vote == $dealerIndex)
                $founders++;
        }
        if ($founders >= count($game->players)-1 || $founders == 0) { // trop facile, tout le monde a trouvé ou personne
            foreach ($game->players as $i => $player)
                if ($i != $game->dealer) {
                    $player->score += 2;
                    //$game->events
                }
        } else {
            foreach ($game->players as $i => $player) {
                if ($game->dealer == $i) {
                    $game->players[$game->dealer]->score += 3;
                    //$game->events
                } else if ($game->owners[$game->votes[$i]] == $game->dealer) { // carte trouvée : +3
                    $game->players[$i]->score += 3;
                    //$game->events;
                } else {
                    $earnerId = $game->owners[$game->votes[$i]];
                    $game->players[$earnerId]->score += 1;
                    //$game->events
                }
            }
        }
        shuffle($game->cards);
        array_push($game->draw, ...($game->cards));
        for ($i = 0; $i < count($game->hands); $i++)
            array_push($game->hands[$i], array_shift($game->draw));
        
        $game->sentence = "";
        $game->cards = [];
        $game->owners = [];
        $game->votes = [];
        if ($game->dealer == count($game->players)-1) {
            $game->dealer = 0;
            $game->turn++;
        } else {
            $game->dealer++;
        }
        $maxScore = 0;
        $firstPlayer = null;
        foreach ($game->players as $index => $player)
            if ($maxScore < $player->score) {
                $maxScore = $player->score;
                $firstPlayer = $index;
            }
        if (($game->winCondition=="score" && $game->winScore==$maxScore) || ($game->winCondition=="turn" && $game->winTurn==$game->turn)) { // end game !
            array_push($game->events, array("name" => "end", "winner" => $firstPlayer));
            $game->state = "room";
        } else {
            $game->phase = 0;
            array_push($game->events, array("name" => "endturn", "dealer" => $game->dealer));
        }
    }
}

sendRequest("UPDATE DIXIT SET data = '", json_encode($game), "' WHERE id = '", $_SESSION["dixitGameId"], "'");
echo "success";
?>