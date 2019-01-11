<script type="text/javascript">
    $(function () {
        var user_id = <?php echo $user->user_id; ?>;
        $(".pic_index .active2 a.ljicj").click(function () {
            $.post('/dev/activity/drawprize', {user_id: user_id}, function (result) {
                var data = eval('(' + result + ')');
//                alert(data.code);
                if (data.code == 1) {
                    $('.Hmask').show();
                    $('#no_times').show();
                } else if (data.code == 3) {
                    $('.Hmask').show();
                    $('#go_loan').show();
                } else if (data.code == 4) {
                    $('.Hmask').show();
                    $('#go_repay').show();
                } else if (data.code == 0) {
                    switch (data.num) {
                        case 0:
                            $('.Hmask').show();
                            $('#nothing').show();
                            $("#prize_times").html(data.times);
                            break;
                        case 1:
                            $('.Hmask').show();
                            $('#coupon58').show();
                            $("#prize_times").html(data.times);
                            break;
                        case 2:
                            $('.Hmask').show();
                            $('#can_norepay').show();
                            $("#prize_times").html(data.times);
                            break;
                    }
                }
            });
        });
        //点击关闭按钮
        $('.tanchuceng .close').click(function () {
            $('.Hmask').hide();
            $('.tanchuceng').hide();
        });
        //点击关闭按钮
        $('.a_close').click(function () {
            $('.Hmask').hide();
            $('.tanchuceng').hide();
        });
        $('#lingqu').click(function () {
            $('#coupon58').hide();
            $('#saveaccount').show();
        });
        $('#norepay_contact').click(function () {
            $('#can_norepay').hide();
            $('#contact_kefu').show();
        });
    });
</script>
<div class="pic_index">
    <img src="/images/activity/active1.jpg">
    <div class="active2">
        <img src="/images/activity/active2.jpg">
        <span id="prize_times"><?php echo!empty($total) ? $total->total_times - $total->use_times : 0; ?></span>
    </div>
    <div class="active2">
        <img src="/images/activity/active3.jpg">
        <a class="ljicj"></a>
        <a class="yqighl" href="/dev/activity/invite"></a>
    </div>
    <div class="active2">
        <img src="/images/activity/active4.jpg">
        <a class="hdonggz" href="/dev/activity/activityrule"></a>
    </div>
</div>
<div class="Hmask" style="display: none;"></div>
<div class="tanchuceng" id="coupon58" style="display: none;">
    <img src="/images/activity/tan1.png">
    <a class="close"></a>
    <a class="contnr" id="lingqu"></a>
</div>
<div class="tanchuceng" id="no_times" style="display: none;">
    <img src="/images/activity/tan4.png">
    <a class="close"></a>
    <a class="contnr" href="/dev/activity/invite"></a>
</div>
<div class="tanchuceng" id="go_loan" style="display: none;">
    <img src="/images/activity/tan8.png">
    <a class="close"></a>
    <a class="contnr" href="/dev/loan"></a>
</div>
<div class="tanchuceng" id="go_repay" style="display: none;">
    <img src="/images/activity/tan9.png">
    <a class="close"></a>
    <a class="contnr" href="/dev/loan"></a>
</div>
<div class="tanchuceng" id="nothing" style="display: none;">
    <img src="/images/activity/tan5.png">
    <a class="close"></a>
    <a class="contnr a_close"></a>
</div>
<div class="tanchuceng" id="saveaccount" style="display: none;">
    <img src="/images/activity/tan7.png">
    <a class="close"></a>
    <a class="contnr a_close"></a>
</div>
<div class="tanchuceng" id="can_norepay" style="display: none;">
    <img src="/images/activity/tan2.png">
    <a class="close"></a>
    <a class="contnr" id="norepay_contact"></a>
</div>
<div class="tanchuceng" id="contact_kefu" style="display: none;">
    <img src="/images/activity/tan3.png">
    <a class="close"></a>
    <a class="contnr"></a>
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
</script>