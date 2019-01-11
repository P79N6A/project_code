<div class="Hcontainer nP">
            <header class="header white">
                <p class="n26">状态：</p>
                <p class="n36 mb20 text-center">借款审核中</p>
                <p class="n26 text-right">通过审核后将直接放款给借款人~</p>
            </header>
        	<img src="/images/title.png" width="100%"/>
            <div class="con">
           		<div class="details">
                    <div class="adver">
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
                            <div class="col-xs-8 text-right n26"><?php echo $loan_info['days'] ;?>天</div>
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
                            <div class="col-xs-4 cor n26">预计收益：</div>
                            <div class="col-xs-8 text-right n26"><span class="red">&yen;<?php echo number_format($loan_info['amount']*0.01,2,'.','')?></span></div>
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