    <div class="friend_tzsy">
        <div class="frien_title">历史标的</div>
        <?php if(!empty($standard_list)):?>
        <?php foreach ($standard_list as $key=>$value):?>
        <a class="link_click" href="/dev/standard/historydetail?standard_id=<?php echo $value->id;?>&user_id=<?php echo $user_id;?>"><div class="frerecord_content">
            <div class="frecontent_left gudwd">
                <dl>
                    <dd>
                        <p class="left_title"><?php echo \Common::truncate_utf8_string($value->name, 6);?></p>
                        <p class="left_datet"><?php echo date('n'.'月'.'j'.'日',strtotime($value->online_date))?></p>
                    </dd>
                </dl>
            </div>
            <div class="frecontent_right history">
                <div class="frecontent_conte">
                    <p class="fren_green"><span>总额</span><span class="bigtxt">年化收益率</span><span>期限</span></p>
                    <p class="fren_point"><span class="bfz33"><?php echo ($value->financed_amount/10000)?>万</span><span class="bfz40"><?php echo sprintf('%.2f',$value->yield); ?>%</span><span><?php echo $value->cycle;?>天</span></p>
                </div>
            </div>
            <div class="xingqcon">详情</div>
        </div>
        </a>
        <?php endforeach;?>
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