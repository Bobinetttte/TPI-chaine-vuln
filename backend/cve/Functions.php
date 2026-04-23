<?php

require_once("./../PDOConnection.php");


/**
 * Renvoie 20 CVE avec leurs cveNumber et leurs criticité (baseScore)
 *
 * @return Array 20 CVE ou une erreur
 */
function getTwentyCve() : Array {
    $response = [
        "code" => 200,
        "result" => []
    ];

    try {
        $db = connectDb();

        $sql = "SELECT c.cveNumber, c.baseScore, s.softwareName, v.version, cat.categoryName
        FROM cve c 
        LEFT JOIN cve_version cv ON cv.cveId = c.cveId 
        LEFT JOIN version v ON v.versionId = cv.versionId 
        LEFT JOIN software s ON s.softwareId = v.softwareId 
        LEFT JOIN cve_category cc ON cc.cveId = c.cveId 
        LEFT JOIN category cat ON cat.categoryId = cc.categoryId 
        LIMIT 20;";

        $result = $db->query($sql);

         $response["result"] = $result->fetchAll();

    } catch (PDOException $e) {
        $response["code"] = 500;
        $response["result"]["error"] = "Erreur : " . $e->getMessage();
    }

    return $response;
}

/**
 * Renvoie tout les CVE selon le nom du logiciel rechercher et sa version
 * @param String $software : Nom du logiciel
 * @param String $version : Version du logiciel
 * @return Array CVE ou une erreur
 */
function searchCve($software, $version) : Array {

    $response = [
        "code" => 200,
        "result" => []
    ];

    try {
        $db = connectDb();

        $sql = "SELECT c.cveNumber, c.baseScore, s.softwareName, v.version, cat.categoryName
        FROM cve c 
        LEFT JOIN cve_version cv ON cv.cveId = c.cveId 
        LEFT JOIN version v ON v.versionId = cv.versionId 
        LEFT JOIN software s ON s.softwareId = v.softwareId 
        LEFT JOIN cve_category cc ON cc.cveId = c.cveId 
        LEFT JOIN category cat ON cat.categoryId = cc.categoryId 
        WHERE s.softwareName = :soft AND v.version LIKE :vers ;";

        $stmt = $db->prepare($sql);
        $stmt->execute(['soft' => $software, 'vers' => $version . "%"]);

        $response["result"] = $stmt->fetchAll();

        if ($response["result"] == null) {
            $response["result"]["error"] = "Aucun CVE trouvé";
        }

    } catch (PDOException $e) {
        $response["code"] = 500;
        $response["result"]["error"] = "Erreur : " . $e->getMessage();
    }

    return $response;
}