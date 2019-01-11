<div class="yhmaold">
    <div class="dbk_inpL">
        <label>持卡人</label><span><?php echo $user->realname;?></span>
    </div>
    <div class="dbk_inpL">
        <label>卡号</label><input placeholder="请输入您的银行卡号" type="text" name="card">
    </div>
</div>
<div class="qtxsmx">请填写该银行卡的预留手机号</div>

<div class="yhmaold">
    <div class="dbk_inpL">
        <label>手机号</label><input placeholder="请输入该银行卡的预留手机号" type="text" name="bank_mobile">
    </div>
    <div class="dbk_inpL dxyznmes">
        <label>验证码</label>
        <input placeholder="输入短信验证码" type="text" name="code">
        <button class="dxyzn" onclick="getCode()" id="get_code">获取验证码</button>
    </div>
</div>
<div class="buttonyi"> <button onclick="doBank()" id="do_bank">提交</button></div>
<div class="tishi_success" id="divbox" hidden><a class="tishi_text">绑定成功</a></div>
<input type="hidden" id="csrf" value="<?php echo $csrf; ?>">
<input type="hidden" id="user_id" value="<?php echo $user->user_id; ?>">
<script type="text/javascript">
    var csrf = $('#csrf').val();
    function getCode() {
        zhuge.track('验证卡-提交短信验证码');
        $("#get_code").attr('disabled', true);
        var user_id = $('#user_id').val();
        var bank_mobile = $("input[name='bank_mobile']").val();
        if(user_id == ''){
            $("#get_code").attr('disabled', false);
            $('.tishi_text').html('系统错误，请稍后重试');
            $('#divbox').show();
            return false;
        }
        if(bank_mobile == ''){
            $("#get_code").attr('disabled', false);
            $('.tishi_text').html('预留手机号不能为空');
            $('#divbox').show();
            return false;
        }
        if(isPhoneNo($.trim(bank_mobile)) == false){
            $("#get_code").attr('disabled', false);
            $('.tishi_text').html('预留手机号格式错误');
            $('#divbox').show();
            return false;
        }
        $.ajax({
            type: "POST",
            url: "/day/userbank/getcode",
            data: {_csrf: csrf,user_id: user_id,bank_mobile: bank_mobile},
            success: function (result) {
                result = eval('('+result+')');
                if (result.rsp_code == '0000') {
                    count = 60;
                    countdown = setInterval(CountDown_bank, 1000);
                    $('.tishi_text').html(result.rsp_msg);
                    $('#divbox').show();
                }else{
                    $("#get_code").attr('disabled', false);
                    $('.tishi_text').html(result.rsp_msg);
                    $('#divbox').show();
                }
            }
        });
    }
    
    function doBank() {
        zhuge.track('点击添加银行卡');
        $("#do_bank").attr('disabled', true);
        var user_id = $('#user_id').val();
        var card = $("input[name='card']").val();
        var bank_mobile = $("input[name='bank_mobile']").val();
        var code = $("input[name='code']").val();
        if(user_id == ''){
            $("#do_bank").attr('disabled', false);
            $('.tishi_text').html('系统错误，请稍后重试');
            $('#divbox').show();
            return false;
        }
        if(card == ''){
            $("#do_bank").attr('disabled', false);
            $('.tishi_text').html('银行卡号不能为空');
            $('#divbox').show();
            return false;
        }
        if(bank_mobile == ''){
            $("#do_bank").attr('disabled', false);
            $('.tishi_text').html('预留手机号不能为空');
            $('#divbox').show();
            return false;
        }
        if(code == ''){
            $("#do_bank").attr('disabled', false);
            $('.tishi_text').html('短信验证码不能为空');
            $('#divbox').show();
            return false;
        }
        if(isPhoneNo($.trim(bank_mobile)) == false){
            $("#do_bank").attr('disabled', false);
            $('.tishi_text').html('预留手机号格式错误');
            $('#divbox').show();
            return false;
        }
        $.ajax({
            type: "POST",
            url: "/day/userbank/addbank",
            data: {_csrf: csrf,user_id: user_id,card: card,bank_mobile: bank_mobile,code: code},
            success: function (result) {
                result = eval('('+result+')');
                if (result.rsp_code == '0000') {
                    location.href = result.url;
                }else{
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
    var CountDown_bank = function() {
        $("#get_code").html("重新获取(" + count + ")");
        if (count <= 0) {
            $("#get_code").bind('click');
            $("#get_code").html("获取验证码").removeAttr("disabled").removeClass('dis');
            clearInterval(countdown);
        }
        count--;
    };
</script>