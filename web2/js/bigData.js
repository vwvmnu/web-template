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


const {createApp, ref, onMounted} = Vue;
const UpIcon = {
    template: ``, setup() {
    }
};

const App = {
    //使用短横线命名
    components: {
        UpIcon,
    }, setup() {
        const enterpriseName = ["星辰科技有限公司", "云峰技术有限公司", "海洋探索有限公司", "智源科技有限公司", "赛博技术有限公司", "超越实业有限公司", "梦幻科技有限公司", "宇宙网络有限公司", "黑金科技有限公司", "风云实业有限公司", "金牛科技有限公司", "神秘岛网络有限公司", "乐游科技有限公司", "飞翼技术有限公司", "时光科技有限公司", "绿宝实业有限公司", "天际线科技有限公司", "青云技术有限公司", "晶莹网络有限公司", "千里科技有限公司", "龙飞科技有限公司", "智慧源技术有限公司", "星空网络有限公司", "风华实业有限公司", "金融宝科技有限公司", "虚拟视界技术有限公司", "世纪龙网络有限公司", "云端科技有限公司", "翼动科技有限公司", "环宇实业有限公司", "海豚科技有限公司", "未来宝网络有限公司", "银河科技有限公司", "空中宫殿技术有限公司", "梦想实现网络有限公司", "金字塔科技有限公司", "云图技术有限公司", "大师网络有限公司", "蓝天科技有限公司", "光影技术有限公司", "神奇网络有限公司", "时空穿越科技有限公司", "星际实业有限公司", "智能核心网络有限公司", "青春科技有限公司", "深海秘密技术有限公司", "超级网络有限公司", "钢铁侠科技有限公司", "闪电实业有限公司", "飞速网络有限公司", "黑洞科技有限公司", "魔法世界技术有限公司", "恒星网络有限公司", "世界之窗科技有限公司", "无限可能技术有限公司", "超能网络有限公司", "火箭科技有限公司", "晶体技术有限公司", "黄金时代网络有限公司", "铁人科技有限公司", "精灵之森技术有限公司", "高峰网络有限公司", "飞龙科技有限公司", "奇迹实现技术有限公司", "天堂网络有限公司", "地心科技有限公司", "神话技术有限公司", "永恒网络有限公司", "量子科技有限公司", "虚拟实业有限公司", "梦幻岛网络有限公司", "火星探索科技有限公司", "太空城技术有限公司", "未来网络有限公司", "光速科技有限公司", "智能城市技术有限公司", "金矿网络有限公司", "无穷科技有限公司", "奇点技术有限公司", "宇宙网络有限公司", "雷神科技有限公司", "天使之城技术有限公司", "神秘网络有限公司", "海洋之心科技有限公司", "风之谷技术有限公司", "世界之巅网络有限公司", "黄金科技有限公司", "无尽之地技术有限公司", "龙之梦网络有限公司", "奇幻科技有限公司", "未来之门技术有限公司", "银月网络有限公司", "星辰大海科技有限公司", "飞跃技术有限公司", "宇宙之星网络有限公司", "时光之旅科技有限公司", "未来探索技术有限公司", "星际迷航网络有限公司", "虚拟现实科技有限公司", "黑科技有限公司",];
        const peopleName = ["许澄邈", "刘德泽", "程海超", "邓海阳", "邓海荣", "陈海逸", "宋海昌", "徐瀚钰", "徐瀚文", "陈涵亮", "程涵煦", "宋明宇", "徐涵衍", "万浩皛", "万浩波", "荣浩博", "陈浩初", "陈浩宕", "赵浩歌", "周浩广", "周浩邈", "周浩气", "章浩思", "徐浩言", "徐鸿宝", "许鸿波", "许鸿博", "许鸿才", "徐鸿畅", "许鸿畴", "宋鸿达", "徐鸿德", "徐鸿飞", "徐鸿风", "徐鸿福", "许鸿光", "徐鸿晖", "章鸿朗", "周鸿文", "章鸿轩", "宋鸿煊", "和鸿骞", "凯鸿远", "宋鸿云", "徐鸿哲", "徐鸿祯", "徐鸿志", "徐鸿卓", "徐嘉澍", "徐光济", "徐澎湃", "徐彭泽", "宋鹏池", "宋鹏海", "宋浦和", "宋浦泽", "方瑞渊", "方越泽", "方博耘", "方德运", "方辰宇", "方辰皓", "方辰钊", "方辰铭", "方辰锟", "方辰阳", "方辰韦", "方辰良", "方辰沛", "方晨轩", "方晨涛", "方晨濡", "方晨潍", "方鸿振", "方吉星", "方铭晨", "方起运", "方运凡", "方运凯", "方运鹏", "方运浩", "方运诚", "方运良", "方运鸿", "方运锋", "方运盛", "方运升", "方运杰", "方运珧", "方运骏", "方运凯", "方运乾", "方维运", "方运晟", "方运莱", "方运华", "方耘豪", "方星爵", "方星腾", "方星睿"];

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

        const getEnterpriseName = () => {
            return enterpriseName[Math.floor(Math.random() * 100)]
        }
        const getPeopleName = () => {
            return peopleName[Math.floor(Math.random() * 100)]
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

        const generateDayData = () => {
            function getRandomDate(startDate, endDate) {
                return new Date(startDate.getTime() + Math.random() * (endDate.getTime() - startDate.getTime()));
            }

            function formatDate(date) {
                const year = date.getFullYear();
                const month = ('0' + (date.getMonth() + 1)).slice(-2);
                const day = ('0' + date.getDate()).slice(-2);
                return `${year}-${month}-${day}`;
            }

            const currentDate = new Date();
            const twoMonthsAgo = new Date();
            twoMonthsAgo.setMonth(currentDate.getMonth() - 2);

            const dateArray = [];

            for (let i = 0; i < 25; i++) {
                dateArray.push(getRandomDate(twoMonthsAgo, currentDate));
            }

            dateArray.sort((a, b) => b - a);

            return dateArray.map(date => formatDate(date));
        }

        const dayDate = generateDayData();

        const albumDetailsData = ref([{
            title: "某某画册", pageView: 120 + "+", whetherItIsGrowthOrNot: true,
        }]);


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


        const data11 = ref([]);
        const data12 = ref([]);
        const data2 = ref([]);
        const data3 = ref([]);
        const data41 = ref([]);
        const data42 = ref([]);
        const data51 = ref([]);
        const data52 = ref([]);
        const genDataList = (size = 23) => {
            const temp = [];
            for (let i = 0; i < size; i++) {
                const data = {
                    enterpriseName: getEnterpriseName(),
                    pictureSize: generateRandomNumber(10, 500),
                    peopleName: getPeopleName()
                }
                temp.push(data)
            }
            return temp;
        }

        for (let i = 0; i < 23; i++) {
            const data = {
                enterpriseName: getEnterpriseName(),
                pictureSize: generateRandomNumber(10, 500),
                peopleName: getPeopleName()
            }
            data11.value.push(data)
        }
        for (let i = 0; i < 23; i++) {
            const data = {
                enterpriseName: getEnterpriseName(),
                pictureSize: generateRandomNumber(10, 500),
                peopleName: getPeopleName()
            }
            data12.value.push(data)
        }
        for (let i = 0; i < 23; i++) {
            const data = {
                enterpriseName: getEnterpriseName(),
                pictureSize: generateRandomNumber(10, 500),
                peopleName: dayDate[i]
            }
            data2.value.push(data)
        }
        for (let i = 0; i < 23; i++) {
            const data = {
                enterpriseName: getEnterpriseName(),
                pictureSize: generateRandomNumber(10, 500),
                peopleName: getPeopleName()
            }
            data3.value.push(data)
        }
        for (let i = 0; i < 9; i++) {
            const data1 = {
                enterpriseName: getEnterpriseName(),
                pictureSize: generateRandomNumber(10, 500),
                peopleName: getPeopleName()
            }
            const data2 = {
                enterpriseName: getEnterpriseName(),
                pictureSize: generateRandomNumber(10, 500),
                peopleName: getPeopleName()
            }
            data41.value.push(data1);
            data42.value.push(data2);
        }
        for (let i = 0; i < 9; i++) {
            const data1 = {
                enterpriseName: getEnterpriseName(),
                pictureSize: generateRandomNumber(10, 500),
                peopleName: getPeopleName()
            }
            const data2 = {
                enterpriseName: getEnterpriseName(),
                pictureSize: generateRandomNumber(10, 500),
                peopleName: getPeopleName()
            }
            data51.value.push(data1)
            data52.value.push(data2)
        }


        // 添加滚动事件监听器
        window.addEventListener('scroll', handleScroll);


        onMounted(() => {
                const chartRef = document.getElementById("main");

                // ECharts 数据和配置
                option = {
                    series: [{
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
                        }, //坐标轴
                        axisTick: {
                            distance: -25, splitNumber: 5, lineStyle: {
                                width: 2, color: '#999'
                            }, length: 4
                        },

                        //间隔标识刻度样式
                        splitLine: {
                            distance: -30, length: 8, lineStyle: {
                                width: 3, color: '#999'
                            }, formatter: "的"
                        }, //间隔标识刻度字体
                        axisLabel: {
                            distance: -10, color: '#999', fontSize: 14, formatter: function (value, index) {
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
                            offsetCenter: [0, 10], fontSize: 18, color: "#ffffff",
                        },
                        data: [{
                            value: 20, name: "已链接数量"
                        }]
                    }, {
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
                            show: true, width: 8
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
                        data: [{
                            value: 20
                        }]
                    }]
                };

                setInterval(function () {
                    const random = +(Math.random() * 600000).toFixed(0);
                    myChart.setOption({
                        series: [{
                            data: [{
                                value: random, name: "已链接数量"
                            }]
                        }, {
                            data: [{
                                value: random,
                            }]
                        }]
                    });

                    this.refreshKey = Date.now();
                }, 2000);

                const mySleep = (ms) => {
                    return new Promise(resolve => setTimeout(resolve, ms));
                }

                const rollList = async ( leftMain11 = document.getElementById("leftMain11"), leftMain12 = document.getElementById("leftMain12"), data1=ref(), data2 = ref(), size=23) => {

                    await mySleep(Math.floor(Math.random() * 20000))
                    leftMain11.style.transition = 'transform 2s ease-in-out';
                    leftMain11.style.transform = 'translateY(-100%)'; // 移动第一个元素
                    leftMain12.style.transition = 'transform 2s ease-in-out';
                    leftMain12.style.transform = 'translateY(-100%)'; // 移动第一个元素

                    await mySleep(2020);
                    data1.value = [...data2.value];
                    leftMain11.style.transition = 'none';
                    leftMain11.style.transform = `translateY(0)`;
                    leftMain12.style.transition = 'none';
                    leftMain12.style.transform = `translateY(0)`;

                    data2.value = genDataList(size);

                    // 使用 ECharts
                    const myChart = echarts.init(chartRef);
                    myChart.setOption(option);
                }

                const startRoll = async () => {
                    const leftMain11 = document.getElementById("leftMain11");
                    const leftMain12 = document.getElementById("leftMain12");
                    const idData41 = document.getElementById("data41");
                    const idData42 = document.getElementById("data42");
                    const idData51 = document.getElementById("idData51");
                    const idData52 = document.getElementById("idData52");

                    while (true) {
                        rollList(leftMain11, leftMain12, data11, data12, 23);
                        rollList(idData41, idData42, data41, data42, 9);
                        rollList(idData51, idData52, data51, data52, 9);
                        await mySleep(25000);
                    }
                }
                startRoll().then()

                // 使用 ECharts
                const myChart = echarts.init(chartRef);
                myChart.setOption(option);
            }
        )

        return {
            albumDetailsData, generateRandomChinese, generateRandomNumber, getEnterpriseName, getPeopleName,
            data11, data12, data2, data3, data41, data42, data51, data52,
        };
    }
};

createApp(App).mount('#app');
