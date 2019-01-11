<div class="friend_tzsy">
		<?php if(!empty($standardList)):?>
        <div class="frien_title">信用理财收益</div>
        <?php else:?>
        <div class="frien_title">没有收益</div>
        <?php endif;?>
        <?php if(!empty($standardList)):?>
    	<?php foreach ($standardList as $key=>$value): ?>
        <div class="frerecord_content">
            <div class="frecontent_left gudwd">
                <dl>
                    <dd>
                        <p class="left_title"><?php echo $value->information->name;?></p>
                        <p class="left_datet"><?php echo date('H:i m月d日',strtotime($value->information->create_time)); ?></p>
                    </dd>
                </dl>
            </div>
            <div class="frecontent_right changewd">
                <div class="frecontent_conte">
                    <p class="fren_green syhuy"><span>收益</span><span>金额</span><span>年化</span><span>周期</span></p>
                    <p class="fren_point"><span><?php echo sprintf("%.2f", $value->achieving_interest);?></span><span><?php echo intval($value->total_onInvested);?>点</span><span><?php echo sprintf("%.2f", $value->information->yield);?>%</span><span><?php echo $value->information->cycle;?>天</span></p>
                </div>
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