$(function(){
	$("#auth_invest_loan").click(function(){
		var user_id = $("#user_id").val();
		var loan_id = $("#loan_id").val();
		var from_user_id = $("#from_user_id").val();
		var first_question = $(".click .check").eq(0).html();
		var second_quesion = $(".click .check").eq(1).html();
		var check_pic = $("#check_pic").val();
		var first_question_value = $("#first_question").val();
		var second_question_value = $("#second_question").val();
		//获取选定的图片路径
		var first_answer = $("#first_answer").val();
		var second_answer = $("#second_answer").val();
		var third_answer = $("#third_answer").val();
		if(first_question == undefined || first_question == '')
		{
			var msg = '请选择'+first_question_value;
			alert(msg);
			return false;
		}
		if(second_quesion == undefined || second_quesion == '')
		{
			var msg = '请选择'+second_question_value;
			alert(msg);
			return false;
		}
		if(check_pic == '' || check_pic == null)
		{
			alert("请选择图片");
			return false;
		}
//		if(first_question != first_answer)
//		{
//			var msg = '认证失败';
//			alert(msg);
//			window.location = '/dev/invest/detail?loan_id='+loan_id;
//			return false;
//		}
//		if(second_quesion != second_answer)
//		{
//			var msg = '认证失败';
//			alert(msg);
//			window.location = '/dev/invest/detail?loan_id='+loan_id;
//			return false;
//		}
//		if(check_pic != third_answer)
//		{
//			var msg = '认证失败';
//			alert(msg);
//			window.location = '/dev/invest/detail?loan_id='+loan_id;
//			return false;
//		}
		$.post("/dev/invest/auth",{user_id:user_id,from_user_id:from_user_id,first_question:first_question,second_quesion:second_quesion,third_quesion:check_pic,first_answer:first_answer,second_answer:second_answer,third_answer:third_answer},function(data){
			if(data == 'success')
			{
				window.location = '/dev/invest/detail?loan_id='+loan_id;
			}
			else if(data == 'exist')
			{
				window.location = '/dev/account/auth';
			}
			else if(data == 'one')
			{
				alert('认证失败，仅剩1次认证机会');
				window.location = '/dev/invest/detail?loan_id='+loan_id;
			}
			else
			{
				window.location = '/dev/account/authfail';
			}
		});
	});
});