<div class="user_real">
        <div class="usere_cont">
            <div class="user_boxb">
                <div class="user_gzgz topradious">
                    <div class="gzgz_left">好友借款券使用规则</div>
                    <div class="gzgz_right"></div>
                </div>
                <div class="user_txtxdt" style="display:none;">
                    1.优惠券用作抵扣利息，不可转让；<br/>
                    2.单次交易中，只限使用一张；<br/>
                    3.优惠券不设找零，不可兑换现金；<br/>
                    4.优惠券在有效期内使用有效；<br/>
                    5.最终解释权归先花一亿元所有；
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
 	  wx.ready(function(){
 		  wx.hideOptionMenu();
 		});
</script>