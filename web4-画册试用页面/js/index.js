const displayAd = () => {
    console.log("hhhh")
    const canvasBottom = new bootstrap.Offcanvas('#canvasBottom')
    canvasBottom.show()
}
const pushData = () => {
    $.ajax({
        async: true,
    })
}
const canvasMainUpload = () => {

    let input = document.createElement('input');
    input.type = 'file';
    input.click();
}
