<!DOCTYPE html>
<html lang="zh">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"> 
	<meta name="viewport" content="width=device-width, initial-scale=1.0,maximum-scale=1, user-scalable=no">
	<title><?php echo $this->title;?></title>
	
	<!--可无视-->
	<link rel="stylesheet" type="text/css" href="/news/css/reset.css">
	<link rel="stylesheet" type="text/css" href="/newdev/css/lottery/egg/inv.css">

</head>
<body>
	<div class="htmleaf-container">
		<img src="<?php echo $activity->banner_url ? $img_url.$activity->banner_url : '/newdev/images/lottery/egg/banner.png';?>">
		<div class="shengdan12">
			<div class="indexfour">
				<h3>剩余砸蛋次数：<em id="chance"><?php echo $lottery_number;?></em>次</h3>
				<img src="/newdev/images/lottery/egg/indexthree.jpg">
				<div class="egg egg3 ">
					<p><img src="/newdev/images/lottery/egg/egg3.png"></p>
				</div>
				<div class="egg egg4 ">
					<p><img src="/newdev/images/lottery/egg/egg3.png"></p>
				</div>
				<div class="egg egg5 ">
					<p><img src="/newdev/images/lottery/egg/egg3.png"></p>
				</div>
			</div>
		</div>
		
		
		<div class="yaoqbo">
			<p class="bobao"><img src="/newdev/images/lottery/egg/bgone.png"></p>
			<div id="content" class="phooneym">
			    <div id="scroll">
			    	<?php foreach($broadcast_list as $broadcast){ ?>
			    		<div><?php echo $broadcast;?></div>
			    	<?php }?>
			    </div>
			</div>
		</div>
		<img src="<?php echo $activity->prize_url ? $img_url.$activity->prize_url : '/newdev/images/lottery/egg/hdgz1.png';?>">
		<img src="<?php echo $activity->rule_url ? $img_url.$activity->rule_url : '/newdev/images/lottery/egg/hdgz2.png';?>">
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

<script type="text/javascript" src="/newdev/js/jquery-1.11.0.min.js"></script>
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
</body>
<script type="text/javascript">
	var flag = true;
	var csrf = $("#csrf").val();
    var activity_id = $('#activity_id').val();
    var activity_condition_rule = $('#activity_condition_rule').val();
    var broken_egg;

	function hasChance(){
		var num_of_chance = parseInt($('#chance').text());
		if(num_of_chance > 0){
			return true;
		}else{
			return false;
		}
	}

    $(".egg img").click(function () {
    	tongji("egg");
    	statistics_user_send();
        broken_egg = $(this);
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
		                	broken_egg.attr('src','/newdev/images/lottery/egg/egg3hover.png');
		                	$('.qjfbz').text('恭喜您，抽中' + datas.rsp_data.title);
		                	$('.qwcky').show();
		                } else {
		                    $('.qjfbz').text('活动暂停,请稍后再试');
		                }
		                $('.Hmask,.dl_tcym').show();
		                $('#scroll').append("<div>" + datas.rsp_data.broad_info + "</div>");
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
    	broken_egg.attr('src','/newdev/images/lottery/egg/egg3.png');
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
</html>