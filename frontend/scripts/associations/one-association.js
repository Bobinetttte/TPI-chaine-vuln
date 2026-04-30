const title = document.querySelector("#title h1")
const infos = document.querySelector("#infos")
const description = document.querySelector("#association-description")


let params = new URLSearchParams(document.location.search);
let associationId = params.get("associationId");

title.textContent = "Association-" + associationId;

let result = await searchOneCVE(associationId);
console.log(result);

result["cves"].forEach((cve, key) => {
    infos.innerHTML += `
    <div class="cve">
        <a href="cve.html?cveNumber=`+cve["cveNumber"]+`"><p>`+cve["cveNumber"]+`</p></a>
        <p>`+cve["categoryName"]+`</p>
    </div>`;

    if (key != result["cves"].length - 1) {
        infos.innerHTML += `<p class="arrow">⟹</p>`;
    }

});

description.textContent = result["explanation"].explanation;



/**
 * Fetch l'api du tpi pour recevoir toutes les infos d'un seul CVE
 * @param String cveNumber : Numéro du CVE
 * @return Json CVE une erreur
 */
async function searchOneCVE(associationId) {
    const asso = await fetch("http://api.tpi.local/associations/GetAssociations.php?associationId=" + associationId);
    const result = await asso.json();
    return result;
}
