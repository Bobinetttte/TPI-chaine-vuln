<?php
require_once("./../secrets/PDO.php");

/**
 * Créer une connection à la base de données
 *
 * @return PDO Connection à la base de données
*/
function connectDb() : PDO {
    
    try {
      $db = new PDO("mysql:host=". SERVER_NAME . ";dbname=" . DB_NAME . "", DB_USERNAME, DB_USERNAME_PASS);

      // Renvoie les exceptions
      $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
      die("Impossible de ce connecter : " . $e->getMessage());
    } 

    return $db;
}