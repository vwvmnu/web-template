function getTime() {
    const date = new Date();
    let hour = date.getHours(); //获取小时
    hour = hour < 10 ? '0' + hour : hour;
    let minute = date.getMinutes(); // 获取分
    minute = minute < 10 ? '0' + minute : minute;
    let seconds = date.getSeconds(); //获取秒
    seconds = seconds < 10 ? '0' + seconds : seconds;
    return hour + ':' + minute + ':' + seconds;
}

function getData() {
    const date = new Date();
    const year = date.getFullYear(); //获取年份
    const month = date.getMonth() + 1; //获取月份
    const day = date.getDate(); //获取日期
    return year + '-' + month + '-' + day;
}

function getWeek() {
    const weekDays = ['日', '一', '二', '三', '四', '五', '六'];
    const today = new Date();
    return "星期" + weekDays[today.getDay()];
}

setInterval(function () {
    $("#header-text-right-time").text(getTime())
}, 500)


$("#header-text-right-data").text(getData())
setInterval(function () {
    $("#header-text-right-data").text(getData())
}, 5000)

$("#header-text-right-week").text(getWeek())
setInterval(function () {
    $("#header-text-right-week").text(getWeek())
}, 5000)

const windowHeight = window.screen.height / 1080
const windowWidth = window.screen.width / 1920
const differenceValueWidth = window.screen.width - 1920
const differenceValueHeight = window.screen.height - 1920
console.log(windowHeight)
document.querySelector('body').style.transform = `scale(${windowWidth}, ${windowHeight})`;
document.querySelector('body').style.transformOrigin = "0 0"
//translateX(12%) translateY(12%)
//translateX(${differenceValueWidth/3}px) translateY(${-differenceValueHeight/3}px)
// $("body").css("transform", `scale(1.3,1.3})`)


const {createApp, ref, onMounted} = Vue;
const UpIcon = {
    template: ``,
    setup() {
    }
};

const App = {
    //使用短横线命名
    components: {
        UpIcon,
    },
    setup() {

        /**
         * 生成{size}大小的汉字
         * @param size 传入大小,默认3
         * @returns {string} 返回汉字
         */
        const generateRandomChinese = (size = 3) => {
            if (typeof size != "number") {
                console.log("请输入数字")
            }
            let randomChineseString = "";
            const commonChars = "的一是了我不人在他有这个上们来到时大地为子中你说生国年着就那和要她出也得里后自以会家可下而过天去能对小多然于心学么之都好看起发菲总统对华为";
            for (let i = 0; i < size; i++) {
                //randomChineseString += String.fromCharCode(Math.floor(Math.random() * (0x9FFF - 0x4E00 + 1)) + 0x4E00);
                randomChineseString += commonChars[Math.floor(Math.random() * commonChars.length)];
            }
            return randomChineseString;
        }

        /**
         * 随机生成颜色
         */
        const generateRandomColor = () => {
            const commonChars = [];
        }
        /**
         * 随机生成数字
         * @param min 最小值
         * @param max 最大值
         * @returns {number} 随机的数字
         */
        const generateRandomNumber = (min = 0, max = 1) => {
            if (typeof min != "number" || typeof max != "number") {
                console.log("请输入数字")
            }
            return Math.floor(Math.random() * (max - min)) + min;
        }

        //今日链接人数
        const PeopleLinkedTodayData = ref([])

        //实时画册在线人数


        const albumDetailsData = ref([
            {
                title: "某某画册",
                pageView: 120 + "+",
                whetherItIsGrowthOrNot: true,
            }
        ]);


        const addData = () => {
            albumDetailsData.value.push({
                title: generateRandomChinese(),
                pageView: (Math.floor(Math.random() * 200) + 100) + "+",
                whetherItIsGrowthOrNot: Math.random() < 0.5,
            })
        }

        for (let i = 0; i < 4; i++) {
            addData();
        }

        const loadMore = () => {
            // 这里只是为了演示，实际情况可能需要从服务器加载数据
            for (let i = 1; i <= 5; i++) {
                addData();
            }
        };

        const handleScroll = (event) => {
            if (window.innerHeight + window.scrollY > 20000) {
                return;
            }
            let nearBottom = window.innerHeight + window.scrollY >= document.body.offsetHeight - 1000;
            if (nearBottom) {
                loadMore();
            }
        };

        // 添加滚动事件监听器
        window.addEventListener('scroll', handleScroll);


        onMounted(() => {
            const chartRef = document.getElementById("main");

            // ECharts 数据和配置
            option = {
                series: [
                    {
                        type: 'gauge',
                        center: ['50%', '60%'],
                        startAngle: 200,
                        endAngle: -20,
                        min: 0,
                        max: 600000,
                        splitNumber: 5,
                        itemStyle: {color: '#00C2FF '},
                        progress: {
                            show: true, width: 20
                        },
                        anchor: {
                            show: false
                        },
                        pointer: {
                            show: false
                        },
                        axisLine: {
                            lineStyle: {
                                width: 20
                            },
                        },
                        //坐标轴
                        axisTick: {
                            distance: -25,
                            splitNumber: 5,
                            lineStyle: {
                                width: 2,
                                color: '#999'
                            },
                            length: 4
                        },

                        //间隔标识刻度样式
                        splitLine: {
                            distance: -30,
                            length: 8,
                            lineStyle: {
                                width: 3,
                                color: '#999'
                            },
                            formatter: "的"
                        },
                        //间隔标识刻度字体
                        axisLabel: {
                            distance: -10,
                            color: '#999',
                            fontSize: 14,
                            formatter: function (value, index) {
                                return value / 10000 + 'w';
                            }
                        },

                        detail: {
                            valueAnimation: true,
                            width: '60%',
                            lineHeight: 40,
                            borderRadius: 8,
                            offsetCenter: [0, '-15%'],
                            fontSize: 40,
                            fontWeight: 'bolder',
                            formatter: '{value}',
                            color: 'inherit'
                        },
                        title: {
                            offsetCenter: [0, 10],
                            fontSize: 18,
                            color: "#ffffff",
                        },
                        data: [
                            {
                                value: 20,
                                name: "已链接数量"
                            }
                        ]
                    },
                    {
                        type: 'gauge',
                        center: ['50%', '60%'],
                        startAngle: 200,
                        endAngle: -20,
                        min: 0,
                        max: 600000,
                        itemStyle: {
                            color: '#D200FF '
                        },
                        progress: {
                            show: true,
                            width: 8
                        },
                        pointer: {
                            show: false
                        },
                        axisLine: {
                            show: false
                        },
                        axisTick: {
                            show: false
                        },
                        splitLine: {
                            show: false
                        },
                        axisLabel: {
                            show: false
                        },
                        detail: {
                            show: false
                        },
                        data: [
                            {
                                value: 20
                            }
                        ]
                    }
                ]
            };

            setInterval(function () {
                const random = +(Math.random() * 600000).toFixed(0);
                myChart.setOption({
                    series: [
                        {
                            data: [
                                {
                                    value: random,
                                    name: "已链接数量"
                                }
                            ]
                        },
                        {
                            data: [
                                {
                                    value: random,
                                }
                            ]
                        }
                    ]
                });
            }, 2000);

            // 使用 ECharts
            const myChart = echarts.init(chartRef);
            myChart.setOption(option);
        })

        return {albumDetailsData, generateRandomChinese, generateRandomNumber};
    }
};

createApp(App).mount('#app');
