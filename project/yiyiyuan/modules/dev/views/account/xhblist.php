 <div class="tzjl_html">
    <div class="tzjl_title">
    	    	<a href="/dev/account/standardlist?user_id=<?php echo $user_id;?>">园丁计划</a><a href="/dev/account/investlist?user_id=<?php echo $user_id;?>">好友</a><a class="active one" href="/dev/account/xhblist?user_id=<?php echo $user_id;?>">先花宝</a>
    </div>
 
    <div class="friend_tzsy" style="margin-top:10px;">
    	<?php if(!empty($user_credit_invest)):?>
        
        <?php foreach ($user_credit_invest as $key=>$value):?>
        <div class="xhb_tzsy">
            <div class="tzsy_txtdate">
                <div class="txtdate_left name_daxie"><?php if($value['type'] == 1):?>投资<?php else:?>赎回<?php endif;?><?php echo sprintf('%.2f',$value['amount']); ?>点</div>
                <div class="txtdate_right"><?php echo date('G'.':'.'i'.' n'.'月'.'j'.'日', strtotime($value['create_time']));?></div>
            </div>
        </div>
        <?php endforeach;?>
        
        <?php else:?>
        
        <img src="/images/scarer.png" style="width:40%; margin-left:30%; margin-top:100px;">
        <p style="text-align:center; font-size:14px;">您当前没有投资</p>
        
		<?php endif;?>
        
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