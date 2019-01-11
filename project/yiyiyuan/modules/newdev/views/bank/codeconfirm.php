<div class="jdall">
    <div class="dxinsure">
        <p>认证<?php if($banktype == 1): ?>银行<?php else: ?>银行<?php endif; ?>卡需要短信确认 </p>
        <p id="confirm_tel" style="display: none">验证码已发送至手机<span><?php echo $mobile; ?></span></p>
    </div>
    
    <div class="jdyyy">
        <div class="dbk_inpL">
            <label>验证码</label>
            <input id="verifyCode" name="verifyCode" placeholder="请填写验证码" type="text">
            <span class="Obtain" style="width: 40%;" ><button  id="get_bankcode" style="width:100%;color:#c90000;background:rgba(255, 255, 255, 0.5)" >获取验证码</button></span>
        </div>
    </div>
    <?php $csrf = \Yii::$app->request->getCsrfToken(); ?>
    <input  id="_csrf" name="_csrf" type="hidden" value="<?php echo $csrf; ?>">
    <input id="banktype" name="banktype" type="hidden" value="<?php echo $banktype; ?>">
    <input type="hidden" id="user_id" name="user_id" value="<?php echo $user_id; ?>">
    <input type="hidden" id="card" name="card" value="<?php echo $card; ?>">
    <input type="hidden" id="realname" name="realname" value="<?php echo $realname; ?>">
    <input type="hidden" id="mobile" name="mobile" value="<?php echo $mobile; ?>">
    <input  id="orderinfo" name="orderinfo" type="hidden" value="<?php echo $orderinfo; ?>">
    <input type="hidden" id="isyeepay" name="isyeepay" value="">
    <div class="tsmes" id="remain"></div>
    <div class="button"> <button id="sub" type="submit" >提交</button></div>

</div>
<script>
    window.onload = function () {
        //绑卡倒计时
        var CountDown_bank = function() {
            $("#get_bankcode").html("重新获取(" + count + ")");
            if (count <= 0) {
                $("#get_bankcode").bind('click')
                $("#get_bankcode").html("获取验证码").removeAttr("disabled").removeClass('dis');
                clearInterval(countdown);
            }
            count--;
        };
        //绑卡短信验证码发送
        $('#get_bankcode').click(function() {
            var mobile = $("#mobile").val();
            var user_id = $("#user_id").val();
            var cardno = $("#card").val();
            var csrf = $("#_csrf").val();
            var banktype = $("#banktype").val();
            $("#get_bankcode").attr('disabled', "false");
            $.post("/new/bank/banksend", {_csrf:csrf, mobile: mobile, user_id: user_id, cardno: cardno, banktype:banktype}, function(result) {
                var data = eval("(" + result + ")");
                if (data.res_code == 0) {
                    //发送成功
                    count = 60;
                    countdown = setInterval(CountDown_bank, 1000);
                    $("#confirm_tel").css('display','block');
                    //$("#isyeepay").val(data.res_data.isyeepay);
                } else if(data.res_code == 1){
                    $("#remain").html('*请绑定正确的银行卡！');
                    $("#mobile").focus();
                    $("#get_bankcode").attr('disabled', "false");
                    return false;
                } else {
                    $("#remain").html('该手机号码已超出每日短信最大获取次数');
                    $("#mobile").focus();
                    $("#get_bankcode").attr('disabled', "false");
                    return false;
                }
            });

        });

        $('#sub').click(function () {
            var userid = $("#user_id").val();
            var card = $("#card").val();
            var mobile = $("#mobile").val();
            var verifyCode = $("#verifyCode").val();
            var isyeepay = $("#isyeepay").val();
            var orderinfo = $("#orderinfo").val();
            var banktype = $("#banktype").val();
            var csrf = $("#_csrf").val();
            $(this).attr('disabled', true);
            if(!verifyCode){
                $("#remain").html('*请填写短信验证码!');
                $("#sub").attr('disabled', false);
                return false;
            }
            if(verifyCode.length != 4){
                $("#remain").html('*请填写正确的短信验证码!');
                $("#sub").attr('disabled', false);
                return false;
            }
            $.post("/new/bank/bindcard?orderinfo="+orderinfo, {_csrf:csrf, userid: userid, card: card, mobile: mobile, code: verifyCode, isyeepay: isyeepay, banktype: banktype}, function (result) {
                var data = eval("(" + result + ")");
                if (data.res_code == 0) {
                    window.location = data.res_data.nextPage + "&orderinfo="+orderinfo;
                } else if (data.res_code == 1) {
                    $("#remain").html('*请填写正确的短信验证码!');
                    $("#sub").attr('disabled', false);
                    return false;
                } else if (data.res_code == 2) {
                    $("#remain").html(data.res_data.msg);
                    $("#sub").attr('disabled', false);
                    return false;
                } else {
                    alert("请求错误！");
                }
            });

        });
    }
</script>