<?php
require_once("./../cors.php");
require_once("./Functions.php");
require_once("./../PDOConnection.php");

// Récupérer la méthode
$method = $_SERVER['REQUEST_METHOD'];
switch ($method) {
    case 'GET':
            try {


                $associationsIds = getTenAssociationIds();
                foreach ($associationsIds as $key => $assoId) {
                    $associations[] = getAssociation($assoId["associationId"]);
                }

                $response["code"] = 200;
                $response["result"]["associations"] = $associations;


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