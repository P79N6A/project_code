 <?php if (!empty($accountinfo)): ?> 
<?php foreach ($accountinfo as $key => $v): ?>
 <a href='/background/webunion/withlist?id=<?php echo $v->id;?>'>
 <div class="txjl_cont">
        <div class="txjl_boxb">
            <div class="disitem txjl_gzgz ">
                <div class="gzgz_tleft">
                    <div class="disitem txdcard">提现到<?php echo $v->bank->bank_name;?>：<div></div><span><?php echo number_format($v->amount, 2, ".", "");?></span><em>RMB</em></div>
                    <div class="disitem txdtime"><em>尾号</em> <?php echo substr($v->bank->card,-4);?><div></div><span><?php echo $v->create_time;?></span></div>
                </div>
                <div class="gzgz_tright"></div>
            </div> 
        </div>
		</div>
 </a>  
 <?php endforeach; ?>
 <?php else:?>
 <div>
	<img style="display:block;" src="/images/cry1.png">
	<p style="text-align:center; font-size:2.2rem;">无记录！</p>
 </div>
<?php endif; ?> 
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
wx.config({
        debug: false,
        appId: '<?php echo $jsinfo['appid']; ?>',
        timestamp: <?php echo $jsinfo['timestamp']; ?>,
        nonceStr: '<?php echo $jsinfo['nonceStr']; ?>',
        signature: '<?php echo $jsinfo['signature']; ?>',
        jsApiList: [
            'closeWindow',
            'hideOptionMenu'
        ]
    });

    wx.ready(function() {
        wx.hideOptionMenu();
    });
</script>
                    