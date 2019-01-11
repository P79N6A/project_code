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
            var password = $("input[name='password']").val();
            if (password.length < 6 || password.length > 8) {
                $('.tsmes').html('*请输入手机服务密码');
                exit();
            }
            first(password);
        }

        function two() {
            var password = $("input[name='password']").val();
            var captcha = $("input[name='captcha']").val();
            if (password.length < 6 || password.length > 8) {
                $('.tsmes').html('请输入手机服务密码');
                exit();
            }
            if (captcha.length != 6) {
                alert('请输入手机服务密码');
                exit();
            }
            oBtn.disabled = true;
            sub.disabled = true;
            second(captcha, password);
        }

        function first(password) {
            $('.Hmask').show();
            $('.loading').show();
            $.post('/dev/account/jufirst', {user_id: user_id, password: password}, function (result) {
                $('.Hmask').hide();
                $('.loading').hide();
                var data = eval("(" + result + ")");
                if (data.code == 0) {
                    window.location = data.url;
                } else if (data.code == 3) {
                    if (data.msg.length != 0) {
                        alert(data.msg);
                    }
//                    window.location = data.url;
                } else if (data.code == 1) {
                    $('.tsmes').html('*请重新提交认证');
                } else {
                    $('#captch').show();
                    countDown();
                    timer = setInterval(countDown, 1000);
                    oBtn.disabled = true;
                    sub.disabled = false;
                    mark = 2;
                }
            });
        }
        function second(captchas, password) {
            $('.Hmask').show();
            $('.loading').show();
            $.post('/dev/account/jusecond', {user_id: user_id, captcha: captchas, password: password}, function (result) {
                $('.Hmask').hide();
                $('.loading').hide();
                var data = eval("(" + result + ")");
                if (data.code == 0) {
//                    window.location = data.url;
                } else if (data.code == 2) {
                    if (data.msg.length != 0) {
                        $('.tsmes').html('*' + data.msg);
                    } else {
                        $('.tsmes').html('*请重新提交认证');
                    }
                    sub.disabled = false;
                } else if (data.code == 3) {
                    if (data.msg.length != 0) {
                        alert(data.msg);
                    }
                    window.location = data.url;
                } else {
                    mark = 1;
                    if (data.msg.length != 0) {
                        $('.tsmes').html('*' + data.msg);
                    } else {
                        $('.tsmes').html('*请重新提交认证');
                    }
                    $('#captch').hide();
                    sub.disabled = false;
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
</script>
<div class="Hmask" style="display: none;"></div>
<div class="loading" style="display: none;">
    <img src="/images/loadings.gif">
    <p class="pleasesh">请稍后...</p>
</div>
<div class="jdall">

    <div class="jdyyy">
        <div class="jdimg">
            <img style="width:20%; margin-left:40%;" src="/images/studyimg3.png">
        </div>
        <div class="dbk_inpL">
            <label>姓名：</label><span class="mingzi"><?php echo $user->realname; ?></span>
        </div>
        <div class="dbk_inpL">
            <label>电话：</label><span class="mingzi"><?php echo $user->mobile; ?></span>
        </div>
        <div class="dbk_inpL">
            <label>密码：</label>
            <input class="phonefu" name="password" placeholder="输入手机服务密码" type="text">
        </div>
        <div class="dbk_inpL" id="captch" style="display: none;">
            <label>验证码：</label><input placeholder="输入手机短信验证码" class="yzmwidth" type="text" name="captcha">
            <button class="hqyzm" id="code">获取验证码</button>
        </div>
    </div>
    <div class="tsmes"></div>
    <div class="button"> <button id="sub" type="button">提交认证</button></div>
</div>