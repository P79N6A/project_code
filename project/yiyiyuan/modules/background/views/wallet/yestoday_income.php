<div class="wrap yestsyh">
    <div class="yesdate"><span><?php echo $yestoday ?></span></div>
    <section class="borderbottom">
      <div class="left">
        <div><em> <?php echo sprintf("%.2f",$total) ?></em><span>RMB</span></div>
        <div>收益金额</div>
      </div>
      <div class="line"></div>
      <div class="right">
        <div><em> <?php echo sprintf("%.2f",$frozen_total) ?></em><span>RMB</span></div>
        <div>冻结收益</div>
      </div>
    </section>
</div>
<script>
    $('.nav_right').click(function(){
        history.go(-1);
    })
</script>
<script>
  $('.button').click(function(){
    $(this).hide();
    var page = $(this).attr('page');
    $.get('/background/wallet/yestodayincome',{page:page},function(res){
      $('.content').append(res.data);
      if (res.page != 0) {
        $('.button').attr('page',res.page);
        $('.button').show();
      };
    },'json');
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