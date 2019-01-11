$(function(){
	//投资首页点击预期收益
	$("#today_income").click(function(){
		$.get("/app/st/statisticssave", { type: 9 },function(data){
			var html = '<div class="mLayerMask"><div class="mLayer"> <i class="icon_wt"></i><div class="info"><h3>今日预期收益</h3><p>今日预期收益，是在您投资"先花宝"与"熟人"后，每日获取的收益金~</p>';
			html += '<p>PS:投资先花宝是当日计息，并且可以实时赎回的；</p><p>投资熟人，如果项目失败，系统会自动将您筹集的金额返还给投资人的账户；</div>';
			html += '<div class="button fCf"><a href="javascript:void(0);" class="aButton" id="i_know">朕知道了</a></div></div></div>';
			$(".mMenu").after(html);
		});
	});
	
	//投资首页点击累计收益
	$("#total_income").click(function(){
		$.get("/app/st/statisticssave", { type: 10 },function(data){
			var html = '<div class="mLayerMask"><div class="mLayer"> <i class="icon_wt"></i><div class="info"><h3> 累计收益</h3><p>顾名思义累计收益就是您投资"先花宝"以及"熟人"，获得的收益总计，都会在这里显示~累计收益满100点后是可以提现的哦~</p>';
			html += '</div>';
			html += '<div class="button fCf"><a href="javascript:void(0);" class="aButton" id="i_know">朕知道了</a></div></div></div>';
			$(".mMenu").after(html);
		});
	});
	
	$("#i_know").live("click",function(){
	　　　　$(".mLayerMask").hide();
	});
	
	$("#i_know_first").click(function(){
		$(".mLayerMask").hide();
	});
	
	$("#i_know_invest").click(function(){
	　　 $("#i_know_invest_up").hide();
	 	$("#i_know_invest_down").css('display','block'); 
	});
});