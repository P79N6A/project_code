<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, minimal-ui;">
    <meta name="format-detection" content="telephone=no">
    <title></title>   
    <link rel="stylesheet" type="text/css" href="/newdev/css/fiveactivity/reset.css"/>
	<link rel="stylesheet" type="text/css" href="/newdev/css/fiveactivity/inv.css?i=201804261516"/>
</head>
<body>
<!-- pv统计 -->
<script  src='/new/st/statisticssave?type=1220&user_id=<?=$user_id ?>'></script>
<div class="yiyyn">
	<input type='hidden' name = 'err_code' value="<?=$err_code?>">
   	<input type='hidden' name = 'user_id' value="<?=$user_id?>">
   	<input type='hidden' name = 'invite_qcode' value="<?=$invite_qcode?>"> <!-- 注册时候用到 -->
   	<input type='hidden' name = 'coupon_val' value="<?=$coupon_val?>">
   	<input type='hidden' name = 'friend_num' value="<?=$friend_num?>">
   	<input type='hidden' name = 'invitation_code' value="<?=$invitation_code?>"> <!-- 转发带上自身邀请码 -->
   	<input type='hidden' name = 'user' value="<?= empty($user)?'':$user ?>">
   	<input type='hidden' name = 'activity_type' value="<?= empty($activity_type) ? '2': $activity_type?>"> 
   	<input type='hidden' name = 'start' value="<?= empty($start) ? '': $start?>"> 
   	<input type='hidden' name = 'end' value="<?= empty($end) ? '': $end?>"> 

   	<!--分享页面弹窗-->
<!-- 	<div class="fenxzshi" hidden>
		<img src="/newdev/images/fiveactivity/share.png">
	</div> -->
	<div class="active">
		<img src="/newdev/images/fiveactivity/active2.jpg">
		<a id="backIndex"></a>
	</div>
	
	<div class="bgyse bgyse3" style="margin-bottom: 0;">
		<h3>邀请好友，拿40元好友助力券</h3>
		<div class="yem1 yem3">
			<img src="/newdev/images/fiveactivity/active3_one.png">
			<p>40<em>元</em></p>
		</div>
		<h3 class="hacth3">新用户还有20元新手礼券</h3>
		<div class="yem1 yem3">
			<img src="/newdev/images/fiveactivity/active3_two.png">
			<p>20<em>元</em></p>
		</div>
		<button class="lijshare"><img src="/newdev/images/fiveactivity/active2_button.png"></button>
	</div>
	<div class="bgyse bgyse3 newbgyn2">
		<h3 class="zhuliq">您已邀请<span><?php echo $friend_num?></span>人，可领取<span><?php echo $coupon_val?></span>元助力券！</h3>
		<div class="yem1 yem3">
			<img src="/newdev/images/fiveactivity/active3_one.png">
			<p><?php echo $coupon_val?><em>元</em></p>
		</div>
		<button class="ljilqv">立即领取</button>
	</div>
	
	
	<div class="cotest">
   		<h3>活动细则 </h3>
		<p>1、将活动页面，通过微信等方式发送给好友，将自己专属的活动告知好友。</p>
		<p>2、成功邀请1位好友注册并实名认证，即可获得8元助力券，邀请2位好友注册并实名认证，即可获得16元助力券，以此类推，每一位用户最多邀请5位好友。</p>
		<p>3、每一位新注册的用户还可以获得20元新手礼券。</p>
		<p>4、通过本活动获取的优惠券仅活动时间内有效。</p>
		<p>5、本活动最终解释权归先花一亿元所有！</p>
		
   	</div>

</div>	
<div id="overDiv"  hidden ></div>
<!--分享页面弹窗-->
<div class="fenxzshi" hidden >
	<img src="/newdev/images/fiveactivity/share.png">
</div>

<div  class="tanceng" hidden >
	<img src="/newdev/images/fiveactivity/tceng.png">
	<div class="niycja">
		<p id="txt_p1"></p>
		<p id="txt_p2"></p>
		<button class="tcbutton" >
			<img src="/newdev/images/fiveactivity/tcbutton.png">
			<p id="txt_p3"></p>
		</button>
	</div>

</div>

<a class="errorer" ><img id="err_img" hidden src="/newdev/images/fiveactivity/errorer.png"></a>
</div>

</body>
</html>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script type="text/javascript">

	
	var err_code = $('input[name="err_code"]').val();
	var user_id = $('input[name="user_id"]').val();
	var coupon_val = $('input[name="coupon_val"]').val();
	var friend_num = $('input[name="friend_num"]').val();
	var regtype = $('input[name="activity_type"]').val();
	var invite_qcode = $('input[name="invite_qcode"]').val();
	var users = $('input[name="user"]').val();
	var start = $('input[name="start"]').val();
	var end = $('input[name="end"]').val();
	var indexUrl=  '/new/fiveyearactivity/index?user_id='+user_id +'&invite_qcode='+invite_qcode;
	var mark = false;
	var mark_login = false;
	var remark = true;
	var share_num = 1221;


	var isApp = <?php
        if( strpos($_SERVER['HTTP_USER_AGENT'], 'YYY_ANDROID') || strpos($_SERVER['HTTP_USER_AGENT'], 'yyyIOS')){
            echo 1;  //app端
        }else {
            echo 2;  //h5端
        }
    	?>;


	//发起借款
	function get_loan(){
		console.log('来到了借款方法');
		var u = navigator.userAgent, app = navigator.appVersion;
        var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Linux') > -1;
        var isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/);
        var android = "com.business.main.MainActivity";
       
        var ios = "loanViewController";

        var position = "-1";

        if(isApp==1){
            if (isiOS) {
           //window.myObj.toPage(ios);
           window.myObj.closeHtml();
           
	        } else if(isAndroid) {
	            window.myObj.toPage(android, position);
	            
	        }
        }else {
        	console.log('去跳转');
        	//window.location.href = 'http://a.app.qq.com/o/simple.jsp?pkgname=com.xianhuahua.yiyiyuan_1';
        	 window.location.href = '/new/loan';
        }

     

	}

	

	//领取优惠券
	function getcoupon(){
		console.log('woshi');
		$.ajax({
        	url: '/new/fiveyearactivity/secondgetcoupon',
        	type: 'get',
        	data:{user_id:user_id},
            dataType: 'json',
            success: function(msg){
            	if(msg.back_code == '0000'){
        		
        		/* $.get("/new/st/statisticssave", {type: 502,user_id:user_id}, function (data) {
        		  });//记录UV (领券人数)*/

        		mark = true;
        		 //遮罩层提醒领取成功
    		    $('#overDiv').show();
				$('.tanceng').show();
				$('#err_img').hide(); 
				$('#txt_p1').html('恭喜您，获得一张'+'<b style="color:yellow">'+ coupon_val + '元好友助力券</b>');
				$('#txt_p2').html('');
				$('#txt_p3').html('立即使用'); //去借款
				$('.tcbutton').show();
				$('.tcbutton').click(function(){
				
					$('#txt_p1').html('恭喜您，获得一张'+'<b style="color:yellow">'+ coupon_val + '元好友助力券</b>');
					$('#txt_p2').html('');
					$('#txt_p3').html('立即使用'); 
					get_loan();
				});
				

                }
                if(msg.back_code == '0001'){
                	
                		$('#overDiv').show();
						$('.tanceng').show();
						$('#err_img').show();
						$('#txt_p1').html('您未登录，请登录后领取');
						$('#txt_p3').html('立即登录');
						$('.tcbutton').show();
						$('.tcbutton').click(function(){
						
							window.location.href="/new/regactivity?atype="+ regtype + "&invite_qcode="+ invite_qcode;
						});
							
						
                }
                if(msg.back_code == '0002'){
                		

					$('#overDiv').show();
					$('.tanceng').show();
					$('#err_img').hide();
					$('#txt_p1').html('您还未邀请好友');
					$('#txt_p2').html('快去邀请好友吧！');
					$('#txt_p3').html('确定');
					$('.tcbutton').show();
					$('.tcbutton').click(function(){
		$.get("/new/st/statisticssave", {type: share_num,user_id:user_id}, function (data) {
            });
						 share_activity();

					})
                }
                if(msg.back_code == '0003'){
                	mark = true;
                	$('#overDiv').show();
					$('.tanceng').show();
					$('#err_img').hide();
					$('#txt_p1').html('领券失败');
					$('#txt_p2').hide();
					$('#txt_p2').hide();
					$('.tcbutton').show();
					$('.tcbutton').click(function(){

						$('#overDiv').hide();
						$('.tanceng').hide();
						$('.fenxzshi').hide();
						$('#err_img').hide();
					}); 
                }
                if(msg.back_code == '0004'){
                	mark = true;
                	$('#overDiv').show();
					$('.tanceng').show();
					$('#err_img').hide();
					$('.tcbutton').show();  
					$('#txt_p1').html('抱歉，您已参加过本次活动。');
					$('#txt_p2').html('不能多次参加。');
					$('.tcbutton').click(function(){
						
						$('#overDiv').hide();
						$('.tanceng').hide();
						$('.fenxzshi').hide();
						$('#err_img').hide();
					
					})
                }
            },
            error:function(msg){
            	console.log('请求领取优惠券接口失败'+msg)
            }
        });
	}



	$(function(){
		
		//返回首页
		$('#backIndex').click(function(){
			window.location.href = indexUrl;
		});

		//遮罩层提示登录
		if((user_id ==null || user_id == '' || user_id==undefined || user_id==0 ) && (users ==null || users == '' || users==undefined || users==0)){
			//遮罩层提醒登录
			
			$('#overDiv').show();
			$('.tanceng').show();
			$('#err_img').show();
			$('#txt_p1').html('您未登录，请登录后领取');
			$('#txt_p3').html('立即登录');
			$('.tcbutton').show();
			$('.tcbutton').click(function(){
				
				window.location.href="/new/regactivity?atype="+ regtype + "&invite_qcode="+ invite_qcode
			});
		}else{
			mark_login  = true;
		}
		//jude_login();

		if(start ==1){
				$('#overDiv').show();
				$('.tanceng').show();
				$('#err_img').hide();
				$('#txt_p1').html('活动暂未开始。');
				$('#txt_p2').html('请参加其他更多精彩活动');
				$('#txt_p3').html('确定');
				$('.tcbutton').show();
				$('.tcbutton').click(function(){
					
				window.location.href= '/new/fiveyearactivity/index';
				
				})
		}
		if(end ==1){
				$('#overDiv').show();
				$('.tanceng').show();
				$('#err_img').hide(); 
				$('#txt_p1').html('抱歉，当前活动已结束');
				$('#txt_p2').html('请参加其他更多精彩活动');

				$('#txt_p3').html('确定');
				$('.tcbutton').show();
				$('.tcbutton').click(function(){
					
					window.location.href= '/new/fiveyearactivity/index';
				
				})
		}


		//取消遮罩层
		$('#err_img').click(function(){
			$('#overDiv').hide();
			$('.tanceng').hide();
			$('.fenxzshi').hide();
			$('#err_img').hide();
		});


		//领取优惠券
		$('button.ljilqv').click(function(){
			if(coupon_val==0 || friend_num==0){
					console.log('login' + mark_login);
					if(mark_login == false){
							//遮罩层提示登录
						if((user_id ==null || user_id == '' || user_id==undefined || user_id==0 ) && (users ==null || users == '' || users==undefined || users==0)){
						console.log(9999);
						//遮罩层提醒登录
						console.log('点击登录');
						$('#overDiv').show();
						$('.tanceng').show();
						$('#err_img').show();
						$('#txt_p1').html('您未登录，请登录后领取');
						$('#txt_p3').html('立即登录');
						$('.tcbutton').show();
						
						$('.tcbutton').click(function(){
							console.log('点击登录');
							window.location.href="/new/regactivity?atype="+ regtype + "&invite_qcode="+ invite_qcode
						});
						
						}
					}else{

							//遮罩层提醒邀请
							$('#overDiv').show();
							$('.tanceng').show();
							$('#err_img').hide();
							$('#txt_p1').html('提示您还未邀请好友');
							$('#txt_p2').html('快去邀请好友吧！');
							$('#txt_p3').html('确定');
							$('.tcbutton').show();
							$('.tcbutton').click(function(){
								console.log(7777);
								
								if(mark_login ==true){
					$.get("/new/st/statisticssave", {type: share_num,user_id:user_id}, function (data) {
            });
									share_activity()
								}

							})

					}
					
			}
			else{
				console.log('领取');
				//遮罩层提醒领取
				$('#overDiv').show();
					$('.tanceng').show();
					$('#err_img').show();
					$('#txt_p1').html('确定领取好友助力券吗？');
					$('#txt_p2').html('活动时间内只可领取一次！');
					$('#txt_p3').html('确定');
					$('.tcbutton').show();
					$('.tcbutton').click(function(){
						console.log('去请求领取ajax');
						if(mark==false){
							getcoupon(); //领取优惠券
						}else{

							$('#overDiv').show();
							$('.tanceng').show();
							$('#err_img').hide();
							$('.tcbutton').show();  
							$('#txt_p1').html('抱歉，您已参加过本次活动。');
							$('#txt_p2').html('不能多次参加。');
							$('.tcbutton').click(function(){
								
								$('#overDiv').hide();
								$('.tanceng').hide();
								$('.fenxzshi').hide();
								$('#err_img').hide();
							
							})

						}


						
					});


			}

		});

	})
</script>


<script type="text/javascript">
		//分享
	var isApp = <?php
        if( strpos($_SERVER['HTTP_USER_AGENT'], 'YYY_ANDROID') || strpos($_SERVER['HTTP_USER_AGENT'], 'yyyIOS')){
            echo 1;  //app端
        }else {
            echo 2;  //h5端
        }
    ?>;

   function share_activity(){
   			
   		var u = navigator.userAgent, app = navigator.appVersion;
        var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Linux') > -1;
        var isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/);
   		if(isApp==1){
    			//弹出微信和朋友圈

			    if(isAndroid || isiOS){
			    	window.myObj.doShare('5');
			    }

    		}
    		if(isApp==2){
    			//引导用户右上角转发
    			$('#overDiv').show();
    			$('.tanceng').hide();
    			$('.fenxzshi').show();
    			$('#err_img').hide();
    			$('#txt_p1').html('');
				$('#txt_p2').html('');
				$('#txt_p3').html('');
				$('.tcbutton').hide();

				$('#overDiv').bind("click",function(event){//点击空白处，设置的弹框消失
				
			        $('#overDiv').hide();
	    			$('.tanceng').hide();
	    			$('.fenxzshi').hide();
	    			$('#err_img').hide();
	    			$('#txt_p1').html('');
					$('#txt_p2').html('');
					$('#txt_p3').html('');
					$('.tcbutton').hide();

			    });

			    $('.fenxzshi').bind("click",function(event){//点击空白处，设置的弹框消失
				
			        $('#overDiv').hide();
	    			$('.tanceng').hide();
	    			$('.fenxzshi').hide();
	    			$('#err_img').hide();
	    			$('#txt_p1').html('');
					$('#txt_p2').html('');
					$('#txt_p3').html('');
					$('.tcbutton').hide();

			    });

    			
    			
    		}
   }

   function jude_login(){

   		//遮罩层提示登录
			if((user_id ==null || user_id == '' || user_id==undefined || user_id==0 ) && (users ==null || users == '' || users==undefined || users==0)){
			
			//遮罩层提醒登录
			console.log('点击登录');
			$('#overDiv').show();
			$('.tanceng').show();
			$('#err_img').show();
			$('#txt_p1').html('您未登录，请登录后领取');
			$('#txt_p3').html('立即登录');
			$('.tcbutton').show();
			//return false;
			$('.tcbutton').click(function(){
				console.log('点击登录');
				window.location.href="/new/regactivity?atype="+ regtype + "&invite_qcode="+ invite_qcode
			});
			
		}
   }

    $(function(){	
    	$('button.lijshare').click(function(){
    		console.log(mark_login);
 
		jude_login();

			if(mark_login == true){

				//分享
			$.get("/new/st/statisticssave", {type: share_num,user_id:user_id}, function (data) {
            });
    			share_activity();
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
                 desc: '拼手气，抽最高888元，还有更多好礼等你来',
                 link: '<?php echo $shareUrl; ?>',
                 
                  imgUrl: '<?php echo $imgUrl; ?>',
                 
                trigger: function (res) {
                    // 不要尝试在trigger中使用ajax异步请求修改本次分享的内容，因为客户端分享操作是一个同步操作，这时候使用ajax的回包会还没有返回
                },
                success: function (res) {
                   // countsharecount();
          		
                },
                cancel: function (res) {
                },
                fail: function (res) {
                }
            });

            // 2.2 监听“分享到朋友圈”按钮点击、自定义分享内容及分享结果接口
            wx.onMenuShareTimeline({
                 title: '庆周年，拿好礼',
                 desc: '拼手气，抽最高888元，还有更多好礼等你来',
                link: '<?php echo $shareUrl; ?>',
               imgUrl: '<?php echo $imgUrl; ?>',
                // imgUrl: '/images/dev/face.png',
                trigger: function (res) {
                    // 不要尝试在trigger中使用ajax异步请求修改本次分享的内容，因为客户端分享操作是一个同步操作，这时候使用ajax的回包会还没有返回
                },
                success: function (res) {
                   // countsharecount();
           
                },
                cancel: function (res) {
                },
                fail: function (res) {
                    alert(JSON.stringify(res));
                }
            });

          
    })
 })
 // function doShare() { }
       

</script>

