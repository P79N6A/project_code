<div class="selfmess">
    <script  src='/dev/st/statisticssave?type=45'></script>
    <div class="bannerer"><img src="/images/coupon/banner.png"></div>
    <div class="selftximg">
        <div class="dbk_inpL">
            <input placeholder="输入您的手机号码" type="text" name="mobile">
        </div>
        <div class="dbk_inpL">
            <input class="yzmwidth" placeholder="输入验证码" type="text" name="code">
            <span class="hqyzm" id="getCode">获取验证码</span>
        </div>
        <div class="tsmes" id="warning"></div>
        <div class="button"> <button id="sub">领取借款免息券</button></div>
    </div>
    <div class="bannerer2"><img src="/images/coupon/banner2.png"></div>
</div>
<script>
    $('#sub').bind('click', function () {
        var mobile = $('input[name="mobile"]').val();
        var code = $('input[name="code"]').val();
        var reg = /^(1(([3578][0-9])|(47)))\d{8}$/;
        $('#sub').attr('disabled', true);
        if (!reg.test(mobile)) {
            alert('请输入正确的手机号');
            $('#sub').attr('disabled', false);
        }
        if (code.length == 4) {
            var code_reg = /^\d{4}$/;
            if (!code_reg.test(code)) {
                alert('请输入正确的验证码');
                $('#sub').attr('disabled', false);
            } else {
                $.post('/dev/coupon/sendcoupon', {mobile: mobile, code: code}, function (result) {
                    var data = eval("(" + result + ")");
                    if (data.ret == '-1') {
                        alert('请输入正确的手机号');
                        $('#sub').attr('disabled', false);
                    } else if (data.ret == '0') {
//                        window.location.href = '/dev/coupon/getsuccess?mobile=' + mobile;
                        alert('领取成功');
                    } else if (data.ret == 1) {
                        alert('验证码错误,请重新填写');
                        $('#sub').attr('disabled', false);
                    } else {
                        alert('领取失败，请重新发送');
                        $('#getCode').attr('disabled', false);
                    }
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
            $.post('/dev/coupon/getcouponcode', {mobile: mobile}, function (result) {
                var data = eval("(" + result + ")");
                if (data.ret == '-1') {
                    $('#warning').html('※&nbsp;请输入正确的手机号');
                    $('#getCode').attr('disabled', false);
                } else if (data.ret == '0') {
                    $('#warning').html('※&nbsp;成功发送验证码到您的手机上，请填写验证码进行领取免息券');
                } else if (data.ret == 1) {
                    $('#warning').html('※&nbsp;每天最多发送6次短信');
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

