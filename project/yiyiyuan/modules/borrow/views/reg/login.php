<style>
    .deal{
        font-size: 0.32rem;
        color: #444444;
        text-align: center;
        bottom: 1rem;
        left: 0;
        width: 100%;
        margin-top: 45px;
        /*margin: 56vw 0 5vw;*/
    }
</style>

<div class="tel-login longin-container">
    <img src="/borrow/311/images/top-logo.png" class="logo">
    <div class="pw-login-title">欢迎登录</div>
    <div class="info-box">
        <div class="input-box">
            <input type="number" class="u pw" placeholder="手机号" index=0 id="mobile">
            <img class="pwclose" src="/borrow/311/images/login-close.png" index=1>
        </div>
    </div>
    <p class="err-tip">*请输入正确的手机号</p>
    <input type="number"  name="agreement_hide" hidden value=2 >
    <div class="login-btn disable">下一步</div>
    
</div>
<p class="deal" style="font-size: 0.32rem;color: #444444;text-align: center;bottom:1rem;">
    <img style="height: 0.4rem;margin-bottom: -0.05rem;" src="/borrow/310/images/agreement_2x.png" alt="" class="check_target" id="agreement_mark" hidden onclick="agreement_mark()">
    <img style="height: 0.4rem;margin-bottom: -0.05rem;" src="/borrow/310/images/not_agreement_2x.png" alt="" class="check_target" id="agreement_no_mark" onclick="no_agreement_mark()" >
    勾选即代表阅读并同意
    <a href="javascript:void(0);" style="color: #6298ff;" onclick="agreement()"><span style="color: #6298ff;">《先花一亿元平台注册协议》</span>
    </a>
</p>
<div class="toast_tishi" id="xtfmang" style="top: 10rem;" hidden>请阅读并同意《先花一亿元平台注册协议》</div>
<style>
    .toast_tishi{ 
        position: fixed;
        top: 33%;
        color: #F9F9F9;
        z-index: 15;
        padding: 0.25rem 0;
        border-radius: 5px;
        text-align: center;
        background: #4C4C4C;
        width: 80%;
        left: 10%;
        font-size: 0.38rem;
        border-top-right-radius:20px;
        border-top-left-radius:20px;
        border-bottom-left-radius:20px;
        border-bottom-right-radius:20px;
    }
</style>
<script>
document.addEventListener('WeixinJSBridgeReady', function onBridgeReady() {
    WeixinJSBridge.call('hideToolbar');
    WeixinJSBridge.call('hideOptionMenu');
});
</script>
<script>
    var invite_code = '<?= $invite_code ?>';
    var comeFrom = '<?= $comeFrom ?>';
    var csrf = '<?= $csrf;?>';
    zhuge.track('登录首页');
    <?php \app\common\PLogger::getInstance('weixin','',''); ?>
    <?php $json_data = \app\common\PLogger::getJson();?>
    var baseInfoss = eval( '('  + '<?php echo $json_data; ?>' + ')' );
    $('input[name="agreement_hide"]').val('<?= $agreement;?>');
    if( $('input[name="agreement_hide"]').val() == 2 ){
        $('#agreement_mark').hide();
        $('#agreement_no_mark').show();
    }else if( $('input[name="agreement_hide"]').val() == 1 ){
        $('#agreement_mark').show();
        $('#agreement_no_mark').hide();
    }


    $('#mobile').bind('input propertychange', function() {
        var mobile = $('#mobile').val();
        if(mobile != ''){
            $('.login-btn').removeClass('disable');
        }else{
            $('.login-btn').addClass('disable');
        }
    });
    
    $("#mobile").focus(function(){
        tongji('do_mobile_input',baseInfoss);
    });

    $('.pwclose').on('click', function() {
        $('#mobile').val('');
    });
      //不勾选用户协议
    function agreement_mark(){
        $('#agreement_mark').hide();
        $('#agreement_no_mark').show();
        $('input[name="agreement_hide"]').val(2);

    }
    //勾选用户协议
    function no_agreement_mark(){
        $('#agreement_mark').show();
        $('#agreement_no_mark').hide();
        $('input[name="agreement_hide"]').val(1);
    }
    $('.login-btn').on('click',function () {
        tongji('do_login_btn',baseInfoss);
        zhuge.track('登录-下一步');
        $('.err-tip').text('*请输入正确的手机号');
        var mobile = $('#mobile').val();
        if ($.trim(mobile).length == 0) {
            $('.err-tip').css('visibility', 'visible');
            return false;
        } else {
            if (isPhoneNo($.trim(mobile)) == false) {
                $('.err-tip').css('visibility', 'visible');
                return false;
            }
        }
        if($('input[name="agreement_hide"]').val() == 2){
            //弹窗提示勾选用户协议
            $('#xtfmang').show();
            setTimeout(function () {
                 $('#xtfmang').hide();
             }, 1500);
            return false;
        }
        $('.err-tip').css('visibility', 'hidden');
        $.ajax({
            type:"POST",
            url:"/borrow/reg/loginmobile",
            data:{_csrf:csrf,mobile:mobile,'invite_code':invite_code,'comeFrom':comeFrom},
            datatype: "json",
            success:function(data){
                data = eval('('+data+')');
                if(data.rsp_code == '0000'){
                    window.location.href = data.url;
                }else{
                    $('.err-tip').text('*'+data.rsp_msg);
                    $('.err-tip').css('visibility', 'visible');
                    return false;
                }
            },
            error: function(){
                $('.err-tip').text('*系统错误，请稍后再试');
                $('.err-tip').css('visibility', 'visible');
                return false;
            }
        });
    });

    function isPhoneNo(phone) {
        var pattern = /^1[34578]\d{9}$/;
        return pattern.test(phone);
    }
    //用户协议
    function agreement() {
        tongji('agreement_one',baseInfoss);
        setTimeout(function(){
              window.location.href = '/borrow/reg/agreement';
        },100);
    }
  
    
</script>