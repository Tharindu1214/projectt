function loadTxt() {
    document.getElementById("tab0").innerHTML = "PÓSTER";
    document.getElementById("tab1").innerHTML = "MPEG4 VIDEO";
    document.getElementById("tab2").innerHTML = "MPEG4 VIDEO";
    document.getElementById("tab3").innerHTML = "WebM VIDEO";
    document.getElementById("lbImage").innerHTML = "Póster / imagen de vista previa (.png o .jpg):";
    document.getElementById("lblMP4").innerHTML = "Video MPEG4 (.mp4):";
    document.getElementById("lblOgg").innerHTML = "Video de Ogg (.ogv):";
    document.getElementById("lblWebM").innerHTML = "Video webm (.webm):";
    document.getElementById("lblDimension").innerHTML = "Introduzca el tamaño del video (ancho x alto):";
    document.getElementById("divNote1").innerHTML = "Para información sobre video HTML5 ver: <a href='http://www.w3schools.com/html5/html5_video.asp' target='_blank'>www.w3schools.com/html5/html5_video.asp</a>." +
        "Para información sobre video HTML5 ver: MP4, WebM (e.g. para MSIE 9+), y Ogg (e.g. para FireFox). El navegador utilizará el primer formato reconocido." +
        "Además, necesitará una vista previa o una imagen de 'póster'.";
    document.getElementById("divNote2").innerHTML = "Para convertir un video en HTML5 (MP4, WebM & Ogg) puede usar: <a href='http://www.mirovideoconverter.com/' target='_blank'>www.mirovideoconverter.com</a>";

    document.getElementById("btnCancel").value = "cerrar";
    document.getElementById("btnInsert").value = "insertar";
}
function writeTitle() {
    document.write("<title>" + "Video HTML5" + "</title>")
}