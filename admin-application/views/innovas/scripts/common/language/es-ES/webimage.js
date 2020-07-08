function loadTxt() {
    document.getElementById("tab0").innerHTML = "Fuentes";
    document.getElementById("tab1").innerHTML = "MIS ARCHIVOS";
    document.getElementById("tab2").innerHTML = "ESTILOS";
    document.getElementById("tab3").innerHTML = "Efectos";
    document.getElementById("lblTag").innerHTML = "ETIQUETA:";
    document.getElementById("lblFlickrUserName").innerHTML = "Nombre de usuario de Flickr:";
    document.getElementById("lnkLoadMore").innerHTML = "Carga más";
    document.getElementById("lblImgSrc").innerHTML = "FUENTE DE IMAGEN:";
    document.getElementById("lblWidthHeight").innerHTML = "Alto x ancho:";
    
    var optAlign = document.getElementsByName("optAlign");
    optAlign[0].text = ""
    optAlign[1].text = "Izquierda"
    optAlign[2].text = "Derecha"

    document.getElementById("lblTitle").innerHTML = "TÍTULO:";
    document.getElementById("lblAlign").innerHTML = "ALINEAR:";
    document.getElementById("lblMargin").innerHTML = "MARGEN: (ARRIBA / DERECHA / INFERIOR / IZQUIERDA)";
    document.getElementById("lblSize1").innerHTML = "CUADRADO PEQUEÑO";
    document.getElementById("lblSize2").innerHTML = "MINIATURA";
    document.getElementById("lblSize3").innerHTML = "PEQUEÑA";
    document.getElementById("lblSize5").innerHTML = "MEDIO";
    document.getElementById("lblSize6").innerHTML = "GRANDE";

    document.getElementById("lblOpenLarger").innerHTML = "ABRIR UNA IMAGEN MÁS GRANDE EN UNA CAJA DE LUZ, O";
    document.getElementById("lblLinkToUrl").innerHTML = "ENLACE A URL:";
    document.getElementById("lblNewWindow").innerHTML = "ABIERTO EN UNA NUEVA VENTANA.";
    document.getElementById("btnCancel").value = "cerrar";
    document.getElementById("btnSearch").value = " Buscar ";

    document.getElementById("lblMaintainRatio").innerHTML = "Mantener la relación";
    document.getElementById("resetdimension").innerHTML = "REINICIAR DIMENSIÓN";

    document.getElementById("btnRestore").value = "Imagen original";
    document.getElementById("btnSaveAsNew").value = "Guardar como nueva imagen"; 
}
function writeTitle() {
    document.write("<title>" + "Imagen" + "</title>")
}
function getTxt(s) {
    switch (s) {
        case "insert": return "insertar";
        case "change": return "De acuerdo";
        case "notsupported": return "La imagen externa no es compatible.";
    }
}