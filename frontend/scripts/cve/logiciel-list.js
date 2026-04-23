const data = document.querySelector("#suggestions-recherche");

let softs = await getSoft();


softs.forEach(soft => {
    data.innerHTML += '<option value="' + soft["softwareName"] + '">';
});


/**
 * Fetch l'api du tpi pour recevoir 20 CVE
 *
 * @return Array|String 20 CVE ou un string qui explique une erreur
 */
async function getSoft() {
    const softwares = await fetch("http://api.tpi.local/softwares/GetSoft.php");
    const result = await softwares.json();
    return result;
}
