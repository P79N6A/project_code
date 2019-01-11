<script>
    var _csrf = '<?php echo $csrf; ?>';
    $(function () {
        $("#getcode_num").click(function () {
            zhuge.track('登录页-改变验证码');
            $(this).attr("src", '/new/reg/imgcode?' + Math.random());
        });
    });
</script>
<img src="/sevenday/images/bannerbg.png">
<div class="yhmaold">
    <div class="dbk_inpL">
        <label>手机号</label><input id="regmobile" placeholder="请输入手机号" maxlength="11"  type="text">
    </div>
    <input type="hidden" name="mark" value="1">
    <div class="dbk_inpL dxyznmes">
        <label>图形码</label>
        <input id="pic_num" name="pic_num" placeholder="图形验证码" type="text">
        <button class="dxyzn txyz"><img id="getcode_num" src="/new/reg/imgcode"></button>
    </div>
    <div class="dbk_inpL dxyznmes">
        <label>验证码</label>
        <input type="text" class="noBorder" name="code" id="regcode" maxlength="4" placeholder="请输入验证码" >
        <button type="button" id="reggetcode_login" class="dxyzn">获取验证码</button>
    </div>
</div>
<div  class="buttonyi"> <button id="login_button">立即进入</button></div>
<div class="dljtyi">登录即同意<a onclick="doAgreement()">《注册协议》</a></div>

<div  class="tishi_success" ><a id="reg_one_error" style="display: none">登录成功</a></div>

<script>
    var count, countdown;
    var _mobileRex = /^(1(([35678][0-9])|(47)))\d{8}$/;
    var _numberRex = /^[0-9]*[1-9][0-9]*$/;
    $(function () {
        //登录发送短信验证码
        $('#reggetcode_login').click(function () {
            var mobile = $("#regmobile").val();
            zhuge.track('登录页-获取验证码', {'phone': mobile});
            if (mobile == '' || !(_mobileRex.test(mobile))) {
                $("#reg_one_error").show();
                $("#reg_one_error").text('请输入正确的手机号码');
                setTimeout(function () {
                    $("#reg_one_error").hide();
                    $("#reg_one_error").text('');
                }, 1000);
                return false;
            }
            var mark = $("input[name='mark']").val();
            var pic_num = $("input[name='pic_num']").val();
            if (pic_num == '') {
                $("#reg_one_error").show();
                $("#reg_one_error").text('请输入正确的图形验证码');
                setTimeout(function () {
                    $("#reg_one_error").hide();
                    $("#reg_one_error").text('');
                }, 1000);
                return false;
            }
            $("#reggetcode_login").attr('disabled', true);
            $.post("/day/reg/loginsend", {_csrf: _csrf, mobile: mobile, pic_num: pic_num, mark: mark}, function (result) {
                var data = eval("(" + result + ")");
                if (data.ret == '0') {
                    //发送成功
                    count = 60;
                    countdown = setInterval(CountDown_login, 1000);
                    $("#reggetcode_login").attr('disabled', false);
                } else if (data.ret == '2')
                {
                    $("#reg_one_error").show();
                    $("#reg_one_error").text('该手机号码已超出每日短信最大获取次数');
                    setTimeout(function () {
                        $("#reg_one_error").hide();
                        $("#reg_one_error").text('');
                    }, 1000);
                    $("#reggetcode_login").attr('disabled', false);
                    return false;
                } else if (data.ret == '4') {
                    $("#reg_one_error").show();
                    $("#reg_one_error").text('图形验证码输入错误');
                    setTimeout(function () {
                        $("#reg_one_error").hide();
                        $("#reg_one_error").text('');
                    }, 1000);
                    $("#reggetcode_login").attr('disabled', false);
                    return false;
                } else if (data.ret == '5') {
                    $("#reg_one_error").show();
                    $("#reg_one_error").text('请输入正确的手机号');
                    setTimeout(function () {
                        $("#reg_one_error").hide();
                        $("#reg_one_error").text('');
                    }, 1000);
                    $("#reggetcode_login").attr('disabled', false);
                    return false;
                } else {
                    $("input[name='mark']").val(1);
                    $('#pic').show();
                    $("#pic_num").focus();
                    $("#reggetcode_login").attr('disabled', false);
                    return false;
                }
            });

        });
        $("#login_button").click(function () {
            var mobile = $("#regmobile").val();
            zhuge.track('登录页-立即进入', {'phone': mobile});
            if (mobile == '' || !(_mobileRex.test(mobile))) {
                $("#reg_one_error").show();
                $("#reg_one_error").text('请输入正确的手机号码');
                setTimeout(function () {
                    $("#reg_one_error").hide();
                    $("#reg_one_error").text('');
                }, 1000);
                return false;
            }
            var code = $("#regcode").val();
            if (code == '' || !(_numberRex.test(code))) {
                $("#reg_one_error").show();
                $("#reg_one_error").text('请输入正确的验证码');
                setTimeout(function () {
                    $("#reg_one_error").hide();
                    $("#reg_one_error").text('');
                }, 1000);
                return false;
            }
            $(this).attr('disabled', true);
            $.post("/day/reg/loginsave", {_csrf: _csrf, mobile: mobile, code: code}, function (result) {
                var data = eval("(" + result + ")");
                if (data.ret == '0') {
                    if (data.url != '') {
                        zhuge.track('登录页-登录成功', {'phone': mobile});
                        zhuge.identify(mobile);
                        window.location = data.url;
                    } else {
                        $("#reg_one_error").show();
                        $("#reg_one_error").text('提交失败');
                        setTimeout(function () {
                            $("#reg_one_error").hide();
                            $("#reg_one_error").text('');
                        }, 1000);
                        $("#login_button").attr('disabled', false);
                    }
                } else if (data.ret == '1') {
                    $("#reg_one_error").show();
                    $("#reg_one_error").text('提交失败');
                    setTimeout(function () {
                        $("#reg_one_error").hide();
                        $("#reg_one_error").text('');
                    }, 1000);
                    $("#login_button").attr('disabled', false);
                } else if (data.ret == '2') {
                    if (data.url != '') {
                        window.location = data.url;
                    } else {
                        $("#reg_one_error").show();
                        $("#reg_one_error").text('提交失败');
                        setTimeout(function () {
                            $("#reg_one_error").hide();
                            $("#reg_one_error").text('');
                        }, 1000);
                        $("#login_button").attr('disabled', false);
                    }
                } else if (data.ret == '3') {
                    $("#reg_one_error").show();
                    $("#reg_one_error").text('验证码错误');
                    setTimeout(function () {
                        $("#reg_one_error").hide();
                        $("#reg_one_error").text('');
                    }, 1000);
                    $("#login_button").attr('disabled', false);
                } else if (data.ret == '4') {
                    $("#reg_one_error").show();
                    $("#reg_one_error").text('对不起，您输入的手机号码有误，请用注册时的手机号码登录！');
                    setTimeout(function () {
                        $("#reg_one_error").hide();
                        $("#reg_one_error").text('');
                    }, 1000);
                    $("#login_button").attr('disabled', false);
                } else if (data.ret == '5') {
                    $("#reg_one_error").show();
                    $("#reg_one_error").text('系统错误');
                    setTimeout(function () {
                        $("#reg_one_error").hide();
                        $("#reg_one_error").text('');
                    }, 1000);
                    $("#login_button").attr('disabled', false);
                } else {
                    $("#reg_one_error").show();
                    $("#reg_one_error").text('该微信已绑定其它的手机号');
                    setTimeout(function () {
                        $("#reg_one_error").hide();
                        $("#reg_one_error").text('');
                    }, 1000);
                    $("#login_button").attr('disabled', false);
                }
            });
        });

        //登录倒计时
        var CountDown_login = function () {

            $("#reggetcode_login").attr("disabled", true).addClass('dis');
            $("#reggetcode_login").html("重新获取(" + count + ")");
            if (count <= 0) {
                $("#reggetcode_login").html("获取验证码").removeAttr("disabled").removeClass('dis');
                clearInterval(countdown);
            }
            count--;
        };

    });

    //注册协议
    function doAgreement() {
        location.href = '/day/agreeloan/register';
    }
</script>