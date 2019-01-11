<div class="wrap wrapwidth">
		<section>
		<div class="left left_know">
			<img src="/images/people.png" style="width: 25%;margin:0 8%">
			<div class="image_conet">
				<div><em>50</em><span>人</span></div>
				<div>一级好友</div>
				<div class="index_img">
					<img src="/images/index_img.png">
				</div>
			</div>
		</div>
		<div class="line"></div>
		<div class="right right_know">
			<img src="/images/people2.png" style="width: 25%;margin:0 8%">
			<div class="image_conet">
				<div><em>35</em><span>人</span></div>
				<div>二级好友</div>
				<div class="index_img" >
					<img src="/images/index_img.png">
				</div>
			</div>
		</div>
	</section>
	<div class="friends">
		<section class="state state_del">
			<div class="add"></div>
			<div class="icon">头像</div>
			<div class="name">昵称</div>
			<div class="phone">手机号</div>
			<div class="state">状态</div>
		</section>
		<section class="list list_del" style="margin-top:0;">
			<img src="/images/add.png" alt="" class="add"/>
			<img src="/images/icon.png" alt="" class="icon" />
			<div class="name">张三</div>
			<div class="phone">13888809090</div>
			<div class="state">已认证</div>
		</section>
		<section class="list list_del gray">
			<img src="/images/add2.png" alt="" class="add"/>
			<img src="/images/icon.png" alt="" class="icon" />
			<div class="name">张三</div>
			<div class="phone">13888809090</div>
			<div class="state">已认证</div>
		</section>
		<div class="erjide">
			<section class="list list_del">
				<div class="add"></div>
				<img src="/images/icon.png" alt="" class="icon" />
				<div class="name">张三</div>
				<div class="phone">13888809090</div>
				<div class="state greengray">未认证</div>
			</section>
			<section class="list list_del">
				<div class="add"></div>
				<img src="/images/icon.png" alt="" class="icon" />
				<div class="name">张三</div>
				<div class="phone">13888809090</div>
				<div class="state">已认证</div>
			</section>
		</div>
		<section class="list list_del gray">
			<img src="/images/add2.png" alt="" class="add"/>
			<img src="/images/icon.png" alt="" class="icon" />
			<div class="name">张三</div>
			<div class="phone">13888809090</div>
			<div class="state">已认证</div>
		</section>
		<div class="erjide">
			<section class="list list_del">
				<img src="/images/add.png" alt="" class="add"/>
				<img src="/images/icon.png" alt="" class="icon" />
				<div class="name">张三</div>
				<div class="phone">13888809090</div>
				<div class="state">已认证</div>
			</section>
			<section class="list list_del">
				<img src="/images/add.png" alt="" class="add"/>
				<img src="/images/icon.png" alt="" class="icon" />
				<div class="name">张三</div>
				<div class="phone">13888809090</div>
				<div class="state">已认证</div>
			</section>
		</div>
	</div>
	<div class="nonefriend">
		<div class="disitem weirz">
			<img src="/images/weirz.png">
			<p>未实名认证的好友</p>
		</div>
		<div class="disitem nonefre">
			<img src="/images/icon.png">
			<p>1878787809</p>
		</div>
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
            'closeWindow',
            'hideOptionMenu'
        ]
    });

    wx.ready(function() {
        wx.hideOptionMenu();
    });
</script>