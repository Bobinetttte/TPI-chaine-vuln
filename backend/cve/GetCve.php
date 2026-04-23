<?php
require_once("./../cors.php");
require_once("./Functions.php");
require_once("./../PDOConnection.php");

// Récupérer la méthode
$method = $_SERVER['REQUEST_METHOD'];
switch ($method) {
    case 'GET':
        $software = filter_input(INPUT_GET, "software", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $version = filter_input(INPUT_GET, "version", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $useNist = filter_input(INPUT_GET, "nist", FILTER_VALIDATE_BOOLEAN);

        if (!isset($software) && !isset($version)) {
            $response = getTwentyCve();
        } 
        elseif (!isset($useNist) || $useNist == false ) {
            $response = searchCve(trim($software), trim($version));
        }
        else {
            $response = searchCveNist(trim($software), trim($version));
        }


        break;

    default:
        // Requête inconnue -> retourner erreur
        $response["code"] = 400;
        $response["result"]["error"] = "Methode non supportée";
        break;
}

http_response_code($response["code"]);
header('Content-type: application/json; charset=utf-8');
echo json_encode($response["result"]);
exit;