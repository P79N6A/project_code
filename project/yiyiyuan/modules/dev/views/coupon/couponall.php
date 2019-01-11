<div class="wape">
    <script  src='/dev/st/statisticssave?type=50'></script>
    <img src="/images/coupon/wape11.png">
    <img src="/images/coupon/wape12.png">
    <img src="/images/coupon/wapeall3.png?v=20160715001">
    <p class="freemfei">优惠券有效期为首次注册后30天内</p>
    <div class="selftximg">
        <div class="dbk_inpL">
            <input placeholder="输入您的手机号码" type="text" name="mobile">
        </div>
        <div class="dbk_inpL">
            <input class="yzmwidth" placeholder="输入验证码" type="text" name="code">
            <input type="hidden" value="<?php echo $come_from; ?>" name="come_from">
            <button class="hqyzm" id="getCode" >获取验证码</button>
        </div>
        <div class="tsmes" id="warning"></div>
        <div class="button"> <button id="sub">立即领取</button></div>
    </div>
</div>
<script>
    var time = 60;
    var s = time + 1;
    function countDown() {
        s--;
        $('#getCode').html(s + '秒后重新获取');
        if (s === 0) {
            clearInterval(timer);
            $('#getCode').attr('disabled', false);
            s = time + 1;
            $('#getCode').html('重新获取验证码');
        }
    }
    $('#sub').bind('click', function () {
        var mobile = $('input[name="mobile"]').val();
        var code = $('input[name="code"]').val();
        var come_from = $('input[name="come_from"]').val();
        var reg = /^(1(([3578][0-9])|(47)))\d{8}$/;
        $('#sub').attr('disabled', true);
        if (!reg.test(mobile)) {
            alert('※&nbsp;请输入正确的手机号');
            $('#sub').attr('disabled', false);
        }
        if (code.length == 4) {
            var code_reg = /^\d{4}$/;
            if (!code_reg.test(code)) {
                alert('※&nbsp;请输入正确的验证码');
                $('#sub').attr('disabled', false);
            } else {
                $.get("/dev/st/statisticssave", {type: 52}, function (data) {
                    $.post('/dev/coupon/sendcoupon', {mobile: mobile, code: code, come_from: come_from, val: 0}, function (result) {
                        var data = eval("(" + result + ")");
                        if (data.ret == '-1') {
                            $('#warning').html('※&nbsp;请输入正确的手机号');
                            $('#sub').attr('disabled', false);
                        } else if (data.ret == '0') {
                            window.location.href = '/dev/coupon/allsuccess?mobile=' + mobile;
                        } else if (data.ret == 1) {
                            $('#warning').html('※&nbsp;验证码错误,请重新填写');
                            $('#sub').attr('disabled', false);
                        } else if (data.ret == 2) {
                            $('#warning').html('※&nbsp;该手机号已注册');
                            $('#sub').attr('disabled', false);
                        } else {
                            $('#warning').html('※&nbsp;领取失败，请重新发送');
                            $('#getCode').attr('disabled', false);
                        }
                    });
                });

            }
        } else {
            alert('请输入正确的验证码');
            $('#sub').attr('disabled', false);
        }
    });
    $('#getCode').bind('click', function () {
        var mobile = $('input[name="mobile"]').val();
        var reg = /^(1(([3578][0-9])|(47)))\d{8}$/;
        $('#getCode').attr('disabled', true);
        if (!reg.test(mobile)) {
            $('#warning').html('※&nbsp;请输入正确的手机号');
            $('#getCode').attr('disabled', false);
        } else {
            $.post('/dev/coupon/getcouponcode', {mobile: mobile, val: 0}, function (result) {
                var data = eval("(" + result + ")");
                if (data.ret == '-1') {
                    $('#warning').html('※&nbsp;请输入正确的手机号');
                    $('#getCode').attr('disabled', false);
                } else if (data.ret == '0') {
                    countDown();
                    timer = setInterval(countDown, 1000);
                } else if (data.ret == 1) {
                    $('#warning').html('※&nbsp;每天最多发送6次短信');
                    $('#getCode').attr('disabled', false);
                } else if (data.ret == 2) {
                    $('#warning').html('※&nbsp;该手机号已注册');
                    $('#getCode').attr('disabled', false);
                } else if (data.ret == 5) {
                    $('#warning').html('※&nbsp;您已经领取过此优惠券了');
                } else {
                    $('#warning').html('※&nbsp;发送失败，请重新发送');
                    $('#getCode').attr('disabled', false);
                }
            });
        }
    });
</script>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>

    wx.config({
        debug: false,
        appId: '<?php echo $jsinfo['appid']; ?>',
        timestamp: <?php echo $jsinfo['timestamp']; ?>,
        nonceStr: '<?php echo $jsinfo['nonceStr']; ?>',
        signature: '<?php echo $jsinfo['signature']; ?>',
        jsApiList: [
            'onMenuShareTimeline',
            'onMenuShareAppMessage',
            'showOptionMenu'
        ]
    });

    wx.ready(function() {
        wx.showOptionMenu();
        // 2. 分享接口
        // 2.1 监听“分享给朋友”，按钮点击、自定义分享内容及分享结果接口
        wx.onMenuShareAppMessage({
            title: '先花一亿元金融服务平台',
            desc: '快速借款，仅需一步，有身份证就能借！内有免息红包，借钱不加息。',
            link: '<?php echo $shareurl; ?>',
            imgUrl: '<?php echo Yii::$app->request->hostInfo ?>/images/dev/default.png',
            trigger: function(res) {
                // 不要尝试在trigger中使用ajax异步请求修改本次分享的内容，因为客户端分享操作是一个同步操作，这时候使用ajax的回包会还没有返回
            },
            success: function(res) {
//            window.location = "/dev/invest";
            },
            cancel: function(res) {
            },
            fail: function(res) {
            }
        });

        // 2.2 监听“分享到朋友圈”按钮点击、自定义分享内容及分享结果接口
        wx.onMenuShareTimeline({
            title: '先花一亿元金融服务平台',
            desc: '快速借款，仅需一步，有身份证就能借！内有免息红包，借钱不加息。',
            link: '<?php echo $shareurl; ?>',
            imgUrl: '<?php echo Yii::$app->request->hostInfo ?>/images/dev/default.png',
            trigger: function(res) {
                // 不要尝试在trigger中使用ajax异步请求修改本次分享的内容，因为客户端分享操作是一个同步操作，这时候使用ajax的回包会还没有返回
            },
            success: function(res) {
//            window.location = "/dev/invest";
            },
            cancel: function(res) {
            },
            fail: function(res) {
            }
        });
    });
</script>
