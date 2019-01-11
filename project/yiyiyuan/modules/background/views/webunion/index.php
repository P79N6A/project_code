<div class="wrap">
        <a href="/background/webunion/pinfor?id=<?php echo $user_id;?>">
		<div class="index_bg" style="position: relative;">
			<div class="disitem bg_with" >
				<img src="<?php echo $userwx->head;?>">
				<div > 
					<p><?php if (date('H')<12): ?>上午好，<?php else: ?>下午好，<?php endif; ?><em><?php echo $userwx->nickname;?></em></p>
					<span>马上开启你的赚钱之旅吧！</span>
				</div>
			</div>
			<a style="width: 100%;display: block;" href='/background/webunion/information'><img style="width:11%;position: absolute; top: 20px; right:20px;" src="/images/mail.png"></a>
		</div>
		</a>
		<section>
			<div class="left">
			    <a href='/background/webunion/torrow'>
				<div><em><?php echo $shouyitotal;?></em><span>RMB</span></div>
				<div>昨日收益</div>
				<div class="index_img">
					<img src="/images/index_img.png">
				</div>
				</a>
			</div>
			<div class="line"></div>
			<div class="right">
			<a href='/background/webunion/mainyou'>
				<div><em><?php echo $haoyou;?></em><span>人</span></div>
				<div>我的好友</div>
				<div class="index_img" >
					<img src="/images/index_img.png">
				</div>
				</a>
			</div>
		</section>

		<div class="disitem dottengg" id='txtMarquee-left'>
			<img src="/images/index_laba.png">
			<span>小广播：</span>
			<div class='bd' style="border:0; color:#000;">
				<ul>
				    <?php if (!empty($bobao)): ?> 
					<?php foreach ($bobao as $key => $v): ?>
					<li>恭喜<?php echo substr_replace($v->user->realname,'**',3,6);?>(<?php echo substr_replace($v->user->mobile,'*',3,4);?>)获得<?php echo number_format($v->amount,2, ".", "");?>元收益</li>
					<?php endforeach; ?>
					<?php endif; ?> 
				</ul>
			</div>
		</div>
		<script type="text/javascript">
		jQuery("#txtMarquee-left").slide({mainCell:".bd ul",autoPlay:true,effect:"leftMarquee",vis:2,interTime:50});
		</script>

		<div class="options">
			<a href="/background/profit">
				<div class="disitem bottomline">
					<img class="firstImg" src="/images/zh_1.png"/>
					<span>我的钱包</span>
					<img class="righGo" src="/images/index_right.png">
				</div>
			</a>
			<a href="/background/webunion/spread">
				<div class="disitem bottomline">
					<img class="firstImg" src="/images/zh_2.png"/>
					<span>我要推广</span>
					<img class="righGo" src="/images/index_right.png">
				</div>
			</a>
			<a href="/background/webunion/mainyou">
				<div class="disitem bottomline">
					<img class="firstImg" src="/images/zh_3.png"/>
					<span>我的好友</span>
					<img class="righGo" src="/images/index_right.png">
				</div>
			</a>
			<a href="/background/webunion/commission">
				<div class="disitem bottomline">
					<img class="firstImg" src="/images/zh_4.png"/>
					<span>佣金制度</span>
					<img class="righGo" src="/images/index_right.png">
				</div>
			</a>
			<a href="/background/webunion/contact">
				<div class="disitem bottomline">
					<img class="firstImg" src="/images/zh_5.png"/>
					<span>联系我们</span>
					<img class="righGo" src="/images/index_right.png">
				</div>
			</a>
			<a href="/background/webunion/option">
				<div class="disitem bottomline">
					<img class="firstImg" src="/images/zh_6.png"/>
					<span>意见反馈</span>
					<img class="righGo" src="/images/index_right.png">
				</div>
			</a>
		</div>
	</div>
	<div style="position:fixed; bottom:5%; right:15%; width:30%;" >
		<a href='/dev/loan'><img style="width:100%;" src="/images/return.png"></a>
	</div>
	<?php if($now_time >= $start_time && $now_time <= $end_time):?>
	<?php if($scan_count == 0):?>
	<div id="overDiv"></div>
	<div class="jrmymoney">
		<div class="ztop1"><img src="/images/yaoguai_close.png"></div>
		<div class="ztop4"><img src="/images/chunjie.png"></div>
	</div>
	<?php endif;?>
	<?php else:?>
	<?php if($scan_count == 0):?>
	<div id="overDiv"></div>
	<div class="jrmymoney">
		<div class="ztop1"><img src="/images/yaoguai_close.png"></div>
		<div class="ztop4"><img src="/images/activetop1.png"></div>
	</div>
	<?php endif;?>
	<?php endif;?>
	<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
wx.config({
        debug: false,
        appId: "<?php echo $jsinfo['appid']; ?>",
        timestamp: "<?php echo $jsinfo['timestamp']; ?>",
        nonceStr: "<?php echo $jsinfo['nonceStr']; ?>",
        signature: "<?php echo $jsinfo['signature']; ?>",
        jsApiList: [
            'onMenuShareTimeline',
            'onMenuShareAppMessage',
            'showOptionMenu'
        ]
    });

    wx.ready(function () {
        wx.showOptionMenu();
        // 2. 分享接口
        // 2.1 监听“分享给朋友”，按钮点击、自定义分享内容及分享结果接口
        wx.onMenuShareAppMessage({
            title: '吸金妖怪来啦！别跑！',
            desc: '赚钱新技能来袭，一步跨入豪门的机会，快来参加。',
            link: '<?php echo $shareUrl; ?>',
            imgUrl: "<?php echo empty($loanuserinfo['head']) ? '/images/dev/face.png' : $loanuserinfo['head']; ?>",
            trigger: function (res) {
                // 不要尝试在trigger中使用ajax异步请求修改本次分享的内容，因为客户端分享操作是一个同步操作，这时候使用ajax的回包会还没有返回
            },
            success: function (res) {
                countsharecount();
            },
            cancel: function (res) {
            },
            fail: function (res) {
            }
        });

        // 2.2 监听“分享到朋友圈”按钮点击、自定义分享内容及分享结果接口
        wx.onMenuShareTimeline({
            title: '吸金妖怪来啦！别跑！',
            desc: '赚钱新技能来袭，一步跨入豪门的机会，快来参加。',
            link: '<?php echo $shareUrl; ?>',
            imgUrl: "<?php echo empty($loanuserinfo['head']) ? '/images/dev/face.png' : $loanuserinfo['head']; ?>",
            trigger: function (res) {
                // 不要尝试在trigger中使用ajax异步请求修改本次分享的内容，因为客户端分享操作是一个同步操作，这时候使用ajax的回包会还没有返回
            },
            success: function (res) {
                countsharecount();
            },
            cancel: function (res) {
            },
            fail: function (res) {
                alert(JSON.stringify(res));
            }
        });
    });

    $(".jrmymoney").click(function(){
		$("#overDiv").hide();
		$(".jrmymoney").hide();
    });
</script>

