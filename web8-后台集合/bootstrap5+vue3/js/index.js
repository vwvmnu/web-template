const uploadDiv = document.getElementById('uploadDiv');

uploadDiv.addEventListener('dragover', function(event) {
    event.preventDefault(); // 防止默认的拖放行为
});

uploadDiv.addEventListener('drop', function(event) {
    event.preventDefault();
    const files = event.dataTransfer.files;

    uploadPDF(files)
    // 处理文件上传的逻辑
    const uploadSpecialEffects = document.getElementById("uploadSpecialEffects");
    uploadSpecialEffects.style

    console.log("拖放的文件:", files.name);
});
