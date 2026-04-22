<?php
require_once("./../cors.php");
require_once("./Functions.php");
require_once("./../PDOConnection.php");

header('Content-type: application/json; charset=utf-8');

// Récupérer la méthode
$method = $_SERVER['REQUEST_METHOD'];
switch ($method) {
    case 'GET':

        try {
            echo json_encode(getTenCve());
            http_response_code("200");
        } catch (\Throwable $th) {
            echo json_encode("Erreur : " . $th);
            http_response_code("500");
        }
        
        break;

    case 'POST':
        http_response_code(response_code: 400);
        break;

    case 'PUT':
        http_response_code(400);
        break;

    case 'DELETE':
        http_response_code(400);
        break;

    default:
        // Requête inconnue -> retourner erreur
        http_response_code(400);
        break;
}