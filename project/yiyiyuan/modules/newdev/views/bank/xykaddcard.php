<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>添加信用卡</title>
    <link rel="stylesheet" type="text/css" href="/newdev/css/coupon/reset.css"/>
    <link rel="stylesheet" type="text/css" href="/newdev/css/coupon/style302.css"/>
</head>
<body>
<div class="yhmaold">
    <div class="dbk_inpL">
        <label>姓名</label>
        <input type="text" name="user_name"  placeholder="请输入姓名" readonly value="<?php echo $newuser['realname']?>" id="user_name">
        <input type="hidden" name="user_name1"  placeholder="请输入姓名" value="<?php echo $user['realname']?>" id="user_name1">
    </div>
    <div class="dbk_inpL">
        <label>身份证</label>
        <input type="text" name="identity" placeholder="请输入身份证" readonly value="<?php echo $newuser['identity']?>" id="user_name">
        <input type="hidden" name="identity1" placeholder="请输入身份证"  value="<?php echo $user['identity']?>" id="user_name1">
    </div>
</div>
<div class="yhmaold">
    <div class="dbk_inpL">
        <label>信用卡号</label><input name="card_no" placeholder="请输入信用卡号" type="tel">
    </div>
    <div class="dbk_inpL">
        <label>预留手机号</label><input name="mobile" placeholder="请输入信用卡预留手机号" type="tel">
    </div>
    <div class="dbk_inpL">
        <label>验证码</label>
        <input class="yzmwidth" name="code" placeholder="输入短信验证码" type="tel">
        <span id="get_bankcode" class="hqyzm">获取验证码</span>
    </div>
</div>
<div id="sub" class="buttonyi"> <button>绑定</button></div>


<div class="jiebangcg" style="display: none">绑定成功</div>
<div class="jiebangcg" style="display: none">绑定失败</div>
</body>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script src="/newdev/js/log.js" type="text/javascript" charset="utf-8"></script>
<script>
    $('.errore').click(function () {
        $('.duihsucc2').hide();
        $('.Hmask').hide();
    });
    $('.sureyemian').click(function () {
        $('.duihsucc2').hide();
        $('.Hmask').hide();
    });
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
</script>
<script>
    <?php \app\common\PLogger::getInstance('weixin','',$user['id']); ?>
    <?php $json_data = \app\common\PLogger::getJson();?>
    var baseInfoss = eval( '('  + '<?php echo $json_data; ?>' + ')' );

    var _csrf = '<?php echo $csrf; ?>';
    window.onload = function () {
        var _mobileRex = /^(1(([0-9][0-9])|(47)))\d{8}$/;
        var _cardRex = /^\d{15,19}$/;
        $('#sub').click(function () {
            tongji('user_xykbangding',baseInfoss);
            var user_name = $('input[name="user_name1"]').val();
            var identity = $('input[name="identity1"]').val();
            var card_no = $('input[name="card_no"]').val();
            var mobile = $('input[name="mobile"]').val();
            var code = $('input[name="code"]').val();
            card_no = card_no.replace(/\s+/g, "");
            var user_id = $('input[name="user_id"]').val();
            var bank_type = 2;
            if(user_name.length == 0) {
//                $("#remain").html('*请填持卡人姓名');
                $(".jiebangcg").show();
                $(".jiebangcg").text('请填持卡人姓名');
                setTimeout(function(){
                    $(".jiebangcg").hide();
                    $(".jiebangcg").text('');
                },1000);
                return false;
            }
            if(identity.length == 0) {
                $(".jiebangcg").show();
                $(".jiebangcg").text('请填写身份证号');
                setTimeout(function(){
                    $(".jiebangcg").hide();
                    $(".jiebangcg").text('');
                },1000);
                return false;
            }
            if(card_no.length == 0) {
                $(".jiebangcg").show();
                $(".jiebangcg").text('请填写信用卡号');
                setTimeout(function(){
                    $(".jiebangcg").hide();
                    $(".jiebangcg").text('');
                },1000);
                return false;
            }
            if(mobile.length == 0) {
//                $("#remain").html('*请填写银行预留手机号');
                $(".jiebangcg").show();
                $(".jiebangcg").text('请填写银行预留手机号');
                setTimeout(function(){
                    $(".jiebangcg").hide();
                    $(".jiebangcg").text('');
                },1000);
                return false;
            }
            if(code.length == 0) {
//                $("#remain").html('*请填写入短信验证码');
                $(".jiebangcg").show();
                $(".jiebangcg").text('请填写入短信验证码');
                setTimeout(function(){
                    $(".jiebangcg").hide();
                    $(".jiebangcg").text('');
                },1000);
                return false;
            }
            if (mobile == '' || !(_mobileRex.test(mobile))) {
//                $("#remain").html('*请填写正确的手机号');
                $(".jiebangcg").show();
                $(".jiebangcg").text('请填写正确的手机号');
                setTimeout(function(){
                    $(".jiebangcg").hide();
                    $(".jiebangcg").text('');
                },1000);
                return false;
            }
            if (!(_cardRex.test(card_no))) {
//                $("#remain").html('*请填写正确的银行卡号');
                $(".jiebangcg").show();
                $(".jiebangcg").text('请填写正确的银行卡号');
                setTimeout(function(){
                    $(".jiebangcg").hide();
                    $(".jiebangcg").text('');
                },1000);
                return false;
            }
            if(!code){
//                $("#remain").html('*请填写短信验证码!');
                $(".jiebangcg").show();
                $(".jiebangcg").text('请填写短信验证码');
                setTimeout(function(){
                    $(".jiebangcg").hide();
                    $(".jiebangcg").text('');
                },1000);
                $("#sub").attr('disabled', false);
                return false;
            }
            if(code.length != 4){
//                $("#remain").html('*请填写正确的短信验证码!');
                $(".jiebangcg").show();
                $(".jiebangcg").text('请填写正确的短信验证码');
                setTimeout(function(){
                    $(".jiebangcg").hide();
                    $(".jiebangcg").text('');
                },1000);
                $("#sub").attr('disabled', false);
                return false;
            }

            $("#sub").attr('disabled', true);
            $.post("/new/bank/bindcard_xyk", {_csrf:_csrf, user_id: user_id, real_name: user_name, identity:identity, card: card_no, mobile: mobile, code: code, banktype: bank_type}, function (result) {
                var data = eval("(" + result + ")");
//                console.log(data);
                if (data.res_code == 0) {
                    $(".jiebangcg").show();
                    $(".jiebangcg").text('绑定成功!3秒后跳转....');
                    setTimeout(function(){
                        $(".jiebangcg").hide();
                        $(".jiebangcg").text('');
                        url ='/new/account/peral';
                        window.location.href =url;
                    },3000);
                    $("#sub").attr('disabled', false);
                } else if (data.res_code == 1) {
//                    $("#remain").html('*短信验证码输入错误，请重新输入!');
                    $(".jiebangcg").show();
                    $(".jiebangcg").text('短信验证码输入错误，请重新输入');
                    setTimeout(function(){
                        $(".jiebangcg").hide();
                        $(".jiebangcg").text('');
                    },1000);
                    $("#sub").attr('disabled', false);
                    return false;
                } else if (data.res_code == 2) {
//                    $("#remain").html(data.res_data);
                    $(".jiebangcg").show();
                    $(".jiebangcg").text(data.res_data);
                    setTimeout(function(){
                        $(".jiebangcg").hide();
                        $(".jiebangcg").text('');
                    },1000);
                    $("#sub").attr('disabled', false);
                    return false;
                } else if (data.res_code == 3) {
//                    $("#remain").html(data.res_data.msg);
                    $(".jiebangcg").show();
                    $(".jiebangcg").text(data.res_data.msg);
                    setTimeout(function(){
                        $(".jiebangcg").hide();
                        $(".jiebangcg").text('');
                    },1000);
                    $("#sub").attr('disabled', false);
                    return false;
                } else {
//                    window.location ='/dev/bank/error';
//                    $("#remain").html('系统繁忙，请稍后重试');
                    $(".jiebangcg").show();
                    $(".jiebangcg").text('系统繁忙，请稍后重试');
                    setTimeout(function(){
                        $(".jiebangcg").hide();
                        $(".jiebangcg").text('');
                    },1000);
                    $("#sub").attr('disabled', false);
                }
            });
        });

        $('.jiebangcg').click(function() {
            $(".jiebangcg").hide();
            $(".jiebangcg").text('');
        })
        //绑卡短信验证码发送
        $('#get_bankcode').click(function() {
            tongji('user_xykhqyzm',baseInfoss);
            var user_id = $("#user_id").val();
            var card_no = $('input[name="card_no"]').val();
            var mobile = $('input[name="mobile"]').val();
            if (mobile == '' || !(_mobileRex.test(mobile))) {
                $(".jiebangcg").show();
                $(".jiebangcg").text('请填写正确的手机号');
                setTimeout(function(){
                    $(".jiebangcg").hide();
                    $(".jiebangcg").text('');
                },1000);
                return false;
            }
            var bank_type = 1;
            $("#get_bankcode").attr('disabled', "true");
            $.post("/new/bank/banksend", {_csrf:_csrf, mobile: mobile, cardno: card_no, banktype:bank_type}, function(result) {
//                alert(data);
                var data = eval("(" + result + ")");
                if (data.res_code == 0) {
                    //发送成功
                    count = 60;
                    console.log(99);
                    countdown = setInterval(CountDown_bank, 1000);
                } else if(data.res_code == 1){
//                    $("#remain").html('*请绑定正确的银行卡！');
                    $(".jiebangcg").show();
                       console.log(10);
                    $(".jiebangcg").text('请绑定正确的银行卡');
                    setTimeout(function(){
                        $(".jiebangcg").hide();
                        $(".jiebangcg").text('');
                    },1000);
                    $("#mobile").focus();
                    $("#get_bankcode").attr('disabled', "false");
                    return false;
                } else if(data.res_code == 5){
//                    $("#remain").html('*请绑填写正确的手机号！');
                    $(".jiebangcg").show();
                    $(".jiebangcg").text('请绑填写正确的手机号');
                    setTimeout(function(){
                        $(".jiebangcg").hide();
                        $(".jiebangcg").text('');
                    },1000);
                    $("#mobile").focus();
                    $("#get_bankcode").attr('disabled', "false");
                    return false;
                }else {
//                    $("#remain").html('短信验证码获取次数已达上限，请24小时候重试');
                    $(".jiebangcg").show();
                    $(".jiebangcg").text('短信验证码获取次数已达上限,请24小时候重试');
                    setTimeout(function(){
                        $(".jiebangcg").hide();
                        $(".jiebangcg").text('');
                    },1000);
                    $("#mobile").focus();
                    $("#get_bankcode").attr('disabled', "false");
                    return false;
                }
            });

        });
        //绑卡倒计时
        var CountDown_bank = function() {
            $("#get_bankcode").html("重新获取(" + count + ")");
            if (count <= 0) {
                $("#get_bankcode").bind('click');
                $("#get_bankcode").html("获取验证码").removeAttr("disabled").removeClass('dis');
                clearInterval(countdown);
            }
            count--;
        };
    };
</script>
</html>