import { associationCard } from '../Components/association-card.js';

const data = document.querySelector("#data");

let tenAssociations = await getAssociations();

console.log(tenAssociations);

// Gestion des erreurs
if (tenAssociations["error"] !== undefined) {
    data.innerHTML += tenAssociations["error"];
} else {

    tenAssociations.associations.forEach(association => {

        if (association["error"] === undefined) {
            data.innerHTML += associationCard(association["categoryNameStart"], association["categoryNameEnd"], association["cvesId"],
                association["versions"], association["softNames"], association["score"]);
        }


    });

}


/**
 * Fetch l'api du tpi pour recevoir les 10 associations avec le score le plus élevé
 *
 * @return Json 10 CVE ou une erreur
 */
async function getAssociations() {
    const asso = await fetch("http://api.tpi.local/associations/GetAssociations.php");
    const result = await asso.json();
    return result;
}
