<div class="friend_tzsy">
		<?php if( !empty( $dataList ) ): ?>
        <div class="frien_title">投资好友收益</div>
        <?php else:?>
        <div class="frien_title">没有收益</div>
        <?php endif;?>
        <?php if( !empty( $dataList ) ): ?>
        <?php foreach ( $dataList as $list ): ?>
        <a class="link_click" href="/dev/account/investdetail?user_id=<?php echo $list['user_id'];?>&invest_id=<?php echo $list['invest_id'];?>">
        <div class="frerecord_content">
            <div class="frecontent_left">
                <dl>
                    <dt><img src="<?php echo empty($list['head']) ? '/images/dev/face.png' : $list['head'];?>"></dt>
                    <dd>
                        <p class="left_ddp"><span class="txt_name"><?php echo $list['realname'];?></span><span class="frered"><?php echo date('H:i m月d日',strtotime($list['create_time'])); ?></span></p>
                        <p class="left_ddate"><?php echo \Common::truncate_utf8_string($list['desc'],6);?></p>
                    </dd>
                </dl>
            </div>
            <div class="frecontent_right">
                <div class="frecontent_conte">
                    <p class="fren_green"><span>收益</span><span>投资金额</span></p>
                    <p class="fren_point"><span><?php echo sprintf('%.2f', $list['income']);?></span><span><?php echo sprintf('%.2f', $list['amount']);?>点</span></p>
                </div>
            </div>
        </div>
        </a>
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