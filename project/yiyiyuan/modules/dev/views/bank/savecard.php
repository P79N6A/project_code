<div class="Hcontainer nP">
    <div class="main">
        <!--<form action="/dev/bank/bindcard" method="post" class="form-horizontal" id="order-pay-form">-->
        <!--<form action="/dev/bank/paylian" method="post" class="form-horizontal" id="order-pay-form">-->
        <div class="border1 jcbd">
            <ul>
                <li class="noBorder">
                    <div class="col-xs-3 text-right n26 grey2">姓名</div>
                    <div class="col-xs-8 n26 grey4"><?php echo $user['realname']; ?></div>
                    <input type="hidden" name="userid" id="user_id" value="<?php echo $user['user_id']; ?>">
                </li>
                <li class="noBorder">
                    <div class="col-xs-3 text-right n26 grey2">银行卡号</div>
                    <div class="col-xs-8 n26 grey4"><?php echo $post_data['card']; ?></div>
                    <input type="hidden" name="card" id="card" value="<?php echo str_replace(' ', '', $post_data['card']); ?>">
                    <input type="hidden" name="pay_type" id="pay_type" value="2">
                    <?php if (isset($post_data['f']) && !empty($post_data['f'])): ?>
                        <input type="hidden" name="f" value="<?php echo $post_data['f']; ?>">
                    <?php endif; ?>
                </li>
                <li class="noBorder">
                    <div class="col-xs-3 text-right n26 grey2">身份证号</div>
                    <div class="col-xs-8 n26 grey4"><?php echo substr($user['identity'], 0, 4) . '**********' . substr($user['identity'], 14, 4); ?></div>                    
                    <input type="hidden" name="identity" id="identity" value="<?php echo $user['identity']; ?>">
                    <input type="hidden" name="pay_key" value="">
                    <input type="hidden" name="biancard_id" value="">
                </li>

                <li class="noBorder">
                    <div class="col-xs-3 text-right n26 grey2" style="line-height:36px;">手机号码</div>
                    <div class="col-xs-9 n26"><input type="text" name="mobile" id="mobile" placeholder="银行卡留存号码" value="<?php echo $user['mobile']; ?>" maxlength="11"></div>
                </li>
                <li class="noBorder">
                    <div class="col-xs-3 text-right n26 grey2" style="line-height:36px;">验证码</div>
                    <div class="col-xs-9 n26 grey4">
                        <div class="col-xs-6" style="padding-right:5px">
                            <input type="text" name="verifyCode" class="form-control" maxlength="6" id="verifyCode">
                        </div>
                        <div class="col-xs-6">
                            <button type="button" class="btn" style="width:100%;font-size:2.6rem;height:36px;line-height:0;text-align:center;padding:0" id="get_bankcode">获取验证码</button>
                        </div>

                    </div>
                </li>

            </ul>
        </div>
        <input type="hidden" id="url" name="url" value="<?php echo $url; ?>">
        <input type="hidden" id="num" name="num" value="<?php echo $num; ?>">
        <input type="hidden" id="card_id" name="card_id" value="<?php echo $card_id; ?>">
        <input type="hidden" id="isyeepay" name="isyeepay" value="">
        <span id="remain" style="color: red;"></span>
        <button class="btn mt40" style="width:100%;" id="lzh">确定</button>

        <div id="overDiv" style="display:none;"></div>
        <div id="diolo_warp" class="diolo_warp" style="display:none;">
            <p class="title_cz">您正在发起绑定银行卡操作</p>
            <p class="pay_bank">将跳转至第三方"易宝支付"进行银行卡扣款验证</p>
            <p class="radious_img"></p>
            <!--<p class="go_on"><span>＊连连支付：</span>支持182家银行无卡支付.</p>-->
            <div class="true_flase">
                <button class="flase_qx" id='hlz'>取消</button>
                <button class="true_qr" id='tbug'>确定</button>
            </div>
        </div>    
        <!--</form>-->

    </div>                            
</div>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>


    $('#hlz').click(function () {
        $('#diolo_warp').hide();
        $('#overDiv').hide();
        return false;
    });

    $('#lzh').bind('click', function () {
        var userid = $("#user_id").val();
        var card = $("#card").val();
        var mobile = $("#mobile").val();
        var verifyCode = $("#verifyCode").val();
        var isyeepay = $("#isyeepay").val();
        var _mobileRex = /^(1(([3578][0-9])|(47)))\d{8}$/;
        var url = $("#url").val();
        var card_id = $("#card_id").val();
        var num = $("#num").val();
        if (mobile == '' || !(_mobileRex.test(mobile))) {
            $("#remain").html('请输入正确的手机号码');
            $("#mobile").focus();
            return false;
        }

        if (verifyCode == '' || verifyCode == undefined) {
            $("#remain").html('请输入验证码');
            $("#verifyCode").focus();
            return false;
        }

        $(this).attr('disabled', true);
        $.post("/dev/bank/bindcard", {userid: userid, card: card, mobile: mobile, code: verifyCode, isyeepay: isyeepay}, function (result) {
            var data = eval("(" + result + ")");
            if (data.ret == '0') {
                if (url != "") {
                    window.location = location.href = url + '?' + 'num=' + num + '&card_id=' + card_id;
                } else {
                    window.location = '/dev/bank'
                }
            } else if (data.ret == '1') {
                $("#remain").html('验证码错误');
                $("#lzh").attr('disabled', false);
                return false;
            } else if (data.ret == '2') {
                var msg = data.msg;
                $("#remain").html(msg);
                $("#lzh").attr('disabled', false);
                return false;
            } else {
                window.location = '/dev/bank/error'
            }
        });
    });
</script>
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

    wx.ready(function () {
        wx.hideOptionMenu();
    });
</script>