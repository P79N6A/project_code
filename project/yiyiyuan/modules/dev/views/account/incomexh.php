<div class="friend_tzsy">
		<?php if( !empty( $incomeList ) ): ?>
        <div class="frien_title">投资先花宝收益</div>
        <?php else:?>
		<div class="frien_title">没有收益</div>
		<?php endif;?>        
        <?php if( !empty( $incomeList ) ): ?>
        <?php foreach ( $incomeList as $val ): ?>
        <div class="xhb_tzsy">
            <div class="tzsy_txtdate">
                <div class="txtdate_left">＋<?php echo $val['income'];?></div>
                <div class="txtdate_right"><?php echo date('Y年m月d日',strtotime($val['create_time'])); ?></div>
            </div>
        </div>
        <?php endforeach;?>
        <?php else:?>
        <img src="/images/scarer.png" style="width:40%; margin-left:30%; margin-top:100px;">
        <?php endif;?>
        
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