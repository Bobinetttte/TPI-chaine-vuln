import { cveCard } from '../Components/cve-card.js';
import { associationCard } from '../Components/association-card.js';

const form = document.querySelector("form");
const error = document.querySelector("#error");
const buttonAdd = document.querySelector("#add");
const data = document.querySelector("#data");
const associationsDiv = document.querySelector("#associations");
const infos = document.querySelector("#infos");
const softwares = document.querySelector("#softwares");

let logiciel = document.querySelector("#logiciel");
let version = document.querySelector("#version");
let softv = [];


// Quand on clique sur ajouter
buttonAdd.addEventListener("click", (event) => {

    event.preventDefault();
    console.log(logiciel.value);
    if (logiciel.value) {


        softv.push([logiciel.value, version.value]);
        logiciel.value = "";
        version.value = "";

        softwares.innerHTML = "";

        softv.forEach(soft => {
            softwares.innerHTML += "<div class='items'><p>" + soft[0] + " " + soft[1] + "</p></div>";
        });

        // Supprime l'erreur afficher si le formulaire est remplit
        error.style.display = "none";

    }
    else {
        // Affiche l'erreur si le formulaire est vide
        error.style.display = "block";
    }



});


// Quand on clique sur chercher
form.addEventListener("submit", async (event) => {

    event.preventDefault();

    error.style.display = "none";

    data.innerHTML = "";
    associationsDiv.innerHTML = "";

    let allCve = [];

    infos.textContent = "Les recherches sont en cours. Merci de ne pas recharger la page. Cela peut prendre plusieurs minutes";

    for (const soft of softv) {
        infos.textContent = "Les recherches sont en cours pour "+ soft[0] + " "+ soft[1] +". Merci de ne pas recharger la page. Cela peut prendre plusieurs minutes";


        let cves = await searchCveNIST(soft[0], soft[1]);
        allCve.push(cves);
        // Gestion des erreurs
        if (cves["error"] !== undefined) {
            data.innerHTML += cves["error"];
        } else {
            cves["cve"].forEach(cve => {
                data.innerHTML += cveCard(cve["cveNumber"], cve["baseScore"],
                    cve["softwareName"] + " " + cve["version"], cve["categoryName"]);
            });
        }


    }

    infos.textContent = "Création des associations. Merci de ne pas recharger la page. Cela peut prendre plusieurs minutes";
    let associations = await createAsso(allCve);

    associations.associations.forEach(association => {
        console.log(association);

        if (association["error"] === undefined) {
            associationsDiv.innerHTML += associationCard(association["categoryNameStart"], association["categoryNameEnd"], association["cvesId"],
            association["versions"], association["softNames"], association["score"]);
        }


    });


    
    infos.textContent = "Recherches terminées. Merci de votre patience";


});



/**
 * Fetch l'api du tpi pour recevoir tout les CVE selon le nom et la version du logiciel grace à l'api du NIST
 * @param String software : Nom du logiciel
 * @param String version : Version du logiciel
 * @return Json retourne des CVE ou une erreur
 */
async function searchCveNIST(software, version) {
    const cve = await fetch("http://api.tpi.local/cve/GetCve.php?software=" + software + "&version=" + version + "&nist=true");
    const result = await cve.json();
    return result;
}


/**
 * Fetch l'api du tpi pour créer des chainages puis recevoir des associations
 * @param Array cveListJSON : Liste de tout les CVE retourner de chaque logiciel, OS et/ou materiels
 * @return Json retourne des associations ou une erreur
 */
async function createAsso(cveList) {

    const associations = await fetch("http://api.tpi.local/associations/CreateAssociations.php?chainSearch=true", {
        method: "POST",
        headers: {
            // Dis au serveur qu'on envoie du JSON
            'Content-Type': 'application/json' 
        },
        body: JSON.stringify(cveList)
    });
    const result = await associations.json();
    return result;
}