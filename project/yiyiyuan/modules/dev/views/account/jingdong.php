<script>
    window.onload = function () {
        var mark = 1;
        var oBtn = document.getElementById('code');
        var user_id = <?php echo $user->user_id; ?>;
        var sub = document.getElementById('sub');
        var time = 60;
        var s = time + 1;
        sub.onclick = function () {
            if (mark == 1) {
                one();
            } else {
                two();
            }
        }
        oBtn.onclick = function () {
            one();
            countDown();
            timer = setInterval(countDown, 1000);
            oBtn.disabled = true;
        }
        function one() {
            var user_name = $("input[name='user_name']").val();
            var password = $("input[name='password']").val();
            if (password.length == 0) {
                $('.tsmes').html('*请填写京东登录密码');
                exit();
            }
            first(password, user_name);
        }

        function two() {
            var user_name = $("input[name='user_name']").val();
            var password = $("input[name='password']").val();
            var captcha = $("input[name='captcha']").val();
//            console.dir(captcha);
            if (password.length == 0) {
                $('.tsmes').html('*请填写京东登录密码');
                exit();
            }
            if (captcha.length != 6) {
                alert('请输入手机服务密码');
                exit();
            }
            oBtn.disabled = true;
            sub.disabled = true;
            second(user_name, captcha, password);
        }

        function first(password, user_name) {
            $('.Hmask').show();
            $('.loading').show();
            $.post('/dev/account/jingfirst', {user_id: user_id, user_name: user_name, password: password}, function (result) {
                $('.Hmask').hide();
                $('.loading').hide();
                var data = eval("(" + result + ")");
                if (data.code == 0) {
                    window.location = data.url;
                } else if (data.code == 3) {
                    if (data.msg.length != 0) {
                        alert(data.msg);
                    }
                    oBtn.disabled = true;
                    sub.disabled = true;
                } else if (data.code == 1) {
                    $('.tsmes').html('*请重新提交认证');
                } else {
                    $('.Hmask').show();
                    $('.layer_border').show();
                    $('#captch').show();
                    oBtn.disabled = true;
                    countDown();
                    timer = setInterval(countDown, 1000);
                    sub.disabled = false;
                    mark = 2;
                }
            });
        }
        function second(user_name, captchas, password) {
            $('.Hmask').show();
            $('.loading').show();
            $.post('/dev/account/jingsecond', {user_id: user_id, user_name: user_name, captcha: captchas, password: password}, function (result) {
                $('.Hmask').hide();
                $('.loading').hide();
                var data = eval("(" + result + ")");
                if (data.code == 0) {
                    window.location = data.url;
                } else if (data.code == 2) {
                    $('#captch').show();
                    clearInterval(timer);
                    oBtn.disabled = true;
                    countDown();
                    timer = setInterval(countDown, 1000);
                    sub.disabled = false;
                    mark = 2;
                } else if (data.code == 3) {
                    if (data.msg.length != 0) {
                        alert(data.msg);
                    }
                    window.location = data.url;
                } else {
                    $('#captch').hide();
                    clearInterval(timer);
                    sub.disabled = false;
                    $('.tsmes').html('*请重新提交认证');
                }
            })
        }
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
            <label>京东密码：</label><input placeholder="填写京东密码" type="text" name="password">
            <img src="/images/icon_remove.png" class="icon_Rem">
        </div>
        <div class="dbk_inpL" id="captch" style="display: none;">
            <label>验证码：</label><input class="yzmwidth" placeholder="填写验证码" type="text" name="captcha">
            <span class="hqyzm" id="code">获取验证码</span>
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