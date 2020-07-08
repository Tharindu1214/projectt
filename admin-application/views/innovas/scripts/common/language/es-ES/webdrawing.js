function loadTxt() {
    document.getElementById("tab0").innerHTML = "DIBUJO";
    document.getElementById("tab1").innerHTML = "AJUSTES";
    document.getElementById("tab3").innerHTML = "SALVADO";

    document.getElementById("lblWidthHeight").innerHTML = "TAMAÑO DEL LIENZO:";
    
    var optAlign = document.getElementsByName("optAlign");
    optAlign[0].text = ""
    optAlign[1].text = "Izquierda"
    optAlign[2].text = "Derecha"

    document.getElementById("lblTitle").innerHTML = "TÍTULO:";
    document.getElementById("lblAlign").innerHTML = "ALINEAR:";
    document.getElementById("lblSpacing").innerHTML = "V-ESPACIADO:";
    document.getElementById("lblSpacingH").innerHTML = "H-ESPACIADO:";

    document.getElementById("btnCancel").value = "close";
}
function writeTitle() {
    document.write("<title>" + "Dibujo" + "</title>")
}
function getTxt(s) {
    switch (s) {
        case "insert": return "insertar";
        case "change": return "De acuerdo";
        case "DELETE": return "BORRAR";
    }
}