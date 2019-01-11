<style>
    body{
        background: #fff;
    }
    .Hmask_newyear { width: 100%;height: 100%;background: rgba(0,0,0,.7);position: fixed;top: 0;left: 0; }
    .dwlhbao{color:#337ceb;padding: 10px 0; border-top:1px solid #c2c2c2; width: 100%; text-align: center; background: #fff; border-radius: 0 0 5px 5px;  font-size: 1.25rem;}
    .Hmask_div{position: fixed;width:80%;top:30%; left: 10%;background: #fff; border-radius: 5px; }
    .Hmask_div p{padding: 20px 0; font-size: 1.15rem; color: #444; text-align:center;}
</style>
<div class="wape_new">
    <script  src='/dev/st/statisticssave?type=<?php echo $type; ?>'></script>
    <img src="/images/coupon/wap1.jpg">
    <img src="/images/coupon/wap2.jpg">
    <div class="selftximg_new">
        <div class="dbk_inpL">
            <input maxlength="11" placeholder="输入您的手机号码" type="text" name="mobile">
        </div>
        <div id="showImgCode" class="dbk_inpL">
            <input id="imgCode" class="yzmwidth" placeholder="图形验证码" type="text">
            <span class="hqyzm" style="background:white;"><img id="getcode_num" style="height:100%;" src="/dev/imgcode/imgcode"></span>
<!--            <span class="hqyzm">图形验证码</span>-->
        </div>
        <div class="dbk_inpL dxyznmes">
            <!--<input placeholder="输入短信验证码" type="text">-->
            <input maxlength="4" placeholder="输入短信验证码" type="text" name="code">
            <input type="hidden" value="<?php echo $come_from; ?>" name="come_from">
            <input type="hidden" value="<?php echo $mob_type; ?>" name="mob_type">
            <input type="hidden" value="<?php echo $downloan_url; ?>" name="downloan_url">
            <button class="dxyzn" id="getCode" style="width:40%;"><nobr>获取验证码</nobr></button>
        </div>
        <!--<div class="tsmes">※    手机号输入错误！</div>-->
        <div class="tsmes" id="warning"></div>
        <div class="button"> <button id="sub">立即拿钱</button></div>
    </div>
    <p class="addreeb">先花信息技术(北京)有限公司</p>
</div>

<!--蒙层背景-->
<div class="Hmask_newyear" style="display: none;"></div>

<div class="Hmask_div" style="display: none;">
    <!--<img style="position: absolute; right:10px;top:10px;width: 5%;"  src="/images/coupon/close.png">-->
    <p id = "show_info">您已成功注册现金白卡</p>
    <button class="dwlhbao">立即下载APP，秒下款</button>
</div>
<script>
    $(function() {
        $("#getcode_num").click(function() {
            $(this).attr("src", '/dev/imgcode/imgcode?' + Math.random());
        });
    });
</script>
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
    $(".dwlhbao").bind('click', function () {
        var mob_type = $('input[name="mob_type"]').val();
//        var downloan_url = $('input[name="downloan_url"]').val();
        var downloan_url = "http://a.app.qq.com/o/simple.jsp?pkgname=com.xianhuahua.yiyiyuan_1";
        var ios_url = "http://a.app.qq.com/o/simple.jsp?pkgname=com.xianhuahua.yiyiyuan_1";
        if(mob_type == "android"){
            window.location.href = downloan_url;
        }else{
            window.location.href = ios_url;
//            window.location.href = downloan_url;
        }
    })
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
                        $('#show_info').html('您已成功注册先花一亿元')
                        $(".Hmask_newyear").show();
                        $(".Hmask_div").show();
//                        $('#warning').html('※&nbsp;恭喜您已注册领到免息借款券，下载App即可使用，请<a href="javascript:downtClick();" style="color:blue;">点此下载</a>');
                        $('#sub').attr('disabled', false);
                    }else if(data.ret == '-3') {
                        $('#warning').html('※&nbsp;请输入短信验证码');
                        $('#sub').attr('disabled', false);
                    }else if (data.ret == '0') {
                        $('#show_info').html('您已成功注册先花一亿元')
                        $(".Hmask_newyear").show();
                        $(".Hmask_div").show();
                        $('#sub').attr('disabled', false);
                    } else if (data.ret == 1) {
                        $('#warning').html('※&nbsp;请输入正确的短信验证码');
                        $('#sub').attr('disabled', false);
                    } else if (data.ret == 2) {
                        $('#show_info').html('您已成功注册先花一亿元')
                        $(".Hmask_newyear").show();
                        $(".Hmask_div").show();
//                        $('#warning').html('※&nbsp;恭喜您已注册领到免息借款券，下载App即可使用，请<a href="javascript:downtClick();" style="color:blue;">点此下载</a>');
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
//        alert(1234);return false;
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
                $('#warning').html('');
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
                      $('#show_info').html('您已经注册过了')
                      $(".Hmask_newyear").show();
                      $(".Hmask_div").show();
//                    $('#warning').html('※&nbsp;恭喜您已注册领到免息借款券，下载App即可使用，请<a href="javascript:downtClick();" style="color:blue;">点此下载</a>');
                    $('#getCode').attr('disabled', false);
                } else if (data.ret == 5) {
                      $('#show_info').html('您已经注册过了')
                      $(".Hmask_newyear").show();
                      $(".Hmask_div").show();
//                    $('#warning').html('※&nbsp;您已经领取过此优惠券了');
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
