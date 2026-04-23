import { cveCard } from '../Components/cve-card.js';

const form = document.querySelector("form");
const data = document.querySelector("#data");

form.addEventListener("submit", async (event) => {
    event.preventDefault();


    const formData = new FormData(event.target);

    const logiciel = formData.get("logiciel");
    const version = formData.get("version");

    let cves = await searchCVE(logiciel, version);

    data.innerHTML = "";

    // Gestion des erreurs
    if (cves["error"] !== undefined) {
        data.innerHTML += cves["error"];
    } else {

        cves.forEach(cve => {
            data.innerHTML += cveCard(cve["cveNumber"], cve["baseScore"], cve["softwareName"] + " " + cve["version"], cve["categoryName"]);
        });

    }
});


/**
 * Fetch l'api du tpi pour recevoir tout les CVE selon le nom et la version du logiciel
 * @param String software : Nom du logiciel
 * @param String version : Version du logiciel
 * @return Json CVE une erreur
 */
async function searchCVE(software, version) {
    const cve = await fetch("http://api.tpi.local/cve/GetCve.php?software=" + software + "&version=" + version);
    const result = await cve.json();
    return result;
}
