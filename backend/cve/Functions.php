<?php

require_once("./../PDOConnection.php");


/**
 * Renvoie 10 CVE avec leurs nombre et leurs criticité (baseScore)
 *
 * @return Array|String 10 CVE ou un string qui explique une erreur
 */
function getTenCve() : Array|String {

    try {
        $db = connectDb();

        $sql = "SELECT cveNumber, baseScore FROM cve LIMIT 10;";

        $result = $db->query($sql);

        return $result->fetchAll();

    } catch (PDOException $e) {
        return "Erreur : " . $e->getMessage();
    }


}