        <div class="Hcontainer">
        	<img src="/images/title.png" width="100%"/>
           <div class="con">
           		<div class="details">
                	<p class="mb50 n44 text-center lh"><img src="/images/icon_unvalid3.png" class="w8 mr2">您的此次借款失效</p>
                    <div class="row mb30">
                        <div class="col-xs-4 cor n26">状态</div>
                        <div class="col-xs-8 text-right n26 "><span class="red"><?php if(($loaninfo->status=='3') || ($loaninfo->status=='15')){ echo '驳回';}else if($loaninfo->status=='4'){ echo '失效';}else if($loaninfo->status=='7'){ echo '申请提现驳回';}else if($loaninfo->status=='17'){ echo '已取消';}?></span></div>
                    </div>
                    <div class="border_bottom_1"></div>
                    <div class="adver border_bottom_1 border_top_1">
                    	<div class="row mb30">
                        	<div class="col-xs-4 cor n26">借款金额</div>
                        	<div class="col-xs-4 nPad">
                      	<?php if($business_type == 2):?>
						  
						  <div class="assureC">担保</div>
						  
						   <?php endif;?>
						   </div>
                            <div class="col-xs-4 text-right n26"><span class="red">&yen;<?php echo sprintf('%.2f',$loaninfo->amount);?></span></div>
                        </div>
                        <div class="row mb30">
                            <div class="col-xs-4 cor n26"><?php if($loaninfo->status!=4&&$loaninfo->status!=17):?>驳回时间<?php else:?>失效时间<?php endif;?></div>
                            <div class="col-xs-8 text-right n26 "><span class="red"><?php echo date('Y年m月d日 H:i:s',  strtotime($loaninfo->last_modify_time));?></span></div>
                        </div>
                        <?php if($loaninfo->status!=4&&$loaninfo->status!=17):?>
                            <div class="row mb30">
                                <div class="col-xs-4 cor n26">驳回理由</div>
                                <div class="col-xs-8 text-right n26 " style="text-align: left;"><span class="red"><?php echo empty($loan_flows->reason)?'不符合借款标准':$loan_flows->reason;?></span></div>
                            </div>
                        <?php endif;?>
                    </div>
                </div>
                <img src="/images/bottom.png" width="100%" style="vertical-align:top"/>
                <a href="/dev/loan"><button class="btn mt20" style="width:100%">再次借款</button></a>
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