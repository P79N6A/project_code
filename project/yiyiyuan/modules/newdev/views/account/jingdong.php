<div class="loading" style="display: none;">
    <img src="/images/loadings.gif">
    <p class="pleasesh">请稍后...</p>
</div>
<div class="jdall">

    <div class="jdyyy">
        <div class="jdimg">
            <img src="/images/jd.png">
        </div>
        <div class="dbk_inpL">
            <label>京东账号：</label><input placeholder="填写京东账号" type="text" name="user_name">
            <img src="/images/icon_remove.png" class="icon_Rem">
        </div>
        <div class="dbk_inpL">
            <label>京东密码：</label><input placeholder="填写京东密码" type="password" name="password">
            <img src="/images/icon_remove.png" class="icon_Rem">
        </div>
        <div class="dbk_inpL" id="captch" style="display: none;">
            <label>验证码：</label><input class="yzmwidth" placeholder="填写验证码" type="text" name="captcha">
<!--            <span class="hqyzm" id="code">获取验证码</span>-->
            <div class="button"> <button style="color: #e74747; background:#fff; position: absolute;width: 28%;right: 10px; padding: 5px;border: 1px solid #e74747; text-align: center; border-radius: 50px; bottom: 10px;" id="code" class="hqyzm">获取验证码</button></div>
        </div>
        <div style="display: none;">
            <input type="hidden" name="type" value = 1>
        </div>
    </div>
    <div class="tsmes"></div> 

    <div class="yuedty">
        <input type="checkbox" checked="checked" id="checkbox-1" class="regular-checkbox">
        <label for="checkbox-1"></label>
        授权先花一亿元访问您的京东账户信息
    </div>

    <div class="button"> <button id="sub" type="button">提交认证</button></div>

</div>
<div class="Hmask" style="display: none;"></div>
<div class="layer_border " style="display: none;">
    <p >为了您的账户安全，  </p>
    <p>请填写您在<span>“京东绑定的手机号”</span></p>
    <p>获取到的验证码。</p>
    <div class="border_top">
        <a href="javascript:;">确定</a>
    </div>
</div>

<script>
    var csrf = '<?php echo $csrf; ?>';
    var nextUrl = '<?php echo $nextPage; ?>';
    window.onload = function () {
        var oBtn = document.getElementById('code');
        var sub = document.getElementById('sub');
        var user_id = <?php echo $user->user_id; ?>;
        var time = 60;
        var s = time + 1;
        oBtn.onclick = function () {
            ajaxJd(1);
            countDown();
            timer = setInterval(countDown, 1000);
            oBtn.disabled = true;
        }
        sub.onclick = function () {
            var type = $("input[name='type']").val();
            ajaxJd(type);
        }

        function ajaxJd(type) {
            var user_name = $("input[name='user_name']").val();
            var password = $("input[name='password']").val();
            var captcha = $("input[name='captcha']").val();

            if (password.length == 0) {
                $('.tsmes').html('*请填写京东登录密码');
                return false;
            }
            $('.Hmask').show();
            $('.loading').show();
            //"process_msg"=>进程码对应的提示,* "step"=>下一步执行(0 采集成功1 执行第一步；2 执行第二步；3 直接结束)
            $.post('/new/account/jindongajax', {"user_id": user_id, "user_name": user_name, "captcha": captcha, "password": password, "type": type, "_csrf":csrf}, function (result) {
                $('.Hmask').hide();
                $('.loading').hide();
                var data = eval("(" + result + ")");
                if(data.step == 0) {//成功
                    window.location = nextUrl;
                }else if(data.step == 1) {//执行第一步
                    $('.tsmes').html(data.process_msg);
                    sub.disabled = false;
                }else if(data.step == 2) {//执行第二步
                    $("input[name='type']").val(2)
                    $('.Hmask').show();
                    $('.layer_border').show();
                    $('#captch').show();
                    oBtn.disabled = true;
                    countDown();
                    timer = setInterval(countDown, 1000);
                    sub.disabled = false;
                }else if(data.step == 3) {//直接结束
                    if (data.process_msg.length != 0) {
                        alert(data.process_msg);
                    }
                }else{
                    if (data.process_msg.length != 0) {
                        alert(data.process_msg);
                    }
                }
            });
        }

        //倒计时
        function countDown() {
            s--;
            oBtn.innerHTML = s + '秒重新获取';
            if (s == 0) {
                clearInterval(timer);
                oBtn.disabled = false;
                s = time + 1;
                oBtn.innerHTML = '获取验证码';
            }
        }
    }

    $(function () {
        $('.icon_Rem').click(function () {
            $(this).siblings('input').prop('value', '');
        });
        //点击关闭按钮
        $('.layer_border .border_top').click(function () {
            $('.Hmask').hide();
            $('.layer_border').hide();
        });

    });
</script>