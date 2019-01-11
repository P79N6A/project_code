<!DOCTYPE html>
<html lang="zh">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"> 
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo $this->title;?></title>
	
	<!--可无视-->
	<link rel="stylesheet" type="text/css" href="/newdev/css/lottery/fruit_machine/normalize.css" />
	<link rel="stylesheet" type="text/css" href="/newdev/css/lottery/fruit_machine/demo.css">
	<link rel="stylesheet" type="text/css" href="/news/css/reset.css">
	<link rel="stylesheet" type="text/css" href="/newdev/css/lottery/fruit_machine/inv.css?version=20180516">

</head>
<body>
	<div class="htmleaf-container">
		<img src="<?php echo $activity->banner_url ? $img_url.$activity->banner_url : '/newdev/images/lottery/fruit_machine/banner.jpg';?>">
		<div class="bgtwo">
			<div id="lottery">
				<img src="/newdev/images/lottery/fruit_machine/bgbgzp.png">
				<div class="jtiy">
					<div class="dangdang" id="demo1"><img src="/newdev/images/lottery/fruit_machine/dya1.png"></div>
					<div class="dangdang2" id="demo2" style="display: none;"><img src="/newdev/images/lottery/fruit_machine/dya2.png"></div>
				</div>
			    <table border="0" cellpadding="0" cellspacing="0">
			        <tr>
			            <td class="lottery-unit lottery-unit-0">
			            	<img src="<?php echo isset($prizes[0]) && $prizes[0]->prize_pic ? $img_url.$prizes[0]->prize_pic : '/newdev/images/lottery/fruit_machine/0.png';?>">
			            	<div class="mask"></div>
			            </td>
			            <td class="lottery-unit lottery-unit-1">
			            	<img src="<?php echo isset($prizes[1]) && $prizes[1]->prize_pic ? $img_url.$prizes[1]->prize_pic : '/newdev/images/lottery/fruit_machine/1.png';?>">
			            	<div class="mask"></div>
			            </td>
			            <td class="lottery-unit lottery-unit-2">
			            	<img src="<?php echo isset($prizes[2]) && $prizes[2]->prize_pic ? $img_url.$prizes[2]->prize_pic : '/newdev/images/lottery/fruit_machine/2.png';?>">
			            	<div class="mask"></div>
			            </td>
			        </tr>
			        <tr>
			            <td class="lottery-unit lottery-unit-7">
			            	<img src="<?php echo isset($prizes[7]) && $prizes[7]->prize_pic ? $img_url.$prizes[7]->prize_pic : '/newdev/images/lottery/fruit_machine/7.png';?>">
			            	<div class="mask"></div>
			            </td>
			            <td style="position: relative;">
		            		<a href="#">
								<img class="cont_img rotate_origin1" id="i_btn" src="<?php echo $activity->button ? $img_url.$activity->button : '/newdev/images/lottery/fruit_machine/11.png';?>">
		            			<p class="syucshu"  style="">剩余次数：<i id="chance"><?php echo $lottery_number;?></i>次</p>
		            		</a>
			            </td>
			            <td class="lottery-unit lottery-unit-3">
			            	<img src="<?php echo isset($prizes[3]) && $prizes[3]->prize_pic ? $img_url.$prizes[3]->prize_pic : '/newdev/images/lottery/fruit_machine/3.png';?>">
			            	<div class="mask"></div>
			            </td>
			        </tr>
			        <tr>
			            <td class="lottery-unit lottery-unit-6">
			            	<img src="<?php echo isset($prizes[6]) && $prizes[6]->prize_pic ? $img_url.$prizes[6]->prize_pic : '/newdev/images/lottery/fruit_machine/6.png';?>">
			            	<div class="mask"></div>
			            </td>
			            <td class="lottery-unit lottery-unit-5">
			            	<img src="<?php echo isset($prizes[5]) && $prizes[5]->prize_pic ? $img_url.$prizes[5]->prize_pic : '/newdev/images/lottery/fruit_machine/5.png';?>">
			            	<div class="mask"></div>
			            </td>
			            <td class="lottery-unit lottery-unit-4">
			            	<img src="<?php echo isset($prizes[4]) && $prizes[4]->prize_pic ? $img_url.$prizes[4]->prize_pic : '/newdev/images/lottery/fruit_machine/4.png';?>">
			            	<div class="mask"></div>
			            </td>
			        </tr>
			    </table>
			</div>
		</div>
		<div class="yaoqbo">
			<p class="bobao"><img src="/newdev/images/lottery/fruit_machine/bgone.jpg"></p>
			<div id="content" class="phooneym">
			    <div id="scroll">
			    	<?php foreach($broadcast_list as $broadcast){ ?>
			    		<div><?php echo $broadcast;?></div>
			    	<?php }?>
			    </div>
			</div>
		</div>
		<img src="<?php echo $activity->rule_url ? $img_url.$activity->rule_url : '/newdev/images/lottery/fruit_machine/hdgz.png';?>">
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
	var lottery={
		    index:-1,    //当前转动到哪个位置，起点位置
		    count:0,    //总共有多少个位置
		    timer:0,    //setTimeout的ID，用clearTimeout清除
		    speed:20,    //初始转动速度
		    times:0,    //转动次数
		    cycle:50,    //转动基本次数：即至少需要转动多少次再进入抽奖环节
		    prize:-1,    //中奖位置
		    init:function(id){
		        if ($("#"+id).find(".lottery-unit").length>0) {
		            $lottery = $("#"+id);
		            $units = $lottery.find(".lottery-unit");
		            this.obj = $lottery;
		            this.count = $units.length;
		            $lottery.find(".lottery-unit-"+this.index).addClass("active");
		        };
		    },
		    roll:function(){
		        var index = this.index;
		        var count = this.count;
		        var lottery = this.obj;
		        $(lottery).find(".lottery-unit-"+index).removeClass("active");
		        index += 1;
		        if (index>count-1) {
		            index = 0;
		        };
		        $(lottery).find(".lottery-unit-"+index).addClass("active");
		        this.index=index;
		        // console.log(index);
		        return false;
		    },
		    stop:function(index){
		        this.prize=index;
		        return false;
		    }
		};


		function roll(){
		    lottery.times += 1;
		    lottery.roll();//转动过程调用的是lottery的roll方法，这里是第一次调用初始化
		    if (lottery.times > lottery.cycle+10 && lottery.prize==lottery.index) {
		        clearTimeout(lottery.timer);
		        $('.Hmask,.dl_tcym,.qwcky').show();
		        $('#scroll').append("<div>" + broad_info + "</div>");
		        lottery.prize=-1;
		        lottery.times=0;
		        click=false;
		    }else{
		        if (lottery.times<lottery.cycle) {
		            lottery.speed -= 10;
		        }else if(lottery.times==lottery.cycle) {
		            // var index = Math.random()*(lottery.count)|0;//中奖物品通过一个随机数生成
		            lottery.prize = prize_index;      
		        }else{
		            if (lottery.times > lottery.cycle+10 && ((lottery.prize==0 && lottery.index==7) || lottery.prize==lottery.index+1)) {
		                lottery.speed += 10;
		            }else{
		                lottery.speed += 20;
		            }
		        }
		        if (lottery.speed<40) {
		            lottery.speed=40;
		        };
		        console.log(lottery.index+'^^^^^^'+lottery.times+'^^^^^^'+lottery.speed+'^^^^^^^'+lottery.prize);
		        lottery.timer = setTimeout(roll,lottery.speed);//循环调用
		    }
		    return false;
		}

	function qswhMarquee(){
		if(document.getElementById("demo1").style.display=="") {
			document.getElementById("demo1").style.display="none";
			document.getElementById("demo2").style.display="";}
		else {
			document.getElementById("demo2").style.display="none";
			document.getElementById("demo1").style.display="";
		} 
	}

	var flag = true;
	var prize_index = -1;
	var csrf = $("#csrf").val();
    var activity_id = $('#activity_id').val();
    var activity_condition_rule = $('#activity_condition_rule').val();

	function hasChance(){
		var num_of_chance = parseInt($('#chance').text());
		if(num_of_chance > 0){
			return true;
		}else{
			return false;
		}
	}

	lottery.init('lottery');
    $("#lottery a").click(function () {
    	tongji('fruit_machine');
    	statistics_user_send();
        if(flag){
        	if(hasChance()){
        		flag = false;
				var inter=setInterval(qswhMarquee,600);
	        	$.ajax({
		            type: "post",
		            url: "/new/lottery/draw",
		            data: {activity_id: activity_id, _csrf: csrf},
		            async: true,
		            success: function (res) {
		                var datas = eval("(" + res + ")");
		                if (datas.rsp_code == '0000') {
		                	flag = false;
		                	$('#chance').text(parseInt($('#chance').text()) - 1);
		                	$('.qjfbz').text('恭喜您，抽中' + datas.rsp_data.title);
		                	lottery.speed=100;
                		  	prize_index = datas.rsp_data.prize_index;
                		  	broad_info = datas.rsp_data.broad_info;
            			  	roll();
		                }else{
		                    $('.qjfbz').text('活动暂停,请稍后再试');
		                    $('.Hmask,.dl_tcym').show();
		                }
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
				'onMenuShareAppMessage',
				'onMenuShareTimeline',
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
						setTimeout(addLotteryNum,500);
						// addLotteryNum();
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