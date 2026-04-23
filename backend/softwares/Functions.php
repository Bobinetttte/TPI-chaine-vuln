<?php

require_once("./../PDOConnection.php");


/**
 * Renvoie la liste de tout les logiciels et leurs versions
 *
 * @return Array la liste de tout les logiciels et leurs versions ou une erreur
 */
function getSofts() : Array {

    $response = [
        "code" => 200,
        "result" => []
    ];

    try {
        $db = connectDb();

        $sql = "SELECT softwareName FROM software;";

        $result = $db->query($sql);

        $response["result"] = $result->fetchAll();

    } catch (PDOException $e) {
        $response["code"] = 500;
        $response["result"]["error"] = "Erreur : " . $e->getMessage();
    }

    return $response;
}