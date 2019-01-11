<?php
//0 已认证；1 未认证；2 借款中；3 已还款；4 已逾期
	$colors = array('black','greengray','red','black','red');
	$status = array('已认证','未认证','借款中','已还款','已逾期');
 ?>
<style>
	.red{color:#e4393c;}
	.black{color:#000;}
</style>
<div style="position:fixed;top:0;width:100%;z-index:10;">
	<div class="all_nav">
		<span class="nav_right"><img src="/images/webunion/nav_right.png"></span>
        <p>好友列表</p>
        <span class="navbanner"><img src="/images/webunion/nav_list.png"></span>
        <div class="nav_ulli" style="display:none;">       
            <ul>
                <a href="/background/default/index"><li>返回首页</li></a>
	            <a href="/background/default/spread"><li>我要推广</li></a>
	            <a href="/background/default/commission"><li>佣金介绍</li></a>
	            <a href="/background/default/question"><li>常见问题</li></a>
	            <a href="/background/default/contact"><li>联系我们</li></a>
	            <a href="/background/default/opinion"><li>意见反馈</li></a>
	            <a href="/dev/loan/index"><li class="return">先花一亿元》</li></a>
            </ul>
            </div>  
    </div> 
    <!-- 透明遮挡层 -->
	<div id="overDiv_n" style="display:none"></div>
</div>
<div class="wrap wrapwidth" >
	<section style="position: fixed; top:47px;border-top: 1px solid #f3f5f7;z-index:5;">
		<div class="left left_know">
			<img src="/images/webunion/people.png" style="width: 25%;margin:0 8%">
			<div class="image_conet">
				<div><em><?php echo $friend_one ?></em><span>人</span></div>
				<div>一级好友</div>
				<div class="index_img">
					<img src="/images/webunion/index_img.png">
				</div>
			</div>
		</div>
		<div class="line"></div>
		<div class="right right_know">
			<img src="/images/webunion/people2.png" style="width: 25%;margin:0 8%">
			<div class="image_conet">
				<div><em><?php echo $friend_two ?></em><span>人</span></div>
				<div>二级好友</div>
				<div class="index_img" >
					<img src="/images/webunion/index_img.png">
				</div>
			</div>
		</div>
		</section>
		<div class="haoyouinput" style="position: fixed;top:46px;">
			<form action="/background/friend/search" method="get">
				<input name="keyword" type="text" value="<?php echo $keyword ?>" placeholder="可搜索手机号/昵称">
				<button type="submit" style="width: 20%;margin-left: 10px;padding: 5px; border-radius: 5px;background: #fff;color: #e74747;">搜索</button>
			</form>
		</div>
	<div id="friend" class="friends"  style="margin-top:192px;">
		<section class="list haoyoulist" style="background:#fff; border-bottom:1px solid #e74747;position: fixed;">
			<div class="icon">头像</div>
			<div class="name">昵称</div>
			<div class="phone">手机号</div>
			<div class="state">
			<select id="type">
				<option value="0">全部</option>
				<option value="5">已认证</option>
				<option value="2">借款中</option>
				<option value="3">已还款</option>
				<option value="4">已逾期</option>
				<option value="1">未认证</option>
			</select>
		</div>
		</section>
		<select style="height:40px;"></select>
		<?php if ($friends): ?>
			<?php foreach ($friends as $key => $val): ?>
				<a href="/background/friend/detail?user_id=<?php echo $val->user_id ?>">
				<section class="list haoyoulist">
					<img src="<?php echo $val->openid ?>" class="icon" />
					<div class="name"><?php echo $val->realname?></div>
					<div class="phone"><?php echo substr_replace($val->mobile,'****',3,4);?></div>
					<div class=" <?php echo $colors[$val->status] ?>"><?php echo $status[$val->status]?></div>
				</section>
			</a>
			<?php endforeach ?>
		<?php endif ?>
	</div>
	<?php if ($more): ?>
		<button class="button" style="width: 50%;padding: 7px 0;margin-left: 25%;margin-top: 15px;border-radius: 20px;font-size: 1.25rem;color: #fff; background: #e74747;" page="<?php echo $more ?>" type="<?php echo $type ?>">加载更多</button>
	<?php endif ?>
		<button class="load" style="width: 50%;padding: 7px 0;margin-left: 25%;margin-top: 15px;border-radius: 20px;font-size: 1.25rem;color: #fff; background: #999;display:none" page="<?php echo $more ?>" type="<?php echo $type ?>">加载中</button>
</div>

<script>
    $('.nav_right').click(function(){
        window.location.href = '<?php echo $returnUrl ?>';
    })
</script>
<script>
$(function(){
	$('.left_know').click(function(){
		window.location.href="/background/friend/friendone";
	})
	$('.right_know').click(function(){
		window.location.href="/background/friend/friendtwo";
	})
	
	var now_type = <?php echo $type ?>;
	$('#type').val(now_type);

	$('.button').click(function(){
		var page = $(this).attr('page');
		var type = $(this).attr('type');
		$(this).hide();
		$('.load').show();
		$.get('/background/friend/index',{page:page,type:type},function(data){
			$('#friend').append(data.data);
			$('.load').hide();
			if (data.page != 0) {
				$('.button').attr('page',data.page);
				$('.button').show();
			};
		},'json');
	})

	$('#type').change(function(){
		var type = $('#type').val();
		window.location.href = "/background/friend/index?type="+type;
	})


	$('.navbanner').click(function(){
		$('.nav_ulli').show();
		$('#overDiv_n').show();
	})
	$('#overDiv_n').click(function(){
		$('.nav_ulli').hide();
		$('#overDiv_n').hide();
	})
})
</script>
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