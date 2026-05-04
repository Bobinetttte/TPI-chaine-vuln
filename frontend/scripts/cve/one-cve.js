const title = document.querySelector("#title h1")
const category = document.querySelector("#title h2")
const softwareNameVersion = document.querySelector("#cve-softwareName-version")
const company = document.querySelector("#cve-company")
const status = document.querySelector("#cve-status")
const baseScore = document.querySelector("#cve-baseScore")
const exploitabilityScore = document.querySelector("#cve-exploitabilityScore")
const impactScore = document.querySelector("#cve-impactScore")
const published = document.querySelector("#cve-published")
const lastModified = document.querySelector("#cve-lastModified")
const description = document.querySelector("#cve-description")


let params = new URLSearchParams(document.location.search);
let cveNumber = params.get("cveNumber");

document.title = cveNumber;
title.textContent = cveNumber;

let result = await searchOneCVE(cveNumber);

category.textContent = result["categoryName"];
softwareNameVersion.textContent = result["softwareName"] + " " + result["version"] + " (" + result["company"] + ")";
status.textContent = result["status"];
baseScore.textContent = result["baseScore"];
exploitabilityScore.textContent = result["exploitabilityScore"];
impactScore.textContent = result["impactScore"];
published.textContent = result["published"];
lastModified.textContent = result["lastModified"];
description.textContent = result["cveDescription"];



/**
 * Fetch l'api du tpi pour recevoir toutes les infos d'un seul CVE
 * @param String cveNumber : Numéro du CVE
 * @return Json CVE une erreur
 */
async function searchOneCVE(cveNumber) {
    const cve = await fetch("http://api.tpi.local/cve/GetCve.php?cveNumber=" + cveNumber);
    const result = await cve.json();
    return result;
}
