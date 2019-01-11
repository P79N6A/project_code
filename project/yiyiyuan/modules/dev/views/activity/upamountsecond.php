<style>
body{
    background: #040b19;
}
html{ width: 100%; position: absolute;}
body{width:100%;  font-family: "Microsoft YaHei"; background: #02050e;}
/*提额活动*/
.actvete .bannerimg img{ width: 100%; display: block;}
.actvete .tebannerbg{display: block; margin-top: -2px; position: relative;}
.actvete .tebannerbg img{ position:relative;}
.actvete .tebannerbg .tiexqg{position: absolute;top:20px; width: 100%;}
.actvete .tebannerbg .tiexqg .rwu1{ display: block; width: 16%; margin:5px auto;}
.actvete .tebannerbg .tiexqg .rwuoned{ text-align: center; font-size: 1rem; color:#ffd374 ;}
.actvete .tebannerbg .tiexqg .yhquan ul { width: 100%; margin:20% 20% 0;}
.actvete .tebannerbg .tiexqg .yhquan ul li{ float: left; background: #6a5b3e; width: 20%;margin-right: 0.5%; height: 0.6rem;}
.actvete .tebannerbg .tiexqg .yhquan ul li.light{background: #ffd374;}
.actvete .tebannerbg .tiexqg .yhquan ol{width: 100%; position: static;}
.actvete .tebannerbg .tiexqg .yhquan ol li{ position: absolute;margin: 0 24%; }
.actvete .tebannerbg .tiexqg .yhquan ol li.yh168{top:2%;left: 17%;width: 20%; }
.actvete .tebannerbg .tiexqg .yhquan.newstyle ul li{width: 11%;}
.actvete .tebannerbg .tiexqg .yhquan.newstyle ul{margin: 35% 0 0 10%;}
.actvete .tebannerbg .tiexqg .yhquan ol li.yh1000{top:56%;width: 30%;left: 11%;}
.actvete .tebannerbg .tiexqg .yhquan .txtcy{ clear: both; margin: 0 5%; text-align: center; color: #ffd374; padding: 10px 0 20px; border-bottom: 1px #ffd374 dashed;}
.actvete .tebannerbg .tiexqg .yhquan .txtcy.noneha{border-bottom: 0;}

.actvete .buttxt{background: url(/images/activity/tebottombg2.jpg) no-repeat bottom center; background-size: 100%;}
.actvete .buttxt .falimy{ font-size: 1.1rem; color: #594d37; padding: 10px 0 20px; text-align: center; }
.actvete .buttxt .yyqshy{ text-align: center; padding-bottom: 5px; font-size: 1.1rem;color:#ffd374 ;}
.actvete  .dwlhbao{ background: #ffd374; width: 90%; margin: 0 5% 40px; border-radius: 5px; color: #040b1a; font-size: 1.35rem; padding: 10px 0; font-weight: bold; }
.actvete .certifn .bortop{ border-top:1px #ffd374 solid;}
.actvete .certifn h3 { color: #ffd374; font-size: 1.4rem; font-weight:bold;text-align: left;width: 30%;background: #02050e;margin: -16px 0 5px;}
.actvete .certifn.hdguize p{ color: #ffd374; font-size: 1rem; line-height: 30px; padding-top: 0;}
.actvete .certifn.hdguize{ width: 90%; margin: 0 auto;}

#overDivs{background: #000;width: 100%;height: 100%;left: 0;top: 0;filter: alpha(opacity=7);opacity: 0.7;z-index: 11;position: fixed!important; position: absolute;_top: expression(eval(document.compatMode &&document.compatMode=='CSS1Compat') ?documentElement.scrollTop + (document.documentElement.clientHeight-this.offsetHeight)/2 :/*IE6*/document.body.scrollTop + (document.body.clientHeight - this.clientHeight)/2);}
.tanchuceng{ position: fixed;top: 0%;left: 0%;border-radius: 5px;z-index: 100;}
.tanchuceng  img{ width: 85%;margin:20px 5% 0 10%;}
.tancymia{ position: fixed;top: 0%;left: 0%;border-radius: 5px;z-index: 100;}
.tancymia img{ width: 90%; margin: 20% 5% 0;}
.tancymia .tcerror{width: 10%; height: 3rem;position: absolute;top:15%;right: 8%;}
.tancymia .tancone{ width: 80%;height:4rem;position: absolute; bottom: 7%;left: 10%; }
</style>
<div class="actvete">
    <div class="bannerimg" >
        <img src="/images/activity/tebanner2.jpg">
    </div>
    <div class="tebannerbg">
    	<img src="/images/activity/tebannerbg2.jpg">	
    	<div class="tiexqg">
    		<div class="yhquan">
    			<ol>
    				<li class="yh168"><img src="/images/activity/yhj168.png"></li>
    			</ol>
    			<ul>
    				<li <?php if($invite_num >= 1){ ?>class="light"<?php } ?>>&nbsp;</li>
    				<li <?php if($invite_num >= 2){ ?>class="light"<?php } ?>>&nbsp;</li>
    				<li <?php if($invite_num >= 3){ ?>class="light"<?php } ?>>&nbsp;</li>
    			</ul>
    			<div class="txtcy">
                            <?php if($invite_num < 3): ?>
                                再邀请<span><?=(3-$invite_num)?></span>个好友可获得168元优惠券
                            <?php else: ?>
                                活动结束后将为您发放168元优惠券
                            <?php endif; ?>
                        </div>
    		</div>
    		
    		<div class="yhquan newstyle">
    			<ol>
    				<li class="yh1000"><img src="/images/activity/yhj1000.png"></li>
    			</ol>
    			<ul>
    				<li <?php if($friend_loan_num >= 1){ ?>class="light"<?php } ?>>&nbsp;</li>
    				<li <?php if($friend_loan_num >= 2){ ?>class="light"<?php } ?>>&nbsp;</li>
    				<li <?php if($friend_loan_num >= 3){ ?>class="light"<?php } ?>>&nbsp;</li>
    				<li <?php if($friend_loan_num >= 4){ ?>class="light"<?php } ?>>&nbsp;</li>
    				<li <?php if($friend_loan_num >= 5){ ?>class="light"<?php } ?>>&nbsp;</li>
    				<li <?php if($friend_loan_num >= 6){ ?>class="light"<?php } ?>>&nbsp;</li>
    				<li <?php if($friend_loan_num >= 7){ ?>class="light"<?php } ?>>&nbsp;</li>
    			</ul>
    			<div class="txtcy noneha">
                            <?php if($friend_loan_num < 7): ?>
                                再邀请<span><?=(7-$friend_loan_num)?></span>个好友完成下款可获得1000元提额
                            <?php else: ?>
                                活动结束后将为您提升1000元额度
                            <?php endif; ?>
                        </div>
    		</div>
    	</div>
    </div>
    
    <div class="buttxt">
        <button class="dwlhbao">我要提额</button>
        <div class="certifn hdguize">
           <div class="bortop"></div>
            <h3>活动规则</h3>
			<p>1. 活动时间4月13日-4月20日（含）</p>
			<p>2. 点击“我要提额”按钮开始参与活动，每成功邀请一名用户注册，步骤一便会成功计数1次，计数满3，活动结束后用户就会获得系统自动发送的168元免息券；</p>
			<p>3. 活动第一步所获得的优惠券只有在优惠券有效期内使用才会获得相应金额的优惠；</p>
			<p>4. 用户活动期间邀请的好友每成功提现一人，步骤二便会成功计数1次，计数满7，活动结束后系统自动为用户永久提额1000元；</p>
			<p>5. 一、二两步中人数计数只在活动有效期内才视为有效；</p>
			<p>6. 本次活动中获得优惠券的用户，若在当次还款时逾期，先花一亿元可单方面取消其优惠金额。</p>
        </div>
        <div class="falimy">最终解释权归先花一亿元所有</div>
    </div>
    
</div>

<div id="overDivs" hidden></div>
<div class="tanchuceng" hidden>
	<img src="/images/activity/sharess2.png">
</div>
<!--首页弹框-->
<div class="tancymia" hidden>
	<img src="/images/activity/tantan2.png">
	<a class="tcerror"></a>
	<div></div>
	<a class="tancone"></a>
</div>

<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
    $(function(){ 
	     $('.actvete .dwlhbao').click(function(){
	        $('#overDivs').show();
	        $('.tanchuceng').show();
	     });
             $('.tanchuceng').click(function(){
	        $('#overDivs').hide();
	        $('.tanchuceng').hide();
	     });
             $('#overDivs').click(function(){
	        $('#overDivs').hide();
	        $('.tanchuceng').hide();
	     });
    })
</script>
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
						            title: '快来领取1000元提额券吧！',
						            desc: '提额盛典第二季强势来袭，1000元提额券速度来领',
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
						            title: '快来领取１０００元提额券吧！',
						            desc: '提额盛典第二季强势来袭，１０００元提额券速度来领',
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

