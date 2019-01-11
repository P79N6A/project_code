<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, minimal-ui;">
    <meta name="format-detection" content="telephone=no">
    <title></title>   
    <link rel="stylesheet" type="text/css" href="/newdev/css/fiveactivity/reset.css"/>
	<link rel="stylesheet" type="text/css" href="/newdev/css/fiveactivity/inv.css"/>
</head>
<body>
<div class="yiyyn">
	<input type='hidden' name = 'err_code' value="<?=$err_code?>">
    <input type='hidden' name = 'invite_qcode' value="<?=$invite_qcode?>">
    <input type='hidden' name = 'user_id' value="<?=$user_id?>">
   
	<img src="/newdev/images/fiveactivity/cjbd1.jpg">
	<div class="cjbd2">
		<img src="/newdev/images/fiveactivity/cjbd2.jpg">
		<button id="first_enter"></button>
	</div>
	<div class="cjbd3">
		<img src="/newdev/images/fiveactivity/cjbd3.jpg">
		<button id="second_enter"></button>
	</div>
	<div class="cjbd4">
		<img src="/newdev/images/fiveactivity/list_activity.png">
		<button id="three_enter"></button>
	</div>
	<img src="/newdev/images/fiveactivity/cjbd5.jpg">
</div>	


<div id="overDiv" hidden  ></div>
<!--分享页面弹窗-->
<div class="fenxzshi" hidden >
	<img src="/newdev/images/fiveactivity/share.png">
</div>

<div  class="tanceng" hidden >
	<img src="/newdev/images/fiveactivity/tceng.png">
	<div class="niycja">
		<p id="txt_p1"></p>
		<p id="txt_p2"></p>
		<button class="tcbutton"  >
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
	
	$(function(){
	var err_code = $('input[name="err_code"]').val();
	 var user_id = $('input[name="user_id"]').val();
	 var invite_qcode = $('input[name="invite_qcode"]').val();
	 var indexUrl=  '/new/fiveyearactivity/index?user_id='+user_id;
	// var activity_type1 = '10008';
	 var activity_type1 = '1';
	 var activity_type2 = '2';
	 //var activity_type2 = '10009';
	 var activity_type3 = '3'; //user表中的come_from
	 //var activity_type3 = '10010'; //user表中的come_from

	 var activity_index = '1219';

	 $.get("/new/st/statisticssave", {type:activity_index,user_id:user_id}, function (data) {
			});

	 //取消遮罩层
		$('#err_img').click(function(){
			$('#overDiv').hide();
			$('.tanceng').hide();
			$('.fenxzshi').hide();
			$('#err_img').hide();
		});


		$('#first_enter').click(function(){
			
            $.ajax({
            	url: '/new/fiveyearactivity/enter',
            	type: 'get',
            	data:{activity_type:activity_type1,invite_qcode:invite_qcode},
                 dataType: 'json',
            	
                success: function(msg){
                
                	//console.log('msg.back_code:'+msg.back_code);
                	
                	if(msg.back_code == 0001){
                		// alert('活动尚未开始');
                		$('#overDiv').show();
						$('.tanceng').show();
						$('#err_img').hide();
						$('#txt_p1').html('活动暂未开始。');
						$('#txt_p2').html('请参加其他更多精彩活动');
						$('#txt_p3').html('确定');
						$('.tcbutton').show();
						$('.tcbutton').click(function(){
							
							$('#overDiv').hide();
							$('.tanceng').hide();
							$('.fenxzshi').hide();
							$('#err_img').hide();
						
						})
						

                	}else if(msg.back_code == 0002){
                		//alert('一重活动已结束，请参与二重活动');
                		$('#overDiv').show();
						$('.tanceng').show();
						$('#err_img').hide(); 
						$('#txt_p1').html('抱歉，当前活动已结束');
						$('#txt_p2').html('请参加其他更多精彩活动');

						$('#txt_p3').html('确定');
						$('.tcbutton').show();
						$('.tcbutton').click(function(){
							
							$('#overDiv').hide();
							$('.tanceng').hide();
							$('.fenxzshi').hide();
							$('#err_img').hide();
						
						})
                	}else{
                		window.location.href="/new/fiveyearactivity/firstactivity?user_id="+user_id+"&invite_qcode="+invite_qcode;
                	}
                },
                error: function(msg){
                   
                    console.log(msg)
                }
            });
		});

		//二重活动点击
			$('#second_enter').click(function(){

            $.ajax({
            	url: '/new/fiveyearactivity/enter',
            	type: 'get',
            	data:{activity_type:activity_type2},
                dataType: 'json',
                success: function(msg){
                	console.log('msg.back_code:'+msg.back_code);
                	
                	if(msg.back_code == 0001){
                		$('#overDiv').show();
						$('.tanceng').show();
						$('#err_img').hide();
						$('#txt_p1').html('本活动暂未开始。');
						$('#txt_p2').html('请先参与其他活动');

						$('#txt_p3').html('确定');
						$('.tcbutton').show();
						$('.tcbutton').click(function(){
							
							$('#overDiv').hide();
							$('.tanceng').hide();
							$('.fenxzshi').hide();
							$('#err_img').hide();
						
						})
                	}else if(msg.back_code == 0002){
                		//alert('二重活动已结束，请参与三重活动');
                		$('#overDiv').show();
						$('.tanceng').show();
						$('#err_img').hide();
						$('#txt_p1').html('抱歉，当前活动已结束');
						$('#txt_p2').html('请参加其他更多精彩活动');
						
						$('#txt_p3').html('确定');
						$('.tcbutton').show();
						$('.tcbutton').click(function(){
							
							$('#overDiv').hide();
							$('.tanceng').hide();
							$('.fenxzshi').hide();
							$('#err_img').hide();
						
						})

                	}else{
                		window.location.href="/new/fiveyearactivity/secondactivity?user_id="+user_id+"&invite_qcode="+invite_qcode 
                	}
                	
                },
                error: function(msg){
                   
                    console.log(msg)
                }
            });
		});

		//三重好礼
			$('#three_enter').click(function(){
			
            $.ajax({
            	url: '/new/fiveyearactivity/enter',
            	type: 'get',
            	data:{activity_type:activity_type3},
                 dataType: 'json',
            	
                success: function(msg){
                
                	//console.log('msg.back_code:'+msg.back_code);
                	
                	if(msg.back_code == 0001){
                		// alert('活动尚未开始');
                		$('#overDiv').show();
						$('.tanceng').show();
						$('#err_img').hide();
						$('#txt_p1').html('本活动暂未开始。');
						$('.tcbutton').show();
						$('#txt_p2').html('请参加其他更多精彩活动');
						$('#txt_p3').html('确定');
						$('.tcbutton').show();
						$('.tcbutton').click(function(){
							
							$('#overDiv').hide();
							$('.tanceng').hide();
							$('.fenxzshi').hide();
							$('#err_img').hide();
						
						})
						

                	}else if(msg.back_code == 0002){
                		//alert('一重活动已结束，请参与二重活动');
                		$('#overDiv').show();
						$('.tanceng').show();
						$('#err_img').hide(); 
						$('#txt_p1').html('抱歉，当前活动已结束');
						$('#txt_p2').html('请参加其他更多精彩活动');
						$('#txt_p3').html('确定');
						$('.tcbutton').show();
						$('.tcbutton').click(function(){
							
							$('#overDiv').hide();
							$('.tanceng').hide();
							$('.fenxzshi').hide();
							$('#err_img').hide();
						
						})
                	}else{
                		window.location.href="/new/activitynew/three?user_id="+user_id+"&fcode="+invite_qcode;
                	}
                },
                error: function(msg){
                   
                    console.log(msg)
                }
            });
		});

	})
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


