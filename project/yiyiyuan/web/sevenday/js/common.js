function codefans() {
    var box = document.getElementById("divbox");
    if (box != null) {
        box.style.display = "none";
    }
}
setInterval("codefans()", 3000);