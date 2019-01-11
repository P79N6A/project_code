<div class="jdyyy" style="padding-bottom: 1rem;">
    <h3 style="margin: 0 5%; border-bottom: 1px solid #c2c2c2; padding: 1rem 0; font-size: 16px; text-align: center; font-weight: bold;">找回密码</h3>
    <div class="dbk_inpL" style="border:1px solid #000; margin-top: 10px; width: 90%; margin-left: 5%; padding:0;border-radius: 0;">
        <input id="verifyCode" name="verifyCode" placeholder="请填写验证码" type="text" style="width: 57%; font-size: 14px;background: #e7ebef; border:0; ">
        <input type="hidden" name="srvAuthCode" value="">
        <span class="Obtain" style="width: 42%;border-left: 0;padding:0 ;"><button  style="background: rgba(0,0,0,0); border: 0; font-size: 13px; color: #828181;" id="get_forgot" >获取验证码</button></span>
    </div>
    <p id="forms" style="padding: 1rem 0; text-align: center;"></p>
    <p id="ok" style="padding: 1rem 0; text-align: center;display: none;">短信验证码已发送至尾号<span id="tel_number"><?php echo substr($userInfo->mobile,-4); ?></span>的手机上</p>
    <p id="no" style="padding: 1rem 0; text-align: center;display: none;">获取失败</p>
    <button  id="resetpwd" style="background: #e74747;color: #fff; width: 40%; margin-left: 30%; font-size: 16px;padding: 0.5rem 0; border-radius: 30px;border: 0;">提交</button>
</div>

<input type="hidden" id="from" value="<?php echo $from ?>">
<input type="hidden" id="mobile" value="<?php echo $userInfo->mobile ?>">
<script type="text/javascript" src="/borrow/310/js/zhuge.js"></script>
<script type="text/javascript">
    var csrf = '<?php echo $csrf ?>';
    var mobile = '<?php echo $userInfo->mobile ?>';
    function getDcode() {
        $("#get_forgot").attr('disabled', true);
        $.post("/new/forgot/getsms", {mobile: mobile, _csrf:csrf}, function(result) {
            var data = eval("(" + result + ")");
            if (data.ret == '0') {//成功
                count = 60;
                countdown = setInterval(CountDowns, 1000);
                $("input[name='srvAuthCode']").val(data.data);
                $("#ok").show();
                $("#no").hide();
                return true;
            }else{
                $("#get_forgot").attr('disabled', false);
                $("#ok").hide();
                $("#no").show();
                return false
            }
        })
    }

    //倒计时
    var CountDowns = function() {
        $("#get_forgot").attr("disabled", true).addClass('dis');
        $("#get_forgot").html("重新获取 ( " + count + " ) ");
        if (count <= 0) {
            $("#get_forgot").html("获取验证码").removeAttr("disabled").removeClass('dis');
            clearInterval(countdown);
        }
        count--;
    };

    getDcode();

    $("#get_forgot").click(function() {
        getDcode();
    });

    $("#resetpwd").click(function() {
        zhuge.track('重置密码-确定按钮');
        $("#resetpwd").attr('disabled', true);
        var code = $("input[name='verifyCode']").val();
        var srvAuthCode = $("input[name='srvAuthCode']").val();
        var from = $("#from").val();
        var mobile = $("#mobile").val();
        $.post("/new/forgot/pwdresetplus", {code: code, srvAuthCode: srvAuthCode,mobile:mobile,from:from, _csrf:csrf}, function(result) {
            var data = eval("(" + result + ")");
            if (data.ret == '0') {//成功
                $("#forms").html(data.data);
                return false;
            }else{
                $("#forms").html(data.msg);
                $("#resetpwd").attr('disabled', false);
                return false;
            }
        })
    })

</script>