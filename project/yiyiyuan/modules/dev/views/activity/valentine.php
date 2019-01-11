<script>
$(function(){
    var user_id = <?php echo $user->user_id; ?>;
    $(".woyaojlk").click(function(){
        $.post("/dev/activity/valreceive", {user_id: user_id}, function(info){
            var data = eval('(' + info + ')');
            console.log(data);
            if (data.code == 1) {
                $('.valentine_Hmask').show();
                $('.overdue_loan').show();
            }
            if (data.code == 2) {
                $('.valentine_Hmask').show();
                $('.already_receive').show();
            }
            if (data.code == 3) {
                $('.valentine_Hmask').show();
                $('.later_nocoupon').show();
            }
            if (data.code == 4) {
                $('.valentine_Hmask').show();
                $('.befor_nocoupon').show();
            }
            if (data.code == 5) {
                $('.valentine_Hmask').show();
                $('.later_coupon').show();
            }
            if (data.code == 6) {
                $('.valentine_Hmask').show();
                $('.befor_coupon').show();
            }
            return false;
        })
    })
    //点击分享
    $(".share").click(function(){
        var thi = $(this);
        thi.parent().hide();
        $(".fenxang").show();
        $(".valentine_Hmask").show();
    })
    //点击蒙层关闭所有弹出
    $(".valentine_Hmask").click(function(){
        if($('.fenxang').is(":visible")){
            $('.valentine_Hmask').hide();
            $('.fenxang').hide();
        }else{
            $('.valentine_Hmask').hide();
        }
    })
    //点击分享图片，关闭所有弹出
    $(".fenxang").click(function(){
        $('.fenxang').hide();
        $('.valentine_Hmask').hide();
    })
    //点击关闭按钮
    $(".tcerror").click(function(){
        var thi_tc = $(this);
        thi_tc.parent().hide();
        $('.valentine_Hmask').hide();
    })
})
</script>
<div class="l-container">
        <div ><img src="/images/activity/yiyi1.jpg"></div>
        <div ><img src="/images/activity/yiyi2.jpg"></div>
        <div class="woyaojlk">
                <img src="/images/activity/yiyi3.jpg">
                <a></a>
        </div>
        <div class="yemxg">
                <img src="/images/activity/yiyi4.jpg">
                <img src="/images/activity/yiyi5.jpg">
                <img src="/images/activity/yiyi6.jpg">
                <div class="yemneirong">
                        <p><span>活动规则</span> </p>
                        <p><span>1.</span> 活动有效期 2017年2月10日至2017年2月21日；</p>
                        <p><span>2.</span> 活动期间，系统每天派发1314个免息名额，点击活动页中按钮即可自助领取免息名额，派完即止，先到先得；</p>
                        <p><span>3.</span> 系统将于2月14日从所有发起借款的用户中抽取1314名幸运用户直接获得520元现金红包； 另外系统还将于2月21日从活动期间所有发起借款的用户中抽取600名幸运用户同样获得520元现金红包（根据活动期间每天的借款记录，按照50名/天的比例进行抽取 共计600名）
                               <br>第一轮抽奖：2月14日13:14，抽取1314名
                                <br>第二轮抽奖：2月21日23:00，抽取600名</p>
                        <p><span>4.</span> 2月14日系统选中的1314位幸运小伙伴会在次日中午12：00前收到中奖信息（转发无效），2月21日系统选中的600位幸运小伙伴会在次日中午12:00前收到中奖信息（转发无效）2月21日活动结束后3个工作日内工作人员会主动联系中奖用户核实身份无误后，直接放送520元现金红包；</p>
                        <p><span>5.</span> 用户发起借款申请唯有在免息券有效期内可享受免息还款福利，非有效时间发起申请的用户无法享受；</p>
                        <p><span>6.</span>  本次活动中获得免息的用户，若在当次还款时逾期，先花一亿元可单方面取消其免息资格；</p>
                </div>
        </div>
</div>
	
<div class="valentine_Hmask" style="display: none;"></div>
<div class="valentine_tanchuceng fenxang" style="display: none;">
	<img src="/images/activity/sharess.gif">
</div> 

<div class="valentine_tancymia overdue_loan" style="display: none;">
	<img src="/images/activity/bgttt.png">
	<a class="tcerror"></a>
	<div class="textxmes">你的借款处于逾期中<br/>还清借款才能领福利</div>
	<a class="tancone" href="/dev/loan">马上还款</a>
</div>
<div class="valentine_tancymia already_receive" style="display: none;">
	<img src="/images/activity/bgttt.png">
	<a class="tcerror"></a>
	<div class="textxmes">你已经领过福利了，<br/>快去兑换吧！</div>
	<a class="tancone" href="/dev/loan">马上借款</a>
</div>
<div class="valentine_tancymia later_coupon" style="display: none;">
	<img src="/images/activity/bgttt.png">
	<a class="tcerror"></a>
	<div class="textxmes">恭喜你！！<br/>获得免息券一张，<br/>快去借款吧～</div>
	<a class="tancone" href="/dev/loan">马上借款</a>
</div>
<div class="valentine_tancymia later_nocoupon" style="display: none;">
	<img src="/images/activity/bgttt.png">
	<a class="tcerror"></a>
	<div class="textxmes">今天的免息券已经<br/>领完啦！<br/>明天早点来吧～</div>
	<a class="tancone" href="/dev/loan">我要借款</a>
</div>
<div class="valentine_tancymia zuihyge befor_nocoupon" style="display: none;">
	<img src="/images/activity/bgttt.png">
	<a class="tcerror"></a>
	<div class="textxmes">今天的免息券已经<br/>领完啦！<br/>明天早点来吧～</div>
	<div class="tbtsi">特别提示！<br/> 2017年2月14日发起借款既有机会获得<br/> 520元现金红包！</div>
	<a class="tancone share">喊好友领红包</a>
</div>
<div class="valentine_tancymia zuihyge befor_coupon" style="display: none;">
	<img src="/images/activity/bgttt.png">
	<a class="tcerror"></a>
	<div class="textxmes">恭喜你～～<br/>获得免息券一张！</div>
	<div class="tbtsi">特别提示！<br/> 2017年2月14日发起借款既有机会获得<br/> 520元现金红包！</div>
	<a class="tancone share">喊好友领红包</a>
</div>
<!--情人节红包免息大作战，立即参加--首页弹框-->
<!--<div class="valentine_tancymia dangeym">
	<img src="/images/activity/rukou.png">
	<a class="tancone"></a>
</div>-->
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
						            title: '浪漫情人节、红包免息大作战',
						            desc: '爱要有“礼”才完美',
						            link: '<?php echo $shareUrl; ?>',
						            imgUrl: '<?php echo!empty($user->userwx) && !empty($user->userwx->head) ? $user->userwx->head : '/images/dev/face.png'; ?>',
						            trigger: function (res) {
						                // 不要尝试在trigger中使用ajax异步请求修改本次分享的内容，因为客户端分享操作是一个同步操作，这时候使用ajax的回包会还没有返回
						            },
						            success: function (res) {
                                                                $('.valentine_Hmask').hide();
                                                                $('.fenxang').hide();
						            },
						            cancel: function (res) {
						            },
						            fail: function (res) {
						            }
						        });

						        // 2.2 监听“分享到朋友圈”按钮点击、自定义分享内容及分享结果接口
						        wx.onMenuShareTimeline({
						            title: '浪漫情人节、红包免息大作战',
						            desc: '爱要有“礼”才完美',
						            link: '<?php echo $shareUrl; ?>',
						            imgUrl: '<?php echo!empty($user->userwx) && !empty($user->userwx->head) ? $user->userwx->head : '/images/dev/face.png'; ?>',
						            trigger: function (res) {
						                // 不要尝试在trigger中使用ajax异步请求修改本次分享的内容，因为客户端分享操作是一个同步操作，这时候使用ajax的回包会还没有返回
						            },
						            success: function (res) {
                                                                $('.valentine_Hmask').hide();
                                                                $('.fenxang').hide();
						            },
						            cancel: function (res) {
						            },
						            fail: function (res) {
						                alert(JSON.stringify(res));
						            }
						        });
						    });
</script>

