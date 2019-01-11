<div class="lxme">
        
        <div class="disitem lxme_two">
          <img src="/images/lxme_two.png">
          <div>
            <p class="lxtwone">微信咨询：</p>
            <p class="lxtwone2">先花花官方公众号</p>
            <p class="lxtwone3">*工作时间：09:00-18:00</p>
          </div>
          <img style="width:25%;" src="/images/tgym_ewm.png">
        </div>
        <div class="disitem lxme_three">
          <img src="/images/lxme_three.png">
          <div>
            <p class="lxoneone">客服QQ：<em>先花一亿元QQ客服群</em></p>
            <p>&nbsp;</p>
            <p>166793670</p>
          </div>
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
            'closeWindow',
            'hideOptionMenu'
        ]
    });

    wx.ready(function() {
        wx.hideOptionMenu();
    });
</script>