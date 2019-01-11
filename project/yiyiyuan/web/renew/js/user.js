// JavaScript Document
var count,countdown;
var _mobileRex = /^(1(([35678][0-9])|(47)))\d{8}$/;
var _numberRex = /^[0-9]*[1-9][0-9]*$/;
$(function(){

    //登录发送短信验证码
    $('#reggetcode_login').click(function() {
        var mobile = $("#regmobile").val();

        if (mobile == '' || !(_mobileRex.test(mobile))) {
            $("#reg_one_error").html('请输入正确的手机号码');
            $("#regmobile").focus();
            return false;
        }
        var mark = $("input[name='mark']").val();
        var pic_num = $("input[name='pic_num']").val();
        if (mark == 1 && pic_num == '') {
            $("#reg_one_error").html('请输入正确的图形验证码');
            $("#pic_num").focus();
            return false;
        }

        $("#reggetcode_login").attr('disabled', true);
        $.post("/renew/login/loginsend", {_csrf:_csrf, mobile: mobile, pic_num: pic_num, mark: mark}, function(result) {
            var data = eval("(" + result + ")");
            if (data.ret == '0') {
                //发送成功
                count = 60;
                countdown = setInterval(CountDown_login, 1000);
                $("#reggetcode_login").attr('disabled', false);
            } else if (data.ret == '2')
            {
                $("#reg_one_error").html('该手机号码已超出每日短信最大获取次数');
                $("#regmobile").focus();
                $("#reggetcode_login").attr('disabled', false);
                return false;
            } else if (data.ret == '4') {
                $("#reg_one_error").html('图形验证码错误');
                $("#regmobile").focus();
                $("#reggetcode_login").attr('disabled', false);
                return false;
            } else if (data.ret == '5') {
                $("#reg_one_error").html('请输入正确的手机号');
                $("#regmobile").focus();
                $("#reggetcode_login").attr('disabled', false);
                return false;
            } else {
                $("input[name='mark']").val(1);
                $('#pic').show();
                $("#pic_num").focus();
                $("#reggetcode_login").attr('disabled', false);
                return false;
            }
        });

    });

    $("#login_button").click(function() {
        var mobile = $("#regmobile").val();
        var from_code = $('input[name="from_code"]').val();
        var come_from = $('input[name="come_from"]').val();
        if (mobile == '' || !(_mobileRex.test(mobile))) {
            $("#reg_one_error").html('请输入正确的手机号码');
            $("#regmobile").focus();
            return false;
        }
        var code = $("#regcode").val();
        if (code == '' || !(_numberRex.test(code))) {
            $("#reg_one_error").html('请输入正确的验证码');
            $("#regcode").focus();
            return false;
        }
        $(this).attr('disabled', true);
        $.post("/renew/login/loginsave", {_csrf:_csrf, mobile: mobile, code: code, from_code: from_code, come_from: come_from}, function(result) {
            var data = eval("(" + result + ")");
            if (data.ret == '0') {
                if (data.url != '') {
                    window.location = data.url;
                } else {
                    $("#reg_one_error").html('提交失败');
                    $("#login_button").attr('disabled', false);
                }
            } else if (data.ret == '1') {
                $("#reg_one_error").html('提交失败');
                $("#login_button").attr('disabled', false);
            } else if (data.ret == '2') {
                if (from_code.length > 0 || come_from.length > 0) {
                    if (data.url != '') {
                        window.location = data.url;
                    } else {
                        $("#reg_one_error").html('提交失败');
                        $("#login_button").attr('disabled', false);
                    }
                } else {
                    $("#reg_one_error").html('');
                    $("#overDiv").show();
                    $(".tanchuceng").show();
                    $("#login_button").attr('disabled', false);
                }
            } else if (data.ret == '3') {
                $("#reg_one_error").html('验证码错误');
                $("#login_button").attr('disabled', false);
            } else if (data.ret == '4') {
                $("#reg_one_error").html('对不起，您输入的手机号码有误，请用注册时的手机号码登录！');
                $("#login_button").attr('disabled', false);
            } else if (data.ret == '5') {
                $("#reg_one_error").html('系统错误');
                $("#login_button").attr('disabled', false);
            }  else if(data.ret == '6'){
            	$('.login_warning').html('该手机号未注册，请前往一亿元注册');
            	$('#overDiv,.tanchuceng').show();
            } else if(data.ret == '7'){
				$('.login_warning').html('您目前尚无可展期的借款，请前往一亿元产看详情');
            	$('#overDiv,.tanchuceng').show();
            } else {
                $("#reg_one_error").html('该微信已绑定其它的手机号');
                $("#login_button").attr('disabled', false);
            }
        });
    });
	

	
	
	$("#loan_confirm").click(function(){
		var agree_xieyi = $("#agree_loan_xieyi").is(":checked");
		if(agree_xieyi)
		{
			var desc = $("input[name='desc']").val();
			var days = $("input[name='days']").val();
			var amount = $("input[name='amount']").val();
			$("#loan_confirm").attr('disabled', true);
			$.post("/dev/loan/confirm", {desc:desc,days:days,amount:amount}, function(result){
				var data = eval("("+ result + ")" ) ;
				if(data.ret == '3')
				{
					$("#loan_confirm").attr('disabled', false);
				}
				else if(data.ret == '4')
				{
					$("#loan_confirm").attr('disabled', false);
					alert('您不能重复借款');
				}
				else if(data.ret == '5')
				{
					$("#loan_confirm").attr('disabled', false);
					alert('您已被驳回，请先去上传自拍照');
				}
				else if(data.ret == '6')
				{
					$("#loan_confirm").attr('disabled', false);
					alert('您提交的信息不符合规则，该账户已被冻结');
				}
				window.location = data.url ;
			});
		}
		else
		{
			alert('同意借款协议才能借款');
			return false;
		}
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
	
	//点击去找熟人按钮，跳转至新页面
	$("#find_friend").click(function(){
		var user_id = $('#open_id').val();
		window.location = "/dev/share/share?open_id="+user_id;
	});
	
	$("#invest_friend").click(function(){
		window.location = "/dev/invest/detail";
	});
	
	$("#invest_detail").click(function(){
		window.location = "/dev/invest/confirm";
	});
	
	$("#input_amount").keyup(function(){
		var input_amount = $(this).val();
		var regamount =  /^[1-9]*[1-9][0-9]*$/;
		if(!regamount.test(input_amount))
		{
			return false;
		}
		//获取年化利率
		var rate = $("#rate").val();
		//获取投资天数
		var invest_days = $("#invest_day").val();
		//计算预计收益
		var profit = (input_amount*(rate/100)/365)*invest_days;
		$("#yuji_profit").html(profit.toFixed(2));
	});
	
	$("#invest_amount").keyup(function(){
		var input_amount = $(this).val();
		var regamount =  /^[1-9]*[1-9][0-9]*$/;
		if(!regamount.test(input_amount))
		{
			return false;
		}
		//获取年化利率
		var rate = 5;
		//获取投资天数
		var invest_days = 1;
		//计算预计收益
		var profit = (input_amount*(rate/100)/365)*invest_days;
		$("#yuji_xhb_profit").html(profit.toFixed(2));
	});
	
	$("#invest_confirm").click(function(){
		var invest_amount = $('#invest_amount').val();
		var input_amount = $('#input_amount').val();
		var loan_id = $('#loan_id').val();
		var agree_xieyi = $("#agree_invest_xieyi").is(":checked");
		var regamount =  /^[1-9]*[1-9][0-9]*$/;
		if(agree_xieyi)
		{
			if(input_amount == '' || input_amount == null)
			{
				alert('请输入投资金额');
				return false;
			}
			if(!regamount.test(input_amount))
			{
				alert('投资金额必须是整数');
				return false;
			}
			if(parseInt(input_amount) > parseInt(invest_amount))
			{
				alert('输入的投资金额不能大于可投资金额');
				return false;
			}
			$("#invest_confirm").attr('disabled', true);
			$.post("/dev/invest/addsave", {loan_id:loan_id,input_amount:input_amount}, function(data){
				if(data == 'fail')
				{
					alert('投资失败')
					$("#invest_confirm").attr('disabled', false);
					return false;
				}
				else if(data == 'moreamount')
				{
					alert('输入的投资金额多于未筹满的额度');
					$("#invest_confirm").attr('disabled', false);
					return false;
				}
				else if(data == 'morethree')
				{
					alert('投资金额过大');
					$("#invest_confirm").attr('disabled', false);
					return false;
				}
				else if(data == 'moresecond')
				{
					alert('投资金额过大');
					$("#invest_confirm").attr('disabled', false);
					return false;
				}
				else
				{
					window.location = "/dev/invest/success?invest_id="+data;
				}
			});
		}
		else
		{
			alert('您需要同意投资协议才能投资')
			return false;
		}
		//window.location = "/dev/invest/success";
	});
	
	$("#invest_again").click(function(){
		window.location = "/dev/invest";
	});
	
	$("#confirm_repay").click(function(){
		var pic_repay1 	= $("input[name='pic_repay1']").val();
		if(pic_repay1.length < 1)
		{
			alert("请添加还款凭证");
			return false;
		}
		$(this).attr('disabled', true);
		$("#form_repay").submit();
	});
	
	$("#reg_two_form").click(function(){
		var school = $('#reg_school').val() ;
		var school_name = $('#reg_school_name').val() ;
		var edu = $('#reg_edu').val() ;
		var school_time = $('#reg_school_time').val() ;
		var realname = $('#reg_realname').val() ;
		var identity = $('#reg_identity').val() ;
		var is_real = $('#reg_identity').attr('is_real');
		if( school == ''){
			alert("请选择学校");
			return false ;
		} 
		if( school_time == '0' ){
			alert("请选择入学年份");
			return false ;
		}
		if( edu == '0' ){
			alert("请选择学历");
			return false ;
		}
		if( realname == '' ){
			alert("请选择你的真实姓名");
			return false ;
		}
		if( identity == '0' || is_real == '0'){
			alert("请填写姓名/身份证号码");
			return false ;
		}
		$.post("/dev/reg/twosave",{school:school,school_name:school_name,edu:edu,school_time:school_time,realname:realname,identity:identity},function(result){
			var data = eval("("+ result + ")" ) ;
			if( data.ret == '0' ){
				if( data.url != '' ){
					
					window.location = data.url ;
				}else{
					alert('提交失败');
				}
				
			}else if(data.ret == '2')
			{
				alert('该身份证号已存在，请更换');
			}
			else if(data.ret == '3')
			{
				alert('您提交的信息不符合规则，请确认是否正确');
			}
			else{
				alert('学籍认证失败，请重新修改');
				
			}
		   
		  });
	});
	$("#reg_shtwo_form").click(function(){
		var industry = $('#reg_industry').val() ;
		var company = $('#reg_company').val() ;
		var position = $('#reg_position').val() ;
		var realname = $('#reg_realname').val() ;
		var identity = $('#reg_identity').val() ;
		var is_real = $('#reg_identity').attr('is_real');
		if( industry == '0'){
			alert("请选择行业");
			return false ;
		} 
		if( company == '' ){
			alert("请输入公司名称");
			return false ;
		}
		if( position == '0' ){
			alert("请选择公司职位");
			return false ;
		}
		if( realname == '' ){
			alert("请选择你的真实姓名");
			return false ;
		}
		if( identity == '0' || is_real == '0'){
			alert("请填写姓名/身份证号码");
			return false ;
		}
		$.post("/dev/reg/shtwosave",{industry:industry,company:company,position:position,realname:realname,identity:identity},function(result){
			alert(result);
//			debugger;
			var data = eval("("+ result + ")" ) ;
			if( data.ret == '0' ){
				if( data.url != '' ){
					
					window.location = data.url ;
				}else{
					alert('提交失败');
				}
				
			}else if(data.ret == '2')
			{
				alert('该身份证号已存在，请更换');
			}
			else if(data.ret == '3')
			{
				alert('您提交的信息不符合规则，请确认是否正确');
			}
			else{
				alert('提交失败，请重新提交');
				
			}
		   
		  });
	});
	$("#get_user_headurl").click(function(){
		$.post("/dev/account/getuserinfo", {}, function(data){
			window.location = "/dev/account/info";
		});
	});
	$("#reg_shthree_form").click(function(){
		var school = $('#reg_school').val() ;
		var school_name = $('#reg_school_name').val() ;
		var edu = $('#reg_edu').val() ;
		var school_time = $('#reg_school_time').val() ;
		if( school == ''){
			alert("请选择学校");
			return false ;
		} 
		if( school_time == '0' ){
			alert("请选择入学年份");
			return false ;
		}
		if( edu == '0' ){
			alert("请选择学历");
			return false ;
		}
		$.post("/dev/reg/shthreesave",{school:school,school_name:school_name,edu:edu,school_time:school_time},function(result){
//			alert(result);
//			debugger;
			var data = eval("("+ result + ")" ) ;
			if( data.ret == '0' ){
				if( data.url != '' ){
					
					window.location = data.url ;
				}else{
					alert('提交失败');
				}
				
			}else{
				alert('学籍认证失败，请重新修改');
				
			}
		   
		  });
	});
	//上传照片
	$("#reg_pic_button").click(function(){
		if( $("#reg_serverid").val() == '' ){
			alert('请按照标准上传证件照');
			return false;
		}
		
		$("#reg_pic_form").submit();
	});
});

var lxfEndtime = function() {
		var endtime = $(".time").attr("endtime")*1000;//取结束日期(毫秒值)
		var nowtime = new Date().getTime();        //今天的日期(毫秒值)
		var youtime = endtime-nowtime;//还有多久(毫秒值)
		var seconds = youtime/1000;
		var minutes = Math.floor(seconds/60);
		var hours = Math.floor(minutes/60);
		var days = Math.floor(hours/24);
		var CDay= days ;
		var CHour= hours % 24;
		if(CHour < 10)
		{
			 CHour = '0'+CHour;
		}
		var CMinute= minutes % 60;
		if(CMinute < 10)
		{
			CMinute = '0'+CMinute;
		}
		var CSecond= Math.floor(seconds%60);//"%"是取余运算，可以理解为60进一后取余数，然后只要余数。
		if(CSecond < 10)
		{
			CSecond = '0'+CSecond;
		}
		if(endtime<=nowtime){
			$(".time").html("<span class='times'>00<span class='font'>时</span></span>&nbsp;<span class='colon'>:</span>&nbsp;<span class='times'>00<span class='font'>分</span></span>&nbsp;<span class='colon'>:</span>&nbsp;<span class='times'>00<span class='font'>秒</span></span>");//如果结束日期小于当前日期就提示过期啦
		}else{
			$(".time").html("<span class='times'>"+CHour+"<span class='font'>时</span></span>&nbsp;<span class='colon'>:</span>&nbsp;<span class='times'>"+CMinute+"<span class='font'>分</span></span>&nbsp;<span class='colon'>:</span>&nbsp;<span class='times'>"+CSecond+"<span class='font'>秒</span></span>");
		}
		setTimeout('lxfEndtime()',1000);
}

//登录倒计时
var CountDown_login = function() {

	$("#reggetcode_login").attr("disabled", true).addClass('dis');
    $("#reggetcode_login").html("重新获取 ( " + count + " ) ");
    if (count <= 0) {
        $("#reggetcode_login").html("获取验证码").removeAttr("disabled").removeClass('dis');
        clearInterval(countdown);
    }
    count--;
};

//倒计时
var CountDown = function() {
	
	$("#reggetcode").attr("disabled", true).addClass('dis');
    $("#reggetcode").html("重新获取 ( " + count + " ) ");
    if (count <= 0) {
        $("#reggetcode").html("获取验证码").removeAttr("disabled").removeClass('dis');
        clearInterval(countdown);
    }
    count--;
};

var log = function(event, data, formatted) {
    $("#reg_school_id").val(data.name);
}