
    <div class="Hcontainer nP">
        <div class="tz_main">
            <img src="/images/xhb_head.png" width="100%">
            <div class="xhb_head on">
                <div class="col-xs-12 nPad">
                    <div class="col-xs-6 text-center">
                        <p class="n24 grey2">预计收益&nbsp;&nbsp;&nbsp;点／天</p>
                        <p class="n72 red" id="yuji_xhb_profit">0.00</p>
                    </div>
                    <div class="col-xs-6 text-center">
                        <p class="n24 grey2">年化收益</p>
                        <p class="n72 red">5%</p>
                    </div>
                </div>
            </div>
            <div class="xhb_head">
                <div class="col-xs-12 nPad">
                    <div class="col-xs-6 text-center">
                        <p class="n24 grey2">已收益&nbsp;&nbsp;&nbsp;点</p>
                        <p class="n72 red"><?php echo sprintf("%.2f", $stat_info['total_income']);?></p>
                    </div>
                    <div class="col-xs-6 text-center">
                        <p class="n24 grey2">年化收益</p>
                        <p class="n72 red">5%</p>
                    </div>
                </div>
            </div>
            <div class="xhb_tab">
                <div class="col-xs-12 nPad mb20">
                    <div class="col-xs-6 item on">投资</div>
                    <div class="col-xs-6 item">我要赎回</div>
                </div>
                <div class="clearfix"></div>
                <div class="tz_main">
                    <div class="tab_item on">
                        <p class="n24">可投资金额<span class="red n36" id='hinvest'><?php echo sprintf("%.2f", $current_amount);?>点</span></p>
                        <input type="text" placeholder="输入投资金额" id="invest_amount" maxlength=10>
						<input type="hidden" id="account_current_amount" value="<?php echo sprintf("%.2f", $current_amount);?>" />
                        <button type="submit" class="btn mt20" id="btn_confirm">确定投资</button>
                        <!--<p class="text-right n20"><input type="checkbox" id="agree_xieyi" checked="checked"/>阅读并同意<a href="/dev/investxhb/agreement">《先花一亿元先花宝投资协议》</a></p>-->
						<div class="n26 mt20">
						<input type="checkbox" checked="checked" id="agree_xieyi" class="regular-checkbox">
						<label for="agree_xieyi"></label>
						阅读并同意
						<a href="/dev/investxhb/agreement" class="underL aColor">《先花一亿元先花宝投资协议》</a>
					    </div>

                    </div>
                    <div class="tab_item">
                        <p class="n24">可赎回金额<span class="red n36"><?php echo sprintf("%.2f", $stat_info['total_amount']);?>点</span></p>
                        <input type="text" placeholder="输入赎回金额" id="reback_amount" maxlength=10>
						<input type="hidden" id="total_amount" value="<?php echo sprintf("%.2f", $total_amount);?>" />
                        <button type="submit" class="btn mt20" id="btn_reback">确定赎回</button>
                        <p class="n26 mt20">＊投资赎回后，本金与收益将立即返还至您的账户</p>
                    </div>
                    
                </div>
            </div> 
        </div>
        <!-- 弹层 -->
        <div class="Hmask" style="display:none;"></div>
        <!-- 弹层1 -->
        <div class="xhb_layer" style="display:none;" id="layer1">
            <p class="n30 grey2 text-center">投资额度<span class="red n36 text-center">不能大于</span>当前额度哦！</p>
            <p class="text-center n48 grey2">快去解冻更多额度吧</p>
            <div class="line mt20">
                <em class="l_l"></em><i></i><em class="l_r"></em>
            </div>
            <div class="clearfix"></div>
            <p class="n24 text-center grey3 mt20">
                <span class="red2">＊提额通道：</span>一亿元首页》更多服务》解冻攻略
            </p>
            <div class="col-xs-6">
                <button type="submit" class="bgrey btn mt20" id="back">返回</button>
            </div>
            <div class="col-xs-6">
                <a href='/dev/account/remain'><button type="submit" class="btn mt20">立即提额</button></a>
            </div>
        </div>
        <!-- 弹层2 -->
        <div class="xhb_layer" style="display:none;" id="layer2">
            <p class="n48 grey2 text-center">没有额度啦～！</p>
            <p class="text-center n48 grey2">快去解冻更多额度吧</p>
            <div class="line mt20">
                <em class="l_l"></em><i></i><em class="l_r"></em>
            </div>
            <div class="clearfix"></div>
            <p class="n24 text-center grey3 mt20">
                <span class="red2">＊提额通道：</span>一亿元首页》更多服务》解冻攻略
            </p>
            <div class="col-xs-6">
                <button type="submit" class="bgrey btn mt20" id='back1'>返回</button>
            </div>
            <div class="col-xs-6">
                <a href='/dev/account/remain'><button type="submit" class="btn mt20">立即提额</button></a>
            </div>
        </div>                               
   </div>
 <script>
    $(document).ready(function(){        
        $('.Hcontainer .xhb_tab .item').each(function(index){
            $(this).click(function(){
                $('.Hcontainer .xhb_tab .item').removeClass('on');
                $(this).addClass('on');
                $('.Hcontainer .xhb_tab .tab_item').removeClass('on');
                $('.Hcontainer .xhb_tab .tab_item').eq(index).addClass('on');
				$('.Hcontainer .xhb_head').removeClass('on');
                $('.Hcontainer .xhb_head').eq(index).addClass('on');
            });
        });
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
    </script>

<script>
$(document).ready(function(){
$("#btn_confirm").click(function(){
	var amount = $("#invest_amount").val();
	var agree_xieyi = $("#agree_xieyi").is(":checked");
	if(agree_xieyi)
	{   
		var hinvest = parseInt($("#hinvest").html());
		//alert(hinvest);
		if(hinvest=='0'){
          $('#layer2').show();
	      $('.Hmask').show();
		  return false;
		}

		var account_current_amount = $("#account_current_amount").val();
		if(amount == '' || amount == null)
		{
			alert("请输入投资金额");
			return  false ;
		}
		if(!amount.match(/^[1-9]*[1-9][0-9]*$/)){
			alert("投资金额必须是整数");
			return  false ;
		}else if(parseInt(amount) > parseInt(account_current_amount)){
			//alert("投资金额不能大于当前可用额度");
			$('#layer1').show();
			$('.Hmask').show();
			return  false ;
		}else if(parseInt(amount) > 4000){
			alert("投资先花宝的额度不能超过4000点哦~，试试去投资熟人吧！");
			return  false ;
		}
		else{
			$(this).attr('disabled', true);
			 $.getJSON("/dev/investxhb/confirm",{amount:amount,action:'1'},function(json){
					if(json.status =='success'){
						window.location.href="/dev/investxhb/success";    
					}else if(json.status == 'more')
					{
						 alert("投资先花宝的额度不能超过4000点哦~，试试去投资熟人吧！");
						 $("#btn_confirm").removeAttr("disabled");
					}
					else{
					 alert("操作处理出错，请重试！");
					 $("#btn_confirm").removeAttr("disabled");
					}
				}); 
		}
	}
	else
	{
		alert('您需要同意投资协议才能投资')
		return false;
	}
});

$('#back').on('click',function(){
  $('#layer1').hide();
  $('.Hmask').hide();
});

$('#back1').on('click',function(){
  $('#layer2').hide();
  $('.Hmask').hide();
});
	
});
</script>

<script>
$(document).ready(function(){
$("#btn_reback").click(function(){
	var amount = $("#reback_amount").val();
	//alert(amount);
	var total_amount = $("#total_amount").val();
	if(!amount.match(/^\d+$/g)){
		alert("请输入投资金额");
		return  false ;
	}else if(parseInt(amount) > parseInt(total_amount)){
		alert("赎回金额不能大于总投资额度");
		return  false ;
	}else{
		 $.getJSON("/dev/investxhb/reback",{amount:amount,action:'1'},function(json){
				if(json.status =='success'){
					window.location.href="/dev/investxhb/suc";    
				}else{
				 alert("操作处理出错，请重试！");
				}
			}); 
	}
})
	
})
</script>

<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
  wx.config({
	debug: false,
	appId: '<?php echo $jsinfo['appid'];?>',
	timestamp: <?php echo $jsinfo['timestamp'];?>,
	nonceStr: '<?php echo $jsinfo['nonceStr'];?>',
	signature: '<?php echo $jsinfo['signature'];?>',
	jsApiList: [
		'hideOptionMenu'
	  ]
  });
  
  wx.ready(function(){
	  wx.hideOptionMenu();
	});
</script>