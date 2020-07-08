function loadTxt() {
    document.getElementById("lblSearch").innerHTML = "BUSCAR:";
    document.getElementById("lblReplace").innerHTML = "REEMPLAZAR:";
    document.getElementById("lblMatchCase").innerHTML = "Caso de coincidencia";
    document.getElementById("lblMatchWhole").innerHTML = "Compare la palabra completa";

    document.getElementById("btnSearch").value = "buscar siguiente"; ;
    document.getElementById("btnReplace").value = "reemplazar";
    document.getElementById("btnReplaceAll").value = "reemplaza todo";
}
function getTxt(s) {
    switch (s) {
        case "Finished searching": return "Terminé de buscar el documento. \ NBuscar nuevamente desde la parte superior?";
        default: return "";
    }
}
function writeTitle() {
    document.write("<title>Buscar y reemplazar</title>")
}