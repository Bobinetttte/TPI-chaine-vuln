<?php
require_once("./../cors.php");
require_once("./Functions.php");
require_once("./../PDOConnection.php");

// Récupérer la méthode
$method = $_SERVER['REQUEST_METHOD'];
switch ($method) {
    case 'UPDATE':
            try {

                $assoId = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
                $action = filter_input(INPUT_GET, "action", FILTER_SANITIZE_FULL_SPECIAL_CHARS);

                if (!isset($assoId)) {
                    throw new Exception("Id non précisé", 1);
                }

                if ($action != "up" && $action != "down") {
                    throw new Exception("Action mal formaté", 1);
                }

                updateAssociation($assoId, $action);

                $response["code"] = 200;
                $response["result"] = ["OK"];


            } catch (\Throwable $th) {
                $response["code"] = 400;
                $response["result"]["error"] = "Erreur: " . $th;

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