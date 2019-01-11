<div class="leijisyi">
    <p class="leijisyi_two"><?php echo !empty($webunion_account)?sprintf("%.2f",$webunion_account->frozen_interest):'0.00' ?><span>RMB</span></p>
    <p class="leijisyi_three"><span>冻结收益</span></p>
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
    $.get('/background/wallet/frozeninterest',{page:page},function(res){
      $('.backwith').append(res.data);
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