<img src="/sevenday/images/bannerbg.png">
<div class="qtxsmx">运营商认证</div>

<div class="yhmaold">
    <div class="dbk_inpL">
        <label>手机号</label><input placeholder="请输入手机号" readonly="readonly" value="<?php echo $user->mobile; ?>" type="text" name="mobile">
    </div>
    <div class="dbk_inpL dxyznmes">
        <label>验证码</label>
        <input placeholder="输入短信验证码" type="text" name="code">
        <button class="dxyzn" onclick="getCode()" id="get_code">获取验证码</button>
    </div>
</div>
<div class="buttonyi"> <button onclick="doMobile()" id="do_bank">提交认证</button></div>
<div class="tishi_success" id="divbox" hidden><a class="tishi_text">认证成功</a></div>
<input type="hidden" id="csrf" value="<?php echo $csrf; ?>">
<input type="hidden" id="user_id" value="<?php echo $user->user_id; ?>">
<script type="text/javascript">
    var csrf = $('#csrf').val();
    function getCode() {
        zhuge.track('运营商认证-提交短信验证码');
        $("#get_code").attr('disabled', true);
        var user_id = $('#user_id').val();
        var mobile = $("input[name='mobile']").val();
        if (user_id == '') {
            $("#get_code").attr('disabled', false);
            $('.tishi_text').html('系统错误，请稍后重试');
            $('#divbox').show();
            return false;
        }
        if (mobile == '') {
            $("#get_code").attr('disabled', false);
            $('.tishi_text').html('预留手机号不能为空');
            $('#divbox').show();
            return false;
        }
        if (isPhoneNo($.trim(mobile)) == false) {
            $("#get_code").attr('disabled', false);
            $('.tishi_text').html('预留手机号格式错误');
            $('#divbox').show();
            return false;
        }
        $.ajax({
            type: "POST",
            url: "/day/userauth/getcode",
            data: {_csrf: csrf, user_id: user_id, mobile: mobile},
            success: function (result) {
                result = eval('(' + result + ')');
                if (result.rsp_code == '0000') {
                    count = 60;
                    countdown = setInterval(CountDown_bank, 1000);
                    $('.tishi_text').html(result.rsp_msg);
                    $('#divbox').show();
                } else {
                    $("#get_code").attr('disabled', false);
                    $('.tishi_text').html(result.rsp_msg);
                    $('#divbox').show();
                }
            }
        });
    }

    function doMobile() {
        zhuge.track('运营商认证');
        $("#do_bank").attr('disabled', true);
        var user_id = $('#user_id').val();
        var card = $("input[name='card']").val();
        var mobile = $("input[name='mobile']").val();
        var code = $("input[name='code']").val();
        if (user_id == '') {
            $("#do_bank").attr('disabled', false);
            $('.tishi_text').html('系统错误，请稍后重试');
            $('#divbox').show();
            return false;
        }
        if (mobile == '') {
            $("#do_bank").attr('disabled', false);
            $('.tishi_text').html('预留手机号不能为空');
            $('#divbox').show();
            return false;
        }
        if (code == '') {
            $("#do_bank").attr('disabled', false);
            $('.tishi_text').html('短信验证码不能为空');
            $('#divbox').show();
            return false;
        }
        if (isPhoneNo($.trim(mobile)) == false) {
            $("#do_bank").attr('disabled', false);
            $('.tishi_text').html('预留手机号格式错误');
            $('#divbox').show();
            return false;
        }
        $.ajax({
            type: "POST",
            url: "/day/userauth/addjuxinli",
            data: {_csrf: csrf, user_id: user_id, mobile: mobile, code: code},
            success: function (result) {
                result = eval('(' + result + ')');
                if (result.rsp_code == '0000') {
                    location.href = result.url;
                } else {
                    $("#do_bank").attr('disabled', false);
                    $('.tishi_text').html(result.rsp_msg);
                    $('#divbox').show();
                }
            }
        });
    }

    function isPhoneNo(phone) {
        var pattern = /^1[34578]\d{9}$/;
        return pattern.test(phone);
    }

    //绑卡倒计时
    var CountDown_bank = function () {
        $("#get_code").html("重新获取(" + count + ")");
        if (count <= 0) {
            $("#get_code").bind('click');
            $("#get_code").html("获取验证码").removeAttr("disabled").removeClass('dis');
            clearInterval(countdown);
        }
        count--;
    };
</script>