$(function () {
	$("#click_open").click(function(){
		  var red_packet_id = $("#red_packet_id").val();
		  var user_id = $("#user_id").val();
		  var is_yyy = $("#is_yyy").val();
		  var auth_user_id = $("#auth_user_id").val();
		  $(this).attr("disabled", true);
		  $.post("/dev/redpackets", {red_packet_id: red_packet_id,user_id: user_id,is_yyy: is_yyy,auth_user_id: auth_user_id}, function(result){
			  var data = eval('('+result+')');
			if(data.ret == 'complete' || data.ret == 'noredpackets'){
				$("#click_open").attr('disabled', false);
				$(".potiablo").html('抢光了');
			}else if(data.ret == 'error'){
				$("#click_open").attr('disabled', false);
				$(".potiablo").html('系统错误');
			}else if(data.ret == 'exist'){
				$("#click_open").attr('disabled', false);
				$(".potiablo").html('您已抢过红包');
			}else{
				$("#click_open").attr('disabled', false);
				var html = "<em>"+data.amount+"</em><apsn>元</apsn>"
				$(".potiablo").html(html);
				$("#quick_withdraw").removeClass('lijitq');
				if(is_yyy == 'no'){
					var left_time = '<div class="endsheng"><img src="/images/account/timt.png"><span>剩余领取时间 <a id="left_time">1:00:00</a></span></div>';
					$(".trueall").after(left_time);
					$(".trueall").attr('endtime', data.end_time);
				}
			}
		 });  
	});
});	  
var leftEndtime = function () {
    var endtime = $(".trueall").attr("endtime") * 1000;//取结束日期(毫秒值)
	    var nowtime = new Date().getTime();        //今天的日期(毫秒值)
	    var youtime = endtime - nowtime;//还有多久(毫秒值)
	    var seconds = youtime / 1000;
	    var minutes = Math.floor(seconds / 60);
	    var hours = Math.floor(minutes / 60);
	    var days = Math.floor(hours / 24);
	    var CDay = days;
	    var CHour = hours % 24;
	    if (CHour < 10)
	    {
	        CHour = '0' + CHour;
	    }
	    var CMinute = minutes % 60;
	    if (CMinute < 10)
	    {
	        CMinute = '0' + CMinute;
	    }
	    var CSecond = Math.floor(seconds % 60);//"%"是取余运算，可以理解为60进一后取余数，然后只要余数。
	    if (CSecond < 10)
	    {
	        CSecond = '0' + CSecond;
	    }
	    if (endtime <= nowtime) {
	        //$(".time").html("<span class='times'>00<span class='font'>时</span></span>&nbsp;<span class='colon'>:</span>&nbsp;<span class='times'>00<span class='font'>分</span></span>&nbsp;<span class='colon'>:</span>&nbsp;<span class='times'>00<span class='font'>秒</span></span>");//如果结束日期小于当前日期就提示过期啦
	        $("#left_time").html("00:00:00");
	    } else {
	        //$(".time").html("<span class='times'>"+CHour+"<span class='font'>时</span></span>&nbsp;<span class='colon'>:</span>&nbsp;<span class='times'>"+CMinute+"<span class='font'>分</span></span>&nbsp;<span class='colon'>:</span>&nbsp;<span class='times'>"+CSecond+"<span class='font'>秒</span></span>");
	        $("#left_time").html("0:" + CMinute + ":" + CSecond);
	    } 
	    setTimeout('leftEndtime()', 1000);
};