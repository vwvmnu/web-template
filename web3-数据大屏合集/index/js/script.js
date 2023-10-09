document.addEventListener("DOMContentLoaded", function() {
    // showPopup();
});

function showPopup() {
    $('#staticBackdrop').modal({
        keyboard: false
    })
    console.log("start")
    document.getElementById('overlay').style.display = 'block';
    document.getElementById('popup').style.display = 'block';
}

function closePopup() {
    document.getElementById('overlay').style.display = 'none';
    document.getElementById('popup').style.display = 'none';

}

function openModal() {
    $('#intelligentLogisticsModal').modal({
        keyboard: false
    })
}
function clickClose() {
    $('#intelligentLogisticsModal').modal('hide');
}
async function clickCopy() {
    try {
        await navigator.clipboard.writeText("https://001/index.html");
        alert('已复制到剪切板');
    } catch (err) {
        alert('复制失败');
    }
}

/**
 * qq和vx图标特效
 */
document.querySelectorAll('.icon').forEach(icon => {
    icon.addEventListener('click', function() {
        // 为图标添加 "icon-clicked" 类来实现缩放效果
        this.classList.add('icon-clicked');

        // 在动画结束后移除 "icon-clicked" 类，恢复图标的原始大小
        setTimeout(() => {
            this.classList.remove('icon-clicked');
        }, 300);  // 注意，这里的 300 毫秒应与上面 CSS 中的 transition 持续时间相匹配
    });
});

/**
 * 密码判断
 */
function affirm() {

    const tempMsg = $("#modal-body-input").val();
    console.log(tempMsg);
    if (tempMsg === "123456"){
        $('#staticBackdrop').modal('hide');
    }else {
        $("#modal-body-div").css("display", "block");
    }

}

