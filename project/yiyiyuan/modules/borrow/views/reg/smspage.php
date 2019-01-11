<div class="code-n">
    <h2 class="code-n-title">短信验证码</h2>
    <p class="code-n-result">验证码已发送至 <?php echo $format_mobile;?></p>
    <div class="input-number">
        <input type="number" class="input-lists" id="number1">
        <input type="number" class="input-lists" id="number2">
        <input type="number" class="input-lists" id="number3">
        <input type="number" class="input-lists" id="number4">
    </div>
    <div class="input-tip" style="visibility: hidden;">*验证码错误，请重新输入</div>

    <div class="reset" style="display: none;">重新获取</div>
    <div class="timer" style="display: none;">56s 后可重新获取</div>
</div>

<!-- 短信验证码弹窗 -->
<div class="code-alert" style="display: none;">
    <div class="code-alert-box">
        <img class="code-close" src="/borrow/311/images/code-n-close.png" alt="">
        <h3>图形验证码</h3>
        <div class="input-box">
            <input class="img-input" type="text" placeholder="请输入右侧验证码">
            <img src="/borrow/reg/imgcode" alt="" id="imgcode">
        </div>
        <div class="err-tip" style="visibility: hidden;">*验证码错误</div>
        <div class="alert-btn">验证</div>
        <div class="dim">看不清？换一张</div>
    </div>
</div>
<input type="hidden" id="img_code_val" value="">
<input type="hidden" id="img_init" value="1">
<script>
    var count,countdown;
    var mobile = '<?php echo $mobile;?>';
    var csrf = '<?php echo $csrf; ?>';
    var sms_type = '<?php echo $type; ?>';

    var invite_code = '<?php echo $invite_code; ?>';
    var comeFrom = '<?php echo $comeFrom; ?>';
    <?php \app\common\PLogger::getInstance('weixin','',''); ?>
    <?php $json_data = \app\common\PLogger::getJson();?>
    var baseInfoss = eval( '('  + '<?php echo $json_data; ?>' + ')' );
    $(function() {
        sendSms(1);
        // 进入页面默认焦点设置
        $('input').eq(0).focus();

        // 获取input长度
        var leng = $('input').length;
        console.log(leng);

        $('.img-input').focus(function(){
            tongji('do_img_code_input',baseInfoss);
        });

        $('.input-lists').focus(function(){
            $(this).css({
                border:'0.027rem solid #f00d0d'
            })
        })
        $('.input-lists').blur(function(){
            $(this).css({
                border:'0.027rem solid #e1e1e1'
            })
        })


        // 自动跳下一个
        $('.input-lists').keyup(function(e) {
            var event = e || window.e;
            var keyNum = event.keyCode;
            if(keyNum == 8){
                var i = $(this).index()-1;
                $('.input-lists').eq(i).val('');
                $('input').eq(i).focus();
                return;
            }

            var val = $(this).val();
            var next = $(this).next().length;
            if (val.length > 1) {
                $(this).val(val.slice(0, 1));
            }
            if (next) {
                $(this).next().val('');
                $(this).next().focus();
            } else {
                tongji('check_send_sms',baseInfoss);
                if(sms_type == 1){
                    zhuge.track('验证码输入', {
                        '来源': '注册',
                    });
                }else{
                    zhuge.track('验证码输入', {
                        '来源': '忘记密码',
                    });
                }
                setTimeout(function(){},100);
                $('input').blur();
                var number1 = $('#number1').val();
                var number2 = $('#number2').val();
                var number3 = $('#number3').val();
                var number4 = $('#number4').val();
                if(number1 == '' || number2 == '' || number3 == '' || number4 == ''){
                    $('.input-tip').text('*请正确输入验证码');
                    $('.input-tip').css('visibility', 'visible');
                    return false;
                }
                var code = number1+number2+number3+number4;
                var img_code = $('#img_code_val').val();
                $.ajax({
                    type:"POST",
                    url:"/borrow/reg/smscode",
                    data:{_csrf:csrf,mobile:mobile,code:code,img_code:img_code,type:sms_type,invite_code:invite_code,comeFrom:comeFrom},
                    datatype: "json",
                    success:function(data){
                        data = eval('('+data+')');
                        if(data.rsp_code == '0000'){
                            if(data.is_imgcode == 1){
                                $('.img-input').val('');
                                $('#imgcode').attr("src", '/borrow/reg/imgcode?' + Math.random());
                                $('.code-alert').show();
                            }else{
                                if(data.type == 2){
                                    window.location.href = '/borrow/reg/checkid?mobile='+mobile;
                                }else{
                                    window.location.href = '/borrow/reg/setpassword?mobile='+mobile;
                                }
                            }
                        }else{
                            $('.input-tip').text('*'+data.rsp_msg);
                            $('.input-tip').css('visibility', 'visible');
                            return false;
                        }
                    },
                    error: function(){
                        $('.input-tip').text('*系统错误，请稍后再试');
                        $('.input-tip').css('visibility', 'visible');
                        return false;
                    }
                });
            }
        });
        
        $('.reset').on('click',function () {
            $('#number1').val('');
            $('#number2').val('');
            $('#number3').val('');
            $('#number4').val('');
            $('.img-input').eq(0).focus();
            if(sms_type == 1){
                zhuge.track('验证码-重新获取按钮', {
                    '来源': '注册',
                });
            }else{
                zhuge.track('验证码-重新获取按钮', {
                    '来源': '忘记密码',
                });
            }
            sendSms(2);
        });

        //发送验证码
        function sendSms(type) {
            tongji('send_sms',baseInfoss);
            setTimeout(function(){},100);
            var img_code = $('#img_code_val').val();
            var img_init = $('#img_init').val();
            $.ajax({
                type:"POST",
                url:"/borrow/reg/sendsms",
                data:{_csrf:csrf,mobile:mobile,img_code:img_code,type:type,sms_type:sms_type},
                datatype: "json",
                success:function(data){
                    data = eval('('+data+')');
                    if(data.rsp_code == '0000'){
                        if(data.is_imgcode == 1){
                            $('.img-input').val('');
                            if(img_init != 1){
                                $('#imgcode').attr("src", '/borrow/reg/imgcode?' + Math.random());
                            }else{
                                $('#img_init').val(2);
                            }
                            $('.code-alert').show();
                        }else{
                            count = 60 ;
                            countdown = setInterval(CountDown, 1000);
                        }
                    }else{
                        $('.input-tip').text('*'+data.rsp_msg);
                        $('.input-tip').css('visibility', 'visible');
                        return false;
                    }
                },
                error: function(){
                    $('.input-tip').text('*系统错误，请稍后再试');
                    $('.input-tip').css('visibility', 'visible');
                    return false;
                }
            });
        }

        //图形验证码验证
        $('.alert-btn').on('click',function () {
            tongji('check_img_code',baseInfoss);
            if(sms_type == 1){
                zhuge.track('图片验证码-验证按钮', {
                    '来源': '注册',
                });
            }else{
                zhuge.track('图片验证码-验证按钮', {
                    '来源': '忘记密码',
                });
            }
            setTimeout(function(){},100);
            var img_code = $('.img-input').val();
            $.ajax({
                type:"POST",
                url:"/borrow/reg/checkimgcode",
                data:{_csrf:csrf,img_code:img_code},
                datatype: "json",
                success:function(data){
                    data = eval('('+data+')');
                    if(data.rsp_code == '0000'){
                        $('#number1').val('');
                        $('#number2').val('');
                        $('#number3').val('');
                        $('#number4').val('');
                        $('.code-alert').hide();
                        $('#img_code_val').val(img_code);
                        sendSms(1);
                    }else{
                        $('.img-input').val('');
                        $('.err-tip').text('*'+data.rsp_msg);
                        $('.err-tip').css('visibility', 'visible');
                        $('#imgcode').attr("src", '/borrow/reg/imgcode?' + Math.random());
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

        //图形验证码换图片
        $('.dim').on('click',function () {
            tongji('get_img_code',baseInfoss);
            if(sms_type == 1){
                zhuge.track('图片验证码-看不清？换一张按钮', {
                    '来源': '注册',
                });
            }else{
                zhuge.track('图片验证码-看不清？换一张按钮', {
                    '来源': '忘记密码',
                });
            }
            setTimeout(function(){},100);
            $('.img-input').val('');
            $('#imgcode').attr("src", '/borrow/reg/imgcode?' + Math.random());
        });

        //图形验证码关闭
        $('.code-close').on('click',function () {
            $('.code-alert').hide();
            $('.reset').show();
        });
    });

    var CountDown = function() {
        $('.reset').hide();
        $('.timer').show();
        $(".timer").html(count + "s 后可重新获取");
        if (count <= 0) {
            $('.reset').show();
            $('.timer').hide();
            clearInterval(countdown);
        }
        count--;
    };
</script>