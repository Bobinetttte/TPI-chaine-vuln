<?php
require_once("./../cors.php");
require_once("./Functions.php");
require_once("./../PDOConnection.php");

// Récupérer la méthode
$method = $_SERVER['REQUEST_METHOD'];
switch ($method) {
    case 'GET':
            try {
                $associationId = filter_input(INPUT_GET, "associationId", FILTER_VALIDATE_INT);


                if (isset($associationId)) {
                    $response["result"] = getAllDataAssociation($associationId);
                }
                else {

                    $associationsIds = getTenAssociationIds();
                    foreach ($associationsIds as $key => $assoId) {
                        $associations[] = getAssociation($assoId["associationId"]);
                    }

                    $response["result"]["associations"] = $associations;

                }

                $response["code"] = 200;
                
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