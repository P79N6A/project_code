<style>
        .deal{
               font-size: 0.32rem;
        color: #444444;
        text-align: center;
        width: 100%;
            margin: 45vw 0 5vw;
       
    }
</style>
<div class="pw-login longin-container">
    <img src="/borrow/311/images/pw-logo.png" class="logo">
    <div class="pw-login-title">欢迎登录</div>
    <div class="info-box">
        <div class="input-box">
            <input type="number" class="u pw" placeholder="手机号码" index=0 id="mobile" readonly="readonly">
        </div>
        <div class="input-box">
            <input type="password" class="u pw" placeholder="密码" index=1 id="password" value="<?php echo $password;?>">
            <div class="right">
                <img class="pwclose" src="/borrow/311/images/login-close.png" index=1>
                <img class="look" src="/borrow/311/images/login-no-see.png" index=1>
            </div>
        </div>
    </div>
    <p class="err-tip">*密码错误</p>
    <div class="login-btn">确定</div>
    <div class="password">
        <a style="color:#f00d0d;" href="javascript:void(0);" onclick="doSetPassword()"><div class="forget-pw" >忘记密码？</div></a>
        <div class="forget" onclick="remember()">
            <input type="hidden" id="remember" value="<?php echo $remember;?>" />
            <img src="<?php if ($remember == 1): ?>/borrow/311/images/active-pw.png<?php else: ?>/borrow/311/images/active-pw-n.png<?php endif; ?>" alt="" id="remember_img">
            <span>记住密码</span>
        </div>
    </div>
    
</div>
<!--<p class="deal">登录即同意<a href="javascript:void(0);" onclick="agreement()"><span style="color: #6298ff;">《用户协议》</span></a></p>-->
<script>
document.addEventListener('WeixinJSBridgeReady', function onBridgeReady() {
    WeixinJSBridge.call('hideToolbar');
    WeixinJSBridge.call('hideOptionMenu');
});
</script>
<script>
    var csrf = '<?= $csrf;?>';
    var mobile = '<?php echo $mobile;?>';
    <?php \app\common\PLogger::getInstance('weixin','',''); ?>
    <?php $json_data = \app\common\PLogger::getJson();?>
    var baseInfoss = eval( '('  + '<?php echo $json_data; ?>' + ')' );

    if(mobile.length != 0){
        $('#mobile').val(mobile);
    }

    $("#password").focus(function(){
        tongji('do_password_input',baseInfoss);
    });

    function doSetPassword() {
        tongji('do_set_password',baseInfoss);
        zhuge.track('登录-忘记密码');
        setTimeout(function(){
            window.location.href = '/borrow/reg/smspage?mobile=<?php echo $mobile;?>&type=2';
        },100);
    }

    // 显示隐藏密码
    $('.look').on('click', function() {
        var i = Number($(this).attr('index'));
        if ($(this).attr('look') === 'true') {
            $(this).attr('src', '/borrow/311/images/login-no-see.png').attr('look', '');
            $('.pw').eq(i).attr('type', 'password');
        } else {
            $(this).attr('src', '/borrow/311/images/login-see.png').attr('look', true);
            $('.pw').eq(i).attr('type', 'text');
        }
    });

    // 密码清空
    $('.pwclose').on('click', function() {
        var i = Number($(this).attr('index'));
        $('.pw').eq(i).val('');
    });

    // 登录
    $('.login-btn').on('click', function() {
        tongji('do_login',baseInfoss);
        zhuge.track('登录按钮');
        setTimeout(function(){},100);
        if($('.login-btn').hasClass('lock')){
            return false;
        }
        $('.login-btn').addClass('lock');
        $('.err-tip').css('visibility', 'hidden');
        var mobile = $('#mobile').val();
        var password = $('#password').val();
        var remember = $('#remember').val();
        if(mobile.length == 0 || password.length == 0){
            $('.login-btn').removeClass('lock');
            $('.err-tip').text('*手机号码或密码不能为空');
            $('.err-tip').css('visibility', 'visible');
            return false;
        }
        $.ajax({
            type:"POST",
            url:"/borrow/reg/ajaxsignin",
            data:{_csrf:csrf,mobile:mobile,password:password,remember:remember},
            datatype: "json",
            success:function(data){
                data = eval('('+data+')');
                if(data.rsp_code == '0000'){
                    window.location.href = data.url;
                }else{
                    $('.login-btn').removeClass('lock');
                    $('.err-tip').text('*'+data.rsp_msg);
                    $('.err-tip').css('visibility', 'visible');
                    return false;
                }
            },
            error: function(){
                $('.login-btn').removeClass('lock');
                $('.err-tip').text('*系统错误，请稍后再试');
                $('.err-tip').css('visibility', 'visible');
                return false;
            }
        });
    });
    
    //记住密码
    function remember() {
        tongji('remember',baseInfoss);
        var remember = $('#remember').val();
        if(remember == 1){
            $('#remember').val(2);
            $('#remember_img').attr('src','/borrow/311/images/active-pw-n.png');
        }else{
            $('#remember').val(1);
            $('#remember_img').attr('src','/borrow/311/images/active-pw.png');
        }
    }

    //用户协议
    function agreement() {
        tongji('agreement',baseInfoss);
        setTimeout(function(){
            window.location.href = '/borrow/reg/agreement?mobile=<?php echo $mobile;?>';
        },100);
    }
</script>