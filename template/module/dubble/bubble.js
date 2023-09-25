let count = 0;

const size = 5;
let runningMark = true;
let height = 25;

const initMyBarrageConfig = {
    display: "absolute",
    zIndex: 1,
    width: "100%",
    position: "fixed",
    bottom: "18%",
    left: "2%",
    textAlign: "left",
    overflow: "hidden",
    height: "50px",
    // 显示几行
    numberOfColumns: 3,
    // 每个显示的高度大小
    barrageHeight: 20,
}

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
        slide.style.transition = 'transform 0.8s ease-in-out';
        slide.style.transform = `translateY(-${marginTop + height}px)`;
    }


    await mySleep(811);  // Match the duration of the transition
    container.removeChild(slides[0]);

    // Reset transform for remaining slides
    slides = document.querySelectorAll('.mySlide');
    for (let slide of slides) {
        slide.style.transition = 'none';
        slide.style.transform = `translateY(0px)`;
    }

    await mySleep(700);  // Remaining time to reach the total sleep time of 1000ms as earlier
    // debugger
}

/**
 * 初始化列表以及布局
 * @param msgList 消息列表 msgList = ["123", "246", "789"]
 * @param size 初始化大小
 * @param myBarrage 父级document元素
 * @param myBarrageConfig 配置
 */
const initCarouserList = (msgList = ["123", "246", "789"], myBarrage = document.querySelector('.myBarrage'), myBarrageConfig= {}) => {

    // 弹幕的相关配置

    myBarrageConfig = Object.assign(initMyBarrageConfig, myBarrageConfig);

    myBarrageConfig.height = myBarrageConfig.numberOfColumns * myBarrageConfig.barrageHeight + "px"
    myBarrage.style.setProperty('--height', myBarrageConfig.height);
    myBarrage.style.setProperty('--numberOfColumns', myBarrageConfig.numberOfColumns);

    myBarrage.style.display = myBarrageConfig.display;
    myBarrage.style.zIndex = myBarrageConfig.zIndex;
    myBarrage.style.height = myBarrageConfig.height;
    myBarrage.style.width = myBarrageConfig.width;
    myBarrage.style.position = myBarrageConfig.position;
    myBarrage.style.bottom = myBarrageConfig.bottom;
    myBarrage.style.left = myBarrageConfig.left;
    myBarrage.style.textAlign = myBarrageConfig.textAlign;
    myBarrage.style.overflow = myBarrageConfig.overflow;


    // 绘制列表
    for (let i = 0; i < myBarrageConfig.numberOfColumns; i++) {
        let newSlide = document.createElement('div');
        newSlide.className = 'mySlide';
        newSlide.innerText = msgList[msgList.length - i - 1];
        // debugger
        myBarrage.appendChild(newSlide);
    }
}

/**
 * 启动滚动列表
 * @param msgList 数据
 * @param container 组件
 * @param myBarrageConfig 配置 { numberOfColumns: 3, barrageHeight: 20,}
 * @returns {Promise<void>}
 */
const startCarouserList = async (msgList = ["123", "246", "789"], container = document.querySelector('.carouselList-container'), myBarrageConfig={}) => {
    initCarouserList(msgList,  container, myBarrageConfig);
    runningMark = true;
    while (runningMark) {
        for (let i = 0; i < msgList.length; i++) {
            await addSlide(msgList[i], container);
        }
    }
}


const StopCarouserList = () => {
    runningMark = false;
}
