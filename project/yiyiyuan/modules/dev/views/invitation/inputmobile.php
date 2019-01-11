<div class="fInvitation">
    <div class="lingqvxj513">
        <h3>认证成功！</h3>
        <div class="lqmesone">
            <img src="/images/firstimg2.png">
        </div>
    </div>
    <div class="selftstxtd">
        <div class="dbk_ones">
            <input placeholder="输入您的手机号码" type="text" name="mobile">
            <input type="hidden" name="wid" value="<?php echo $wid; ?>">
        </div>
        <div class="dbk_ones" id="code" style="display: none;">
            <input class="yzmwidth" placeholder="输入验证码" type="text" name="code">
            <span class="hqyzm" id="codes">获取验证码</span>
        </div>
        <div class="tsmes" id="mark">
            <!--※  请输入正确的短信验证码。-->
        </div>
    </div>
    <div class="button" id="but1"> <button type="button" id="submobile">马上领取</button></div>
    <div class="button" id="but2" style="display: none;"> <button type="button" id="getpacket">马上领取</button></div>

</div>

<!--页面的弹窗-->
<div id="overDiv" style="display: none;"></div>
<div class="tchucye" style="display: none;">
    <div class="cuocuo"><img src="/images/cuocuo.png"></div>
    <p class="neiryem">五月理财月，送您<span>5000</span>元体验金，<br/>
        获得的收益可直接提取哦！</p>
    <button class="lingqv">领取</button>
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


    window.onload = function () {
        var oBtn = $('#codes');
        var timer = null;
        var time = 60;
        var s = time + 1;
        oBtn.onclick = function (mobile) {
            countDown();
            timer = setInterval(countDown, 1000);
//            oBtn.disabled = true;
            $.post("/dev/redpackets/sendcode", {mobile: mobile}, function (result) {
                var data = eval('(' + result + ')');
                if (data.ret == 0) {
                    $("#mark").html('');
                    $("#mark").show();
                } else if (data.ret == 1) {
                    $("#mark").html('※每天最多可以发6次短信');
                    $("#mark").show();
                } else if (data.ret == 2) {
                    $("#mark").html('※发送失败');
                    $("#mark").show();
                }
            });
        };

        function countDown() {
            s--;
            oBtn.html(s + '秒后重新获取');
            if (s == 0) {
                clearInterval(timer);
                s = time + 1;
                oBtn.html('重新获取验证码');
            }
        }
        $("#submobile").click(function () {
            var mobile = $('input[name="mobile"]').val();
            var wid = $('input[name="wid"]').val();
            var reg = /^(1(([3578][0-9])|(47)))\d{8}$/;
            $('#submobile').attr('disabled', true);
            if (!reg.test(mobile)) {
                alert('※请输入正确的手机号');
                $('#submobile').attr('disabled', false);
            }
            $.post("/dev/redpackets/mobile", {mobile: mobile, userid: wid}, function (result) {
                var data = eval('(' + result + ')');
                if (data.ret == 0) {
                    $('#code').show();
                    oBtn.onclick(mobile);
                    $('#but1').hide();
                    $('#but2').show();
                } else if (data.ret == 1) {
                    $('#submobile').attr('disabled', false);
                    $("#mark").html('※请使用该手机号绑定的微信认证');
                    $("#mark").show();
                } else if (data.ret == 2) {
                    window.location = '/dev/invitation/success?userid=' + wid;
                } else if (data.ret == 3) {
                    $('#submobile').attr('disabled', false);
                    $("#mark").html('※自己不能进行认证自己');
                    $("#mark").show();
                }
            });
        });
        $("#getpacket").click(function () {
            var mobile = $('input[name="mobile"]').val();
            var wid = $('input[name="wid"]').val();
            var code = $('input[name="code"]').val();
            var reg = /^(1(([3578][0-9])|(47)))\d{8}$/;
            $('#submobile').attr('disabled', true);
            if (!reg.test(mobile)) {
                $("#mark").html('※验证码错误');
                $("#mark").show();
                $('#submobile').attr('disabled', false);
                return false;
            }
            $.post("/dev/redpackets/savemobile", {mobile: mobile, code: code}, function (result) {
                var data = eval('(' + result + ')');
                if (data.ret == 0) {
                    window.location = '/dev/invitation/success?userid=' + wid;
                } else if (data.ret == 1) {
                    $('#submobile').attr('disabled', false);
                    $("#mark").html('※请重新提交验证码');
                    $("#mark").show();
                } else if (data.ret == 2) {
                    $('#submobile').attr('disabled', false);
                    $("#mark").html('※验证码错误');
                    $("#mark").show();
                }
            });
        });
    };



</script>