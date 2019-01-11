<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, minimal-ui;">
    <meta name="format-detection" content="telephone=no">
    <title>感恩提速</title>
    <link rel="stylesheet" type="text/css" href="/newdev/css/fiveactivity/reset.css"/>
	<link rel="stylesheet" type="text/css" href="/newdev/css/fiveactivity/inv.css?i=201804261516"/>
</head>
<body>


<div class="yiyyn">
	<div class="active">
		<img src="/newdev/images/fiveactivity/active3.jpg">
		<a href="/new/fiveyearactivity/index?user_id=<?php echo $user_id?>&invite_qcode=<?php echo $fcode?>"></a>
	</div>
	<div class="active3_one">
		<img src="/newdev/images/fiveactivity/active3_one.jpg">
		<button class="ljfx"></button>
	</div>
	<div class="bgyse bgyse3">
		<h3>您当前可用加速包<em id="jsb"><?php echo $count ?></em>个！</h3>
		<!--<div class="yem1">
			<img src="images/active1_one.png">
			<p>888<em>元</em></p>
		</div>
		<input type="tel" placeholder="请输入注册时手机号码"/>-->
		<img class="active3_bshu" src="/newdev/images/fiveactivity/active3_bshu.jpg">
		<div class="therrb_left"><p id='jdt' style="width:0%;"></p>
		</div>
		
		<button class="ljsh"><img src="/newdev/images/fiveactivity/active3_button.png"></button>
		<p class="gzhu3">（请关注先花一亿元公众号）</p>
	</div>

	
	<div class="cotest">
   		<h3>活动细则 </h3>
		<p>1、用户首次登陆活动页面可获得1次加速机会。</p>
		<p>2、用户首次转发可获得1次加速机会。</p>
		<p>3、好友首次打开链接并登录可获得1次加速机会。</p>
		<p>4、好友首次转发可获得1次加速机会。</p>
		<p>5、加速状态只用于已发起借款的用户。</p>
		<p>6、本活动最终解释权归先花一亿元所有！</p>
   	</div>
   	
</div>
<div id="overDiv" style="display: none"></div>
<div class="fenxzshi" hidden>
	<img src="/newdev/images/fiveactivity/share.png">
</div>

<div class="tanceng" style="display: none;">
	<img src="/newdev/images/fiveactivity/tceng.png">
	<div class="niycja">
		<p id="t1"></p>
		<p id="t2"></p>
		<button class="tcbutton">
		<img src="/newdev/images/fiveactivity/tcbutton.png">
		<p id="butname">确定</p>
		</button>
	</div>
	
</div>

<a class="errorer" style="display: none"><img src="/newdev/images/fiveactivity/errorer.png"></a>
</div>
</body>
</html>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script src="/newdev/js/fiveactivity/jquery-1.10.1.min.js"></script>
<script type="text/javascript">

	var user_id = "<?php echo $user_id; ?>";
	var count = "<?php echo $count; ?>";
	var url = "<?php echo $url; ?>";
	var js_jk = "<?php echo $is_jk; ?>";
	var isapp = "<?php echo $isapp; ?>";
	var is_activity = "<?php echo $is_activity; ?>";
	var score = "<?php echo $score; ?>";
	window.onload = function(){
		if(is_activity=='end'){
			$(".tanceng").show();
			$(".errorer").show();
			$('#overDiv').show();
			$("#t1").text('抱歉，当前活动已结束');
			$("#t2").text('请参加其他更多精彩活动！');
		}else{
			$("#butname").text('确定');
			//统计代码
			$.get("/new/st/statisticssave", {type: 1222}, function (data) {
			});
			if(user_id==''){
				$(".tanceng").show();
				$(".errorer").show();
				$('#overDiv').show();
				$("#t1").text('您未登录，请登录后领取');
				$("#butname").text('立即登录');
				$('.tcbutton').click(function () {
					if(user_id==''){
						window.location = url;
					}
				});
			}else{
//				if(js_jk==1){
//
//					$(".tanceng").show();
//					$(".errorer").show();
//					$('#overDiv').show();
//					$("#t1").text('您好，您当前没有借款申请，请');
//					$("#t2").text('到APP申请借款后再来加速!');
//					$("#butname").text('去借款');
//					$(".tcbutton").show();
//					$('.tcbutton').click(function () {
//							get_loan();
//						    js_jk==2	;//不让它弹
//					});
//				}
			}
//            if(count==0){
//				jdt=0;
//			}else{
//				jdt=count+5;
//			}
			document.getElementById("jdt").style.width=score+'%';

		}

	}


	$('.ljsh').click(function () {//立即使用
		$.ajax({
			type: "get",
			url: "/new/activitynew/use_jihui",
			data: {user_id:user_id},
			success: function(data){
//				alert(data);
				var data = eval("(" + data + ")");
				$(".tanceng").show();
				$(".tcbutton").hide();
				$(".errorer").show();
				$(".fenxzshi").hide();
				$('#overDiv').show();
				$("#butname").text('确定');
				if(data.code=='0000'){
					var score=data.score;
					$(".tanceng").show();
					$(".tcbutton").show();
					$(".errorer").hide();
					$(".fenxzshi").hide();
					$("#butname").text('我知道了');
					$("#t1").text('恭喜您获得'+score+'倍加速特权！');
					document.getElementById("jdt").style.width=score+'%';
					document.getElementById("jsb").innerText=0;
					$(".tcbutton").click(function(){
						$(".fenxzshi").hide()
						$('.tanceng').hide();
						$('.errorer').hide();
						$('#overDiv').hide();
					});
				}else if(data.code=='0002'){
					if(isapp==1){
						$(".tanceng").show();
						$(".tcbutton").show();
						$(".fenxzshi").hide();
						$("#butname").text('立即分享');
						$("#t1").text('您好，您当前没有加速机会');
						$("#t2").text('赶快分享活动获得加速机会吧！');
						$(".tcbutton").click(function(){
							window.myObj.doShare('5');
						});

					}else {
						$(".tcbutton").show();
						$("#butname").text('立即分享');
						$('.tcbutton').click(function () {
							$(".tanceng").hide();
							$(".tcbutton").hide();
							$(".errorer").hide();
							$(".fenxzshi").show();
							$('#overDiv').show();
							$("#t1").text('点击右上角【更多】按钮分享');
							$("#t2").text('');
						});
						$("#t1").text('您好，您当前没有加速机会');
						$("#t2").text('赶快分享活动获得加速机会吧！');
					}

				}else if(data.code=='0003') {
					$(".tcbutton").show();
					$("#t1").text('您好请先登录后使用！');
					$("#t2").text('');
					$("#butname").text('立即登陆');
					$('.tcbutton').click(function () {
                      window.location=url;
					});
				}else if(data.code=='0004') {
					$(".tcbutton").show();
					$("#t1").text('您好，您当前没有借款申请，请');
					$("#t2").text('到APP申请借款后再来加速!');
					$("#butname").text('前往借款');
					$('.tcbutton').click(function () {
						get_loan();
					});
				}else if(data.code=='0005') {
					$(".tcbutton").show();
					$("#t1").text('您好，您当前没有借款申请，请');
					$("#t2").text('到APP申请借款后再来加速!');
					$('.tcbutton').click(function () {
						get_loan();
					});
				}else{
					$("#t1").text('系统繁忙稍后重试！');
				}

			}
		});
	});
//	$('.tcbutton').click(function () {
//		   if(user_id==''){
//			   window.location = url;
//		   }else if(js_jk==1 || jieguan==1){
//			   get_loan();
//		   }else if(nojsjh==1){
//			   //弹出微信和朋友圈
//			   var u = navigator.userAgent, app = navigator.appVersion;
//			   var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Linux') > -1;
//			   var isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/);
//			   if(isAndroid || isiOS){
//				   window.myObj.doShare('5');
//			   }
//			   nojsjh=0;
//		   }else if(nojsjh==2){
//			   //弹出微信和朋友圈
//			   var u = navigator.userAgent, app = navigator.appVersion;
//			   var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Linux') > -1;
//			   var isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/);
//			   if(isAndroid || isiOS){
//				   window.myObj.doShare('5');
//			   }
//			   nojsjh=0;
//		   }else{
//			   window.location = url;
//		   }
//
//	});

	$('.errorer').click(function () {//关闭
		$(".fenxzshi").hide()
		$('.tanceng').hide();
		$('.errorer').hide();
		$('#overDiv').hide();
	});

	//发起借款
	function get_loan(){
		var count = "<?php echo $count; ?>";
		var u = navigator.userAgent, app = navigator.appVersion;
		var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Linux') > -1;
		var isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/);
		var android = "com.business.main.MainActivity";
		var ios = "loanViewController";
		var position = "-1";
       if(isapp==1){
		   if (isiOS) {
//			   window.myObj.toPage(ios);
			   window.myObj.closeHtml();
		   } else if(isAndroid) {
			   window.myObj.toPage(android, position);
		   }
	   }else{
		   window.location.href = '/new/loan';
	   }


	}


	//分享
	var isApp = <?php
        if( strpos($_SERVER['HTTP_USER_AGENT'], 'YYY_ANDROID') || strpos($_SERVER['HTTP_USER_AGENT'], 'yyyIOS')){
            echo 1;  //app端
        }else {
            echo 2;  //h5端
        }
    ?>;

	$(function(){
		$('.ljfx').click(function(){
			$("#butname").text('确定');
			$.ajax({
				type: "get",
				url: "/new/activitynew/add_jihui",
				data: {user_id:user_id},
				success: function(data){
					var data = eval("(" + data + ")");
//					document.getElementById("jdt").style.width=jdt+'%';
//					document.getElementById("jsb").innerText=data.jsb;
				}
			});
//			var u = navigator.userAgent, app = navigator.appVersion;
//			var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Linux') > -1;
//			var isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/);
			if(isApp==1){
				//弹出微信和朋友圈
					window.myObj.doShare('5');

			}
			if(isApp==2){
				//引导用户右上角转发

				$(".tanceng").hide();
				$(".tcbutton").show();
				$(".errorer").hide();
				$(".fenxzshi").show();
				$('#overDiv').show();
//				$("#butname").text('立即分享');
				$("#t1").text('点击右上角【更多】按钮分享');
				$("#t2").text('');

			}

		});

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
				title: '庆周年，拿好礼',
				desc: '拼手气，抽最高888元，还有更多好礼等你来！',
				imgUrl: '<?php echo $imgUrl; ?>',
				link: '<?php echo $shareUrl; ?>',

				trigger: function (res) {
					// 不要尝试在trigger中使用ajax异步请求修改本次分享的内容，因为客户端分享操作是一个同步操作，这时候使用ajax的回包会还没有返回
				},
				success: function (res) {
					// countsharecount();
					$.get("/new/st/statisticssave", {type: 1223,user_id:user_id}, function (data) {
					});
				},
				cancel: function (res) {
				},
				fail: function (res) {
				}
			});

			// 2.2 监听“分享到朋友圈”按钮点击、自定义分享内容及分享结果接口
			wx.onMenuShareTimeline({
				title: '庆周年，拿好礼',
				desc: '拼手气，抽最高888元，还有更多好礼等你来！',
				imgUrl: '<?php echo $imgUrl; ?>',
				link: '<?php echo $shareUrl; ?>',

				trigger: function (res) {
					// 不要尝试在trigger中使用ajax异步请求修改本次分享的内容，因为客户端分享操作是一个同步操作，这时候使用ajax的回包会还没有返回
				},
				success: function (res) {
					// countsharecount();
					$.get("/new/st/statisticssave", {type: 1223,user_id:user_id}, function (data) {
					});
				},
				cancel: function (res) {
				},
				fail: function (res) {
					alert(JSON.stringify(res));
				}
			});
		})
	})
//	function doShare() {
//
//	}

</script>
