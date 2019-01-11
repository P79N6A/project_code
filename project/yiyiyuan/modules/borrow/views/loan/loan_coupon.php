<style>
    .alert{
        position: fixed;
        width: 100vw;
        height: 100vh;
        background: rgba(0,0,0,.3);
        z-index: 999;
        top: 0;
        left: 0;
    }
    .box{
        width: 80vw;
/*        height: 50vw;*/
        background: #ffffff;
        margin: 50vw auto 0;
        border-radius: 0.2rem;
        text-align: center;
        color: #4d4c4c;
        position: relative;
            padding-bottom: 4vw;
    box-sizing: border-box;
    }
    .box h4{
        font-size: 0.43rem;
        padding: 0.65rem;
        font-weight: bold;
    }
    .box p{
        font-size: 0.45rem;
        text-align: left;
        padding: 0 0.7rem;
        box-sizing: border-box;
        line-height: 2;
        font-weight: 500;
        display: flex;
        justify-content: flex-start;
    }
    .btn{
        width: 30vw;
        height: 10vw;
        background: #999;
        /*background: red;*/
        margin: 0.4rem auto;
        border-radius: 0.5rem;
        text-align: center;
        line-height: 10vw;
        color: #ffffff;
        font-size: 0.35rem;
        display: inline-block;
    }
    .box p span{
        font-size: 0.35rem;
        display: inline-block;
        color: #000;
        line-height: 0.65rem;
    }
    .box .close-icon{
        position: absolute;
        top: 0.35rem;
        right: 0.35rem;
        height: 0.35rem;
    }
</style>
<div class="yyhuqmeg">
        <?php if( !empty($couponlist )):?>
        <?php foreach ($couponlist as $key => $value):?>
        <div class="vipquan">
                <?php if(!empty($coupon_id) && ($coupon_id == $value['id']) && $use_coupon == 1):?>
                 <img style="position: absolute;right: 0.5rem;top: 0.4rem;width: 0.5rem;" src="/borrow/310/images/coupon_use.png">
                <?php else:?>
                 <img onclick="choose_coupon(<?php echo $value['id']?>)" style="position: absolute;right: 0.5rem;top: 0.4rem;width: 0.5rem;" src="/borrow/310/images/coupon_no_use.png">
                <?php endif;?>  
                <div class="vipqzuo">
                        <h3><?php echo empty($value['title']) ? '' : $value['title']?>（借款券）</h3>
                        <p class="yxioqi">有效期：<?php echo  date('Y.m.d',strtotime($value['start_date'])) ?>-<?php echo date('Y.m.d',strtotime($value['end_date'])) ?></p>
                </div>
                <div class="vipqyou">
                    ¥<em><?php echo empty($value['val']) ? 0 : $value['val'] ?></em>
                </div>
        </div>
        <?php endforeach;?>
        <?php endif;?>
    <p class="znouser" id="no_use">暂不使用优惠券</p>
</div>
<div class="userrule" onclick="look_coupon_tip()">优惠券使用规则</div>
<div class="alert" hidden >
   <div class="box">
        <h4 style="text-align:center">优惠券使用规则</h4> 
        <img class="close-icon" onclick="close_tip()"  src="/borrow/310/images/bill-close.png">
        <p><span class="boxNum">1、</span><span class="boxText">同类优惠券不可叠加使用</span></p>
        <p ><span class="boxNum">2、</span><span class="boxText">借款优惠券只可用户抵息，多余部分不予退还</span></p>
        <p ><span class="boxNum">3、</span><span class="boxText">还款优惠券仅限全额还款时使用，部分还款优惠券不会生效</span></p>
        <p><span class="boxNum">4、</span><span class="boxText">所有优惠券不可找零、兑换及提现 </span></p>
        <p><span class="boxNum">5、</span><span class="boxText">优惠券最终解释权归先花一亿元所有</span></p>
    </div>
</div>
<script>
    var coupon_id = '<?php echo $coupon_id?>';
    $(function(){
        //不使用优惠券
        no_use_coupon();       
        
        //重写返回按钮
        pushHistory();
        var bool=false;
        setTimeout(function(){
            bool=true;
        },1500);
        window.addEventListener("popstate", function(e) {
            if(bool){
               //根据自己的需求返回到不同页面
                setTimeout(function(){
                    window.location.href= '/borrow/loan/startloan?coupon_id='+coupon_id;
                 },100);

            }
                pushHistory();
        }, false);
    });
    
    //不使用优惠券
    function no_use_coupon(){
        $("#no_use").bind('click',function(){
            window.location.href = '/borrow/loan/getloancoupon?use_coupon=2';
        });
    }
    
   function pushHistory() {
        var state = {
            url: "#"
        };
        window.history.pushState(state,  "#");
    }
    
    //选中优惠券
    function choose_coupon(coupon_id){
        console.log(coupon_id);
         $("#choosed").attr('src','/borrow/310/images/coupon_use.png'); 
        window.location.href = '/borrow/loan/getloancoupon?coupon_id='+coupon_id;
    }
    
    //关闭优惠券规则
    function close_tip(){
        $('.alert').hide();
    }
    //查看优惠券规则
    function look_coupon_tip(){
        $('.alert').show();
    }
</script>