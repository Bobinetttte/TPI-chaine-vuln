import { associationCard } from '../Components/association-card.js';

const data = document.querySelector("#data");


updateAssociations();

data.addEventListener("click", async (event) => {
    const card = event.target.closest(".card");
    const btn = event.target.closest(".association-btn-up, .association-btn-down");

    if (card != null && btn != null) {

        let idAsso = card.id;
        let action = "up";

        if (btn.attributes.class.nodeValue == "association-btn-down") {
            action = "down";
        }

        await updateAssociation(idAsso, action);
        await updateAssociations();

    }

});



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


/**
 * Fetch l'api du tpi pour mettre à jour le score d'une association
 * @param Int idAsso : Id de l'association cible
 * @param String action : action souhaitée (up/down)
 */
async function updateAssociation(idAsso, action) {

    const association = await fetch("http://api.tpi.local/associations/UpdateAssociation.php?id=" + idAsso + "&action=" + action, {
        method: "PUT",
    });
     
}

/**
 * Met à jour l'affichage des associations
 */
async function updateAssociations() {
    let tenAssociations = await getAssociations();

    data.innerHTML = "";

    // Gestion des erreurs
    if (tenAssociations["error"] !== undefined) {
        data.innerHTML += tenAssociations["error"];
    } else {

        tenAssociations.associations.forEach(association => {

            if (association["error"] === undefined) {
                data.innerHTML += associationCard(association["categoryNameStart"], association["categoryNameEnd"], association["cvesId"],
                    association["versions"], association["softNames"], association["score"], association["idAsso"]);
            }


        });

    }
}