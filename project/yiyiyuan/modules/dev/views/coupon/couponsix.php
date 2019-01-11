<style>
    element.style{
        height:100%;
    }
</style>
<div class="wape">
    <script  src='/dev/st/statisticssave?type=<?php echo $type;?>'></script>
    <img src="/images/coupon/registerone.jpg">
    <img src="/images/coupon/registertwo.jpg">
    <img src="/images/coupon/registerthree.jpg">
    <p class="freemfei">优惠券有效期为首次注册后30天内</p>
    <div class="selftximg">
        <div class="dbk_inpL">
            <input maxlength="11" placeholder="输入您的手机号码" type="text" name="mobile">
        </div>
        <div id="showImgCode"  class="dbk_inpL">
            <input id="imgCode" class="yzmwidth" maxlength="4" placeholder="输入图形验证码" type="text" name="imgcode">
            <span class="hqyzm" style="background:white;"><img id="getcode_num" style="height:100%;" src="/dev/imgcode/imgcode"></span>
        </div>
        <div class="dbk_inpL">
            <input class="yzmwidth" maxlength="4" placeholder="输入验证码" type="text" name="code">
            <input type="hidden" value="<?php echo $come_from; ?>" name="come_from">
            <button class="hqyzm" id="getCode" >获取验证码</button>
        </div>
        <div class="tsmes" id="warning"></div>
        <div class="button"> <button id="sub">立即领取</button></div>
    </div>
</div>
 <script>
     function downtClick() {
         $.get("/dev/st/statisticssave", {type: 130}, function(data) {
             var ua = window.navigator.userAgent.toLowerCase();
             if(ua.match(/MicroMessenger/i) == 'micromessenger'){
                 window.location = "http://a.app.qq.com/o/simple.jsp?pkgname=com.xianhuahua.yiyiyuan_1";
             }else{
                 window.location = "http://t.cn/R4K2tn5";
             }
         });
     }
     $(function(){ 
 	    $("#getcode_num").click(function(){ 
 	        $(this).attr("src",'/dev/imgcode/imgcode?' + Math.random()); 
 	    }); 
 	}); 
</script>
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
        var from_code = $('input[name="from_code"]').val();
        var imgCode = $("#imgCode").val();
        var reg = /^(1(([3578][0-9])|(47)))\d{8}$/;
        $('#sub').attr('disabled', true);
        if (!mobile) {
            $('#warning').html('※&nbsp;请输入手机号');
            $('#sub').attr('disabled', false);
            return false;
        }
        if (!reg.test(mobile)) {
            $('#warning').html('※&nbsp;请输入正确的手机号');
            $('#sub').attr('disabled', false);
            return false;
        }
        if (!imgCode) {
            $('#warning').html('※&nbsp;请填写图片验证码');
            $('#sub').attr('disabled', false);
            return false;
        }else if(imgCode.length != 4){
            $('#warning').html('※&nbsp;请填写正确的图片验证码');
            $('#sub').attr('disabled', false);
            return false;
        }else{
            $.get("/dev/st/statisticssave", {type: 48}, function (data) {
                //$('#warning').html('');
                $.post('/dev/coupon/sendcoupon', {img_code:imgCode, mobile: mobile, code: code, come_from: come_from, from_code: from_code, val: 66}, function (result) {
                    var data = eval("(" + result + ")");
                    if (data.ret == '-1') {
                        $('#warning').html('※&nbsp;请输入正确的手机号');
                        $('#sub').attr('disabled', false);
                    }else if(data.ret == '6') {
                        $('#warning').html('※&nbsp;请填写正确的图片验证码');
                        $('#sub').attr('disabled', false);
                    }else if (data.ret == '-2') {
                        $('#warning').html('※&nbsp;恭喜您已注册领到免息借款券，下载App即可使用，请<a href="javascript:downtClick();" style="color:blue;">点此下载</a>');
                        $('#sub').attr('disabled', false);
                    }else if(data.ret == '-3') {
                        $('#warning').html('※&nbsp;请输入短信验证码');
                        $('#sub').attr('disabled', false);
                    }else if (data.ret == '0') {
                        window.location.href = '/dev/coupon/sixsuccess?mobile=' + mobile;
                    } else if (data.ret == 1) {
                        $('#warning').html('※&nbsp;请输入正确的短信验证码');
                        $('#sub').attr('disabled', false);
                    } else if (data.ret == 2) {
                        $('#warning').html('※&nbsp;恭喜您已注册领到免息借款券，下载App即可使用，请<a href="javascript:downtClick();" style="color:blue;">点此下载</a>');
                        $('#sub').attr('disabled', false);
                    } else if (data.ret == 11) {
                        $('#warning').html('※&nbsp;邀请码错误,请重新填写');
                        $('#sub').attr('disabled', false);
                    } else {
                        $('#warning').html('※&nbsp;领取失败，请重新发送');
                        $('#getCode').attr('disabled', false);
                    }
                });
            });
        }
    });
    $('#getCode').bind('click', function () {
        var mobile = $('input[name="mobile"]').val();
        var reg = /^(1(([3578][0-9])|(47)))\d{8}$/;
        $('#getCode').attr('disabled', true);
        if (!mobile) {
            $('#warning').html('※&nbsp;请输入手机号');
            $('#getCode').attr('disabled', false);
            return false;
        }
        if (!reg.test(mobile)) {
            $('#warning').html('※&nbsp;请输入正确的手机号');
            $('#getCode').attr('disabled', false);
        } else {
            var imgCode = $("#imgCode").val();
            if (!imgCode) {
                $('#warning').html('※&nbsp;请填写图片验证码');
                $('#getCode').attr('disabled', false);
                return false;
            }
            if(imgCode.length !=4){
                $('#warning').html('※&nbsp;请填写正确的图片验证码');
                $('#getCode').attr('disabled', false);return false;
            }
            var jsonData = {mobile: mobile, val: 66 , img_code:imgCode};
            
            $.post('/dev/coupon/getcouponcode', jsonData, function (result) {
                var data = eval("(" + result + ")");
                if (data.ret == '-1') {
                    $('#warning').html('※&nbsp;请输入正确的手机号');
                    $('#getCode').attr('disabled', false);
                } else if (data.ret == '0') {
                    countDown();
                    timer = setInterval(countDown, 1000);
                } else if (data.ret == 1) {
                    $('#warning').html('※&nbsp;您今天的验证码获取过次数过多，请明天再试');
                    $('#getCode').attr('disabled', false);
                } else if (data.ret == 2) {
                    $('#warning').html('※&nbsp;恭喜您已注册领到免息借款券，下载App即可使用，请<a href="javascript:downtClick();" style="color:blue;">点此下载</a>');
                    $('#getCode').attr('disabled', false);
                } else if (data.ret == 5) {
                    $('#warning').html('※&nbsp;您已经领取过此优惠券了');
                }else if (data.ret == 6) {
                    $("#showImgCode").show();
                    $('#warning').html('※&nbsp;请填写正确的图片验证码');
                    $('#getCode').attr('disabled', false);
                }else {
                    $('#warning').html('※&nbsp;发送失败，请重新发送');
                    $('#getCode').attr('disabled', false);
                }
            });
        }
    });
</script>

