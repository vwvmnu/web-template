
const {createApp, ref, onMounted} = Vue;
const UpIcon = {
    template: ``, setup() {
    }
};

const App = {
    //使用短横线命名
    components: {
        UpIcon,
    },
    setup() {

        // 预加载文件
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'path/to/pdf.worker.js';
        const zip = new JSZip();


        const pdfFileClick = (e) => {
            // debugger
            const file = e.target.files[0];
            if (file.type !== 'application/pdf') {
                alert('Please upload a PDF file.');
                return;
            }

            const fileReader = new FileReader();

            fileReader.onload = async function () {
                const typedarray = new Uint8Array(this.result);

                // 加载pdf
                const loadingTask = pdfjsLib.getDocument(typedarray);
                const pdf = await loadingTask.promise;
                debugger

                const showPdf = document.getElementById('showPdf');

                for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++){
                    if (pageNum > 2){
                        return;
                    }
                    console.log(`开始解析第${pageNum}页`);
                    pdf.getPage(pageNum).then(  page => {
                        const scale = 3;
                        const viewport = page.getViewport({scale: scale});

                        const canvas = document.createElement("canvas");
                        canvas.height = viewport.height;
                        canvas.width = viewport.width;
                        const renderContext = {
                            canvasContext: canvas.getContext('2d'),
                            viewport: viewport
                        };

                        // 渲染PDF页到canvas
                        page.render(renderContext).promise.then(() => {
                            canvas.toBlob(blob => {
                                const link = document.createElement('a');
                                link.href = URL.createObjectURL(blob);
                                link.download = 'page.png';
                                link.click();

                                // 可选：释放对象URL
                                URL.revokeObjectURL(link.href);
                            }, 'image/png');

                            // 当页面渲染完成后，将canvas转换为图片并保存
                            canvas.toBlob(blob => {
                                console.log(`开始压缩第${pageNum}页`);
                                zip.file(`page_${pageNum}.png`, blob);

                                if (pageNum === pdf.numPages) {
                                    zip.generateAsync({type: "blob"}).then(function (content) {
                                        // 生成ZIP并提供下载
                                        const link = document.createElement('a');
                                        link.href = URL.createObjectURL(content);
                                        link.download = 'pdf_images.zip';
                                        link.click();
                                    });
                                }

                            }, 'image/png');
                        });


                    });
                }
            };

            fileReader.readAsArrayBuffer(file);
        }

        onMounted(() => {

        })

        return {
            pdfFileClick,
        };
    }
};

createApp(App).mount('#app');
