function loadTxt() {
    document.getElementById("tab0").innerHTML = "YOUTUBE";
    document.getElementById("tab1").innerHTML = "ESTILOS";
    document.getElementById("tab2").innerHTML = "DIMENSIÓN";
    document.getElementById("lnkLoadMore").innerHTML = "Carga más";
    document.getElementById("lblUrl").innerHTML = "URL:";
    document.getElementById("btnCancel").value = "cerrar";
    document.getElementById("btnInsert").value = "insertar";
    document.getElementById("btnSearch").value = " Buscar ";    
}
function writeTitle() {
    document.write("<title>" + "Video de Youtube" + "</title>")
}