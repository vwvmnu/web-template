const displayAd = () => {
    console.log("hhhh")
    const canvasBottom = new bootstrap.Offcanvas('#canvasBottom')
    canvasBottom.show()
}
const pushData = () => {
    displayAd2();
    $.ajax({
        async: true,
    })
}
const canvasMainUpload = () => {

    let input = document.createElement('input');
    input.type = 'file';
    input.click();
}
// const canvasBottom1 = new bootstrap.Offcanvas('#canvasBottom1')
// const canvasBottom2 = new bootstrap.Offcanvas('#canvasBottom2')
// const canvasBottom3 = new bootstrap.Offcanvas('#canvasBottom3')

const displayAd1 = () => {
    console.log("hhhh1")
    const canvasBottom1 = new bootstrap.Offcanvas('#canvasBottom1')
    canvasBottom1.show()
}
const displayAd2 = () => {
    console.log("hhhh2")
    const canvasBottom1 = new bootstrap.Offcanvas('#canvasBottom1')
    const canvasBottom2 = new bootstrap.Offcanvas('#canvasBottom2')
    canvasBottom1.hide()
    canvasBottom2.show()
}
const displayAd3 = () => {
    console.log("hhhh3")
    const canvasBottom2 = new bootstrap.Offcanvas('#canvasBottom2')
    const canvasBottom3 = new bootstrap.Offcanvas('#canvasBottom3')
    canvasBottom2.hide()
    canvasBottom3.show()
}
