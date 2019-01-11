        <div class="container pb100">
        <script  src='/dev/st/statisticssave?type=11'></script> 
           <header class="header">
           		<p class="n22 white">预计收益（点）/天</p>
                <p class="white">
                	<span class="n100" id="yuji_xhb_profit">0.00</span>
                </p>
           </header>
            <header class="header border_top_white">
           		<div class="row white">
                    <div class="col-xs-8 n22">可投资金额： <span class="n40"><?php echo sprintf("%.2f", $current_amount);?></span> 点</div>
                    <div class="col-xs-4 n22 text-right">年化收益：<span class="n40">5</span> %</div>
               </div>
           </header>
           <div class="main">
                <input class="mb100 mt40" type="text" id="invest_amount" maxlength=10 style="width:100%;" placeholder="输入投资金额"/>
                <input type="hidden" id="account_current_amount" value="<?php echo sprintf("%.2f", $current_amount);?>" />
                <button class="btn" id="btn_confirm" style="width:100%">确定</button>
				<p class="text-right n20"><input type="checkbox" id="agree_xieyi" checked="checked"/>阅读并同意<a href="/dev/investxhb/agreement">《先花一亿元先花宝投资协议》</a></p>
            </div>
       </div>
       
<script>
$(document).ready(function(){
$("#btn_confirm").click(function(){
	var amount = $("#invest_amount").val();
	var agree_xieyi = $("#agree_xieyi").is(":checked");
	if(agree_xieyi)
	{
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
			alert("投资金额不能大于当前可用额度");
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
	
});
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