<div class="Hcontainer nP">
<script  src='/app/st/statisticssave?type=20'></script>
            <div class="main">
            	<div class="title">
                    <span>可使用</span>
                    <div class="link_right">
                         <img src="/images/icon_ques.png" class="icon_ques"> 
                        <a href="javascript:void(0);" id="use_rules" class="link">使用规则</a>  
                    </div>
                </div>
                <?php if(!empty($couponlist_use)):?>
                <?php foreach ($couponlist_use as $key=>$value):?>
                <div class="available">
                    <div class="price_left"><?php if($value['val'] == 0):?><span>全免</span><i>券</i><?php else:?><span>&yen;<?php echo $value['val'];?></span><i>元</i><?php endif;?></div>
                    <div class="price_right"><p class="pBig"><?php echo $value['title'];?></p><p>有效期至：<?php echo date('Y'.'年'.'m'.'月'.'d'.'日', (strtotime($value['end_date'])-24*3600));?><br /><?php if($value['limit'] == 0):?>不限金额<?php else:?>满<?php echo $value['limit'];?>元可用<?php endif;?></p></div>
                </div>
				<?php endforeach;?>
                <?php endif;?>
                <?php if(!empty($couponlist_overdue)):?>
                <div class="line"><em class="l_l"></em><i></i><em class="l_r"></em></div>
                <div class="clearfix"></div>
                <div class="title">
                    <span>已过期</span>
                </div>
                <?php foreach ($couponlist_overdue as $key=>$value):?>
                <div class="available un">
                    <div class="price_left"><?php if($value['val'] == 0):?><span>全免</span><i>券</i><?php else:?><span>&yen;<?php echo $value['val'];?></span><i>元</i><?php endif;?></div>
                    <div class="price_right"><p class="pBig"><?php echo $value['title'];?></p><p>有效期至：<?php echo date('Y'.'年'.'m'.'月'.'d'.'日', (strtotime($value['end_date'])-24*3600));?><br /><?php if($value['limit'] == 0):?>不限金额<?php else:?>满<?php echo $value['limit'];?>元可用<?php endif;?></p></div>
                </div>
                <?php endforeach;?>
                <?php endif;?> 			 
            </div>
            
            <div class="Hmask" style="display: none;"></div>
            <div class="layer" style="display: none;">
                <h1>优惠券使用规则</h1>
                <div class="content">
                    <p>a.优惠券用作抵扣服务费，不可转让；</p>
                    <p>b.单次交易中，只限使用一张；</p>
                    <p>c.优惠券不设找零，不可兑换现金；</p>
                    <p>d.优惠券在有效期内使用有效；</p>
                    <p>e.最终解释权归先花一亿元所有；</p>
                    <div class="button"><a href="javascript:void(0);" id="account_coupon_i_know" class="aButton">朕知道了</a></div>
                </div>                    
            </div>
          <div class="bottomBtn n36 red text-center"><a href="https://open.weixin.qq.com/connect/oauth2/authorize?appid=<?php echo Yii::$app->params['AppID'];?>&redirect_uri=<?php echo Yii::$app->params['app_url'];?>/dev/loan&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect"><span>立即使用</span></a></div>   
        </div>