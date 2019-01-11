<script>
    window.onload = function() {
        var oBtn = document.getElementById('code');
        var tBug = document.getElementById('tbug');
        var card_type = <?php echo $post_data['card_type']; ?>;
        var timer = null;
        var time = 60;
        var s = time + 1;
        oBtn.onclick = function() {
            var mobile = $('input[name="mobile"]').val();
            if (mobile.length == 0 || !_mobileRex.test(mobile)) {
                $('#remain').html('请输入正确格式的手机号!');
                return false;
            } else {
                $('#remain').html('');
            }
            if (card_type != 0) {
                var month = $('input[name="month"]').val();
                var year = $('input[name="year"]').val();
                var cvv2 = $('input[name="cvv2"]').val();
                var two = /^\d{2}$/;
                var three = /^\d{3}$/;
                if (!two.test(month) || !two.test(year) || !three.test(cvv2)) {
                    $('#remain').html('请输入正确的信用卡信息');
                    return false;
                } else {
                    $('#remain').html('');
                }
            }
            countDown();
            timer = setInterval(countDown, 1000);
            oBtn.disabled = true;
            $.ajax({
                type: "POST",
                dataType: "json",
                url: "/dev/bank/activity",
                data: $('#pay').serialize(), // 你的formid
                async: false,
            });
        };
        tBug.onclick = function() {
            var mobile = $('input[name="mobile"]').val();
            if (mobile.length == 0 || !_mobileRex.test(mobile)) {
                $('#remain').html('请输入正确格式的手机号!');
                return false;
            } else {
                $('#remain').html('');
            }
            var reg = /^\d{4}$/;
            if (!reg.test($('input[name="verifyCode"]').val())) {
                $('#remain').html('请输入正确的验证码!');
                return false;
            }
            if (card_type != 0) {
                var month = $('input[name="month"]').val();
                var year = $('input[name="year"]').val();
                var cvv2 = $('input[name="cvv2"]').val();
                var two = /^\d{2}$/;
                var three = /^\d{3}$/;
                if (!two.test(month) || !two.test(year) || !three.test(cvv2)) {
                    $('#remain').html('请输入正确的信用卡信息');
                    return false;
                } else {
                    $('#remain').html('');
                }
            }
            mark = false;
            $.ajax({
                type: "POST",
                dataType: "json",
                url: "/dev/bank/addeventcard",
                data: $('#pay').serialize(), // 你的formid
                async: false,
                error: function(result) {
                    $("#remain").html('出现错误请重新获取验证码');
                },
                success: function(result) {
                    if (result.ret == 0) {
                        $("#remain").html('');
                        if ($('input[name="f"]').val() == undefined) {
                            location.href = "/dev/bank/success?old=1";
                        } else {
                            location.href = "/dev/loan/second";
                        }
                    } else {
                        $("#remain").html(result.msg);
                    }
                }
            });
            return mark;
        };

        function countDown() {
            s--;
            oBtn.innerHTML = s + '秒后重新获取';
            if (s == 0) {
                clearInterval(timer);
                oBtn.disabled = false;
                s = time + 1;
                oBtn.innerHTML = '重新获取验证码';
            }
        }
    };
</script>
<div class="Hcontainer nP">
    <div class="main">
        <form class="form-horizontal" role="form" id="pay">
            <div class="border1 jcbd">
                <ul>
                    <li class="noBorder">
                        <div class="col-xs-3 text-right n26 grey2">姓名</div>
                        <div class="col-xs-8 n26 grey4"><?php echo $user['realname']; ?></div>
                        <input type="hidden" name="userid" value="<?php echo $user['user_id']; ?>">
                    </li>
                    <li class="noBorder">
                        <div class="col-xs-3 text-right n26 grey2">银行卡号</div>
                        <div class="col-xs-8 n26 grey4"><?php echo $post_data['cards']; ?></div>
                        <input type="hidden" name="card" value="<?php echo str_replace(' ', '', $post_data['cards']); ?>">
                        <input type="hidden" name="pay_type" value="<?php echo $post_data['card_type']; ?>">
                        <?php if (isset($post_data['f']) && !empty($post_data['f'])): ?>
                            <input type="hidden" name="f" value="<?php echo $post_data['f']; ?>">
                        <?php endif; ?>
                    </li>
                    <li class="noBorder">
                        <div class="col-xs-3 text-right n26 grey2">身份证号</div>
                        <div class="col-xs-8 n26 grey4"><?php echo substr($user['identity'], 0, 4) . '**********' . substr($user['identity'], 14, 4); ?></div>
                    </li>
                    <li class="noBorder">
                        <div class="col-xs-3 text-right n26 grey2" style="line-height:36px;">手机号码</div>
                        <div class="col-xs-9 n26"><input type="text" name="mobile" placeholder="银行卡留存号码"></div>
                    </li>
                    <?php if ($post_data['card_type'] != 0): ?>
                        <li class="noBorder">
                            <div class="col-xs-3 text-right n26 grey2" style="line-height:36px;">有效期</div>
                            <div class="col-xs-9 n26">
                                <div class="col-xs-4"><input name="month" type="text"></div><div class="col-xs-2">月</div>
                                <div class="col-xs-4"><input name="year" type="text"></div><div class="col-xs-2">年</div>
                            </div>
                        </li>
                        <li class="noBorder">
                            <div class="col-xs-3 text-right n26 grey2" style="line-height:36px;">卡后3位数</div>
                            <div class="col-xs-9 n26"><input name="cvv2" type="text" placeholder="卡后3位数"></div>
                        </li>
                    <?php endif; ?>
                    <li class="noBorder">
                        <div class="col-xs-3 text-right n26 grey2" style="line-height:36px;">验证码</div>
                        <div class="col-xs-9 n26 grey4">
                            <div class="col-xs-6" style="padding-right:5px">
                                <input type="text" name="verifyCode" maxlength="4" class="form-control">
                            </div>
                            <div class="col-xs-6">
                                <button type="submit" class="btn" style="width:100%;font-size:2.6rem;height:36px;line-height:0;text-align:center;padding:0" id="code">获取验证码</button>
                            </div>

                        </div>
                    </li>
                </ul>
            </div>
            <span id="remain" style="color: red;"></span>
            <button class="btn mt40" style="width:100%;" id="tbug">确定</button>
        </form>
    </div>                            
</div>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
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

    wx.ready(function() {
        wx.hideOptionMenu();
    });
</script>