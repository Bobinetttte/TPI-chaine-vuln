<?php

require_once("./../PDOConnection.php");
require_once("./../secrets/infomaniak.php");


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

    } catch (\Throwable $th) {
        $response["code"] = 500;
        $response["result"]["error"] = "Erreur : " . $th->getMessage();
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

        // GROUP_CONCAT(DISTINCT  evite les doublons à cause des cve sur plusieurs versions et categorie
        $sql = "SELECT c.cveNumber, c.baseScore, s.softwareName, v.version, GROUP_CONCAT(DISTINCT cat.categoryName) as categories
        FROM cve c 
        LEFT JOIN cve_version cv ON cv.cveId = c.cveId 
        LEFT JOIN version v ON v.versionId = cv.versionId 
        LEFT JOIN software s ON s.softwareId = v.softwareId 
        LEFT JOIN cve_category cc ON cc.cveId = c.cveId 
        LEFT JOIN category cat ON cat.categoryId = cc.categoryId 
        WHERE s.softwareName = :soft AND v.version LIKE :vers
        GROUP BY c.cveNumber, v.version;";

        $stmt = $db->prepare($sql);
        $stmt->execute(['soft' => $software, 'vers' => $version . "%"]);

        $response["result"] = $stmt->fetchAll();

        if ($response["result"] == null) {
            $response["result"]["error"] = "Aucun CVE trouvé";
        }

    } catch (\Throwable $th) {
        $response["code"] = 500;
        $response["result"]["error"] = "Erreur : " . $th->getMessage();
    }

    return $response;
}

/**
 * Renvoie maximum 100 CVE selon le CPE renseigné
 * @param String $cpe : CPE défini selon le nom et la version du logiciel recherché
 * @return Array|Error CVE ou une erreur
 */
function searchCveWithCpe($cpe) : Array|Error {

        // Appeler l'api du NIST pour trouver le CPE exact

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://services.nvd.nist.gov/rest/json/cves/2.0?cpeName=" . $cpe . "&resultsPerPage=100");
        // N'affiche pas le résultat automatiquement
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $respNist = curl_exec($ch);


        $respNistJSON = json_decode($respNist);

        if (empty($respNistJSON->vulnerabilities)) {
            throw new Exception("Aucun CVE trouvé pour ce CPE", 1);
        }

        $cve = $respNistJSON->vulnerabilities;


        return $cve;
}


function searchCveNist($softName, $version) {
        $response = [
        "code" => 200,
        "result" => []
    ];


    if ($softName == "" || $version == "") {

        $response["code"] = 403;
        $response["result"]["error"] = "Erreur : Le nom du logiciel ou sa version est vide";

        return $response;
    }

    try {

        // Point 2 et 3 de l'algorythme
        $cpe = findCPE($softName, $version);

        if ($cpe == null) {
            throw new Exception("Aucun CPE trouvé", 1);
        }

        saveCPE($softName, $version, $cpe);

        // Point 4 et 5 de l'algorythme
        $cves = searchCveWithCpe($cpe);

        $cvesClean = [];

        foreach ($cves as $key => $cve) {
            $exploitabilityScore = null;
            $impactScore = null;
            $baseScore = null;

            $cveId = $cve->cve->id;
            $publishedDate = new DateTime($cve->cve->published);
            $publishedDate = $publishedDate->format("Y-m-d");

            $lastModifiedDate = new DateTime($cve->cve->lastModified);
            $lastModifiedDate = $lastModifiedDate->format("Y-m-d");

            $status = $cve->cve->vulnStatus;
            $description = $cve->cve->descriptions[0]->value;


            // Vérifie la version du cvss pour trouver des scores d'impact défini
            if (!empty($cve->cve->metrics->cvssMetricV31)) {
                $exploitabilityScore = $cve->cve->metrics->cvssMetricV31[0]->exploitabilityScore;
                $impactScore = $cve->cve->metrics->cvssMetricV31[0]->impactScore;
                $baseScore = $cve->cve->metrics->cvssMetricV31[0]->cvssData->baseScore;

            } 
            elseif (!empty($cve->cve->metrics->cvssMetricV3)){
                $exploitabilityScore = $cve->cve->metrics->cvssMetricV3[0]->exploitabilityScore;
                $impactScore = $cve->cve->metrics->cvssMetricV3[0]->impactScore;
                $baseScore = $cve->cve->metrics->cvssMetricV3[0]->cvssData->baseScore;

            } elseif (!empty($cve->cve->metrics->cvssMetricV2)) {
                $exploitabilityScore = $cve->cve->metrics->cvssMetricV2[0]->exploitabilityScore;
                $impactScore = $cve->cve->metrics->cvssMetricV2[0]->impactScore;
                $baseScore = $cve->cve->metrics->cvssMetricV2[0]->cvssData->baseScore;
            }

            

            $cvesClean[$cveId] = [
                "cveNumber" => $cveId,
                "published" => $publishedDate,
                "lastModified" => $lastModifiedDate,
                "status" => $status,
                "description" => $description,
                "exploitabilityScore" => $exploitabilityScore,
                "impactScore" => $impactScore,
                "baseScore" => $baseScore,
                "softwareName" => $softName,
                "version" => $version,
                "categoryName" => "",
            ];


            saveCVE($cveId, $publishedDate, $lastModifiedDate, $status,
            $description, $exploitabilityScore, $impactScore, $baseScore);

            saveVersionLinkCve($cveId, $cpe);
        }

        // Point 6 et 7 de l'algorythme
        $cve_category = json_decode(findCategoryWithLLM($cvesClean));

        foreach ($cve_category as $key => $value) {
            saveCategory($value->cveNumber, $value->categoryName);
            $cvesClean[$value->cveNumber]["categoryName"] = $value->categoryName;
        }
        
        // array_values() evite d'avoir un objet à cause du cveId en clé. Transforme en array pour le front
        $response["result"] = array_values($cvesClean);


    } catch (\Throwable $th) {
        $response["code"] = 500;
        $response["result"]["error"] = "Erreur : " . $th->getMessage();
    }


    return $response;
}

/**
 * Renvoie le CPE complet selon le nom du logiciel et sa version
 * @param String $software : Nom du logiciel
 * @param String $version : Version du logiciel
 * @return String CPE
 */
function findCPE($softName, $version) {
    try {
        $db = connectDb();

        $sql = "SELECT cpeMatchWithoutVersion FROM software WHERE softwareName = :softName;";

        $stmt = $db->prepare($sql);
        $stmt->execute(['softName' => $softName]);

        // Si retourne rien alors false
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row == false) {
            throw new Exception("Logiciel, OS ou matériel inconnu", 1);
        }

        $cpeWithoutVersion = $stmt->fetch()["cpeMatchWithoutVersion"];



        $cpeMatch = $cpeWithoutVersion . ":" . $version;


        // Appeler l'api du NIST pour trouver le CPE exact

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://services.nvd.nist.gov/rest/json/cpes/2.0?cpeMatchString=" . $cpeMatch);
        // N'affiche pas le résultat automatiquement
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $respNist = curl_exec($ch);


        $respNistJSON = json_decode($respNist);

        if (empty($respNistJSON->products)) {
            throw new Exception("Aucun CPE trouvé pour cette version", 1);
        }

        $cpe = $respNistJSON->products[0]->cpe->cpeName;


        return $cpe;
        

    } catch (\Throwable $th) {
        $response["code"] = 500;
        $response["result"]["error"] = "Erreur : " . $th->getMessage();
    }

}


/**
 * Enregistre le CPE dans la db avec le bon logiciel
 * @param String $softName : Nom du logiciel
 * @param String $version : Version du logiciel
 * @param String $cpeName : Le CPE complet
 */
function saveCPE($softName, $version, $cpeName) {
    try {
        $db = connectDb();

        // Obtenir l'id du logiciel
        $sql = "SELECT softwareId FROM software WHERE softwareName = :softName;";

        $stmt = $db->prepare($sql);
        $stmt->execute(['softName' => $softName]);

        $softwareId = $stmt->fetch()["softwareId"];


        // Inserer le cpeName avec l'id du logiciel
        $sql = "INSERT IGNORE INTO version (cpeName, version, softwareId) VALUES (:cpe, :versionSoft, :softId)";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            'cpe' => $cpeName,
            'versionSoft' => $version,
            'softId' => $softwareId
        ]);


    } catch (\Throwable $th) {
        "Erreur : " . $th->getMessage();
    }

}


/**
 * Enregistre un CVE dans la db
 * @param String $cveId : Nom du logiciel
 * @param Date $publishedDate : Version du logiciel
 * @param Date $lastModifiedDate : Version du logiciel
 * @param String $status : Version du logiciel
 * @param String $description : Version du logiciel
 * @param String $exploitabilityScore : Version du logiciel
 * @param String $impactScore : Version du logiciel
 * @param String $baseScore : Le CPE complet
 */
function saveCVE($cveId, $publishedDate, $lastModifiedDate, $status, $description, $exploitabilityScore, $impactScore, $baseScore){
    
    try {
        $db = connectDb();

        $sql = "INSERT IGNORE INTO cve (cveNumber, published, lastModified, status, cveDescription, exploitabilityScore, impactScore, baseScore)
        VALUES (:cveId, :publishedDate, :lastModifiedDate, :status, :description, :exploitabilityScore, :impactScore, :baseScore)";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            'cveId' => $cveId,
            'publishedDate' => $publishedDate,
            'lastModifiedDate' => $lastModifiedDate,
            'status' => $status,
            'description' => $description,
            'exploitabilityScore' => $exploitabilityScore,
            'impactScore' => $impactScore,
            'baseScore' => $baseScore
        ]);

    } catch (\Throwable $th) {
        "Erreur : " . $th->getMessage();
    }
}

/**
 * Enregistre le lien entre une category et un cve dans la bdd
 * @param String $cveNumber : Numero du CVE
 * @param String $categoryName : Nom de la categorie
 */
function saveCategory($cveNumber, $categoryName) {

    try {
        $db = connectDb();


        // Obtenir l'id du CVE
        $sql = "SELECT cveId FROM cve WHERE cveNumber = :cveNumber;";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'cveNumber' => $cveNumber
        ]);

        $cveId = $stmt->fetch();
        if ($cveId === false) {
            throw new Exception("CVE non trouvée");
        }

        // Obtenir l'id de la categorie
        $sql = "SELECT categoryId FROM category WHERE categoryName = :categoryName;";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'categoryName' => $categoryName
        ]);

        $categoryId = $stmt->fetch();

        if ($categoryId === false) {
            throw new Exception("Categorie non trouvée");
        }

        $sql = "INSERT IGNORE INTO cve_category (cveId, categoryId)
        VALUES (:cveId, :categoryId)";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            'cveId' => $cveId["cveId"],
            'categoryId' => $categoryId["categoryId"]
        ]);

    } catch (\Throwable $th) {
        "Erreur : " . $th->getMessage();
    }

}

/**
 * Enregistre le lien entre une version et un cve dans la bdd
 * @param String $cveNumber : Numero du CVE
 * @param String $cpe : cpeName de la versiob
 */
function saveVersionLinkCve($cveNumber, $cpe) {

    try {
        $db = connectDb();


        // Obtenir l'id du CVE
        $sql = "SELECT cveId FROM cve WHERE cveNumber = :cveNumber;";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'cveNumber' => $cveNumber
        ]);

        $cveId = $stmt->fetch();
        if ($cveId === false) {
            throw new Exception("CVE non trouvée");
        }

        // Obtenir l'id de la version
        $sql = "SELECT versionId FROM version WHERE cpeName = :cpe;";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'cpe' => $cpe
        ]);

        $versionId = $stmt->fetch();

        if ($versionId === false) {
            throw new Exception("Version non trouvée");
        }

        $sql = "INSERT IGNORE INTO cve_version (versionId, cveId)
        VALUES (:versionId, :cveId)";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            'versionId' => $versionId["versionId"],
            'cveId' => $cveId["cveId"]
        ]);

    } catch (\Throwable $th) {
        "Erreur : " . $th->getMessage();
    }

}


/**
 * Demande au LLM qwen3 de donner une categorie par CVE et de retourner le resultat au format JSON
 * @param String $cves : La liste des CVE
 * @return String|Error Retourne la réponse JSON de l'api sous la forme d'un string ou une erreur
 */
function findCategoryWithLLM($cves) {
    try {
        $categories = getAllCategory();
        $prompt = 'You are a cyber specialyst. Your ultimate goal is to assign a category to a list of CVE.
        Each CVE can have only one category.
        You will get a list of categories, use only this list. You also will get a list of CVE, use only this list.
        I want you to respond for each CVE you will get, one category.

        Rules:
        - No explanation
        - No extra text
        - Exactly one object per CVE

        Return only valid JSON.
        ';

        $data = [
            "model" => "qwen3",
            "messages" => [
                [
                    "role" => "system",
                    "content" => json_encode($prompt)
                ],
                [
                    "role" => "user",
                    "content" => "List of category: " . json_encode($categories) . "\nList of CVE: " . json_encode($cves)
                ]
            ],
            "response_format" => [
                "type" => "json_schema",
                "json_schema" => [
                    "name" => "cve_category",
                    "schema" => [
                        "type" => "array",
                        "items" => [
                            "type" => "object",
                            "properties" => [
                                "cveNumber" => ["type" => "string"],
                                "categoryName" => ["type" => "string"]
                            ],
                            "required" => ["cveNumber", "categoryName"]
                        ]
                    ]
                ]
            ]
        ];


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.infomaniak.com/1/ai/" . PRODUCT_ID . "/openai/chat/completions");

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . TOKEN,
            'Content-Type: application/json'
        ]);

        // Le body
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        // N'affiche pas le résultat automatiquement
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $respLLM = json_decode(curl_exec($ch));

        $result = $respLLM->choices[0]->message->content;

        return $result;
    } catch (\Throwable $th) {
        "Erreur : " . $th->getMessage();
    }
    
}

/**
 * Cherche le nom et la description de chaque categorie
 * @return Array|Error $Le nom de chaque categorie avec leur description ou une erreur
 */
function getAllCategory() : Array|Error {
    $categories = [];
    try {
        $db = connectDb();

        $sql = "SELECT categoryName, categoryDescription FROM category";

        $result = $db->query($sql);

        $result = $result->fetchAll();

        foreach ($result as $key => $category) {
            $categories[] = [
               "categoryName" => $category["categoryName"], 
               "categoryDescription" => $category["categoryDescription"] 
            ];
        }       

    } catch (\Throwable $th) {
        "Erreur : " . $th->getMessage();
    }

    return $categories;
}


