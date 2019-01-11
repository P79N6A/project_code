<div class="messtg_con">
<?php if (!empty($notice)): ?> 
    <?php foreach ($notice as $key => $v): ?>
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
<script>
    $('.nav_right').click(function(){
        window.location.href = '<?php echo $returnUrl ?>';
    })
</script>
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