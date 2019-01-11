<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, minimal-ui;">
    <meta name="format-detection" content="telephone=no">
    <title><?php echo $this->title;?></title>
    <link rel="stylesheet" type="text/css" href="/newdev/css/reset.css"/>
    <link rel="stylesheet" type="text/css" href="/newdev/css/lottery/turntable/inv.css?version=20180516"/>
    <link rel="stylesheet" type="text/css" href="/newdev/css/lottery/turntable/style.css"/>
</head>
<body>

<div class="xqcjact">
		<img src="<?php echo $activity->banner_url ? $img_url.$activity->banner_url : '/newdev/images/lottery/turntable/banner.png';?>">
		<div class="xqcj2" style="position: relative;">
			<img src="/newdev/images/lottery/turntable/bgwpan.png">
			<div class="rotate_wrap" >
				<img class="bg_img rotate_origin" id="i_bg" src="<?php echo $activity->prize_url ? $img_url.$activity->prize_url : '/newdev/images/lottery/turntable/img1.png';?>">
				<img class="cont_img rotate_origin1" id="i_btn" src="<?php echo $activity->button ? $img_url.$activity->button : '/newdev/images/lottery/turntable/img2.png';?>">
			</div>
		</div>
	<p class="sycjcs"><a>剩余抽奖次数：<i id="chance"><?php echo $lottery_number;?></i>次</a></p>
	<div class="yaoqbo">
		<p class="bobao"><img src="/newdev/images/lottery/turntable/bgone.png"></p>
		<div id="content" class="phooneym">
		    <div id="scroll">
		    	<?php foreach($broadcast_list as $broadcast){ ?>
		    		<div><?php echo $broadcast;?></div>
		    	<?php }?>
		    </div>
		</div>
	</div>
	<img src="<?php echo $activity->rule_url ? $img_url.$activity->rule_url : '/newdev/images/lottery/turntable/footer.png';?>">
</div>

<div class="Hmask hidden"></div>
<div class="dl_tcym hidden">
	<p class="qjfbz">很遗憾，未中奖</p>
	<p class="qwcky hidden">前往我的奖品进行查看</p>
	<button>确定</button>
</div>

<input id="csrf" type="hidden" name="_csrf" value="<?php echo $csrf; ?>"/>
<input id="activity_id" type="hidden" name="activity_id" value="<?php echo $activity->id; ?>">
<input id="activity_condition_rule" type="hidden" name="activity_condition_rule" value="<?php echo $activity->condition->rule_condition; ?>">


<script src="http://libs.baidu.com/jquery/1.9.0/jquery.min.js"></script>
<script type="text/javascript">
    $(function(){
        var h = $("#scroll div").height() ? $("#scroll div").height() : 25;
        $("#scroll div").clone().appendTo("#scroll").end().clone().appendTo("#scroll");
        var interval = setInterval(scrolling,1000);
        function scrolling(){
            var $scroll=$("#scroll");         
            if($scroll.height()<=25) return false;
            $scroll.animate({top:'-=25px'});
            if(parseInt($scroll.css("top"))+$scroll.height()==h){
            	$scroll.stop().css({'top':'0'});
                // clearInterval(interval);
            }
        }
    });
</script>
	
<script type="text/javascript">
	var flag = true;
	var during_time = 1;
	var csrf = $("#csrf").val();
    var activity_id = $('#activity_id').val();
    var activity_condition_rule = $('#activity_condition_rule').val();
    var init_angle = 0; //记录上次抽奖返回的角度
    var last_angle = 0; //记录上次转动结束的角度

	function hasChance(){
		var num_of_chance = parseInt($('#chance').text());
		if(num_of_chance > 0){
			return true;
		}else{
			return false;
		}
	}

	function rotate(angle){
		var rand_circle = Math.ceil(Math.random() * 2) + 1; // 附加多转几圈，2-3
		var rotate_angle = last_angle + (360 - init_angle) + rand_circle*360 + angle;
		init_angle = angle;
		last_angle = rotate_angle;
        $('#i_bg').css({
			'transform': 'rotate('+rotate_angle+'deg)',
			'-ms-transform': 'rotate('+rotate_angle+'deg)',
			'-webkit-transform': 'rotate('+rotate_angle+'deg)',
			'-moz-transform': 'rotate('+rotate_angle+'deg)',
			'-o-transform': 'rotate('+rotate_angle+'deg)',
			'transition': 'transform ease-out '+during_time+'s',
			'-moz-transition': '-moz-transform ease-out '+during_time+'s',
			'-webkit-transition': '-webkit-transform ease-out '+during_time+'s',
			'-o-transition': '-o-transform ease-out '+during_time+'s'
		});
	}

	$("#i_btn").click(function () {
		tongji("turntable");
		statistics_user_send();
        if(flag){
        	if(hasChance()){
	        	$.ajax({
		            type: "post",
		            url: "/new/lottery/draw",
		            data: {activity_id: activity_id, _csrf: csrf},
		            async: false,
		            success: function (res) {
		                var datas = eval("(" + res + ")");
		                if (datas.rsp_code == '0000') {
		                	flag = false;
		                	$('#chance').text(parseInt($('#chance').text()) - 1);
		                	rotate(datas.rsp_data.angle);
	                		$('.qjfbz').text('恭喜您，抽中' + datas.rsp_data.title);
	                		$('.qwcky').show();
		                } else {
		                    $('.qjfbz').text('活动暂停,请稍后再试');
		                }
		                setTimeout(function(){
		                	$('.Hmask,.dl_tcym').show();
		                	$('#scroll').append("<div>" + datas.rsp_data.broad_info + "</div>");
		                },during_time*1000);
		            }
		        });
	        }else{
	        	$('.qjfbz').text('亲，暂无抽奖次数');
	        	$('.Hmask,.dl_tcym').show();
	        }
        }
})

$('.dl_tcym button').click(function(){
	flag = true;
	$('.Hmask,.dl_tcym').hide();
});

$('.qwcky').click(function(){
    window.location = '/new/prize';
});

function addLotteryNum(){
	$.post("/new/lottery/share", {activity_id:activity_id,'_csrf':csrf}, function (data) {
		var datas = eval("(" + data + ")");
        if (datas.rsp_code == '0000') {
        	$('#chance').text(parseInt($('#chance').text()) + datas.num);
        }
	});
}

function addBoradInfo(data){
	var broad_dom = $("<div>" + data + "</div>");
	$('#scroll').append(broad_dom);
}
</script>

<script type="text/javascript">
    function tongji(event) {
        <?php \app\common\PLogger::getInstance('weixin','',$user->id); ?>
        <?php $json_data = \app\common\PLogger::getJson();?>
        var baseInfoss = eval( '('  + '<?php echo $json_data; ?>' + ')' );
        baseInfoss.url = baseInfoss.url+'&event='+event;
        console.log(baseInfoss);
        var ortherInfo = {
            screen_height: window.screen.height,//分辨率高
            screen_width: window.screen.width,  //分辨率宽
            user_agent: navigator.userAgent,
            height: document.documentElement.clientHeight || document.body.clientHeight,  //网页可见区域宽
            width: document.documentElement.clientWidth || document.body.clientWidth,//网页可见区域高
        };
        var baseInfos = Object.assign(baseInfoss, ortherInfo);
        var turnForm = document.createElement("form");
        turnForm.id = "uploadImgForm";
        turnForm.name = "uploadImgForm";
        document.body.appendChild(turnForm);
        turnForm.method = 'post';
        turnForm.action = baseInfoss.log_url+'weixin';
        //创建隐藏表单
        for (var i in baseInfos) {
            var newElement = document.createElement("input");
            newElement.setAttribute("name",i);
            newElement.setAttribute("type","hidden");
            newElement.setAttribute("value",baseInfos[i]);
            turnForm.appendChild(newElement);
        }
        var iframeid = 'if' + Math.floor(Math.random( 999 )*100 + 100) + (new Date().getTime() + '').substr(5,8);
        var iframe = document.createElement('iframe');
        iframe.style.display = 'none';
        iframe.id = iframeid;
        iframe.name = iframeid;
        iframe.src = "about:blank";
        document.body.appendChild( iframe );
        turnForm.setAttribute("target",iframeid);
        turnForm.submit();
    }
</script>
<script  src='/new/st/statisticssave?type=<?php echo $activity->statistics_pv;?>'></script>
<script type="text/javascript">
    function statistics_user_send(){
        $.get('/new/st/statisticssave?type=<?php echo $activity->statistics_user;?>');
    }
</script>

<script src="http://res.wx.qq.com/open/js/jweixin-1.2.0.js"></script>
<script type="text/javascript">
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
				title: '<?php echo $share_info['title']; ?>',
				desc: '<?php echo $share_info['desc']; ?>',
				imgUrl: '<?php echo $share_info['imgUrl']; ?>',
				link: '<?php echo $share_info['link']; ?>',

				trigger: function (res) {
					// 不要尝试在trigger中使用ajax异步请求修改本次分享的内容，因为客户端分享操作是一个同步操作，这时候使用ajax的回包会还没有返回
				},
				success: function (res) {
					// countsharecount();
					if(activity_condition_rule == 3){
						addLotteryNum();
					}
				},
				cancel: function (res) {
				},
				fail: function (res) {
				}
			});

			// 2.2 监听“分享到朋友圈”按钮点击、自定义分享内容及分享结果接口
			wx.onMenuShareTimeline({
				title: '<?php echo $share_info['title']; ?>',
				desc: '<?php echo $share_info['desc']; ?>',
				imgUrl: '<?php echo $share_info['imgUrl']; ?>',
				link: '<?php echo $share_info['link']; ?>',

				trigger: function (res) {
					// 不要尝试在trigger中使用ajax异步请求修改本次分享的内容，因为客户端分享操作是一个同步操作，这时候使用ajax的回包会还没有返回
				},
				success: function (res) {
					// countsharecount();
					if(activity_condition_rule == 3){
						// addLotteryNum();
						setTimeout(addLotteryNum,500);
					}
				},
				cancel: function (res) {
				},
				fail: function (res) {
					alert(JSON.stringify(res));
				}
			});
		})
</script>
</body>
</html>

