<div class="code-n">
    <h2 class="code-n-title">身份验证</h2>
    <p class="code-n-result">为保证您的账户安全，请验证身份证后6位</p>
    <div class="input-number">
        <input type="number" class="input-lists" id="number1">
        <input type="number" class="input-lists" id="number2">
        <input type="number" class="input-lists" id="number3">
        <input type="number" class="input-lists" id="number4">
        <input type="number" class="input-lists" id="number5">
        <input type="text" class="input-lists" id="number6">
    </div>
    <div class="input-tip" style="visibility: hidden;">*身份证信息输入有误，请重新输入</div>
</div>
<script>
    var mobile = '<?php echo $mobile;?>';
    var csrf = '<?php echo $csrf; ?>';
    <?php \app\common\PLogger::getInstance('weixin','',''); ?>
    <?php $json_data = \app\common\PLogger::getJson();?>
    var baseInfoss = eval( '('  + '<?php echo $json_data; ?>' + ')' );

    $(function() {
//        document.onkeydown=function(e){
//            var event = e || window.e;
//            var keyNum = event.keyCode;
//
//            if(keyNum == 8){
//                var a = $('.input-number input')
//                console.log(a)
//            }
//        };

        // 进入页面默认焦点设置
        $('input').eq(0).focus().css({ border:'1px solid #f00d0d'});

        // 获取input长度
        var leng = $('input').length;
        
        $('.input-lists').focus(function(){
            $(this).css({
                border:'1px solid #f00d0d'
            });
        });
        $('.input-lists').blur(function(){
            console.log($(this))
            $(this).css({
                border:'1px solid #e1e1e1'
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
                $(this).next().focus();
            } else {
                tongji('check_identity',baseInfoss);
                zhuge.track('身份证号码输入');
                setTimeout(function(){
                    $('input').blur();
                    var number1 = $('#number1').val();
                    var number2 = $('#number2').val();
                    var number3 = $('#number3').val();
                    var number4 = $('#number4').val();
                    var number5 = $('#number5').val();
                    var number6 = $('#number6').val();
                    if(number1 == '' || number2 == '' || number3 == '' || number4 == '' || number5 == '' || number6 == ''){
                        $('.input-tip').text('*请正确输入身份证号码');
                        $('.input-tip').css('visibility', 'visible');
                        return false;
                    }
                    var identity = number1+number2+number3+number4+number5+number6;
                    $.ajax({
                        type:"POST",
                        url:"/borrow/reg/ajaxcheckid",
                        data:{_csrf:csrf,mobile:mobile,identity:identity},
                        datatype: "json",
                        success:function(data){
                            data = eval('('+data+')');
                            if(data.rsp_code == '0000'){
                                window.location.href = '/borrow/reg/setpassword?mobile='+mobile;
                            }else{
                                $('#number1').val('');
                                $('#number2').val('');
                                $('#number3').val('');
                                $('#number4').val('');
                                $('#number5').val('');
                                $('#number6').val('');
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
                },100);
            }
        });
    });

</script>