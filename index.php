<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <title>Dixit en ligne</title>
        <link rel="stylesheet" href="style.css?<?php echo time() ?>" />
        <script defer src="index.js"></script>
    </head>
    <body>
        <div id="loading"><?php $rdm = random_int(0,10) ?>
            <div class="card"><?=$rdm==0?"Wai":"Charg"?></div>
            <div class="card"><?=$rdm==0?"fu":"ement"?></div>
        </div>
        <div id="join" style="display:none;">
            <img id="logo" />
            <div id="join-menu">
                <input id="name" placeholder="Pseudonyme" />
                <div>
                    <div id="create-form">
                        <button onclick="createGame()">Créer une partie</button>
                    </div>
                    <div id="join-form">
                        <input id="room-code" placeholder="Code de salle" />
                        <button onclick="joinGame()">Rejoindre une partie</button>
                    </div>
                </div>
            </div>
            <div id="footer"></div>
        </div>
        <div id="room" style="display:none;">
            <div id="players">
                
            </div>
            <span id="aff-room-code"></span>
            <div id="room-infos">
                Thème des cartes :
                <div id="theme-select" class="selector"></div>
                Fin de la partie :
                <div id="gm-select" class="selector"></div>
                <div id="turn-select" class="selector"></div>
                <div id="score-select" class="selector"></div>
            </div>
            <div id="room-menu">
                <button id="share-room" onclick="shareRoom()">Inviter</button>
                <button id="start" onclick="startGame()">Commencer</button>
                <button id="leave-room" onclick="leaveRoom()">Quitter</button>
            </div>
        </div>
        <div id="game" style="display:none;">
            <div id="playersInGame"></div>
            <span id="tip"></span>
            <div id="waiting" style="display:none;">
                <span>🔴</span>
                <span>🟢</span>
                <span>🔵</span>
            </div>
            <span id="sentence"></span>
            <input id="query-sentence" placeholder="Une phrase pour ton image" style="display:none;">
            <button id="confirm-button" style="display:none;">Confirmer</button>
            <img id="waiting-image" src="assets/waiting<?= random_int(0,1) ?>.jpg" style="display:none;" />
            <div id="cards"></div>
            <div id="hand"></div>
        </div>
    </body>
</html>