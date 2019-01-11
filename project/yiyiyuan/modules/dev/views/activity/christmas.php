<style>
    body{
        background: #cd2c24;
    
}
</style>
<script type="text/javascript">
    $(function(){
        var user_id = <?php echo $user->user_id; ?>;
        $(".chenmo .eggegg .egg").click(function(){
            $.post('/dev/activity/christmasprize', {user_id: user_id}, function (info) {
                var data = eval('(' + info + ')');
//                data.code = 1;
//                data.num = 0;
                console.log(data);
                if (data.code == 1) {
                    $('.Hmask').show();
                    $('#christmas_no_times').show();
                } else if (data.code == 3) {
                    $('.Hmask').show();
                    $('#christmas_go_loan').show();
                } else if (data.code == 4) {
                    $('.Hmask').show();
                    $('#christmas_go_repay').show();
                } else if (data.code == 0) {
                    switch (data.num) {
                        case 0:
                            $('.Hmask').show();
                            $('#christmas_nothing').show();
                            $("#christmas_prize_times").html(data.times);
                            break;
                        case 1:
                            $('.Hmask').show();
                            $('#christmas_coupon58').show();
                            $("#christmas_prize_times").html(data.times);
                            break;
                        case 2:
                            $('.Hmask').show();
                            $('#christmas_can_norepay').show();
                            $("#christmas_prize_times").html(data.times);
                            break;
                    }
                }
            });
        });
        //关闭按钮
        $('.christmas_close').click(function(){
            var thi = $(this);
            $('.Hmask').hide();
            thi.parent().hide();
        });
        //去邀请弹出蒙层
        $('.christmas_yq').click(function () {
            var thi = $(this);
            thi.parent().hide();
            $('.christmas_fenxang').show();
        });
        //分享时点击图片或蒙层关闭
        $('.Hmask').click(function () {
            if($('.christmas_fenxang').is(":visible")){
                $('.Hmask').hide();
                $('.christmas_fenxang').hide();
            };
        });
        $('.christmas_fenxang').click(function(){
            $('.Hmask').hide();
            $('.christmas_fenxang').hide();
        })
    });
</script>
<div class="shengdan12">
        <div><img src="/images/activity/bannertop.png"></div>
        <div class="chenmo">
                <p class="chenm1"><img src="/images/activity/chmo2.png"></p>
                <div class="txtxmes"><img src="/images/activity/dianwo.png"></div>
                <div class="eggegg">
                        <div class="egg egg1"><img src="/images/activity/eggegg.png"></div>
                        <div class="egg egg2"><img src="/images/activity/eggegg.png"></div>
                        <div class="egg egg3"><img src="/images/activity/eggegg.png"></div>
                </div>
                <p class="chenm2"><img src="/images/activity/chmo1.png"></p>

                <div class="zadjhu">当前剩余砸蛋机会：<em id="christmas_prize_times"><?php echo!empty($total) ? $total->total_times - $total->use_times : 0; ?></em>次</div>
                <a class="hdguze"  href="/dev/activity/christmasrule" class="hdguize"><img src="/images/activity/hdguze.png"></a>
        </div>
        <div class="bottomdbu"><img src="/images/activity/bottomb.png"></div>
</div>
<!--弹窗-->
<div class="Hmask" style="display: none;"></div>
<!-- 58元优惠券 -->
<div class="tanchuceng shareone" id="christmas_coupon58" style="display: none;">
    <img src="/images/activity/shareone.png">
    <a class="christmas_close"></a>
    <a class="christmas_contnr christmas_yq"></a>
</div>
<!-- 没有砸蛋机会去邀请 -->
<div class="tanchuceng shareone" id="christmas_no_times" style="display: none;" data = "87878">
    <img src="/images/activity/sharefour.png">
    <a class="christmas_close"></a>
    <a class="christmas_contnr" href="/dev/activity/christmasinvite"></a>
</div>
<!-- 去借款 -->
<div class="tanchuceng shareone" id="christmas_go_loan" style="display: none;">
    <img src="/images/activity/sharesix.png">
    <a class="christmas_close"></a>
    <a class="christmas_contnr" href="/dev/loan"></a>
</div>
<!-- 去还款 -->
<div class="tanchuceng shareone" id="christmas_go_repay" style="display: none;">
    <img src="/images/activity/sharetwo.png">
    <a class="christmas_close"></a>
    <a class="christmas_contnr" href="/dev/loan"></a>
</div>
<!-- 没中奖 -->
<div class="tanchuceng shareone" id="christmas_nothing" style="display: none;">
    <img src="/images/activity/sharethree.png">
    <a class="christmas_close"></a>
    <a class="christmas_contnr christmas_yq"></a>
</div>
<!-- 已领取 -->
<!--<div class="tanchuceng" id="christmas_saveaccount" style="display: none;">
    <img src="/images/activity/tan7.png">
    <a class="close"></a>
    <a class="contnr a_close"></a>
</div>-->
<!-- 免还款特权 -->
<div class="tanchuceng shareone" id="christmas_can_norepay" style="display: none;">
    <img src="/images/activity/sharefive.png">
    <a class="christmas_close"></a>
    <a class="christmas_contnr christmas_yq" id="christmas_norepay_contact"></a>
</div>
<!-- 邀请好友弹出蒙层 -->
<div class="tanchuceng christmas_fenxang" style="display: none;">
    <img src="/images/activity/sharefx.png">
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
                        'hideOptionMenu',
						'onMenuShareAppMessage',
						'showOptionMenu'
                    ]
                });

                wx.ready(function () {
						        wx.showOptionMenu();
						        // 2. 分享接口
						        // 2.1 监听“分享给朋友”，按钮点击、自定义分享内容及分享结果接口
						        wx.onMenuShareAppMessage({
						            title: '双蛋钜惠 借钱不用还',
						            desc: '好友给你一次借钱不用还的机会，快来借款吧！',
						            link: '<?php echo $shareUrl; ?>',
						            imgUrl: '<?php echo!empty($user->userwx) && !empty($user->userwx->head) ? $user->userwx->head : '/images/dev/face.png'; ?>',
						            trigger: function (res) {
						                // 不要尝试在trigger中使用ajax异步请求修改本次分享的内容，因为客户端分享操作是一个同步操作，这时候使用ajax的回包会还没有返回
						            },
						            success: function (res) {
						                $('.Hmask').hide();
						                $('.christmas_fenxang').hide();
						            },
						            cancel: function (res) {
						            },
						            fail: function (res) {
						            }
						        });

						        // 2.2 监听“分享到朋友圈”按钮点击、自定义分享内容及分享结果接口
						        wx.onMenuShareTimeline({
						            title: '双蛋钜惠 借钱不用还',
						            desc: '好友给你一次借钱不用还的机会，快来借款吧！',
						            link: '<?php echo $shareUrl; ?>',
						            imgUrl: '<?php echo!empty($user->userwx) && !empty($user->userwx->head) ? $user->userwx->head : '/images/dev/face.png'; ?>',
						            trigger: function (res) {
						                // 不要尝试在trigger中使用ajax异步请求修改本次分享的内容，因为客户端分享操作是一个同步操作，这时候使用ajax的回包会还没有返回
						            },
						            success: function (res) {
						                $('.Hmask').hide();
						                $('.christmas_fenxang').hide();
						            },
						            cancel: function (res) {
						            },
						            fail: function (res) {
						                alert(JSON.stringify(res));
						            }
						        });
						    });
</script>
