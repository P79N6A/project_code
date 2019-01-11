  <div class="Investment_record">
        <div class="record_title">
            <h3 class="yuan_sy">已收益（元）</h3>
            <p class="record_titimg"></p>
            <p class="shouyi_yu"><?php echo number_format($accountinfo['total_income'], 2, '.', '');?></p>
        </div>
         <?php if( $investlist ) {?>
         <?php foreach ($investlist as $key=>$investinfo):?>
        <a class="click_link" href="/dev/account/investdetail?user_id=<?php echo $user_id;?>&invest_id=<?php echo $investinfo['invest_id'];?>"><div class="record_content">
            <div class="content_left">
                <dl>
                    <dt><img src="<?php echo $investinfo['head'];?>" width="90%"/></dt>
                    <dd>
                        <p class="left_ddp"><span><?php echo $investinfo['user']['realname'];?> </span>
                        <?php if ($investinfo['profit_status'] == 1): ?>
						<span class="red goon">待收益</span>
						<?php elseif ($investinfo['profit_status'] == 2): ?>
						<span class="red goon">收益中</span>
						<?php elseif ($investinfo['profit_status'] == 4): ?>
						<span class="red nobegin">失效</span>
						<?php elseif ($investinfo['profit_status'] == 3): ?>
						<span class="red">已收益</span>
						<?php endif; ?>
						</p>
                        <p class="left_ddate"><?php echo $investinfo['invest_time'];?></p>
                    </dd>
                </dl>
            </div>
            <div class="content_right"><span><?php echo sprintf("%.2f", $investinfo['amount']);?></span> <em>元</em> <img src="/images/jiantou.png"></div>
        </div></a>
        <?php endforeach;?>
 		<?php }else{?>
 			<div class="text-center empty">
	        	<img src="/images/dev/empty.png" width="53.1%" style="display:block; width:55%; margin:0 auto;"/>
	            <p class="n40" style="text-align:center; margin-top:10px;">您还没有投资！</p>
	        </div>
 	   <?php }?>
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