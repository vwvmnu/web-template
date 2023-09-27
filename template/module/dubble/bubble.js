class MyBarrage {
    constructor(msgList = [], myBarrage = document.querySelector('.myBarrage'), myBarrageConfig = {}) {
        this.size = 5;
        this.runningMark = true;
        this.height = 25;
        this.myBarrageConfig = myBarrageConfig;
        this.initMyBarrageConfig = {
            zIndex: 1,
            width: "100%",
            position: "fixed",
            bottom: "18%",
            left: "2%",
            right: "",
            top: "",
            textAlign: "left",
            overflow: "hidden",
            height: "50px",

            display: "flex",
            flexDirection: "column",
            alignItems: "flex-start",
            // 显示几行
            numberOfColumns: 3,
            // 每个显示的高度大小
            barrageHeight: 20,
            // 滚动间隔
            rollInterval: 0.5,
            // 滚动时长
            rollTime: 0.5,
            // 样式类型
            styleType: 1,
            // 子集元素类名称
            slideClassName: "mySlide",
            // 是否使用子集元素样式
            isUseChildStyle: false,
            // 子集元素样式
            childStyle: {
                color: "rgba(236, 240, 241, 1.0)",
                backgroundColor: "rgba(0, 0, 0, 1)",
                height: 22.5,
                borderRadius: 5,
                padding: [5, 5, 5, 5],
                marginTop: 2.5,
                lineHeight: 12.5,
                fontSize: 12.5,
            }

        };
        this.myBarrage = myBarrage;
        this.msgList = msgList;
    }

    /**
     * 初始化组件
     */
    initializeComponent() {
        // 去除msgList可能出现的空值
        for (let i = 0; i < this.msgList.length; i++) {
            const str = this.msgList[i]; // 请替换为您要检查的字符串
            //这里为过滤的值
            if (!str || typeof (str) != "string" || str.trim() === "") {
                this.msgList.splice(i, 1);
                i = i - 1;
            }
        }

        // 对配置文件的数据处理
        if (!this.myBarrageConfig.rollInterval > 0) {
            this.myBarrageConfig.rollInterval = this.initMyBarrageConfig.rollInterval;
        }
        if (!this.myBarrageConfig.rollTime > 0) {
            this.myBarrageConfig.rollTime = this.initMyBarrageConfig.rollTime;
        }


        if (this.myBarrageConfig.isUseChildStyle) {
            const height = this.myBarrageConfig.barrageHeight;
            if (!this.myBarrageConfig.childStyle.borderRadius) {
                this.myBarrageConfig.childStyle.borderRadius = height * 0.4;
            }
            if (!this.myBarrageConfig.childStyle.fontSize) {
                this.myBarrageConfig.childStyle.fontSize = height * 0.5;
            }
            if (!this.myBarrageConfig.childStyle.padding) {
                this.myBarrageConfig.childStyle.padding =
                    [height * 0.2, this.myBarrageConfig.childStyle.borderRadius + height * 0.2,
                        height * 0.2, this.myBarrageConfig.childStyle.borderRadius + height * 0.2];
            }
            if (!this.myBarrageConfig.childStyle.lineHeight) {
                this.myBarrageConfig.childStyle.lineHeight = this.myBarrageConfig.childStyle.fontSize;
            }
            if (!this.myBarrageConfig.childStyle.height) {
                this.myBarrageConfig.childStyle.height = this.myBarrageConfig.childStyle.padding * 2 + this.myBarrageConfig.childStyle.fontSize;
            }
            if (!this.myBarrageConfig.childStyle.marginTop) {
                this.myBarrageConfig.childStyle.marginTop = height - this.myBarrageConfig.childStyle.height;
            }
            debugger
        }

        // 弹幕的相关配置
        this.myBarrageConfig = {...this.initMyBarrageConfig, ...this.myBarrageConfig};


        this.myBarrageConfig.height = this.myBarrageConfig.numberOfColumns * this.myBarrageConfig.barrageHeight + "px"
        this.myBarrage.style.setProperty('--height', this.myBarrageConfig.height);
        this.myBarrage.style.setProperty('--numberOfColumns', this.myBarrageConfig.numberOfColumns);

        this.myBarrage.style.zIndex = this.myBarrageConfig.zIndex;
        this.myBarrage.style.height = this.myBarrageConfig.height;
        this.myBarrage.style.width = this.myBarrageConfig.width;
        this.myBarrage.style.position = this.myBarrageConfig.position;
        this.myBarrage.style.textAlign = this.myBarrageConfig.textAlign;
        this.myBarrage.style.overflow = this.myBarrageConfig.overflow;

        this.myBarrage.style.display = this.myBarrageConfig.display;
        this.myBarrage.style.flexDirection = this.myBarrageConfig.flexDirection;
        this.myBarrage.style.alignItems = this.myBarrageConfig.alignItems;

        // 同时出现以左上为主
        // 去掉right和left同时出现的情况
        if (this.myBarrageConfig.left !== "") {
            this.myBarrageConfig.right = "";
            this.myBarrage.style.left = this.myBarrageConfig.left;
        } else {
            this.myBarrage.style.right = this.myBarrageConfig.right;
        }

        // 去掉top和bottom同时出现的情况
        if (this.myBarrageConfig.top !== "") {
            this.myBarrageConfig.bottom = "";
            this.myBarrage.style.top = this.myBarrageConfig.top;
        } else {
            this.myBarrage.style.bottom = this.myBarrageConfig.bottom;
        }


        this.initCarouserList(this.msgList, this.myBarrage, this.myBarrageConfig);
    }


    /**
     * 休眠函数
     * @param ms
     * @returns {Promise<unknown>}
     */
    mySleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    /**
     * 添加动画
     * @param htmlElement 元素
     * @param innerHtml 动画样式
     * @param intervalTime 延时单位秒
     */
    myAddAnimation({htmlElement, innerHtml = "@keyframes myAddAnimation {}", intervalTime = 1}) {
        if (!htmlElement) {
            console.error('htmlElement is null or undefined!');
            return;
        }

        // 创建一个样式元素
        const styleElement = document.createElement('style');

        // 定义 CSS 动画的关键帧
        // 将关键帧插入到样式元素中
        styleElement.innerHTML = `
            @keyframes myAddAnimation {
              0% {
                opacity: 1;
                transform: translate(0, 0);
              }
              100% {
                opacity: 0;
                transform: translate(0, -${this.myBarrageConfig.barrageHeight / 5}px);
              }
            }
          `;
        // 将样式元素插入到文档的头部
        document.head.appendChild(styleElement);
        // 应用动画
        htmlElement.style.animation = `myAddAnimation ${intervalTime}s forwards`;
    }

    /**
     * 添加子元素
     * @param msg 消息
     * @returns
     */
    generateChildNode(msg) {
        let newSlide = document.createElement('div');
        newSlide.className = this.myBarrageConfig.slideClassName;
        newSlide.innerText = msg;
        if (this.myBarrageConfig.isUseChildStyle) {
            const childStyle = this.myBarrageConfig.childStyle;
            newSlide.style.backgroundColor = this.myBarrageConfig.childStyle.backgroundColor;
            newSlide.style.color = this.myBarrageConfig.childStyle.color;
            newSlide.style.borderRadius = this.myBarrageConfig.childStyle.borderRadius + "px";
            newSlide.style.padding = childStyle.padding[0] + "px " + childStyle.padding[1] + "px " + childStyle.padding[2] + "px " + childStyle.padding[3] + "px ";
            newSlide.style.fontSize = this.myBarrageConfig.childStyle.fontSize + "px";
            newSlide.style.lineHeight = this.myBarrageConfig.childStyle.lineHeight + "px";
            newSlide.style.height = this.myBarrageConfig.childStyle.height + "px";
            newSlide.style.marginTop = this.myBarrageConfig.childStyle.marginTop + "px"
        }


        this.myBarrage.appendChild(newSlide);
    }

    /**
     * 添加子元素的动画
     * @param msg 消息
     * @returns {Promise<void>}
     */
    async addSlide(msg) {

        this.generateChildNode(msg);

        await this.mySleep(32);  // Small delay to ensure the new slide's transform is applied

        // Move the slides upwards
        let slides = this.myBarrage.querySelectorAll("." + this.myBarrageConfig.slideClassName);
        // debugger

        if (this.myBarrageConfig.styleType === 2) {
            const slide = slides[0];
            this.myAddAnimation({htmlElement: slide, intervalTime: this.myBarrageConfig.rollTime})
            // 等待动画完成
            await this.mySleep(this.myBarrageConfig.rollTime * 1000 + 50);
        }

        for (let i = 0; i < slides.length; i++) {
            const slide = slides[i];
            let computedStyle = window.getComputedStyle(slide);
            let marginTop = parseInt(computedStyle.marginTop, 10);
            let height = parseInt(computedStyle.height, 10);
            slide.style.transition = `transform ${this.myBarrageConfig.rollTime}s ease-in-out`;
            slide.style.transform = `translateY(-${marginTop + height}px)`;
        }

        // 等待动画完成
        await this.mySleep(this.myBarrageConfig.rollTime * 1000 + 50);

        this.myBarrage.removeChild(slides[0]);

        for (let slide of slides) {
            slide.style.transition = 'none';
            slide.style.transform = `translateY(0px)`;
        }

        await this.mySleep(700);
        // debugger
    }

    /**
     * 初始化列表以及布局
     * @param msgList 消息列表 msgList = ["123", "246", "789"]
     * @param myBarrage 父级document元素
     * @param myBarrageConfig 配置
     */
    initCarouserList(msgList = [], myBarrage = document.querySelector('.myBarrage'), myBarrageConfig = {}) {
        if (msgList.length >= myBarrageConfig.numberOfColumns) {
            // 绘制列表
            for (let i = 0; i < myBarrageConfig.numberOfColumns; i++) {
                this.generateChildNode(msgList[msgList.length - i - 1]);
            }
        }
    }

    start() {
        this.AsyncRunCarouser().then();
        return this;
    }

    /**
     * 异步启动滚动列表
     * @returns {Promise<MyBarrage>}
     */
    async AsyncRunCarouser() {
        this.initializeComponent();
        this.runningMark = true;
        if (this.msgList.length >= this.myBarrageConfig.numberOfColumns) {
            while (this.runningMark) {
                for (let i = 0; i < this.msgList.length; i++) {
                    await this.mySleep(this.myBarrageConfig.rollInterval * 1000)
                    await this.addSlide(this.msgList[i]);
                }
            }
        }
        return this;
    }

    /**
     * 停止运行
     */
    stop() {
        this.runningMark = false;
    }

}
