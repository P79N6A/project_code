
        <div class="container">
           <header class="header white">
           		<p>状态</p>
           		<?php if($investinfo['profit_status'] == 1):?>
                <p class="n36 mb20 text-center">等待获取收益</p>
                <?php elseif($investinfo['profit_status'] == 2):?>
                <p class="n36 mb20 text-center">持续收益中</p>
                <p class="n22 text-right">计息时间：<?php if(!empty($investinfo['start_date'])):?><?php echo $investinfo['start_date'];?><?php else:?>待确定<?php endif;?></p>
                <?php elseif($investinfo['profit_status'] == 3):?>
                <p class="n36 mb20 text-center">已收益</p>
                <p class="n22 text-right">收益时间：<?php echo $end_date;?></p>
                <?php else:?>
                <p class="n36 mb20 text-center">已失效，借款人取消借款</p>
                <?php endif;?>
           </header>
           <img src="/images/dev/title.png" width="100%"/>
           <div class="con">
           		<div class="details">
                	 <div class="row mb20 details_info">
                        <div class="col-xs-2 photo"><img width="100%" src="<?php echo $investinfo['head'];?>"/></div>
                        <div class="col-xs-4 pd b30"><a><?php echo $investinfo['nickname'];?></a></div>
                        <div class="col-xs-6 text-right">周期 <span class="red"><?php echo $investinfo['days'];?></span> 天</div>
                    </div>
                	<p class="mb20 text-center n36"><?php echo $investinfo['desc'];?></p>
                    <div class="adver border_bottom_1">
                    	<div class="row mb30">
                        	<div class="col-xs-4 cor">借款人姓名</div>
                            <div class="col-xs-8 text-right"><?php echo $investinfo['realname'];?></div>
                        </div>
                        <div class="row mb30">
                        	<div class="col-xs-3 cor">身份证号</div>
                            <div class="col-xs-9 text-right"><?php echo $identity;?></div>
                        </div>
                    </div>
                    <div class="adver border_bottom_1 border_top_1">
                        <div class="row mb30">
                        	<div class="col-xs-3 cor">投资金额</div>
                            <div class="col-xs-9 text-right red">&yen;<?php echo sprintf("%.2f", $investinfo['amount']);?></div>
                        </div>
                        <div class="row mb30">
                        	<div class="col-xs-3 cor"><?php if($investinfo['status'] == 2):?>预期收益时间<?php else:?>收益时间<?php endif;?></div>
                            <div class="col-xs-9 text-right"><?php if(empty($investinfo['end_date'])):?>待确定<?php else:?><?php echo $end_date;?><?php endif;?></div>
                        </div>
                    </div>
                    <div class="adver border_top_1">
                    	<div class="row">
                        	<div class="col-xs-4 cor">预期收益</div>
                            <div class="col-xs-8 n50 text-right red">&yen;<?php echo $profit;?></div>
                        </div>
                    </div>
                </div>
                <img src="/images/dev/bottom.png" width="100%" style="vertical-align:top"/>
           </div>
       </div>
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