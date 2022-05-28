// 2 fonctions toujours utiles d'Ambi ;)
function createElement(tag, properties={}, inner=[], eventListeners={}) {
    let el = document.createElement(tag);
    for (let p of Object.keys(properties)) if (p != "style") el[p] = properties[p];
    if (properties.style) for (let p of Object.keys(properties.style)) el.style[p] = properties.style[p];
    if (typeof inner == "object") for (let i of inner) el.appendChild(typeof i == "string" ? document.createTextNode(i) : i);
    else el.innerText = inner;
    for (let l of Object.keys(eventListeners)) el.addEventListener(l, eventListeners[l]);
    return el;
}
function sendRequest(method, url, body=undefined, headers={"Content-Type":"application/x-www-form-urlencoded"}) {
    var promise = new (Promise||ES6Promise)(function(resolve, reject) {
        var xhr = new XMLHttpRequest();
        xhr.open(method, url);
        for (h of Object.keys(headers))
            xhr.setRequestHeader(h, headers[h]);
        xhr.onreadystatechange = function() {
            if (this.readyState == XMLHttpRequest.DONE && this.status == 200) {
                resolve(this.response);
            }
        }
        xhr.send(body);
    });
    return promise;
}


var game = undefined;
var currentEvent = undefined;
var requestIntervalId = undefined;
var selectingCard = undefined;

get();
document.getElementById("confirm-button").addEventListener("click", confirmSelect);
document.getElementById("name").addEventListener("change", (e)=>localStorage.setItem("dixit.name", e.target.value));

var themes = [{image:"assets/waifu.jpg",name:"Waifus",value:"waifu"},{image:"assets/photo.jpg",name:"Photos",value:"pictures"},{image:"assets/ac.jpg",name:"Animal crossing",value:"ac"}];
initSelector(document.getElementById("theme-select"), themes);

var gamemodes = [{image:"assets/score.jpg",name:"Objectif score",value:"score"},{image:"assets/turns.jpg",name:"Nombre de tours",value:"turn"}];
initSelector(document.getElementById("gm-select"), gamemodes);
document.getElementById("gm-select").addEventListener("change", function(e) {
    for (let i of ["turn","score"]) {
        document.getElementById(i+"-select").style.display = e.target.value==i ? "" : "none";
    }
});
let changeEvent = new Event("change");
document.getElementById("gm-select").dispatchEvent(changeEvent);

var turns = [{image:"assets/3turns.jpg",name:"3 tours",value:"3"},{image:"assets/5turns.jpg",name:"5 tours",value:"5"},{image:"assets/7turns.jpg",name:"7 tours",value:"7"}];
initSelector(document.getElementById("turn-select"), turns);

var scores = [{image:"assets/15points.jpg",name:"Objectif : 15",value:"15"},{image:"assets/30points.jpg",name:"Objectif : 30",value:"30"},{image:"assets/45points.jpg",name:"Objectif : 45",value:"45"}];
initSelector(document.getElementById("score-select"), scores);

window.addEventListener("load", function() {
    const urlParams = new URLSearchParams(window.location.search);
    let joinCode = urlParams.get('j');
    if (joinCode) {
        document.getElementById("room-code").value = joinCode;
    }
    document.getElementById("name").value = localStorage ? localStorage.getItem("dixit.name")||"" : "";
});

function initSelector(selector, values, preselected=parseInt(values.length/2)) {
    selector.value = values[preselected].value;
    for (let i = 0; i < values.length; i++) {
        let fixedI = i;
        selector.appendChild(createElement("div", {style:{backgroundImage:"url('"+values[i].image+"')",left:50+25*(i-preselected)+"%",zIndex:20-Math.abs(i-preselected)},className:preselected==i?"selected":""}, values[i].name, {click:function(){
            if (selector.disabled) return;
            selector.value = values[fixedI].value;
            for (let j = 0; j < selector.children.length; j++) {
                selector.children[j].style.left = 50+25*(j-fixedI)+"%";
                selector.children[j].style.zIndex = 20-Math.abs(j-fixedI);
                selector.children[j].className = fixedI==j ? "selected" : "";
            }
            let changeEvent = new Event("change");
            selector.dispatchEvent(changeEvent);
        }}));
    }
}

function leaveRoom() {
    sendRequest("POST", "leave.php").then(function(r) {
        if (r == "leaved")
            get();
    });
}

function get() {
    sendRequest("GET", "get.php").then(function(r) {
        game = JSON.parse(r);
        refresh();
    });
}
function startGetting() {
    if (requestIntervalId!==undefined)
        clearInterval(requestIntervalId);
    requestIntervalId = setInterval(function() {
        get();
    }, 3000);
}
function stopGetting() {
    if (requestIntervalId!==undefined)
        clearInterval(requestIntervalId);
}
function isGetting() {
    return requestIntervalId!==undefined;
}
function createGame() {
    let name = document.getElementById("name").value;
    if (name.length < 3) return alert("Le pseudonyme doit être composé d'au moins 3 caractères");
    sendRequest("POST", "create.php", "name="+encodeURIComponent(name)).then(function(r) {
        get();
    });
}

function joinGame() {
    let name = document.getElementById("name").value;
    if (name.length < 3) return alert("Le pseudonyme doit être composé d'au moins 3 caractères");
    let roomCode = document.getElementById("room-code").value;
    if (roomCode.length < 1) return alert("il faut un code de salle à rejoindre");
    //if (roomCode.length > 8) return alert("le code de salle est trop long");
    sendRequest("POST", "join.php", "id="+encodeURIComponent(roomCode)+"&name="+encodeURIComponent(name)).then(function(r) {
        if (r == "nogame")
            alert("Aucune partie ne correspond à ce code");
        else if (r == "playing")
            alert("Cette partie est en cours...");
        else if (r == "nametaken")
            alert("Ce pseudonyme est déjà utilisé");
        else get();
    });
}
function shareRoom() {
    var url = window.location.origin+window.location.pathname+"?j="+encodeURIComponent(game.id);
    if (navigator.share) {
        navigator.share({url:url,title:document.title,text:"Rejoins une partie de Dixit en ligne"});
    } else {
        let input = document.createElement("input");
        document.body.appendChild(input);
        input.value = url;
        input.select();
        input.setSelectionRange(0, 99999);
        document.execCommand("copy");
        document.body.removeChild(input);
        alert("Lien copié !");
    }
}

function startGame() {
    if (game.players.length > 9 && !confirm("Les parties a plus de 9 joueurs peuvent ne pas s'afficher correctement, voulez-vous continuer ?")) return;
    sendRequest("POST", "start.php", "theme="+document.getElementById("theme-select").value+"&winCondition="+document.getElementById("gm-select").value+"&winScore="+document.getElementById("score-select").value+"&winTurn="+document.getElementById("turn-select").value+"").then(function(r) {
        if (r == "needmoreplayers")
            alert("Il faut au moins 3 joueurs, invite-en !");
        else if (r == "started")
            get();
    });
}




function refresh() {
    //if (currentEvent===undefined) {
        setPanel(game.state);
        if (game.state == "game")
            refreshGame();
        if (game.state == "room")
            refreshRoom();
        if (game.state == "join") {
            if (isGetting())
                stopGetting();
        } else {
            if (!isGetting())
                startGetting();
        }
        currentEvent = game.events ? game.events.length : 0;
    /*} else {
        while (currentEvent < game.events.length) {
            onGameEvent(game.events[currentEvent]);
            currentEvent++;
        }
    }*/
}

function onGameEvent(event) {
    switch (event.name) {
        case "playerjoin":
            
            break;
        case "playerleave":
            
            break;
        case "start": // dealer
            setPanel("game");
            setPlayersInGame(game.players);
            break;
        case "choosesentence": // sentence, player
            
            break;
        case "choosecard": // player
            
            break;
        case "startvote": // cards
            
            break;
        case "vote": // player
            
            break;
        case "endturn": // dealer
            
            break;
    }
}

function setPanel(panelName) {
    var panels = ["join","room","game","loading"];
    for (let panel of panels)
        document.getElementById(panel).style.display = panel==panelName ? "" : "none";
}
function setPlayersInGame(players) {
    var playersDiv = document.getElementById("playersInGame");
    playersDiv.innerHTML = "";
    for (let player of players)
        playersDiv.appendChild(createElement("div", {className:game.selfId==player.id?"you":""}, [
            createElement("span", {className:"name"}, player.name),
            createElement("span", {className:"score"}, player.score)
        ]));
}

function refreshRoom() {
    var playersDiv = document.getElementById("players");
    playersDiv.innerHTML = "";
    for (let player of game.players)
        playersDiv.appendChild(createElement("span", {}, player.name));
    document.getElementById("start").style.display = (game.selfIndex === 0) ? "" : "none";
    document.getElementById("theme-select").disabled = document.getElementById("gm-select").disabled = document.getElementById("turn-select").disabled = document.getElementById("score-select").disabled = game.selfIndex != 0;
    document.getElementById("aff-room-code").innerText = game.id;
}
function refreshGame() {
    setPlayersInGame(game.players);
    var handDiv = document.getElementById("hand");
    for (let i = 0; i < Math.max(game.hand.length, handDiv.children.length); i++) {
        if (handDiv.children[i] && game.hand[i]) {
            handDiv.children[i].style.backgroundImage = "url('"+game.hand[i]+"')";
        } else if (game.hand[i]) {
            let finalI = i;
            handDiv.appendChild(createElement("div", {style:{backgroundImage:"url('"+game.hand[i]+"')"}}, [], {click:function() {
                selectHandCard(finalI);
            }}));
        } else {
            handDiv.removeChild(handDiv.children[i]);
        }
    }
    document.getElementById("sentence").innerText = game.sentence;
    var cardsDiv = document.getElementById("cards");
    for (let i = 0; i < Math.max(game.cards.length, cardsDiv.children.length); i++) {
        if (cardsDiv.children[i] && game.cards[i]) {
            cardsDiv.children[i].style.backgroundImage = "url('"+game.cards[i]+"')";
        } else if (game.cards[i]) {
            let finalI = i;
            cardsDiv.appendChild(createElement("div", {style:{backgroundImage:"url('"+game.cards[i]+"')"}}, [], {click:function() {
                selectCard(finalI);
            }}));
        } else {
            cardsDiv.removeChild(cardsDiv.children[i]);
        }
    }
    var tip = "";
    var dealerName = game.players[game.dealer].name;
    document.getElementById("waiting").style.display = (game.dealer==game.selfIndex&&game.phase==0) || (game.dealer!=game.selfIndex&&game.phase!=0) ? "none" : "";
    switch (game.phase) {
        case 0: 
            tip = game.dealer==game.selfIndex ? "Choisis une carte à faire devinez aux autres et donner une phrase qui lui correspond mais pas trop" : dealerName+" choisit une carte et une phrase à présenter";
            if (game.dealer==game.selfIndex) {
                if (selectingCard!=undefined)
                    document.getElementById("cards").innerHTML = '<div style="background-image:url(\''+game.hand[selectingCard]+'\');"></div>';
            }
            break;
        case 1:
            tip = game.dealer==game.selfIndex ? "Les autres choisissent une carte à présenter à partir de ta phrase" : "Choisis une carte qui correspond à la phrase";
            
            break;
        case 2:
            tip = game.dealer==game.selfIndex ? "Les autres votent pour deviner votre carte" : "Vote pour la carte que "+dealerName+" aurait pû mettre";
            break;
    }
    document.getElementById("tip").innerText = tip;
}

function confirmSelect() {
    if (game.dealer==game.selfIndex && game.phase==0) {
        var sentence = document.getElementById("query-sentence").value.trim();
        if (sentence.length == 0)
            return alert("La phrase ne peut pas être vide");
        sendRequest("POST", "choose.php", "card="+selectingCard+"&sentence="+encodeURIComponent(sentence)).then(function(r) {
            if (r=="success") {
                document.getElementById("query-sentence").style.display = document.getElementById("confirm-button").style.display = "none";
                document.getElementById("cards").innerHTML = "";
                document.getElementById("waiting").style.display = "";
                selectingCard = undefined;
                document.getElementById("query-sentence").value = "";
                get();
            }
        });
    }
}

function selectHandCard(index) {
    if (game.dealer==game.selfIndex) {
        if (game.phase==0) { // choix carte + phrase
            document.getElementById("query-sentence").style.display = document.getElementById("confirm-button").style.display = "";
            selectingCard = index;
            refresh();
        }
    } else {
        if (game.phase==1) { // choix carte
            sendRequest("POST", "choose.php", "card="+index).then(function(r) {
                if (r=="success") {
                    get();
                }
            });
        }
    }
    
}

function selectCard(index) {
    var dealerName = game.players[game.dealer].name;
    if (game.dealer!=game.selfIndex) {
        if (game.phase==2) { // vote carte
            sendRequest("POST", "choose.php", "vote="+index).then(function(r) {
                if (r=="success") {
                    get();
                }
            });
        }
    }
}