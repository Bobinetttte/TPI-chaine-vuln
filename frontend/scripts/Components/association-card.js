

export function associationCard(catStart, catEnd, cves, versions, softNames, score, assoId) {

    let listSoftHTML = "";
    versions.forEach((version, index) => {
        listSoftHTML += "<p>" + softNames[index] + " " + version + "</p>"
    });

    let card = `
                <div class="card" id="`+ assoId +`">
                <a href="association.html?associationId=`+ assoId +`"><h3>`+ catStart + ` ⟹ ` + catEnd + `</h3></a>
                <div class="association-data">

                    <div class="association-softwares">
                        `+ listSoftHTML + `
                    </div>

                    <div class="association-footer">
                        <p>Utilise `+ cves.length + ` CVE</p>

                        <div class="association-score">
                            <p>`+ score + `</p>
                            <div>
                                <button class="association-btn-up" >△</button>
                                <button class="association-btn-down" >▽</button>
                        </div>
                    </div>
                </div>
            </div>
    `;

    return card;
}