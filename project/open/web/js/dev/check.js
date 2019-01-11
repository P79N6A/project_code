var _numberRex = /^[0-9]*[1-9][0-9]*$/;
$(function(){
	
	$("#close_window").click(function(){
		$(".mLayerMask").hide();
	});
	
	$("#close_window_button").click(function(){
		$(".mLayerMask").hide();
	});
	
	$(".mLayerMask").live('click',function(){
		$(this).hide();
	});
	
	$("#go_invite").click(function(){
		$(".mMask").css('display','block'); 
	});
	
	$("#invite_cancle").click(function(){
		$(".mMask").hide();
	});
	
	$("#input_vite_code").keyup(function(){
		var invite_code = $(this).val();
		if((invite_code != '') && (invite_code.length == 6))
		{
			$.post("/dev/account/getinvitecode",{invite_code:invite_code},function(result){
				if(result == 'success')
				{
					$("#invite_ok").removeClass("aButton sure fFr").addClass("aButton sure fFr on");
					$("#invite_ok").attr('disabled', false);
				}
			});
		}
		else
		{
			$("#invite_ok").removeClass("aButton sure fFr on").addClass("aButton sure fFr");
			$("#invite_ok").attr('disabled', true);
		}
	});
	
	$("#invite_ok").click(function(){
		var invite_code = $("#input_vite_code").val();
		$.post("/dev/account/setinvitecode",{invite_code:invite_code},function(result){
			if(result == 'success')
			{
				$(".mMask").hide();
				window.location = '/dev/account/remain';
			}
			else
			{
				alert('系统错误，提额失败');
				return false;
			}
		});
	});
	
	$("#shareTip").click(function(){
		var html = '<div class="mLayerMask"><img src="/images/dev/guide.png" width="100%" alt="点击右上角分享"/></div>';
		$(".mHelpPartner").append(html);
	});
	
	$("#make_money").click(function(){
		var html = '<div class="mLayerMask"><div class="mLayer"> <i class="icon_wt"></i><div class="info"><h3>如何赚钱？</h3><p>申请注册成功，并完善身份信息，</p>';
		html += '<p>我们会给您一部分信用额度，您可以用于投资好友或者先花宝</p><p style="font-size:13px; color:#e74747;">（不需要真金白银，免费把钱拿，如果这还不能满足您，还可以进行提额哦~）</p><p>额度多多，收益多多</p></div>';
		html += '<div class="button fCf"><a href="javascript:void(0);" class="aButton" id="i_know">朕知道了</a></div></div></div>';
		$(".mZhuDeng").after(html);
	});
	
	$("#i_know").bind("click",function(){
	　　　　$(".mLayerMask").remove();
	　　});
	
	$("#get_money").click(function(){
		var html = '<div class="mLayerMask"><div class="mLayer"> <i class="icon_wt"></i><div class="info"><h3>如何借款？</h3><p>提交注册成功，完善资料并通过信息审核，您就可以发起借款了，</p>';
		html += '<p>借款标的生成后后，您可以邀请朋友一起来帮助你~</p><p style="font-size:13px; color:#e74747;">（朋友投资是不需要真金白银的哦~）</p><p>朋友多多，借款多多</p></div>';
		html += '<div class="button fCf"><a href="javascript:void(0);" class="aButton" id="i_know">朕知道了</a></div></div></div>';
		$(".mZhuDeng").after(html);
	});

	$("#get_invite_code").click(function(){
		var html = '<div class="mLayerMask"><div class="mLayer"> <i class="icon_wt"></i><div class="info"><h3>如何获取邀请码？</h3><p>邀请码是用于记录邀请信息的串码。填写后会给邀请人10点信用值；</p>';
		html += '<h3 style="margin-top:8px;">如何获取？</h3><p>寻找身边使用先花花一亿元的伙伴，去获取邀请码。</p><p style="font-size:13px; color:#e74747;">PS:在您的朋友圈中，发布关于先花一亿元链接的用户，就有邀请码哦~</p></div>';
		html += '<div class="button fCf"><a href="javascript:void(0);" class="aButton" id="i_know">朕知道了</a></div></div></div>';
		$(".mZhuDeng").after(html);
	});
	
	$("#reg_fromcode_button").click(function(){
		var code = $('#reg_from_code').val() ;
		if( code == '' || !(_numberRex.test(code)) ){
			$("#reg_code_tip").html('请输入正确的邀请码');
			return false;			   
		}
		$.post("/dev/invite/invitesave",{code:code},function(result){
			var data = eval("("+ result + ")" ) ;
			if( data.ret == '0' ){
				window.location = data.url ;
			}else if( data.ret == '1' ){
				$("#reg_code_tip").html('邀请码错误');
				
			}else if( data.ret == '3'){
				$("#reg_code_tip").html('您使用的邀请码不符合规则，请从其他渠道获取');
			}
			else if( data.ret == '2' ){
				$("#reg_code_tip").html('网络失败，请重试');
			}
		  });
	});
	
	$(".first_click").die().bind("click",function(){
		var spanIndex = $(this).index();
		$("li",$(this).parent()).each(function(index, element) {
		   if(index == spanIndex){
			   $(this).addClass("valid");
			   var click_answer = $(this).attr("name");
			   var first = $("#first_answer").val();
			   var wid=$("#wid").val();
			   var array_key = $("#array_key").val();
			   if(click_answer == first)
			     {
				   	//点击正确答案,则跳转到第二个页面
				   window.location = '/dev/auth/second?wid='+wid+'&key='+array_key;
			     }
			   else
			   	 {
				   //点击错误答案
				   $.post("/dev/auth/firstsave",{wid:wid,array_key:array_key},function(data){
					   window.location = '/dev/auth/fail'; 
				   });
			     }
			   }else{
				   $(this).removeClass("valid");
			   }
		});
		return false;
	});
	
	
	$(".second_click").die().bind("click",function(){
		var spanIndex = $(this).index();
		$("li",$(this).parent()).each(function(index, element) {
		   if(index == spanIndex){
			   $(this).addClass("valid");
			   var click_answer = $(this).attr("name");
			   var second = $("#second_answer").val();
			   var wid=$("#wid").val();
			   var array_key = $("#array_key").val();
			   if(click_answer == second)
			     {
				   	//点击正确答案,则跳转到第二个页面
				   
				   window.location = '/dev/auth/third?wid='+wid+'&key='+array_key;
			     }
			   else
			   	 {
				   //点击错误答案
				   $.post("/dev/auth/secondsave",{wid:wid,array_key:array_key},function(data){
					   window.location = '/dev/auth/fail'; 
				   });
			     }
			   }else{
				   $(this).removeClass("valid");
			   }
		});
		return false;
	});
	
	
	$(".list2 li").die().bind("click",function(){
		var spanIndex = $(this).index();
		$("li",$(this).parent()).each(function(index, element) {
		   if(index == spanIndex){
			   $(this).addClass("valid");
			   var click_answer = $(this).attr("url");
			   var third= $("#third_answer").val();
			   var wid=$("#wid").val();
			   if(click_answer == third)
			     {
				   	//点击正确答案,则跳转到第二个页面
				   $.post("/dev/auth/successsave",{wid:wid},function(data){
					   if(data == 'success')
						  {
						   window.location = '/dev/auth/success'; 
						   }
					   else
						   {
						   window.location = '/dev/auth/fail'; 
						   }
				   });
			     }
			   else
			   	 {
				   //点击错误答案
				   $.post("/dev/auth/thirdsave",{wid:wid},function(data){
					   window.location = '/dev/auth/fail'; 
				   });
			     }
			   }else{
				   $(this).removeClass("valid");
			   }
		});
		return false;
	});
	
});
