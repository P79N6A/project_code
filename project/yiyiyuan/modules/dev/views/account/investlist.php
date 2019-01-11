<div class="tzjl_html">
    <div class="tzjl_title">
    	    	<a href="/dev/account/standardlist?user_id=<?php echo $user_id;?>">园丁计划</a><a class="active one" href="/dev/account/investlist?user_id=<?php echo $user_id;?>">好友</a><a href="/dev/account/xhblist?user_id=<?php echo $user_id;?>">先花宝</a>
    </div>
      
        <div class="haoyoufrien" style="margin-top:10px;">
        <?php if( !empty($investlist) ) {?>
        
         <?php foreach ($investlist as $key=>$investinfo):?>
         <a class="click_link" href="/dev/account/investdetail?user_id=<?php echo $user_id;?>&invest_id=<?php echo $investinfo['invest_id'];?>">
        <div class="record_contents">
            <div class="content_lefts">
                <dl>
                    <dt><img src="<?php echo $investinfo['head'];?>" width="90%"/></dt>
                    <dd>
                        <p class="left_ddps"><span><?php echo $investinfo['user']['realname'];?></span>
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
                        <p class="left_ddates"><?php echo $investinfo['invest_time'];?></p>
                    </dd>
                </dl>
            </div>
            <div class="content_rights"><span><?php echo sprintf("%.2f", $investinfo['amount']);?></span> <em>元</em> <img src="/images/jiantou33.png"></div>
        </div>
		</a>
		<?php endforeach;?>
		<?php }else{?>
		 	
        <img src="/images/scarer.png" style="width:40%; margin-left:30%; margin-top:100px;">
        <p style="text-align:center; font-size:14px;">您当前没有投资</p>
		<?php }?>
        
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