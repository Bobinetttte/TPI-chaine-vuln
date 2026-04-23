import { cveCard } from '../Components/cve-card.js';

const form = document.querySelector("form");
const buttonAdd = document.querySelector("#add");
const data = document.querySelector("#data");
const softwares = document.querySelector("#softwares");

let logiciel = document.querySelector("#logiciel");
let version = document.querySelector("#version");
let softv = [];


// Quand on clique sur ajouter
buttonAdd.addEventListener("click", (event) => {

    event.preventDefault();

    if (logiciel.value != undefined && version.value != undefined) {
        softv.push([logiciel.value, version.value]);
        logiciel.value = "";
        version.value = "";
    }

    softwares.innerHTML = "";

    softv.forEach(soft => {
        softwares.innerHTML += "<div class='items'><p>" + soft[0] + " " + soft[1] + "</p></div>";
    });

});


// Quand on clique sur chercher
form.addEventListener("submit", async (event) => {

    event.preventDefault();


    data.innerHTML = "";

    softv.forEach(async soft => {

        let cves = await searchCveNIST(soft[0], soft[1]);

        // Gestion des erreurs
        if (cves["error"] !== undefined) {
            data.innerHTML += cves["error"];
        } else {
            cves.forEach(cve => {
                data.innerHTML += cveCard(cve["cveNumber"], cve["baseScore"], cve["softwareName"] + " " + cve["version"], cve["categoryName"]);
            });
        }

    });

});



/**
 * Fetch l'api du tpi pour recevoir tout les CVE selon le nom et la version du logiciel grace à l'api du NIST
 * @param String software : Nom du logiciel
 * @param String version : Version du logiciel
 * @return Json CVE une erreur
 */
async function searchCveNIST(software, version) {
    const cve = await fetch("http://api.tpi.local/cve/GetCve.php?software=" + software + "&version=" + version + "&nist=true");
    const result = await cve.json();
    return result;
}
