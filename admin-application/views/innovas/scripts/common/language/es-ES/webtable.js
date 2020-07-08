function loadTxt() {
    document.getElementById("tab0").innerHTML = "INSERTAR";
    document.getElementById("tab1").innerHTML = "MODIFY";
    document.getElementById("tab2").innerHTML = "AUTO FORMATO";
    document.getElementById("btnDelTable").value = "Eliminar la tabla seleccionada";
    document.getElementById("btnIRow1").value = "Insertar fila (arriba)";
    document.getElementById("btnIRow2").value = "Insertar fila (abajo)";
    document.getElementById("btnICol1").value = "Insertar columna (izquierda)";
    document.getElementById("btnICol2").value = "Insertar columna (derecha)";
    document.getElementById("btnDelRow").value = "Borrar fila";
    document.getElementById("btnDelCol").value = "Eliminar columna";
    document.getElementById("btnMerge").value = "Fusionar celular";
    document.getElementById("lblFormat").innerHTML = "FORMATO:";
    document.getElementById("lblTable").innerHTML = "Mesa";
    document.getElementById("lblCell").innerHTML = "Célula";
    document.getElementById("lblEven").innerHTML = "Incluso filas";
    document.getElementById("lblOdd").innerHTML = "Filas impares";
    document.getElementById("lblCurrRow").innerHTML = "Fila actual";
    document.getElementById("lblCurrCol").innerHTML = "Columna actual";
    document.getElementById("lblBg").innerHTML = "Fondo:";
    document.getElementById("lblText").innerHTML = "Texto:";    
    document.getElementById("lblBorder").innerHTML = "FRONTERA:";
    document.getElementById("lblThickness").innerHTML = "Espesor:";
    document.getElementById("lblBorderColor").innerHTML = "Color:";
    document.getElementById("lblCellPadding").innerHTML = "Acolchado de células:";
    document.getElementById("lblFullWidth").innerHTML = "Ancho completo";
    document.getElementById("lblAutofit").innerHTML = "Autofit";
    document.getElementById("lblFixedWidth").innerHTML = "Ancho fijo:";
    document.getElementById("lnkClean").innerHTML = "LIMPIAR";
    document.getElementById("lblTextAlign").innerHTML = "TEXTO ALINEADO:";
    document.getElementById("btnAlignLeft").value = "Izquierda";
    document.getElementById("btnAlignCenter").value = "Centrar";
    document.getElementById("btnAlignRight").value = "Derecha";
    document.getElementById("btnAlignTop").value = "Parte superior";
    document.getElementById("btnAlignMiddle").value = "Medio";
    document.getElementById("btnAlignBottom").value = "Fondo";

    document.getElementById("lblColor").innerHTML = "COLOR:";
    document.getElementById("lblCellSize").innerHTML = "TAMAÑO CELULAR:";
    document.getElementById("lblCellWidth").innerHTML = "Anchura:";
    document.getElementById("lblCellHeight").innerHTML = "Altura:";       
}
function writeTitle() {
    document.write("<title>" + "Mesa" + "</title>")
}
function getTxt(s) {
    switch (s) {
        case "Clean Formatting": return "Formato limpio";
    }
}