<div class="Hcontainer nP">
            <header class="header white">
                <p class="n26">状态：</p>
                <p class="n36 mb20 text-center">借款已逾期</p>
                <p class="n26 text-right">请尽快对借款人进行催收 否则账户将处于冻结状态</p>
            </header>
        	<img src="/images/title.png" width="100%"/>
            <div class="con">
           		<div class="details">
                    <div class="adver border_bottom_1">
                        <div class="row mb30">
                            <div class="col-xs-4 cor n26">担保金额:</div>
                            <div class="col-xs-8 text-right n26"><span class="red">&yen;<?php echo number_format($loan_info['amount'],2,'.','');?></span></div>
                        </div>
                        <div class="row mb30">
                            <div class="col-xs-4 cor n26">借款人姓名:</div>
                            <div class="col-xs-8 text-right n26"><?php echo $userinfo['realname'];?></div>
                        </div>
                        <div class="row mb30">
                            <div class="col-xs-4 cor n26">学校:</div>
                            <div class="col-xs-8 text-right n26"><?php echo $userinfo['school'];?></div>
                        </div>
                        <div class="row mb30">
                            <div class="col-xs-4 cor n26">身份证号:</div>
                            <div class="col-xs-8 text-right n26"><?php echo $userinfo['identity'];?></div>
                        </div>
                        <div class="row mb30">
                            <div class="col-xs-4 cor n26">入学年份:</div>
                            <div class="col-xs-8 text-right n26"><?php echo $userinfo['school_time'];?></div>
                        </div>
                        <div class="row mb30">
                            <div class="col-xs-4 cor n26">联系电话:</div>
                            <div class="col-xs-8 text-right n26"><?php echo $userinfo['mobile'];?></div>
                        </div>
                    </div>
                    <div class="adver">
                        <div class="row">
                            <div class="col-xs-5 cor n26">应还款日期:</div>
                            <div class="col-xs-7 text-right n26"><?php echo date('Y-m-d',strtotime($loan_info['end_date'])-24*3600);?></div>
                        </div>
                    </div>
                    <div class="adver">
                        <div class="row">
                            <div class="col-xs-5 cor n26">逾期天数:</div>
                            <div class="col-xs-7 text-right n26"><?php echo ceil((time()-strtotime($loan_info['end_date']))/86400);?>天</div>
                        </div>
                    </div>
                    <div class="adver">
                        <div class="row">
                            <div class="col-xs-5 cor n26">逾期罚息:</div>
                            <div class="col-xs-7 text-right n26"><span class="red">&yen;<?php echo number_format($loan_info['chase_amount']-$loan_info['amount']-$loan_info['interest_fee']-$loan_info['withdraw_fee'],2,'.','');?></span></div>
                        </div>
                    </div>
                    <div class="adver">
                        <div class="row">
                            <div class="col-xs-5 cor n26">应还款金额:</div>
                            <div class="col-xs-7 text-right n26"><span class="red n36 lh">&yen;<?php echo number_format($loan_info['chase_amount']+$loan_info['collection_amount'],2,'.','');?></span></div>
                        </div>
                    </div>
                </div>
                <img src="/images/bottom.png" width="100%" style="vertical-align:top"/>
                <input type='hidden' name='mobile' id='mobile' value="<?php echo $userinfo['mobile'];?>">
                <button class="btn1 mt20" style="width:100%" id='fdx'>短信催收</button>
                <a href="tel:<?php echo $userinfo['mobile'];?>" class="btn mt20 mb40" style="width:100%">电话催收</a>
           </div>
		   <div class="Hmask" style="display:none;"></div>
		   <div class="layer_border text-center succ" style="display: none;" id="succ">
				<p class="n30 mb30">发送成功！</p>
			</div>

		   <div class="Hmask" style="display:none;"></div>
		   <div class="layer_border text-center succ" style="display: none;" id="fa">
				<p class="n30 mb30">12个小时内不能重复发送！</p>
			</div>
       </div>
    <script>
    $('#fdx').click(function(){
	   var mobile = $('#mobile').val();
	   $.post("/dev/guarantoraccount/mobile",{mobile:mobile},function(result){
		//alert(result);
		//alert('已发送');
		if(result=='1'){
			$('.Hmask').show();
			$('#succ').show();
			setTimeout(function(){
				$('.Hmask').hide();
				$('#succ').hide();
			},2000);
		}else{
		   $('.Hmask').show();
			$('#fa').show();
			setTimeout(function(){
				$('.Hmask').hide();
				$('#fa').hide();
			},2000); 
		}
	});
	
});
</script>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
    wx.config({
        debug: false,
        appId: '<?php echo $jsinfo['appid']; ?>',
        timestamp: <?php echo $jsinfo['timestamp']; ?>,
        nonceStr: '<?php echo $jsinfo['nonceStr']; ?>',
        signature: '<?php echo $jsinfo['signature']; ?>',
        jsApiList: [
            'hideOptionMenu'
        ]
    });

    wx.ready(function() {
        wx.hideOptionMenu();
    });
</script>