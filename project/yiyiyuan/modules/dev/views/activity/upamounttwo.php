<script>
$(function(){
    //点击我要提额
    $(".dwlhbao").click(function(){
        $("#overDivs").show();
        $(".tanchuceng").show();
    })
    //点击蒙层关闭所有弹出
    $(".tanchuceng").click(function(){
        $("#overDivs").hide();
        $(".tanchuceng").hide();
    })
    //点击分享图片，关闭所有弹出
    $("#overDivs").click(function(){
        $('#overDivs').hide();
        $('.tanchuceng').hide();
    })
})
</script>
<style>
body{
    background: #040b19;
}
</style>
<div class="actvete">
    <div class="bannerimg" >
        <img src="/images/activity/tebanner.jpg">
    </div>
    <div class="tebannerbg">
    	<img src="/images/activity/tebannerbg.jpg">
    	<div class="tiexqg">
    		<p class="dqyqrs">当前已邀请 <?=$activity_newyear['invite_num']?> 人！</p>
    		<p class="sanjiao"><img src="/images/activity/sanjiao.png"></p>
    		<div class="rwu1"><img src="/images/activity/rwu2.png"></div>
    		<p class="rwuoned">已成功下款 <?=$activity_newyear['friend_loan_num']?> 人</p>
    		<div class="yhquan newstyle">
    			<ol>
    				<li class="yh68"><img src="/images/activity/eds500.png"></li>
    			</ol>
    			<ul>
    				<li <?php if($activity_newyear['friend_loan_num'] >= 1){ ?>class="light"<?php } ?>>&nbsp;</li>
    				<li <?php if($activity_newyear['friend_loan_num'] >= 2){ ?>class="light"<?php } ?>>&nbsp;</li>
    				<li <?php if($activity_newyear['friend_loan_num'] >= 3){ ?>class="light"<?php } ?>>&nbsp;</li>
    				<li <?php if($activity_newyear['friend_loan_num'] >= 4){ ?>class="light"<?php } ?>>&nbsp;</li>
    				<li <?php if($activity_newyear['friend_loan_num'] >= 5){ ?>class="light"<?php } ?>>&nbsp;</li>
    				<li <?php if($activity_newyear['friend_loan_num'] >= 6){ ?>class="light"<?php } ?>>&nbsp;</li>
    				<li <?php if($activity_newyear['friend_loan_num'] >= 7){ ?>class="light"<?php } ?>>&nbsp;</li>
    			</ul>
    			
    		</div>
    	</div>
    </div>
    <div class="buttxt">
       	<p class="yyqshy">完成7个好友成功下款立即提升500额度</p>
        <button class="dwlhbao">我要提额</button>
        <div class="certifn hdguize">
           <div class="bortop"></div>
            <h3>活动规则</h3>
            <p>1. 活动时间2月23日-3月1日</p>
            <p>2. 点击“我要提额”按钮开始参与活动，每成功邀请一名用户注册，任务一便会成功计数1次，计数满3、6、9、12、15时，用户分别会获得系统自动发送的18元、28元、38元、58、68元优惠券；</p>
			<p>3. 活动任务一所获得的优惠券只有在优惠券有效期内使用才会获得相应金额的优惠；</p>
			<p>4. 活动期间邀请注册的好友满15人后，继而开启任务二活动，用户活动期间邀请的好友每成功提现一人，任务二便会成功计数1次，计数满7，系统自动为用户永久提额500元；统一在活动结束后进行提额</p>
			<p>5. 任务一、二中人数计数只在活动有效期内才视为有效；</p>
			<p>6. 本次活动中获得优惠券的用户，若在当次还款时逾期，先花一亿元可单方面取消其优惠金额。</p>
        </div>
        <div class="falimy">最终解释权归先花一亿元所属</div>
    </div>
    
</div>

<div id="overDivs" style="display: none;"></div>
<div class="tanchuceng" style="display: none;">
	<img src="/images/activity/sharess.png">
</div>
<!--弹框-->
<div class="tancymia" style="display: none;">
	<img src="/images/activity/tantan.png">
	<a class="tcerror"></a>
	<div></div>
	<a class="tancone"></a>
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
						            title: '68元红包任你拿',
						            desc: '刚在“先花一亿元”领取了2个68元红包，你也来试试你的手气吧',
						            link: '<?php echo $shareUrl; ?>',
						            imgUrl: '<?php echo!empty($user->userwx) && !empty($user->userwx->head) ? $user->userwx->head : '/images/dev/face.png'; ?>',
						            trigger: function (res) {
						                // 不要尝试在trigger中使用ajax异步请求修改本次分享的内容，因为客户端分享操作是一个同步操作，这时候使用ajax的回包会还没有返回
						            },
						            success: function (res) {
                                                                $('#overDivs').hide();
                                                                $('.tanchuceng').hide();
						            },
						            cancel: function (res) {
						            },
						            fail: function (res) {
						            }
						        });

						        // 2.2 监听“分享到朋友圈”按钮点击、自定义分享内容及分享结果接口
						        wx.onMenuShareTimeline({
						            title: '68元红包任你拿',
						            desc: '刚在“先花一亿元”领取了2个68元红包，你也来试试你的手气吧',
						            link: '<?php echo $shareUrl; ?>',
						            imgUrl: '<?php echo!empty($user->userwx) && !empty($user->userwx->head) ? $user->userwx->head : '/images/dev/face.png'; ?>',
						            trigger: function (res) {
						                // 不要尝试在trigger中使用ajax异步请求修改本次分享的内容，因为客户端分享操作是一个同步操作，这时候使用ajax的回包会还没有返回
						            },
						            success: function (res) {
                                                                $('#overDivs').hide();
                                                                $('.tanchuceng').hide();
						            },
						            cancel: function (res) {
						            },
						            fail: function (res) {
						                alert(JSON.stringify(res));
						            }
						        });
						    });
</script>