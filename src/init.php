<?php
// si le fichier credentials.php existe, on l'inclut
@include 'credentials.php';

// initialisation BDD
$mysqli = new mysqli(get_config('DB_HOST'), get_config('DB_USER'), get_config('DB_PASS'), get_config('DB_NAME'));
if ($mysqli->connect_errno) {
	echo 'Erreur de connexion côté serveur, veuillez réessayer plus tard';
	exit;
}

// obtenir une variable de configuration
function get_config($name) {
	if (defined($name) && !empty(constant($name)))
			return constant($name);
	return getenv($name) ?? NULL;
}

// fonction de requête BDD
function sendRequest(...$requestFrags) {
	global $mysqli;
	$request = '';
	$var = false;
	foreach ($requestFrags as $frag) {
		$request .= ($var ? $mysqli->real_escape_string($frag) : $frag);
		$var = !$var;
	}
	if (!$result = $mysqli->query($request)) {
		echo 'Erreur de requête côté serveur, veuillez réessayer plus tard';
		//echo $request;
		exit;
	}
	return $result;
}

// retourner la clé API Pixabay
function getPixabayApiKey() {
	return get_config('PIXABAY_API_KEY');
}
?>