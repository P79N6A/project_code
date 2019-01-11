<script>
    var secs = 5; //倒计时的秒数
    var URL;
    function Load(url) {
        URL = url;
        for (var i = secs; i >= 0; i--)
        {
            window.setTimeout('doUpdate(' + i + ')', (secs - i) * 1000);
        }
    }
    function doUpdate(num)
    {
        document.getElementById('nums').innerHTML = num;
        if (num == 0) {
            window.location = URL;
        }
    }
    /**
     **/
    //重写返回按钮
    pushHistory();
    var bool = false;
    setTimeout(function () {
        bool = true;
    }, 100);
    window.addEventListener("popstate", function (e) {
        if (bool) {
            setTimeout(function () {
                    window.location.href = "/mall/store/orderdetails?order_id=<?=$order_id?>";
                    return false;

            }, 1000);
        }
        pushHistory();
    }, false);
    function pushHistory() {
        var state = {
            url: "#"
        };
        window.history.pushState(state, "#");
    }

    function goApp() {
        if (isApp == 1) {
            setTimeout(function () {
                window.myObj.closeHtml();
                function closeHtml() {
                }
            });
        }
    }
    /**/
</script>
<div class="order-wrap">
    <div class="y-processing">
        <img src="/292/images/success-icon.png" alt="">
        <p class="success-tips">恭喜您分期订单提交成功</p>
        <span><em  id="nums">5</em>s跳转至订单详情，查看订单进度</span>
    </div>
    <a href = "/mall/store/orderdetails?order_id=<?=$order_id?>"><div class="add-address">查看订单</div></a>
</div>
<script type="text/javascript">Load("/mall/store/orderdetails?order_id=<?=$order_id?>");</script>
