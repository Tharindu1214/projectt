function loadTxt() {
    document.getElementById("lblProtocol").innerHTML= "PROTOCOLO:";
    
    document.getElementById("tab0").innerHTML = "MIS ARCHIVOS";
    document.getElementById("tab1").innerHTML = "ESTILOS";
    document.getElementById("lblUrl").innerHTML = "URL:";
    document.getElementById("lblName").innerHTML = "NOMBRE:";
    document.getElementById("lblTitle").innerHTML = "TÍTULO:";
    document.getElementById("lblTarget1").innerHTML = "Abrir en la página";
    document.getElementById("lblTarget2").innerHTML = "Abrir en una nueva ventana";
    document.getElementById("lblTarget3").innerHTML = "Abrir en una caja de luz";
    document.getElementById("lnkNormalLink").innerHTML = "Enlace normal &raquo;";    
    document.getElementById("btnCancel").value = "cerrar";
    
}
function writeTitle() {
    document.write("<title>" + "Enlazar" + "</title>")
}
function getTxt(s) {
    switch (s) {
        case "insert": return "insertar";
        case "change": return "De acuerdo";
    }
}