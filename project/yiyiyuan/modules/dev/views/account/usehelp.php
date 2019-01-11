<div class="user_real">
        <div class="usere_cont">
            <div class="user_boxb">
                <div class="user_gzgz topradious">
                    <div class="gzgz_left">好友借款券使用规则</div>
                    <div class="gzgz_right"></div>
                </div>
                <div class="user_txtxdt" style="display:none;">
                    1.优惠券用作抵扣服务费，不可转让；<br/>
                    2.单次交易中，只限使用一张；<br/>
                    3.优惠券不设找零，不可兑换现金；<br/>
                    4.优惠券在有效期内使用有效；<br/>
                    5.最终解释权归先花一亿元所有；
                </div>
            </div>
            <div class="user_boxb">
                <div class="user_gzgz bottomline">
                    <div class="gzgz_left ">投资双倍收益券使用规则</div>
                    <div class="gzgz_rights"></div>
                </div>
                <div class="user_txtxdts" style="display:none;">
                	1.优惠券用作投资标的，增加收益，不可转让；<br/>
                    2.投资单个标的（无论几次），只限使用一张，<br/>
                    3.优惠券对单个标的的所有投资均有效<br/>
                    4.若提前赎回投资，该优惠券按已使用处理<br/>
                    5.优惠券在有效期内使用有效；<br/>
                    6.最终解释权归先花一亿元所有；
                </div>
            </div>

        </div>
    </div>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
    $('.usere_cont .user_gzgz .gzgz_right').each(function(){
        $(this).click(function(){
            $(this).toggleClass("two");
            $('.usere_cont .user_txtxdt').toggle();
            
        });
    });    

     $('.usere_cont .user_gzgz .gzgz_rights').each(function(){
        $(this).click(function(){
            $(this).toggleClass("two");
            $('.usere_cont .user_txtxdts').toggle();
        });
    }); 
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