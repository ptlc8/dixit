<?php
// initialisation session + BDD
require('credentials.php');
$mysqli = new mysqli(DIXIT_DB_HOST, DIXIT_DB_USER, DIXIT_DB_PASSWORD, DIXIT_DB_NAME);
if ($mysqli->connect_errno) {
	echo 'Erreur de connexion côté serveur, veuillez réessayer plus tard';
	exit;
}

// fonction de requête BDD
function sendRequest(...$requestFrags) {
	$request = '';
	$var = false;
	foreach ($requestFrags as $frag) {
		$request .= ($var ? str_replace(array('\\', '\''), array('\\\\', '\\\''), $frag) : $frag);
		$var = !$var;
	}
	global $mysqli;
	if (!$result = $mysqli->query($request)) {
		echo 'Erreur de requête côté serveur, veuillez réessayer plus tard';
		//echo $request;
		exit;
	}
	return $result;
}
?>