<div class="tzjl_html">
    <div class="tzjl_title">
    	<a class="active one" href="/dev/account/standardlist?user_id=<?php echo $user_id;?>">园丁计划</a><a href="/dev/account/investlist?user_id=<?php echo $user_id;?>">好友</a><a href="/dev/account/xhblist?user_id=<?php echo $user_id;?>">先花宝</a>
    </div>
    <div class="tzjl_content">
    <?php if(!empty($standard_list)):?>
    <?php foreach ($standard_list as $key=>$value):?>
    <a href="/dev/standard/investdetail?standard_id=<?php echo $value->information->id;?>"/>
    	<div class="tzjl_box">
    		<div class="tzjl_boxleft">
    			<div class="boxleft_first">
    				<h3><?php echo $value->information->name;?></h3>
    				<span><?php echo date('H'.':'.'i'.' n'.'月'.'j'.'日', strtotime($value->last_modify_time));?></span>
    				<span></span>
    			</div>
    			<div class="boxleft_send">
    				<p class="fren_green"><span>收益</span><span>金额</span><span>年化</span><span>周期</span></p>
                    <p class="fren_point"><span><?php echo sprintf("%.2f", $value->achieving_interest);?></span><span><?php echo intval($value->total_onInvested);?>点</span><span><?php echo sprintf("%.2f", $value->information->yield);?>%</span><span><?php echo $value->information->cycle;?>天</span></p>
    			</div>
    		</div>
    		<?php if($value->information->status == 'AUDITED'):?><div class="tzjl_boxright">待收益</div><?php elseif($value->information->status == 'SUCCEED'):?><?php if($value->total_onInvested_share > 0):?><?php if(date('Y-m-d H:i:s') >= $value->information->start_date):?><div class="tzjl_boxright">收益中</div><?php else:?><div class="tzjl_boxright">待收益</div><?php endif;?><?php else:?><div class="tzjl_boxright green">已赎回</div><?php endif;?><?php elseif($value->information->status == 'FINISHED'):?><?php if($value->achieving_interest > 0):?><div class="tzjl_boxright blue">已收益</div><?php else:?><div class="tzjl_boxright green">已赎回</div><?php endif;?><?php endif;?>
    	</div>
    </a>
    <?php endforeach;?>
    <?php else:?>
    <img src="/images/scarer.png" style="width:40%; margin-left:30%; margin-top:100px;">
    <p style="text-align:center; font-size:14px;">您当前没有投资</p>
    <?php endif;?>
        
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