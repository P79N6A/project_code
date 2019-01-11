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


<div class="yiyyn">
	<input type='hidden' name = 'err_code' value="<?=$err_code?>">
   	<input type='hidden' name = 'user_id' value="<?=$user_id?>">
   	<input type='hidden' name = 'coupon_status' value="<?=$coupon_status?>">
   	<input type='hidden' name = 'coupon_val' value="<?= empty($coupon_val)?'':$coupon_val ?>">
   	<input type='hidden' name = 'invite_qcode' value="<?= empty($invite_qcode)?'':$invite_qcode ?>">
   	<input type='hidden' name = 'user' value="<?= empty($user)?'':$user ?>">
   	<input type='hidden' name = 'orderinfo' value="<?= $orderinfo ?>">
   	<input type='hidden' name = 'end' value="<?= empty($end) ? '': $end?>"> 
	<div class="active">
		<img  src="/newdev/images/fiveactivity/active3.jpg">
		<a id="backIndex"></a>
	</div>
	
	<div class="bgyse">
		<div class="yem1">
			<img id="ling_status" src="/newdev/images/fiveactivity/active1_one.png">
			<p><?= empty($coupon_val)?'':$coupon_val ?><em>元</em></p>
		</div>
		<input id="phone" type="tel" onkeyup="value=value.replace(/[^\d]/g,'')"  maxlength="11" placeholder="请输入注册时手机号码"/>
		<div id="moileMsg"></div>
		<button id="get_coupon" ><img src="/newdev/images/fiveactivity/active1_button.png"></button>
	</div>
	
	
	<div class="cotest">
   		<h3>活动细则 </h3>
		<p>1、先花一亿元用户可以输入登录先花一亿元的手号，随机领取一张神秘感恩礼券。</p>
		<p>2、感恩礼券最高888元，仅在活动期间有效！</p>
		<p>3、本活动最终解释权归先花一亿元所有！</p>
		
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

</body>
</html>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script type="text/javascript">
	
	var err_code = $('input[name="err_code"]').val();
	var user_id = $('input[name="user_id"]').val();
	var coupon_val = $('input[name="coupon_val"]').val();
	var invite_qcode = $('input[name="invite_qcode"]').val();
	var orderinfo = $('input[name="orderinfo"]').val();
	var coupon_status = $('input[name="coupon_status"]').val();
	var users = $('input[name="user"]').val();
	var indexUrl=  '/new/fiveyearactivity/index?user_id='+user_id+'&invite_qcode='+invite_qcode ;
	var path = '/newdev/images/fiveactivity/yilingqu.png';
	var regtype = '1';
	var activity2_uv = '1218';
	var end = $('input[name="end"]').val();
	var isApp = <?php
	    if( strpos($_SERVER['HTTP_USER_AGENT'], 'YYY_ANDROID') || strpos($_SERVER['HTTP_USER_AGENT'], 'yyyIOS')){
	        echo 1;  //app端
	    }else {
	        echo 2;  //h5端
	    }?>;
		//发起借款
	function get_loan(){
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
        	
        	window.location.href = 'http://a.app.qq.com/o/simple.jsp?pkgname=com.xianhuahua.yiyiyuan_1';
        }

	}
	function toPage(activityName, position) {

   }

    function get_identity(){
    	var u = navigator.userAgent, app = navigator.appVersion;
        var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Linux') > -1;
        var isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/);
        // var android = "com.business.main.MainActivity";
        var android = "com.business.userinfo.Limit_Identity_VerificationAct";
        var ios = "loanViewController";
        var position = "-1";
        if(isApp==1){
        	 if (isiOS) {
           			
           	//遮罩层提醒认证
			$('#overDiv').show();
			$('.tanceng').show();
			$('#err_img').hide();
			$('#txt_p1').html('您还未完成实名认证');
			$('#txt_p2').html('请实名认证后再参与本活动！');
			$('#txt_p3').html('确定');
			$('.tcbutton').show(); 
			$('.tcbutton').click(function(){

				$('#overDiv').hide();
				$('.tanceng').hide();
				$('.fenxzshi').hide();
				$('#err_img').hide();

			});

	        } else if(isAndroid) {
	        	//安卓中跳到安卓认证
	          //window.myObj.toPage(android, position);
	           	$('#overDiv').show();
				$('.tanceng').show();
				$('#err_img').show();
				$('#txt_p1').html('您还未完成实名认证');
				$('#txt_p2').html('请实名认证后再参与本活动！');
				$('#txt_p3').html('立即前往认证');
				$('.tcbutton').show(); 
				$('.tcbutton').click(function(){
					window.myObj.toPage(android, position);
				});
	            
	        } 
        }else {

        	   	$('#overDiv').show();
				$('.tanceng').show();
				$('#err_img').show();
				$('#txt_p1').html('您还未完成实名认证');
				$('#txt_p2').html('请实名认证后再参与本活动！');
				$('#txt_p3').html('立即前往认证');
				$('.tcbutton').show(); 
				$('.tcbutton').click(function(){
					
					window.location.href="/new/userauth/nameauth?orderinfo="+orderinfo;
				});
        	// window.location.href="/new/userauth/nameauth?orderinfo="+orderinfo;
        }
    }



	$(function(){
		
		//返回首页
		$('#backIndex').click(function(){
			window.location.href = indexUrl;
		});

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

		//领取状态图片更改
		if(coupon_status == 2){
			$('#ling_status').attr('src',path); 
		}
		

			if((user_id ==null || user_id == '' || user_id==undefined || user_id==0 ) && (users ==null || users == '' || users==undefined || users==0)){
			//遮罩层提醒登录
			$('#overDiv').show();
			$('.tanceng').show();
			$('#err_img').show();
			$('#txt_p1').html('您未登录，请登录后领取');
			$('#txt_p3').html('立即登录');
			//$('.tcbutton').onclick(window.location.href="/new/regactivity?atype="+ regtype);
			$('.tcbutton').click(function(){
				window.location.href="/new/regactivity?atype="+ regtype+'&invite_qcode='+invite_qcode
			});
		}


		//取消遮罩层
		$('#err_img').click(function(){
			$('#overDiv').hide();
			$('.tanceng').hide();
			$('.fenxzshi').hide();
			$('#err_img').hide();
		});

		if(err_code == '0002'){
			//提醒注册
			$('#overDiv').show();
			$('.tanceng').show();
			$('#err_img').show();
			$('#txt_p1').html('您未登录，请登录后领取');
			$('#txt_p3').html('立即登录');
			$('.tcbutton').click(function(){
				window.location.href="/new/regactivity?atype="+ regtype
			});
		}

		//领取优惠券
	    $('button#get_coupon').click(function(){

	    	var phone =  $('input[id="phone"]').val();
	    	//console.log(phone);
	    	if(checkSubmitMobil(phone)){ //领取优惠券
            	$.ajax({
	            	url: '/new/fiveyearactivity/firstgetcoupon',
	            	type: 'get',
	            	data:{phone:phone,user_id:user_id},
	                dataType: 'json',
	                success: function(msg){
	                	
	                	console.log('msg.back_code:'+msg.back_code);
	         
		           		if(msg.back_code == '0000'){
		                	
		                //记录UV (领券人数)
                		 $.get("/new/st/statisticssave", {type: activity2_uv,user_id:user_id}, function (data) {
                		  });
                		 var val = msg.coupon_val;
                		 //遮罩层提醒领取成功
                		 	$('#txt_p3').html('立即使用'); //去借款
							$('.tcbutton').show();
                		    $('#overDiv').show();
							$('.tanceng').show();
							$('#err_img').show();
							$('#txt_p1').html('恭喜您抽中'+ val + '元感恩券');
							$('#txt_p2').html('5分钟后发放到您的账户');
							$('.tcbutton').show();
							$('.tcbutton').click(function(){
								get_loan();
							});

		                }
		                if(msg.back_code == '0001'){
		                		//alert('请登录');
		                		//遮罩层提醒登录
								$('#overDiv').show();
								$('.tanceng').show();
								$('#err_img').show();
								$('#txt_p1').html('您未登录，请登录后领取');
								$('#txt_p3').html('立即登录');
								$('.tcbutton').show();
								$('.tcbutton').click(function(){
									window.location.href="/new/regactivity?atype="+ regtype+'&invite_qcode='+invite_qcode;
								});
		                }
		                if(msg.back_code == '0002'){
		                		// alert('请输入注册时手机号码');
		                	$("#moileMsg").html("<font color='red' style='margin: 0 5%;'>请输入注册时手机号码！</font>"); 
		                }
		                if(msg.back_code == '0003'){
		                		// alert('手机号码格式不正确！请重新输入！');
		                	$("#moileMsg").html("<font color='red' style='margin: 0 5%;'>手机号码格式不正确！请重新输入！</font>"); 
		                }
		                if(msg.back_code == '0004'){
		                	
		                	$('#overDiv').show();
							$('.tanceng').show();
							$('#err_img').show();
							$('#txt_p1').html('您未登录，请登录后领取');
							$('#txt_p3').html('立即登录');
							$('.tcbutton').show();
							$('.tcbutton').click(function(){
								window.location.href="/new/regactivity?atype="+ regtype
							} );
		                }
		                if(msg.back_code == '0005'){
		                		// alert('输入手机号与注册手机号不一致，请重新输入');
		                	$("#moileMsg").html("<font color='red' style='margin: 0 5%;'>输入手机号与注册手机号不一致，请重新输入！</font>"); 
		                }
		               	if(msg.back_code == '0006'){
		                	
		                	// 实名认证 根据不同的平台，提示不同的弹窗

		                		get_identity();
		             
		                }
		                if(msg.back_code == '0007'){
		                		//alert('领取失败');
		                	$('#overDiv').show();
							$('.tanceng').show();
							$('#err_img').hide();
							$('#txt_p1').html('领取失败');
							$('#txt_p3').html('确定');
							$('.tcbutton').show();
							$('.tcbutton').click(function(){
								$('#overDiv').hide();
								$('.tanceng').hide();
								$('.fenxzshi').hide();
								$('#err_img').hide();
							})
							
		                }  
		                if(msg.back_code == '0008'){
		                	$('#overDiv').show();
							$('.tanceng').show();
							$('#err_img').hide();
							$('#txt_p1').html('抱歉，您已参加过本次活动。');
							$('#txt_p2').html('不能多次参加。');
							$('#txt_p3').html('确定');
							$('.tcbutton').show();
							$('.tcbutton').click(function(){
								$('#overDiv').hide();
								$('.tanceng').hide();
								$('.fenxzshi').hide();
								$('#err_img').hide();
							})
						
		                }


	                },
	                error: function(msg){
	                    
	                    console.log('请求领取优惠券接口失败'+msg)
	                }
                });
	    	}
	    	
	    })


	    //jquery验证手机号码 
		function checkSubmitMobil(phone) { 
		if (phone == "") { 
		//alert("手机号码不能为空！"); 
		$("#moileMsg").html("<font color='red' style='margin: 0 5%;'>手机号码不能为空！</font>"); 
		//$("#phone").focus(); 
		return false; 
		} 

		if (!(/^1[3|4|5|7|8][0-9]\d{4,8}$/).exec(phone)) { 
		//alert("手机号码格式不正确！"); 
		$("#moileMsg").html("<font color='red' style='margin: 0 5%;'>手机号码格式不正确！请重新输入！</font>"); 
		//$("#phone").focus(); 
		return false; 
		} 
		return true; 
		} 


	});

</script>
<script type="text/javascript">
	
		wx.config({
            debug: false,
            appId: "<?php echo $jsinfo['appid']; ?>",
            timestamp: "<?php echo $jsinfo['timestamp']; ?>",
            nonceStr: "<?php echo $jsinfo['nonceStr']; ?>",
            signature: "<?php echo $jsinfo['signature']; ?>",
            jsApiList: [
               'hideOptionMenu'
            ]
        });


		  wx.ready(function () {
        	wx.hideOptionMenu();
    	});
</script>


