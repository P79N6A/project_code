<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title><?= $this->title; ?></title>
    <link rel="stylesheet" type="text/css" href="/news/css/reset.css"/>
    <script src="/bootstrap/js/jquery.min.js"></script>
    <script src="/bootstrap/js/bootstrap.min.js"></script>
    <script src="/js/dev/custom.js?v=20170911"></script>
    <script src="/js/dev/script.js?v=2015092901"></script>
    <script src="/js/dev/user.js?v=20170911"></script>
    <style>
        html,body{width:100%;height:100%; background: #dfe7ec; font-family: "Microsoft YaHei"; color: #474d74; font-family: "微软雅黑";}
        .jdyyy{ background: #fff; position: relative;}
        .jdyyy .jdimg img{ width: 30%; margin: 40px 35% 50px; }
        .jdyyy .dbk_inpL {border-top: 1px solid #e7edf0;border-radius: 5px;background: #fff; padding: 4% 6.3%;overflow: hidden; position: relative;}
        .jdyyy .dbk_inpL .icon_Rem{ position: absolute; right: 3%;width: 7%;}
        .jdyyy .dbk_inpL label {text-align: left;float: left;margin-bottom: 0;font-size: 1.2rem;color: #444;}
        .jdyyy .dbk_inpL input{border: none;float: left;padding-left:5px;font-size: 1.2rem;}
        .jdyyy .dbk_inpL select { width: 72%; background: transparent; -webkit-appearance: none; /*for chrome*/    font-size: 1.2rem; padding-left: 10px;}
        .jdyyy .dbk_inpL .hqyzm{color: #e74747; position: absolute;width: 30%;right: 10px; padding: 5px;border: 1px solid #e74747; text-align: center; border-radius: 50px; bottom: 10px;}
        .button {margin: 0 5%; margin-top: 15px;}
        .button button {width: 100%;background: #e74747;border-radius:5px;color: #fff;font-size: 1.25rem; padding: 10px 0;}
        .jdyyy.studtyimg  .dbk_inpL label{ text-align: right; width: 26%;}
        .jdyyy .dbk_inpL span.mingzi{ color: #444; padding-left: 10px; font-size: 1.1rem;}
        .tsmes{ text-align:center; padding-top: 10px; color: #e74747;}
    </style>
</head>
<body>
<div class="jdall">
    <div class="jdyyy" style="margin: 10px 0;">
        <div class="dbk_inpL">
            <label>支付金额：</label><span class="mingzi">¥<?php echo sprintf('%.2f', $repay->money); ?></span>
        </div>
    </div>
    <div class="jdyyy">
        <div class="dbk_inpL">
            <label>姓名：</label><span class="mingzi"><?php echo '**'.mb_substr($repay->user->realname,-1,1,"utf-8"); ?></span>
        </div>
        <div class="dbk_inpL">
            <label>身份证号：</label><span class="mingzi"><?php echo substr_replace($repay->user->identity,'***********',3,11); ?></span>
        </div>
        <div class="dbk_inpL">
            <label>手机号：</label><span class="mingzi"><?php echo substr_replace($repay->user->mobile,'****',3,4); ?></span>
        </div>
        <div class="dbk_inpL">
            <label>银行卡：</label><span class="mingzi"><?php echo '****'.substr($repay->bank->card, -4); ?></span>
        </div>
        <div class="dbk_inpL">
            <label>短信序列号：</label><span class="mingzi"><input class="yzmwidth smsSeq" placeholder="填写短信序列号" type="text"></span>
        </div>
        <div class="dbk_inpL">
            <label>验证码：</label>
            <input class="yzmwidth smsCode" placeholder="填写验证码" type="text">
            <span class="hqyzm"><button id="getRepayCode" style="width:100%;color:#c90000;background:rgba(255, 255, 255, 0.5)" >获取验证码</button></span>
        </div>
    </div>
    <div class="tsmes" style="display: none;">*手机号错误</div>
    <div class="button"> <button id="submitRepay">提交认证</button></div>
</div>
<input type="hidden" id="mobile" value="<?php echo $repay->user->mobile; ?>">
<input type="hidden" id="card" value="<?php echo $repay->bank->card; ?>">
<input type="hidden" id="key" value="<?php echo $key; ?>">
<script>
    //发送验证码
    $('#getRepayCode').click(function () {
        var mobile = $("#mobile").val();
        var card = $("#card").val();
        $("#getRepayCode").attr('disabled', true);

        $.post("/new/depositoryapi/sendcode", {mobile: mobile, card: card}, function(result) {
            var data = eval("(" + result + ")");
            console.log(result);
            if (data.res_code == '0') {
                count = 60;
                countdown = setInterval(CountDowns, 1000);
                $("#getRepayCode").attr('disabled', false);
                $(".tsmes").hide();
            } else if(data.res_code == '2'){
                $(".tsmes").html('您今天获取验证码的次数过多，请明天再试');
                $(".tsmes").show();
                return false;
            } else {
                $(".tsmes").html('发送失败，请重试');
                $(".tsmes").show();
                return false;
            }
        });
    });

    //确认支付
    $('.button').click(function () {
        var key = $("#key").val();
        var smsCode = $(".smsCode").val();
        var smsSeq = $(".smsSeq").val();
        $("#submitRepay").attr('disabled', true);
        $("#submitRepay").css("opacity","0.65");
        if(key == null || key == ''){
            $("#submitRepay").attr('disabled', false);
            $("#submitRepay").css("opacity","1");
            $(".tsmes").html('还款失败，请重试');
            $(".tsmes").show();
            return false;
        }
        if(smsCode == null || smsCode == '' || smsSeq == null || smsSeq == ''){
            $("#submitRepay").attr('disabled', false);
            $("#submitRepay").css("opacity","1");
            $(".tsmes").html('请输入验证码和序列号');
            $(".tsmes").show();
            return false;
        }
        $.post("/new/depositoryapi/directpayonline", {key: key,smsCode:smsCode,smsSeq:smsSeq}, function(result) {
            var data = eval("(" + result + ")");
            console.log(result);
            if (data.res_code == '0') {
                if(data.source == 5 || data.source == 6){
                    window.location.href = '/new/repay/verify?source='+data.source;
                }else{
                    window.location.href = '/new/repay/verify';
                }
            } else{
                if(data.source == 5 || data.source == 6){
                    window.location.href = '/new/repay/errorapp';
                }else{
                    window.location.href = '/new/repay/error';
                }
            }
        });
    });

    //倒计时
    var CountDowns = function() {
        $("#getRepayCode").attr("disabled", true).addClass('dis');
        $("#getRepayCode").html("重新获取 ( " + count + " ) ");
        if (count <= 0) {
            $("#getRepayCode").html("获取验证码").removeAttr("disabled").removeClass('dis');
            clearInterval(countdown);
        }
        count--;
    };

    function payResult() {

    }
</script>
</body>
</html>