

export function associationCard(catStart, catEnd, cves, versions, softNames, score) {

    let listSoftHTML = "";
    versions.forEach((version, index) => {
        listSoftHTML += "<p>" + softNames[index] + " " + version + "</p>"
    });

    let card = `
                <div class="card">
                <h3>`+ catStart + ` ⟹ ` + catEnd + `</h3>
                <div class="association-data">

                    <div class="association-softwares">
                        `+ listSoftHTML + `
                    </div>

                    <div class="association-footer">
                        <p>Utilise `+ cves.length + ` CVE</p>

                        <div class="association-score">
                            <p>`+ score + `</p>
                            <div>
                                <button>△</button>
                                <button>▽</button>
                        </div>
                    </div>
                </div>
            </div>
    `;

    return card;
}