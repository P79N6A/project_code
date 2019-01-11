<div class="hkcguy">
    <img class="click" src="/images/click.png">
    <p style="text-align: center;">您的消费凭证上传成功</p>
</div>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
    wx.config({
        debug: false,
        appId: '<?php echo $jsinfo['appid']; ?>',
        timestamp: <?php echo $jsinfo['timestamp']; ?>,
        nonceStr: '<?php echo $jsinfo['nonceStr']; ?>',
        signature: '<?php echo $jsinfo['signature']; ?>',
        jsApiList: [
            'hideOptionMenu'
        ]
    });

    wx.ready(function () {
        wx.hideOptionMenu();
    });

    var u = navigator.userAgent, app = navigator.appVersion;
    var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Linux') > -1;
    var isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/);
    var position = "-1";
    var android = "com.business.main.MainActivity";
    var ios = "loanViewController";
    //重写返回按钮
    var isApp = <?php
if (strpos($_SERVER['HTTP_USER_AGENT'], 'YYY_ANDROID') || strpos($_SERVER['HTTP_USER_AGENT'], 'yyyIOS')) {
    echo 1;  //app端
} else {
    echo 2;  //h5端
}
?>;
    if (isApp == 1) {
        setTimeout(function () {
            window.myObj.closeHtml();
        }, 1000);
    } else if (isApp == 2) {
        setTimeout(function () {
            window.location.href = '/borrow/account';
        }, 1000);
    }
</script> 