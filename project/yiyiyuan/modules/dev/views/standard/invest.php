
    <div class="touzibd">
        <div class="touzibiaode_tz">
            <div class="touzbiao_yuan">
                <span class="yuand_left"><img src="/images/title_xhhb.png"><em><?php echo $title;?></em></span>
            </div>
            <div class="touzi_dbe">
                <div class="touzi_dbeleft">担保额度: <em><?php echo intval($userinfo->account->real_guarantee_amount);?>点</em></div>
                <a href="/dev/guarantee/buycard"><div class="touzi_dberight">购买担保卡</div></a>
            </div>
            <div class="touzijine">投资金额: <input placeholder="可投<?php echo $investing_amount;?>点(100点起投)" maxlength="10" id="invest_share"></div>
            <div class="yujsy"><em>预计收益: </em> <span id="achieving_profit">0.00</span>点</div>
            <?php if(!empty($standard_statistics->coupon_id) && ($standard_statistics->coupon_id != 0)):?>
            <div class="youhuijuan">
                <em>优惠卷: </em>
                <span class="tupian_img"><?php echo $standard_statistics->coupon->cycle;?>天<?php echo $standard_statistics->coupon->field;?>倍优惠券</span>
                <a><img src="/images/black_right.png"></a>
            </div>
            <?php else:?>
            <?php if(!empty($couponlist)):?>
            <div class="youhuijuan" id="invest_coupon_use">
                <em class="gray_true">优惠卷: </em>
                <span class="tupian_img emem">使用优惠券可获得双倍收益</span>
                <a><img src="/images/black_right.png"></a>
            </div>
            <?php else:?>
            <div class="youhuijuan">
                <em class="gray_true">优惠卷: </em>
                <span class="tupian_img emem">使用优惠券可获得双倍收益</span>
                <a><img src="/images/black_right.png"></a>
            </div>
            <?php endif;?>
            <?php endif;?>
        </div>
        <?php if(!empty($standard_statistics->coupon_id) && ($standard_statistics->coupon_id != 0)):?>
        <p style="margin-left:8%;">*已使用优惠卷,对当前标的投资始终有效</p>
        <?php endif;?>
        <div class="agree">  <input type="checkbox" checked="checked" name="agree_check" id="agree_check" />  同意 <a>《先花一亿元投资咨询与管理服务协议》</a></div>
         <input type="hidden" name="coupon_id" id="coupon_id" value="<?php if(!empty($standard_statistics->coupon_id)):?><?php echo $standard_statistics->coupon_id;?><?php endif;?>">
         <?php if(!empty($standard_statistics->coupon_id)):?>
         <input type="hidden" name="coupon_days" id="coupon_days" value="<?php echo $standard_statistics->coupon->cycle;?>">
         <input type="hidden" name="coupon_times" id="coupon_times" value="<?php echo $standard_statistics->coupon->field;?>">
         <?php endif;?>
        <input type="hidden" name="standard_id" id="standard_id" value="<?php echo $standard_id;?>">
        <input type="hidden" name="cycle" id="cycle" value="<?php echo $cycle;?>">
        <input type="hidden" name="yield" id="yield" value="<?php echo sprintf('%.2f',$yield); ?>">
        <input type="hidden" name="investing_standard" id="investing_standard" value="<?php echo $investing_amount;?>">
        <input type="hidden" name="guarantee_amount" id="guarantee_amount" value="<?php echo intval($userinfo->account->guarantee_amount);?>">
        <button class="true_touzi" id="standard_confirm_invest">投资</button>
    </div>

<?php if(!empty($couponlist)):?>
    <div class="Hcontainer" style="display:none;">
        <div class="Hmask"></div>
        <div class="layer overflow" style="position:absolute;">
            <div class="boldera">优惠券 <span class="queding" id="invest_coupon_confirm">确定</span></div>
            <div class="content padlr">
            
            <?php foreach ($couponlist as $key=>$value):?>
                <div class="item">
                    <img src="/images/unchoose.png" class="available2">
                    <div class="price_left left3">
                        <p class="black"><?php echo $value->field;?>倍收益<span>券</span></p>
                        <p class="green">担保理财</p>
                    </div>
                    <div class="price_right">
                        <p class="one_one"><?php echo $value->cycle;?>天<?php echo $value->field;?>倍收益券</p>
                        <p class="one_two">自计息日起<?php echo $value->cycle;?>天收益双倍</p>
                        <p class="one_three">有效期至：<?php echo date('Y'.'年'.'n'.'月'.'j'.'日',strtotime($value->end_date));?></p>
                    </div>
                    <input type="radio" value="<?php echo $value->id;?>" days="<?php echo $value->cycle;?>" cycle="<?php echo $value->field;?>"> 
                </div>
             <?php endforeach;?>
                
            </div>                    
        </div>
    </div>
    <?php endif;?>
    

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