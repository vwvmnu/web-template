let count = 0;

const msgList = ["123在9秒前浏览的画册", "456在3秒前浏览的画册", "小黑在8秒前浏览的画册", "小白小在8秒前浏览的画册", "小白小在8秒前浏览的画册", "小白小在8前浏览的画册"]
const msgList1 = ["小白小在8秒前提交了ai画册", "456在3秒前开启了ai画册", "小黑在8秒前提交了ai画册", "小白小在8秒前提交了ai画册", "小白小在8秒前提交了ai画册", "小白小在8秒前提交了ai画册"]
const msgList3 = ["123****1231上传了PDF文件", "123****1233上传了PDF文件", "123****1234上传了PDF文件", "123****1235上传了PDF文件", "123****1236上传了PDF文件", "123****1237上传了PDF文件"]

const size = 5;
let runningMark = true;
let height = 25;

/**
 * 休眠函数
 * @param ms
 * @returns {Promise<unknown>}
 */
const mySleep = (ms) => {
    return new Promise(resolve => setTimeout(resolve, ms));
}

/**
 * 添加字元素
 * @param msg 消息
 * @param container
 * @returns {Promise<void>}
 */
async function addSlide(msg, container = document.querySelector('.carouselList-container')) {
    // Create a new slide
    let newSlide = document.createElement('div');
    newSlide.className = 'mySlide';
    newSlide.innerText = msg;
    count++;

    // Initially place the new slide below the visible area based on container's height

    // Append the new slide to the container
    container.appendChild(newSlide);

    await mySleep(32);  // Small delay to ensure the new slide's transform is applied

    // Move the slides upwards
    let slides = container.querySelectorAll('.mySlide');
    for (let slide of slides) {
        let computedStyle = window.getComputedStyle(slide);
        let marginTop = parseInt(computedStyle.marginTop, 10);
        let height = parseInt(computedStyle.height, 10);
        slide.style.transition = 'transform 0.5s ease-in-out';
        slide.style.transform = `translateY(-${marginTop + height}px)`;
    }



    await mySleep(510);  // Match the duration of the transition
    container.removeChild(slides[0]);

    // Reset transform for remaining slides
    slides = document.querySelectorAll('.mySlide');
    for (let slide of slides) {
        slide.style.transition = 'none';
        slide.style.transform = `translateY(0px)`;
    }

    await mySleep(700);  // Remaining time to reach the total sleep time of 1000ms as earlier
}

/**
 * 初始化列表
 * @param msgList 消息列表
 * @param size 初始化大小
 * @param container 父级document元素
 */
const initCarouserList = (msgList = ["123", "246", "789"], size = 3, container = document.querySelector('.carouselList-container')) => {
    // 绘制列表
    for (let i = 0; i < size; i++) {
        let newSlide = document.createElement('div');
        newSlide.className = 'mySlide';
        newSlide.innerText = msgList[msgList.length - i - 1];
        // debugger
        container.appendChild(newSlide);
    }
}

/**
 * 启动滚动列表
 * @param msgList 数据
 * @param container 组件
 * @returns {Promise<void>}
 */
const startCarouserList = async (msgList=["123", "246", "789"], container = document.querySelector('.carouselList-container')) => {
    initCarouserList(msgList, 3, container);
    runningMark = true;
    while (runningMark) {
        for (let i = 0; i < msgList.length; i++) {
            await addSlide(msgList[i], container);
        }
    }
}


const StopCarouserList = () =>{
    runningMark = false;
}
container1 = document.querySelector('.carouselList-container')
startCarouserList(msgList, container1).then();




const size3 = 5;
let runningMark3 = true;


/**
 * 初始化列表
 * @param msgList 消息列表
 * @param size 初始化大小
 * @param container 父级document元素
 */
const initCarouserList3 = (msgList = [""], size = 3, container = document.querySelector('.carouselList-container3')) => {
    // 绘制列表
    for (let i = 0; i < size; i++) {
        let newSlide = document.createElement('div');
        newSlide.className = 'mySlide3';
        newSlide.innerText = msgList[msgList.length - i - 1];
        // debugger
        container.appendChild(newSlide);
    }
}

const startCarouserList3 = async (timeSize = 0) => {
    runningMark3 = true;
    while (runningMark3) {
        for (let i = 0; i < msgList3.length; i++) {
            await addSlide3(msgList3[i]);
        }
    }
}

const StopCarouserList3 = () =>{
    runningMark3 = false;
}

/**
 * 添加字元素
 * @param msg 消息
 * @param container
 * @returns {Promise<void>}
 */
async function addSlide3(msg, container = document.querySelector('.carouselList-container3')) {
    // Create a new slide
    let newSlide = document.createElement('div');
    newSlide.className = 'mySlide3';
    newSlide.innerText = msg;
    count++;

    // Initially place the new slide below the visible area based on container's height

    // Append the new slide to the container
    container.appendChild(newSlide);

    await mySleep(64);  // Small delay to ensure the new slide's transform is applied

    // Move the slides upwards
    let slides = container.querySelectorAll('.mySlide3');
    for (let slide of slides) {
        let computedStyle = window.getComputedStyle(slide);
        let marginTop = parseInt(computedStyle.marginTop, 10);
        let height = parseInt(computedStyle.height, 10);
        slide.style.transition = 'transform 0.5s ease-in-out';
        slide.style.transform = `translateY(-${height}px)`;
    }

    await mySleep(600);  // Match the duration of the transition
    container.removeChild(slides[0]);

    // Reset transform for remaining slides
    slides = document.querySelectorAll('.mySlide3');
    for (let slide of slides) {
        slide.style.transition = 'none';
        slide.style.transform = `translateY(0px)`;
    }

    await mySleep(600);  // Remaining time to reach the total sleep time of 1000ms as earlier
}



