 <div class="message">
          <a class="messxx">消息</a>
          <div class="line"></div>
          <a class="messtg">公告</a>
      </div>
      <div class="moveline"></div>
      <div class="messtg_xiax">
          <?php if (!empty($userinfo)): ?> 
					<?php foreach ($userinfo as $key => $v): ?>
	            	
                    <div class="mecon_one">
            <p class="meone_one">消息</p>
            <p class="meone_two">邀请好友<?php echo $v['user']['realname']; ?> 获佣金<em><?php echo number_format($v['profit_amount'], 2, ".", ""); ?></em>RMB</p>
            <div class="meone_three">
              <div></div>
              <span><?php echo $v['create_time'];?></span>
            </div>
          </div>
					<?php endforeach; ?>
					<?php endif; ?> 
                    
					<?php if (!empty($loaninfo)): ?> 
					<?php foreach ($loaninfo as $key => $v): ?>
	            	   <div class="mecon_one">
            <p class="meone_one">消息</p>
            <p class="meone_two"><?php echo $v['loan']['user_id']; ?>借款<?php echo number_format($v['loan']['amount'], 2, ".", ""); ?>元 获佣金<em><?php echo number_format($v['profit_amount'], 2, ".", ""); ?></em>RMB</p>
            <div class="meone_three">
              <div></div>
              <span><?php echo $v['create_time'];?></span>
            </div>
          </div> 
					<?php endforeach; ?>
					<?php endif; ?> 

					<?php if (!empty($investinfo)): ?> 
					<?php foreach ($investinfo as $key => $v): ?>
	            	   <div class="mecon_one">
            <p class="meone_one">消息</p>
            <p class="meone_two"><?php echo $v['invest']['user_id']; ?>投资<?php echo number_format($v['invest']['amount'], 2, ".", ""); ?>元 获佣金<em><?php echo number_format($v['profit_amount'], 2, ".", ""); ?></em>RMB</p>
            <div class="meone_three">
              <div></div>
              <span><?php echo $v['create_time'];?></span>
            </div>
          </div>
					<?php endforeach; ?>
					    </div>
        	</div>
					<?php endif; ?> 
      </div>
      <div class="messtg_con" style="display:none;">
          <?php if (!empty($gao)): ?> 
		  <?php foreach ($gao as $key => $v): ?>
          <div class="mecon_one">
            <p class="meone_one"><?php echo $v->title;?></p>
            <p class="meone_two"><?php echo $v->content;?></p>
            <div class="meone_three">
              <div></div>
              <span><?php echo $v->create_time;?></span>
            </div>
          </div>
          <?php endforeach; ?>
		  <?php endif; ?> 
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
            'closeWindow',
            'hideOptionMenu'
        ]
    });

    wx.ready(function() {
        wx.hideOptionMenu();
    });
</script>