let count = 0;

const msgList = ["小白小在xxxx前提交了xxxxxx", "456在xxxx前提交了xxxxxx", "789在xxxx前提交了xxxxxx", "-1235在xxxx前提交了xxxxxx", "-4563在xxxx前提交了xxxxxx", "我的你的在xxxx前提交了xxxxxx"]
const size = 3;
const runningMark = true;

/**
 * 休眠函数
 * @param ms
 * @returns {Promise<unknown>}
 */
const sleep = (ms) => {
    return new Promise(resolve => setTimeout(resolve, ms));
}

const initCarouserList = (msgList = [""], size = 3) => {
    // 绘制列表
    let container = document.querySelector('.carouselList-container');
    for (let i = 0; i < size; i++) {
        let newSlide = document.createElement('div');
        newSlide.className = 'slide';
        newSlide.innerText = msgList[msgList.length - i - 1];
        // debugger
        container.appendChild(newSlide);
    }
}

const startCarouserList = async (timeSize = 0) => {
    while (runningMark) {
        for (let i = 0; i < msgList.length; i++) {
            await addSlide(msgList[i]);
        }
    }
}


async function addSlide(msg) {
    // Create a new slide
    let newSlide = document.createElement('div');
    newSlide.className = 'slide';
    newSlide.innerText = msg;
    count++;

    // Initially place the new slide below the visible area based on container's height

    // Append the new slide to the container
    let container = document.querySelector('.carouselList-container');
    container.appendChild(newSlide);

    await sleep(32);  // Small delay to ensure the new slide's transform is applied

    // Move the slides upwards
    let slides = document.querySelectorAll('.slide');
    for (let slide of slides) {
        slide.style.transition = 'transform 0.4s ease-in-out';
        slide.style.transform = `translateY(-50px)`;
    }

    await sleep(400);  // Match the duration of the transition
    container.removeChild(slides[0]);

    // Reset transform for remaining slides
    slides = document.querySelectorAll('.slide');
    for (let slide of slides) {
        slide.style.transition = 'none';
        slide.style.transform = `translateY(0px)`;
    }

    await sleep(600);  // Remaining time to reach the total sleep time of 1000ms as earlier
}




initCarouserList(msgList, 5);
startCarouserList().then();

