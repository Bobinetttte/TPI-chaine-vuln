<?php
require_once("./../cors.php");
require_once("./Functions.php");
require_once("./../PDOConnection.php");

// Récupérer la méthode
$method = $_SERVER['REQUEST_METHOD'];
switch ($method) {
    case 'POST':
        $chainSearch = filter_input(INPUT_GET, "chainSearch", FILTER_VALIDATE_BOOLEAN);

        if ($chainSearch) {
            try {
                $body = file_get_contents('php://input');
                $jsonCve = json_decode($body);

                if (count($jsonCve) <= 1) {
                    $response["code"] = 200;
                    $response["result"]["associations"] = [];
                    break;
                }
                
                $chains = createChains($jsonCve);
                

                $associations = [];
                foreach ($chains as $key => $chain) {
                    $associationId = saveChain($chain);
                    $associations[] = getAssociation($associationId);
                }

                $response["code"] = 200;
                $response["result"]["associations"] = $associations;


            } catch (\Throwable $th) {
                $response["code"] = 400;
                $response["result"]["error"] = "JSON incorrect";

                break;
            }

        }
        else {
            $response["code"] = 400;
            $response["result"]["error"] = "Paramètre chainSearch non défini";
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