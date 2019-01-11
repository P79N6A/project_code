<div class="haimoney">
    <div class="addawh"><img src="/images/addawh.png">什么是续期还款？</div>
    <p class="haititle">应支付金额</p>
    <p class="haitxt">0 <em>元</em></p>
    <p class="hailast">最后还款日 <em><?php echo $end_date; ?></em></p>
</div>
<div class="button"><button id="is_submit">确认续期</button></div>

<div class="Hmask helps" style="display: none;" ></div>
<div class="xuqhk helps" style="display: none;" >
    <h3>什么是续期还款？</h3>
    <p>续期还款是用户通过支付一定续期费用延长最后还款日的操作。</p>
    <h3>续期还款是否收费？</h3>
    <p>续期还款会收取一定费用，具体以页面展示为准。</p>
    <h3>续期还款可将还款日延长多久？</h3>
    <p>续期还款最长可延长一个借款周期。</p>
    <button>知道了</button>
</div>
<div id="overDiv " class="Hmask open1" style="<?php echo $isCungan['isOpen'] == 1 ? 'display:none;' : ''; ?>"></div>
<div class="xuqhk open1" style="padding: 0 20px;box-sizing: border-box; <?php echo $isCungan['isOpen'] == 1 ? 'display: none;' : ''; ?>">
    <p class="error"></p>
    <h3 style="padding: 10px 0; border-bottom: 0; font-size: 16px;text-align: center;">开通存管账户</h3>
    <p style="font-size:14px;">本平台现已接入银行存管体系，为保障您的资金安全，请马上开通存管账户</p>
    <button class="btnsure" id="opens_new" style="width: 80%; padding: 7px 0; margin: 20px 10% 10px; font-size:16px; font-weight: normal;">马上开户</button>
</div>
<div id="overDiv" class="Hmask open2"  style="<?php echo ($isCungan['isPass'] == 1 || $isCungan['isOpen'] == 0) ? 'display: none;' : ''; ?>"></div>
<div class="xuqhk open2 " style="padding: 0 20px;box-sizing: border-box;<?php echo ($isCungan['isPass'] == 1 || $isCungan['isOpen'] == 0) ? 'display: none;' : ''; ?>">
    <p class="error"></p>
    <h3 style="padding: 10px 0; border-bottom: 0; font-size: 16px;text-align: center;">设置交易密码</h3>
    <p style="font-size:14px;">本平台现已接入银行存管体系，为保证顺利展期，请设置存管密码。</p>
    <button class="btnsure" id="pwd_new" style="width: 80%; padding: 7px 0; margin: 20px 10% 10px; font-size:16px; font-weight: normal;">马上设置</button>
</div>

<div id="overDiv" class="Hmask open3"  style="<?php echo ($isCungan['isAuth'] == 1 || $isCungan['isPass'] == 0) ? 'display: none;' : ''; ?>"></div>
<div class="xuqhk open3 " style="padding: 0 20px;box-sizing: border-box;<?php echo ($isCungan['isAuth'] == 1 || $isCungan['isPass'] == 0) ? 'display: none;' : ''; ?>">
    <p class="error"></p>
    <h3 style="padding: 10px 0; border-bottom: 0; font-size: 16px;text-align: center;">设置存管授权</h3>
    <p style="font-size:14px;">本平台现已接入银行存管体系，为保证顺利展期，请设置存管还款授权。</p>
    <button class="btnsure" id="auth_new3" style="width: 80%; padding: 7px 0; margin: 20px 10% 10px; font-size:16px; font-weight: normal;">马上设置</button>
</div>

<div id="overDiv " class="Hmask erors_msg" hidden></div>
<div class="xuqhk erors_msg" hidden style="padding: 0 20px;box-sizing: border-box">
    <p class="error"></p>
    <h3 style="padding: 10px 0; border-bottom: 0; font-size: 16px;text-align: center;">展期结果</h3>
    <p style="font-size:14px;" id="msg_content">展期数据存在，请等待结果</p>
    <button class="btnsure" style="width: 80%; padding: 7px 0; margin: 20px 10% 10px; font-size:16px; font-weight: normal;">知道了</button>
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
    wx.ready(function () {
        wx.hideOptionMenu();
    });
    $(function () {
        var loan_id = <?php echo $loan['loan_id']; ?>;
        $('#is_submit').click(function () {
//            $("#is_submit").attr('disabled', true);
            $.post("/renew/renewal/repay", {loan_id: loan_id}, function (result) {
                var data = eval("(" + result + ")");
                if (data.code == '0000') {
                    window.location.href = data.url;
                } else if (data.code == '10001') {
                    $('.open' + data.msg).show();
                } else if (data.code == '5000') {
                    $('.msg_content').html('展期数据存在，请等待结果');
                    $('.erors_msg').show();
                } else {
                    $('.msg_content').html(data.msg);
                    $('.erors_msg').show();
                    $("#is_submit").attr('disabled', false);
                    return false;
                }
            });
        });
        //什么是续期还款
        $('.haimoney .addawh').click(function () {
            $('.helps').show();
        });
        $(".xuqhk button").click(function () {
            $('.Hmask').hide();
            $('.xuqhk').hide();
        });
        var csrf = '<?php echo $csrf; ?>';
        var user_id = <?php echo $loan->user_id; ?>;
        //马上开户-新
        $("#opens_new").click(function () {
            $("#opens_new").attr('disabled', true);
            $.ajax({
                type: "post",
                url: "/renew/depositorynew/newopenwx",
                data: {user_id: user_id, _csrf: csrf},
                async: false,
                success: function (res) {
                    var datas = eval("(" + res + ")");
                    if (datas.res_code == '0000') {
                        window.location = datas.res_data;
                    } else {
                        alert('开户失败')
                        $("#opens_new").attr('disabled', false);
                    }
                },
                error: function (data) {
                    $("#opens_new").attr('disabled', false);
                }
            });
        })
        //马上设置密码-新
        $("#pwd_new").click(function () {
            $("#pwd_new").attr('disabled', true);
            $.ajax({
                type: "POST",
                url: "/renew/depositorynew/setpwd",
                data: {user_id: user_id, _csrf: csrf},
                success: function (data) {
                    data = eval('(' + data + ')');
                    if (data.res_code == '0000') {
                        location.href = data.res_data;
                    } else {
                        location.href = "/new/depositorynew?user_id=" + user_id;
                    }
                },
                error: function (data) {
                    $("#pwd_new").attr('disabled', false);
                }
            });
        })
        //马上授权1-新
        $("#auth_new3").click(function () {
            $("#auth_new3").attr('disabled', true);
            $.ajax({
                type: "POST",
                url: "/renew/depositorynew/authorize",
                data: {user_id: user_id, is_repay: 2, _csrf: csrf},
                success: function (data) {
                    data = eval('(' + data + ')');
                    if (data.res_code == '0000') {
                        location.href = data.res_data;
                    } else {
                        location.href = "/new/depositorynew?user_id=" + user_id;
                    }
                },
                error: function (data) {
                    $("#auth_new3").attr('disabled', false);
                }
            });
        })
    });
</script>