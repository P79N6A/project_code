var countdown;
var _numberRex = /^[0-9]*[1-9][0-9]*$/;
var _mobileRex = /^(1(([3578][0-9])|(47)))\d{8}$/;
$(function(){
	//投资首页点击预期收益
	$("#today_income").click(function(){
		$.get("/dev/st/statisticssave", { type: 9 },function(data){
			var html = '<div id="overDiv"></div><div id="diolo_warp" class="diolo_warp jrsyde"><h3 class="diolaxk"><em></em>今日预期收益</h3>';
			html += '<div class="xylc"><p>今日预期收益，是在您投资"园丁计划"、"熟人"以及"先花宝"后，每日获取的收益金~</p><p>PS:投资园丁计划,期满后获得收益;';
			html += '投资先花宝是当日计息，并且可以实时赎回的；投资熟人，如果项目失败，系统会自动将您筹集的金额返还给投资人的账户;</p></div>';
			html += '<p class="radious_img"></p>';
			html += '<p class="go_on"></p>';
			html += '<div class="true_flase"><a href="javascript:void(0);" class="true_qr">朕知道了</a></div></div>';
			$(".mMenu").after(html);	    
		});
	});
	
	//投资首页点击累计收益
	$("#total_income").click(function(){
		$.get("/dev/st/statisticssave", { type: 10 },function(data){			
			var html = '<div id="overDiv"></div><div id="diolo_warp" class="diolo_warp zhenzhidao"><h3 class="diolaxk"><em></em>累计收益</h3>';
			html += '<div class="xylc"><p>顾名思义累计收益就是您投资"园丁计划"、"熟人"以及"先花宝"，获得的收益总计，都会在这里显示~累计收益满10点后是可以提现的哦~</p></div>';
			html += '<p class="radious_img"></p><p class="go_on"></p><div class="true_flase"><a href="javascript:void(0);" class="true_qr">朕知道了</a></div></div>';
			$(".mMenu").after(html);
		});
	});
	
	$(".true_qr").die().live("click",function(){
		$("#overDiv").css('display','none');
		$("#diolo_warp").css('display','none');
	});
	
	$("#i_know").die().live("click",function(){
	　　　　$(".mLayerMask").hide();
	});
	
	$("#i_know_first").click(function(){
		$(".mLayerMask").hide();
	});
	
	$("#i_know_invest").click(function(){
	　　 $("#i_know_invest_up").hide();
	 	$("#i_know_invest_down").css('display','block'); 
	});
	
	$("#invest_share").bind('keyup',function(){
		var invest_share = $(this).val();
		var cycle = $("#cycle").val();
		var yield = $("#yield").val();
		var coupon_id = $("#coupon_id").val();
		
		//计算预期收益
		if(_numberRex.test(invest_share)){
			var achieving_profit = (invest_share*yield*cycle)/100/365;

			if(coupon_id != ''){
				//计算优惠券的收益
				var coupon_days = $("#coupon_days").val();
				alert
				if(coupon_days == '' || coupon_days == undefined){
					var days = $("input[type='radio']:checked").attr('days');
				}else{
					var days = coupon_days;
				}
				var coupon_times = $("#coupon_times").val();
				if(coupon_times == '' || coupon_times == undefined){
					var times = $("input[type='radio']:checked").attr('cycle');
				}else{
					var times = coupon_times;
				}
				if(parseInt(days) <= parseInt(cycle)){
					var coupon_profit = (invest_share*yield*(parseInt(times)-1)*days)/100/365;
				}else{
					var coupon_profit = (invest_share*yield*(parseInt(times)-1)*cycle)/100/365;
				}
				$("#achieving_profit").html((achieving_profit+coupon_profit).toFixed(2));
			}else{
				$("#achieving_profit").html(achieving_profit.toFixed(2));
			}
			
		}
	});
	
	//投资标的
	$("#standard_confirm_invest").click(function(){
		var coupon_id = $("#coupon_id").val();
		var standard_id = $("#standard_id").val();
		var invest_share = $("#invest_share").val();
		var investing_standard = $("#investing_standard").val();
		var agree_xieyi = $("#agree_check").is(":checked");
		if(agree_xieyi){
			if (invest_share == '') {
				var html = '<div id="overDiv"></div><div id="diolo_warp" class="diolo_warp diolo_txtx">';
				html += '<p class="txt18">哎呀～投资金额不能为空！</p><p class="txt18 aiyiw"></p>';
				html += '<p class="radious_img"></p><p class="go_on"></p><div class="true_flase"><a href="javascript:void(0);" class="true_qr">朕知道了</a></div></div>';
				$(".touzibd").after(html);
			}else{
				if(_numberRex.test(invest_share)){
					if(invest_share < 100){
						var html = '<div id="overDiv"></div><div id="diolo_warp" class="diolo_warp diolo_txtx">';
						html += '<p class="txt18">哎呀～投资金额必须为大于等于100的整数！</p><p class="txt18 aiyiw"></p>';
						html += '<p class="radious_img"></p><p class="go_on"></p><div class="true_flase"><a href="javascript:void(0);" class="true_qr">朕知道了</a></div></div>';
						$(".touzibd").after(html);
					}else{
						if(parseInt(invest_share) > parseInt(investing_standard)){
							var html = '<div id="overDiv"></div><div id="diolo_warp" class="diolo_warp diolo_txtx">';
							html += '<p class="txt18">哎呀～我的土豪！</p><p class="txt18 aiyiw">您最多可买'+investing_standard+'点！</p>';
							html += '<p class="radious_img"></p><p class="go_on"></p><div class="true_flase"><a href="javascript:void(0);" class="true_qr">朕知道了</a></div></div>';
							$(".touzibd").after(html);
						}else{
							$.post("/dev/standard/investing", {standard_id : standard_id,invest_share : invest_share}, function (result) {
								var data = eval("(" + result + ")");
								if (data.ret == '1') {
									var html = '<div id="overDiv"></div><div id="diolo_warp" class="diolo_warp diolo_txtx">';
									html += '<p class="txt18">哎呀～系统错误！</p><p class="txt18 aiyiw"></p>';
									html += '<p class="radious_img"></p><p class="go_on"></p><div class="true_flase"><a href="javascript:void(0);" class="true_qr">朕知道了</a></div></div>';
									$(".touzibd").after(html);
								}else if(data.ret == '2'){
									var html = '<div id="overDiv"></div><div id="diolo_warp" class="diolo_warp diolo_txtx">';
									html += '<p class="txt18">哎呀～担保额度不足，</p><p class="txt18 aiyiw">快去购买担保卡吧！</p>';
									html += '<p class="radious_img"></p><p class="go_on"></p><div class="true_flase"><a href="javascript:void(0);" class="true_qr">朕知道了</a></div></div>';
									$(".touzibd").after(html);
								}else if(data.ret == '3'){
									var html = '<div id="overDiv"></div><div id="diolo_warp" class="diolo_warp diolo_txtx">';
									html += '<p class="txt18">哎呀～标的已满额！</p><p class="txt18 aiyiw">请投资其它标的(或明天再来投资)</p>';
									html += '<p class="radious_img"></p><p class="go_on"></p><div class="true_flase"><a href="javascript:void(0);" class="true_qr">朕知道了</a></div></div>';
									$(".touzibd").after(html);
								}else if(data.ret == '4'){
									var html = '<div id="overDiv"></div><div id="diolo_warp" class="diolo_warp diolo_txtx">';
									html += '<p class="txt18">哎呀～我的土豪！</p><p class="txt18 aiyiw">当前标的仅剩余'+data.share+'点，您还可购买'+data.share+'点！</p>';
									html += '<p class="radious_img"></p><p class="go_on"></p><div class="true_flase"><a href="javascript:void(0);" class="true_qr">朕知道了</a></div></div>';
									$(".touzibd").after(html);
								}else{
									window.location = "/dev/standard/confirm?standard_id="+standard_id+"&invest_share="+invest_share+"&coupon_id="+coupon_id;
								}
							});
						}	
					}
				}
				else{
					var html = '<div id="overDiv"></div><div id="diolo_warp" class="diolo_warp diolo_txtx">';
					html += '<p class="txt18">哎呀～投资金额必须为大于等于100的整数！</p><p class="txt18 aiyiw"></p>';
					html += '<p class="radious_img"></p><p class="go_on"></p><div class="true_flase"><a href="javascript:void(0);" class="true_qr">朕知道了</a></div></div>';
					$(".touzibd").after(html);
				}
			}
		}else{
			var html = '<div id="overDiv"></div><div id="diolo_warp" class="diolo_warp diolo_txtx">';
			html += '<p class="txt18">哎呀～您需要同意投资协议才能投资！</p><p class="txt18 aiyiw"></p>';
			html += '<p class="radious_img"></p><p class="go_on"></p><div class="true_flase"><a href="javascript:void(0);" class="true_qr">朕知道了</a></div></div>';
			$(".touzibd").after(html);
		}
	});
	
	//点击投资确认标的页得获取验证码按钮
	$("#invest_standard_getcode").click(function(){
		var mobile = $("#mobile").val();
		var standard_id = $("#standard_id").val();
		var invest_share = $("#invest_share").val();
		$.post("/dev/standard/sendsms",{mobile:mobile,type:16,standard_id:standard_id,invest_share:invest_share},function(result){
			count = 60 ;
			countdown = setInterval(CountDown, 1000);
		});
	});
	
	//点击显示优惠券
	$("#invest_coupon_use").click(function(){
		$(".Hcontainer").css('display','block');
	});
	
	//点击优惠券列表
	$('.layer .item').each(function(){
        $(this).click(function(){
            //点击改变样式
            $('.layer .item').find('img').attr('src','/images/unchoose.png');
            $(this).find('img').attr('src','/images/choose.png');
            $('.layer .item').find($('p.black')).removeClass("white");
            $(this).find($('p.black')).addClass("white");
            $('.layer .item').find($('p.green ')).removeClass("basise");
            $(this).find($('p.green')).addClass("basise");
            //点击相对应的radio变为checked
            $('input[type="radio"]').prop('checked',false);
            $('input[type="radio"]').removeAttr('checked');
            $(this).find('input[type="radio"]').prop('checked',true);
            $(this).find('input[type="radio"]').attr('checked','checked');
        });
    });
	
	//点击确定使用优惠券
	$("#invest_coupon_confirm").click(function(){
      var coupon_id = $('input:radio:checked').val();
      if(coupon_id == undefined)
	  {
    	  $(".Hcontainer").hide();
	      return false;
	  }
      $("#coupon_id").val(coupon_id);
      var invest_share = $("#invest_share").val();
      var cycle = $("#cycle").val();
	  var yield = $("#yield").val();
	  var days = $('input:radio:checked').attr('days');
	  var times = $('input:radio:checked').attr('cycle');
      //计算预计收益
		if(_numberRex.test(invest_share)){
			//计算未使用优惠券的收益
			var achieving_profit = (invest_share*yield*cycle)/100/365;
			//计算优惠券的收益
			if(parseInt(days) <= parseInt(cycle)){
				var coupon_profit = (invest_share*yield*(parseInt(times)-1)*days)/100/365;
			}else{
				var coupon_profit = (invest_share*yield*(parseInt(times)-1)*cycle)/100/365;
			}
			$("#achieving_profit").html((achieving_profit+coupon_profit).toFixed(2));
		}

      $(".Hcontainer").hide();
      var content = days+'天'+times+'倍优惠券';
      $(".tupian_img").html(content).removeClass('emem');
      $(".youhuijuan em").removeClass('gray_true');
	});
	
	//点击确认投资
	$("#standard_invest_confirm").click(function(){
		var coupon_id = $("#coupon_id").val();
		var standard_id = $("#standard_id").val();
		var invest_share = $("#invest_share").val();
		var code = $("#invest_standard_code").val();
		$(this).attr('disabled', true);
		if(code == ''){
			$("#standard_invest_confirm").attr('disabled', false);
			var html = '<div id="overDiv"></div><div id="diolo_warp" class="diolo_warp diolo_txtx">';
			html += '<p class="txt18">哎呀～验证码不能为空！</p><p class="txt18 aiyiw"></p>';
			html += '<p class="radious_img"></p><p class="go_on"></p><div class="true_flase"><a href="javascript:void(0);" class="true_qr">朕知道了</a></div></div>';
			$(".ture_sh").after(html);
		}else{
			$.post("/dev/standard/confirminvest",{coupon_id:coupon_id,standard_id:standard_id,invest_share:invest_share,code:code},function(result){
				var data = eval("(" + result + ")");
				if (data.ret == '1'){
					$("#standard_invest_confirm").attr('disabled', false);
					var html = '<div id="overDiv"></div><div id="diolo_warp" class="diolo_warp diolo_txtx">';
					html += '<p class="txt18">哎呀～系统错误！</p><p class="txt18 aiyiw"></p>';
					html += '<p class="radious_img"></p><p class="go_on"></p><div class="true_flase"><a href="javascript:void(0);" class="true_qr">朕知道了</a></div></div>';
					$(".ture_sh").after(html);
				}else if(data.ret == '2'){
					$("#standard_invest_confirm").attr('disabled', false);
					var html = '<div id="overDiv"></div><div id="diolo_warp" class="diolo_warp diolo_txtx">';
					html += '<p class="txt18">哎呀～验证码错误！</p><p class="txt18 aiyiw"></p>';
					html += '<p class="radious_img"></p><p class="go_on"></p><div class="true_flase"><a href="javascript:void(0);" class="true_qr">朕知道了</a></div></div>';
					$(".ture_sh").after(html);
				}else if(data.ret == '3'){
					$("#standard_invest_confirm").attr('disabled', false);
					var html = '<div id="overDiv"></div><div id="diolo_warp" class="diolo_warp diolo_txtx">';
					html += '<p class="txt18">哎呀～担保额度不足，</p><p class="txt18 aiyiw">快去购买担保卡吧！</p>';
					html += '<p class="radious_img"></p><p class="go_on"></p><div class="true_flase"><a href="javascript:void(0);" class="true_qr">朕知道了</a></div></div>';
					$(".ture_sh").after(html);
				}else if(data.ret == '4'){
					$("#standard_invest_confirm").attr('disabled', false);
					var html = '<div id="overDiv"></div><div id="diolo_warp" class="diolo_warp diolo_txtx">';
					html += '<p class="txt18">哎呀～标的已满额！</p><p class="txt18 aiyiw">请投资其它标的(或明天再来投资)</p>';
					html += '<p class="radious_img"></p><p class="go_on"></p><div class="true_flase"><a href="javascript:void(0);" class="true_qr">朕知道了</a></div></div>';
					$(".ture_sh").after(html);
				}else if(data.ret == '5'){
					$("#standard_invest_confirm").attr('disabled', false);
					var html = '<div id="overDiv"></div><div id="diolo_warp" class="diolo_warp diolo_txtx">';
					html += '<p class="txt18">哎呀～我的土豪！</p><p class="txt18 aiyiw">当前标的仅剩余'+data.share+'点，您还可购买'+data.share+'点！</p>';
					html += '<p class="radious_img"></p><p class="go_on"></p><div class="true_flase"><a href="javascript:void(0);" class="true_qr">朕知道了</a></div></div>';
					$(".ture_sh").after(html);
				}else if(data.ret == '6'){
					window.location = "/dev/standard/investfail";
				}else{
					window.location = "/dev/standard/investsuccess?order_id="+data.order_id;
				}
			});
		}
	});
	
	//点击赎回投资页的下一步
	$("#standard_invest_reback").click(function(){
		var standard_id = $("#standard_id").val();
		var reback_share = $("#reback_share").val();
		var total_onInvested_share = $("#total_onInvested_share").val();
		if (reback_share == '') {
			var html = '<div id="overDiv"></div><div id="diolo_warp" class="diolo_warp diolo_txtx">';
			html += '<p class="txt18">哎呀～赎回金额不能为空！</p><p class="txt18 aiyiw"></p>';
			html += '<p class="radious_img"></p><p class="go_on"></p><div class="true_flase"><a href="javascript:void(0);" class="true_qr">朕知道了</a></div></div>';
			$(".ture_sh").after(html);
		}else{
			if(_numberRex.test(reback_share)){
				if(parseInt(reback_share) > parseInt(total_onInvested_share)){
					var html = '<div id="overDiv"></div><div id="diolo_warp" class="diolo_warp diolo_txtx">';
					html += '<p class="txt18">哎呀～赎回金额不能大于已投资金额！</p><p class="txt18 aiyiw"></p>';
					html += '<p class="radious_img"></p><p class="go_on"></p><div class="true_flase"><a href="javascript:void(0);" class="true_qr">朕知道了</a></div></div>';
					$(".ture_sh").after(html);
				}else{
					$.post("/dev/standard/rebacking", {standard_id: standard_id,reback_share : reback_share}, function (result) {
						var data = eval("(" + result + ")");
						if (data.ret == '1'){
							var html = '<div id="overDiv"></div><div id="diolo_warp" class="diolo_warp diolo_txtx">';
							html += '<p class="txt18">哎呀～系统错误！</p><p class="txt18 aiyiw"></p>';
							html += '<p class="radious_img"></p><p class="go_on"></p><div class="true_flase"><a href="javascript:void(0);" class="true_qr">朕知道了</a></div></div>';
							$(".ture_sh").after(html);
							$("#standard_invest_confirm").attr('disabled', false);
						}else if(data.ret == '2'){
							var html = '<div id="overDiv"></div><div id="diolo_warp" class="diolo_warp diolo_txtx">';
							html += '<p class="txt18">哎呀～赎回金额不能大于已投资金额！</p><p class="txt18 aiyiw"></p>';
							html += '<p class="radious_img"></p><p class="go_on"></p><div class="true_flase"><a href="javascript:void(0);" class="true_qr">朕知道了</a></div></div>';
							$(".ture_sh").after(html);
							$("#standard_invest_confirm").attr('disabled', false);
						}else{
							window.location = "/dev/standard/confirmreback?standard_id="+standard_id+"&reback_share="+reback_share;
						}
					});
				}
			}else{
				var html = '<div id="overDiv"></div><div id="diolo_warp" class="diolo_warp diolo_txtx">';
				html += '<p class="txt18">哎呀～赎回金额只能为整数！</p><p class="txt18 aiyiw"></p>';
				html += '<p class="radious_img"></p><p class="go_on"></p><div class="true_flase"><a href="javascript:void(0);" class="true_qr">朕知道了</a></div></div>';
				$(".ture_sh").after(html);
			}
		}
	});
	
	//点击确定赎回页获取验证码按钮
	$("#reback_standard_getcode").click(function(){
		var mobile = $("#mobile").val();
		var reback_share = $("#reback_share").val();
		$.post("/dev/standard/sendsms",{mobile:mobile,type:17,reback_share:reback_share},function(result){
			count = 60 ;
			CountDown = setInterval(CountDown_back, 1000);
		});
	});
	
	$("#standard_reback_confirm").click(function(){
		var code = $("#reback_standard_code").val();
		if(code == ''){
			var html = '<div id="overDiv"></div><div id="diolo_warp" class="diolo_warp diolo_txtx">';
			html += '<p class="txt18">哎呀～验证码不能为空！</p><p class="txt18 aiyiw"></p>';
			html += '<p class="radious_img"></p><p class="go_on"></p><div class="true_flase"><a href="javascript:void(0);" class="true_qr">朕知道了</a></div></div>';
			$(".ture_sh").after(html);
		}else{
			var html = '<div id="overDiv"></div><div id="diolo_warp" class="diolo_warp">';
			html += '<p class="title_cz">提前赎回您将没有收益，确定赎回？</p><p class="radious_img"></p><p class="go_on"></p>';
			html += '<div class="true_flase"><a class="flase_qx" href="javascript:void(0);" id="standard_reback_confirm_cancle">取消</a><button style="float: right; background: #e74747;color: #fff;" id="standard_reback_confirm_button">确定</button></div></div>';
			$(".ture_sh").after(html);
		}
	});
	
	$("#standard_reback_confirm_cancle").die().live('click',function(){
		$("#overDiv").hide();
		$("#diolo_warp").hide();
	});
	
	$("#standard_reback_confirm_button").die().live('click',function(){
		var standard_id = $("#standard_id").val();
		var reback_share = $("#reback_share").val();
		var code = $("#reback_standard_code").val();
		$(this).attr('disabled', true);
		$.post("/dev/standard/confirmrebacking", {standard_id : standard_id,reback_share : reback_share,code : code}, function (result) {
			var data = eval("(" + result + ")");
			if (data.ret == '1'){
				$("#overDiv").hide();
				$("#diolo_warp").hide();
				var html = '<div id="overDiv"></div><div id="diolo_warp" class="diolo_warp diolo_txtx">';
				html += '<p class="txt18">哎呀～系统错误！</p><p class="txt18 aiyiw"></p>';
				html += '<p class="radious_img"></p><p class="go_on"></p><div class="true_flase"><a href="javascript:void(0);" class="true_qr">朕知道了</a></div></div>';
				$(".ture_sh").after(html);
				$("#standard_reback_confirm_button").attr('disabled', false);
			}else if(data.ret == '2'){
				$("#overDiv").hide();
				$("#diolo_warp").hide();
				var html = '<div id="overDiv"></div><div id="diolo_warp" class="diolo_warp diolo_txtx">';
				html += '<p class="txt18">哎呀～验证码错误！</p><p class="txt18 aiyiw"></p>';
				html += '<p class="radious_img"></p><p class="go_on"></p><div class="true_flase"><a href="javascript:void(0);" class="true_qr">朕知道了</a></div></div>';
				$(".ture_sh").after(html);
				$("#standard_reback_confirm_button").attr('disabled', false);
			}else if(data.ret == '3'){
				$("#overDiv").hide();
				$("#diolo_warp").hide();
				var html = '<div id="overDiv"></div><div id="diolo_warp" class="diolo_warp diolo_txtx">';
				html += '<p class="txt18">哎呀～赎回金额大于投资的金额！</p><p class="txt18 aiyiw"></p>';
				html += '<p class="radious_img"></p><p class="go_on"></p><div class="true_flase"><a href="javascript:void(0);" class="true_qr">朕知道了</a></div></div>';
				$(".ture_sh").after(html);
				$("#standard_reback_confirm_button").attr('disabled', false);
			}else if(data.ret == '4'){
				window.location = "/dev/standard/rebackfail?standard_id="+standard_id;
			}else{
				window.location = "/dev/standard/rebacksuccess?standard_id="+standard_id;
			}
		});
	});
	
	//赎回投资
	$("#reback_invest").click(function(){
		var agree_xieyi = $("#agree_check").is(":checked");
		var standard_id = $("#standard_id").val();
		if(agree_xieyi){
			window.location = "/dev/standard/reback?standard_id="+standard_id;
		}else{
			var html = '<div id="overDiv"></div><div id="diolo_warp" class="diolo_warp diolo_txtx">';
			html += '<p class="txt18">哎呀～您需要同意赎回协议才能投资！</p><p class="txt18 aiyiw"></p>';
			html += '<p class="radious_img"></p><p class="go_on"></p><div class="true_flase"><a href="javascript:void(0);" class="true_qr">朕知道了</a></div></div>';
			$(".tzxqing_ye").after(html);
		}
	});
	
	//领取优惠券发送验证码
	$("#getcoupon_sendmobile").click(function(){
		var mobile = $("#mobile").val();
		if( mobile == '' || !(_mobileRex.test(mobile)) ){
			$("#mobile_error").html('请输入正确的手机号码');
			$("#mobile").focus();
			return false;
		}
		$.post("/dev/standard/sendsms",{mobile:mobile,type:18},function(result){
			count = 60 ;
			CountDown = setInterval(CountDown_getcoupon, 1000);
		});
	});
	
	//领取优惠券
	$("#button_getcoupon").click(function(){
		var mobile 	= $("#mobile").val();
		if( mobile == '' || !(_mobileRex.test(mobile)) ){
			var html = '<div id="overDiv"></div><div id="diolo_warp" class="diolo_warp diolo_txtx">';
			html += '<p class="txt18">哎呀～您的手机号码格式不正确！</p><p class="txt18 aiyiw"></p>';
			html += '<p class="radious_img"></p><p class="go_on"></p><div class="true_flase"><a href="javascript:void(0);" class="true_qr">朕知道了</a></div></div>';
			$(".ttuika").after(html);
			return false;
		}
		var code = $("#mobile_code").val();
		if( code == '' || !(_numberRex.test(code)) ){
			var html = '<div id="overDiv"></div><div id="diolo_warp" class="diolo_warp diolo_txtx">';
			html += '<p class="txt18">哎呀～验证码格式不正确！</p><p class="txt18 aiyiw"></p>';
			html += '<p class="radious_img"></p><p class="go_on"></p><div class="true_flase"><a href="javascript:void(0);" class="true_qr">朕知道了</a></div></div>';
			$(".ttuika").after(html);
			return false;
		}
		var from_user_id = $("#from_user_id").val();
		var standard_id = $("#standard_id").val();
		$(this).attr('disabled', true);
		$.post("/dev/standard/getcoupon",{mobile:mobile,code:code,from_user_id:from_user_id,standard_id:standard_id},function(result){
			var data = eval("(" + result + ")");
			if (data.ret == '1'){
				$("#overDiv").hide();
				$("#diolo_warp").hide();
				var html = '<div id="overDiv"></div><div id="diolo_warp" class="diolo_warp diolo_txtx">';
				html += '<p class="txt18">哎呀～系统错误！</p><p class="txt18 aiyiw"></p>';
				html += '<p class="radious_img"></p><p class="go_on"></p><div class="true_flase"><a href="javascript:void(0);" class="true_qr">朕知道了</a></div></div>';
				$(".ttuika").after(html);
				$("#button_getcoupon").attr('disabled', false);
			}else if(data.ret == '2'){
				$("#overDiv").hide();
				$("#diolo_warp").hide();
				var html = '<div id="overDiv"></div><div id="diolo_warp" class="diolo_warp diolo_txtx">';
				html += '<p class="txt18">哎呀～验证码错误！</p><p class="txt18 aiyiw"></p>';
				html += '<p class="radious_img"></p><p class="go_on"></p><div class="true_flase"><a href="javascript:void(0);" class="true_qr">朕知道了</a></div></div>';
				$(".ttuika").after(html);
				$("#button_getcoupon").attr('disabled', false);
			}else if(data.ret == '3'){
				$("#overDiv").hide();
				$("#diolo_warp").hide();
				var html = '<div id="overDiv"></div><div id="diolo_warp" class="diolo_warp diolo_txtx">';
				html += '<p class="txt18">哎呀～您已领取过优惠券！</p><p class="txt18 aiyiw"></p>';
				html += '<p class="radious_img"></p><p class="go_on"></p><div class="true_flase"><a href="javascript:void(0);" class="true_qr">朕知道了</a></div></div>';
				$(".ttuika").after(html);
				$("#button_getcoupon").attr('disabled', false);
			}else if(data.ret == '4'){
				$("#overDiv").hide();
				$("#diolo_warp").hide();
				var html = '<div id="overDiv"></div><div id="diolo_warp" class="diolo_warp diolo_txtx">';
				html += '<p class="txt18">哎呀～优惠券已发完了！</p><p class="txt18 aiyiw"></p>';
				html += '<p class="radious_img"></p><p class="go_on"></p><div class="true_flase"><a href="javascript:void(0);" class="true_qr">朕知道了</a></div></div>';
				$(".ttuika").after(html);
				$("#button_getcoupon").attr('disabled', false);
			}else{
				window.location = "/dev/standard/couponsuccess?user_id="+from_user_id+"&standard_id="+standard_id;
			}
		});
		
	});
	
});

var CountDown = function() {
    var mobile = $("#mobile").val();
    var short_mobile = mobile.substring(7);
    var html = '短信验证码已发至您的尾号<span>'+short_mobile+'</span>的手机';
	$("#invest_standard_getcode").attr("disabled", true);
    $("#invest_standard_getcode").val("重新获取 ( " + count + " ) ");
    $("#send_mobile").html(html);
    if (count <= 0) {
        $("#invest_standard_getcode").val("获取验证码").removeAttr("disabled");
        clearInterval(countdown);
    }
    count--;
};

var CountDown_back = function() {
	$("#reback_standard_getcode").attr("disabled", true);
    $("#reback_standard_getcode").val("重新获取 ( " + count + " ) ");
    if (count <= 0) {
        $("#reback_standard_getcode").val("获取验证码").removeAttr("disabled");
        clearInterval(CountDown_back);
    }
    count--;
};

var CountDown_getcoupon = function() {
	$("#getcoupon_sendmobile").attr("disabled", true);
    $("#getcoupon_sendmobile").val("重新获取 ( " + count + " ) ");
    if (count <= 0) {
        $("#getcoupon_sendmobile").val("获取验证码").removeAttr("disabled");
        clearInterval(CountDown_getcoupon);
    }
    count--;
};