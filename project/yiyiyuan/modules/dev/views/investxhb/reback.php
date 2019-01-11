       <div class="container pb100 text-center">  
        	<div class="content text-left">         
                <input type="text" id="reback_amount" placeholder="输入赎回金额" style="width:100%;"/>
                <p class="n22 mt10">＊投资赎回后，本金与收益将立即返还至您的先花一亿元账户</p>
            </div> 
            <div class="con">
           		<div class="details mt20 border_top text-left pl">
                    <p class="mb30">年化收益：5%</p> 
                    <p class="mb30">已投资金额：<?php echo sprintf("%.2f", $stat_info['total_amount']);?>点</p> 
                    <p class="n30 red">已收益：<?php echo sprintf("%.2f", $stat_info['total_income']);?>点</p>
                </div>
                <img src="/images/dev/bottom.png" width="100%" style="vertical-align:top"/>
                <input type="hidden" id="total_amount" value="<?php echo sprintf("%.2f", $total_amount);?>" />
                <button id="btn_reback" class="btn mt40 " style="width:100%">确认赎回</button>
            <p class="text-left mt10">预计到账时间：<?php echo date('Y'.'年'.'m'.'月'.'d'.'日');?></p>
           </div>
       </div>
<script>
$(document).ready(function(){
$("#btn_reback").click(function(){
	var amount = $("#reback_amount").val();
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
					window.location.href="/dev/invest/index";    
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