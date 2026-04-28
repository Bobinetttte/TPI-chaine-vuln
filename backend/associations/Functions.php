<?php

require_once("./../PDOConnection.php");
require_once("./../secrets/infomaniak.php");


/**
 * Fetch l'api AITools d'infomaniak pour créer les chaines de CVE
 * @param String $cves : Liste des CVE détecté lors de la première partie
 * @param String $version : Version du logiciel
 * @return Json retourne des CVE ou une erreur
 */
function createChains($cves) {
    try {
        

        // prompt amélioré à l'aide de ChatGPT
        $prompt = 'You are a cybersecurity specialist. Your goal is to create chains of CVE from a given list.

        A chain is a sequence of vulnerabilities (CVE) that can be combined in a logical order,
        where each vulnerability enables or enhances the exploitation of the next one,
        ultimately leading to a higher-impact outcome (e.g., remote code execution, privilege escalation, or full system compromise).
        A chain have to use at least two CVE but a chain can be more that only two.

        Each chain must follow a realistic attack path:

            - Start from an initial access vector (e.g., unauthenticated access, XSS, SSRF)
            - Progress through intermediate steps (e.g., credential theft, lateral movement)
            - End with a final impact (e.g., root access, data exfiltration)

        Rules:

            - Use only the CVEs provided
            - Do not invent vulnerabilities
            - Respect technical coherence (versions, software compatibility)
            - Each CVE must have a clear role in the chain
            - Avoid unrelated or redundant CVEs

        STRICT RULES:
        - Return ONLY a JSON array.
        - Do NOT add any fields except "explanation" and "chain".
        - Do NOT use chainId, steps, role, description, or finalImpact.
        - Each "chain" must be an ordered list of CVE.
        - The order MUST reflect a real attack path (initial access → escalation → impact).
        - Each CVE must logically enable the next one.

        FORMAT:
        [
          {
            "explanation": "short explanation of the chain",
            "chain": [
              { "cveNumber": "CVE-XXXX-XXXX" }
            ]
          }
        ]

        If no valid chain exists, return an empty array [].

        Return only valid JSON.
        Answer in french.
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
                    "content" => "List of CVE: " . json_encode($cves)
                ]
            ],
            "response_format" => [
                "type" => "json_schema",
                "json_schema" => [
                    "name" => "chains",
                    "schema" => [
                        "type" => "array",
                        "items" => [
                            "type" => "object",
                            "properties" => [
                                "explanation" => [
                                    "type" => "string"
                                ],
                                "chain" => [
                                    "type" => "array",
                                    "items" => [
                                        "type" => "object",
                                        "properties" => [
                                            "cveNumber" => [
                                                "type" => "string"
                                            ]
                                        ],
                                        "required" => ["cveNumber"]
                                    ]
                                ]
                            ],
                            "required" => ["explanation", "chain"]
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


        // Point 9 et 10 de l'algorytme
        $respLLM = json_decode(curl_exec($ch));

        $result = $respLLM->choices[0]->message->content;

        return json_decode($result);
    } catch (\Throwable $th) {
        "Erreur : " . $th->getMessage();
    }
}


/**
 * Transforme les chaînes en associations
 * @param Object $chain : Liste des CVE utilise pour cette association
 * @return String $associationId : Retourne l'id de l'association
 */
function saveChain($chain) {
    try {
        $db = connectDb();


        $sql = "INSERT INTO association (score, explanation) VALUES (0, :explanation) ;";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'explanation' => $chain->explanation
        ]);

        // Obtenir l'id de l'association
        $sql = "SELECT associationId FROM association WHERE explanation = :explanation;";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'explanation' => $chain->explanation
        ]);

        $associationId = $stmt->fetch();

        if ($associationId === false) {
            $response["code"] = 500;
            $response["result"]["error"] = "Association introuvable";
            return $response;
        }



        foreach ($chain->chain as $order => $cve) {
            
            // Obtenir l'id du cve
            $sql = "SELECT cveId FROM cve WHERE cveNumber = :cveNumber;";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'cveNumber' => $cve->cveNumber
            ]);

            $cveId = $stmt->fetch();

            if ($cveId === false) {
                $response["code"] = 500;
                $response["result"]["error"] = "CVE introuvable";
                return $response;
            }



            $sql = "INSERT INTO association_cve (associationId, cveId, cveOrder)
            VALUES (:associationId, :cveId, :cveOrder)";

            $stmt = $db->prepare($sql);
            $stmt->execute([
                'associationId' => $associationId["associationId"],
                'cveId' => $cveId["cveId"],
                'cveOrder' => $order
            ]);
        }

        return $associationId["associationId"];


    } catch (\Throwable $th) {
        "Erreur : " . $th->getMessage();
    }
}

function getAssociation($associationId) {
    try {

        $association = [];

        $db = connectDb();


        // Obtenir la categorie du premier CVE de l'association
        $sql = "SELECT cat.categoryName
        FROM association_cve ac
        LEFT JOIN cve_category cc ON cc.cveId = ac.cveId
        LEFT JOIN category cat ON cat.categoryId = cc.categoryId
        WHERE ac.associationId = :associationId AND ac.cveOrder = 0
        LIMIT 1;";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            'associationId' => $associationId
        ]);

        $categoryNameStart = $stmt->fetch();

        if ($categoryNameStart["categoryName"] == null) {
            $response["result"]["error"] = "Aucune categorie trouvée";
        }

        $association["categoryNameStart"] = $categoryNameStart["categoryName"];


        // Obtenir la categorie du dernier CVE de l'association
        $sql = "SELECT cat.categoryName
        FROM association_cve ac
        LEFT JOIN cve_category cc ON cc.cveId = ac.cveId
        LEFT JOIN category cat ON cat.categoryId = cc.categoryId
        WHERE ac.associationId = :associationId AND ac.cveOrder = (
            SELECT MAX(cveOrder)
            FROM association_cve
            WHERE associationId = :associationId
        )
        LIMIT 1;";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            'associationId' => $associationId
        ]);

        $categoryNameEnd = $stmt->fetch();

        if ($categoryNameEnd["categoryName"] == null) {
            $response["result"]["error"] = "Aucune categorie trouvée";
        }

        $association["categoryNameEnd"] = $categoryNameEnd["categoryName"];



        // Obtenir les CVE
        $sql = "SELECT cveId
        FROM association_cve
        WHERE associationId = :associationId;";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            'associationId' => $associationId
        ]);

        $cveIds = $stmt->fetchAll();

        //if ($categoryNameEnd["categoryName"] == null) {
        //    $response["result"]["error"] = "Aucune categorie trouvée";
        //}

        $association["cvesId"] = $cveIds;



        $response["associations"] = $association;

        return $response;

    } catch (\Throwable $th) {
        "Erreur : " . $th->getMessage();
    }
}