<div class="longin-container">
    <h2 class="longin-container-title"><?php if ($type == 2): ?>重置登录密码<?php else: ?>设置登录密码<?php endif; ?></h2>
    <div class="info-box">
        <div class="input-box">
            <input type="password" class="u pw" placeholder="请输入登录密码" index=0 id="password">
            <div class="right">
                <img class="pwclose" src="/borrow/311/images/login-close.png" index=0>
                <img class="look" src="/borrow/311/images/login-no-see.png" index=0>
            </div>
        </div>
        <div class="input-box">
            <input type="password" class="u pw" placeholder="请再次输入登录密码" index=1 id="repassword">
            <div class="right">
                <img class="pwclose" src="/borrow/311/images/login-close.png"  index=1>
                <img class="look" src="/borrow/311/images/login-no-see.png" index=1>
            </div>
        </div>
    </div>
    <p class="err-tip">*两次密码输入不一致</p>
    <div class="login-btn">确定</div>
    <!-- 提示窗 -->
    <div class="login-tip-a" hidden>重置登录密码成功！</div>
</div>
<script>
    var csrf = '<?= $csrf;?>';
    var mobile = '<?php echo $mobile;?>';
    <?php \app\common\PLogger::getInstance('weixin','',''); ?>
    <?php $json_data = \app\common\PLogger::getJson();?>
    var baseInfoss = eval( '('  + '<?php echo $json_data; ?>' + ')' );

    // 显示隐藏密码
    $('.look').on('click', function() {
        setTimeout(function(){},100);
        var i = Number($(this).attr('index'));
        if ($(this).attr('look') === 'true') {
            tongji('no_see_password',baseInfoss);
            $(this).attr('src', '/borrow/311/images/login-no-see.png').attr('look', '');
            $('.pw').eq(i).attr('type', 'password');
        } else {
            tongji('see_password',baseInfoss);
            $(this).attr('src', '/borrow/311/images/login-see.png').attr('look', true);
            $('.pw').eq(i).attr('type', 'text');
        }
    });

    $('#password').focus(function(){
        tongji('do_password_input',baseInfoss);
    });

    $('#repassword').focus(function(){
        tongji('do_repassword_input',baseInfoss);
    });

    // 密码清空
    $('.pwclose').on('click', function() {
        var i = Number($(this).attr('index'));
        console.log(i);
        $('.pw').eq(i).val('');
    });

    // 密码验证
    $('.login-btn').on('click', function() {
        tongji('do_password',baseInfoss);
        zhuge.track('设置密码-确定按钮');
        setTimeout(function(){},100);
        if($('.login-btn').hasClass('lock')){
            return false;
        }
        $('.login-btn').addClass('lock');
        $('.err-tip').css('visibility', 'hidden');
        var o = $('.pw').eq(0).val();
        var t = $('.pw').eq(1).val();
        if (o !== t) {
            $('.login-btn').removeClass('lock');
            $('.err-tip').text('*两次密码输入不一致');
            $('.err-tip').css('visibility', 'visible');
            return false;
        }
        if(o.length < 6){
            $('.login-btn').removeClass('lock');
            $('.err-tip').text('*密码长度不小于6位');
            $('.err-tip').css('visibility', 'visible');
            return false;
        }
        $.ajax({
            type:"POST",
            url:"/borrow/reg/ajaxsetpassword",
            data:{_csrf:csrf,mobile:mobile,password:o,repassword:t},
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
</script>