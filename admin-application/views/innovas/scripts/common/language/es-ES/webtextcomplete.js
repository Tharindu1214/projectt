function loadTxt() {
    document.getElementById("tab0").innerHTML = "FUENTES";
    //document.getElementById("tab1").innerHTML = "FUENTES BÁSICAS";
    document.getElementById("tab2").innerHTML = "TAMAÑO";
    document.getElementById("tab3").innerHTML = "OSCURIDAD";
    document.getElementById("tab4").innerHTML = "PÁRRAFOS";
    document.getElementById("tab5").innerHTML = "LISTADOS";


    document.getElementById("lblColor").innerHTML = "COLOR:";
    document.getElementById("lblHighlight").innerHTML = "REALCE:";
    document.getElementById("lblLineHeight").innerHTML = "ALTURA DE LA LÍNEA:";
    document.getElementById("lblLetterSpacing").innerHTML = "ESPACIADO DE LETRAS:";
    document.getElementById("lblWordSpacing").innerHTML = "Espacio de palabras:";
    document.getElementById("lblNote").innerHTML = "Esta característica no es compatible actualmente en IE.";
    document.getElementById("divShadowClear").innerHTML = "CLARO";   
}
function writeTitle() {
    document.write("<title>" + "Texto" + "</title>")
}
function getTxt(s) {
    switch (s) {
        case "DEFAULT SIZE": return "TAMAÑO POR DEFECTO";
        case "Heading 1": return "Título 1";
        case "Heading 2": return "Título 2";
        case "Heading 3": return "Título 3";
        case "Heading 4": return "Título 4";
        case "Heading 5": return "Título 5";
        case "Heading 6": return "Título 6";
        case "Preformatted": return "Preformateado";
        case "Normal": return "Normal";
        case "Google Font": return "FUENTES DE GOOGLE:";
    }
}