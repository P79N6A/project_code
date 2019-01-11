<div class="Hcontainer nP">
            <header class="header white">
                <p class="n26">状态：</p>
                <p class="n36 mb20 text-center">等待还款</p>
                <p class="n26 text-right">请注意借款人的还款情况~</p>
            </header>
        	<img src="/images/title.png" width="100%"/>
            <div class="con">
           		<div class="details">
                    <div class="adver border_bottom_1">
					     <div class="row mb30">
                            <div class="col-xs-4 cor n26">借款人：</div>
                            <div class="col-xs-8 text-right n26"><?php echo $userinfo1['realname'];?></div>
                        </div>
                        <div class="row mb30">
                            <div class="col-xs-4 cor n26">学校：</div>
                            <div class="col-xs-8 text-right n26"><?php echo $userinfo1['school'] ;?></div>
                        </div>
						<div class="row mb30">
                            <div class="col-xs-4 cor n26">担保金额：</div>
                            <div class="col-xs-8 text-right n26"><span class="red">&yen;<?php echo number_format($loan_info['amount'],2,'.','');?></span></div>
                        </div>
                        <div class="row mb30">
                            <div class="col-xs-4 cor n26">借款期限：</div>
                            <div class="col-xs-8 text-right n26"><?php echo $loan_info['days'];?>天</div>
                        </div>
                        <div class="row mb30">
                            <div class="col-xs-4 cor n26">借款理由：</div>
                            <div class="col-xs-8 text-right n26"><?php echo $loan_info['desc'];?></div>
                        </div>
                        <div class="row mb30">
                            <div class="col-xs-4 cor n26">联系电话：</div>
                            <div class="col-xs-8 text-right n26"><?php echo $userinfo1['mobile'];?></div>
                        </div>
                        
                        <div class="row mb30">
                            <div class="col-xs-4 cor n26">担保人：</div>
                            <div class="col-xs-8 text-right n26"><?php echo $userinfo['realname']; ?></div>
                        </div>
                    </div>
                    <div class="adver">
                        <div class="row">
                            <div class="col-xs-5 cor n26">应还款日期：</div>
                            <div class="col-xs-7 text-right n26"><?php echo date('Y-m-d',strtotime($loan_info['end_date'])-24*3600) ;?></div>
                        </div>
                    </div>
                    <div class="adver">
                        <div class="row">
                            <div class="col-xs-5 cor n26">应还款金额：</div>
                            <div class="col-xs-7 text-right n26"><span class="red n36 lh">&yen;<?php echo $damount;?></span></div>
                        </div>
                    </div>
                </div>
                <img src="/images/bottom.png" width="100%" style="vertical-align:top"/>
           </div>
       </div>
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