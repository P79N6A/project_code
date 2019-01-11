<style>
    .y-pop-win{
        /*height: 8.8rem;*/
        width: 7.8rem;
        position: fixed;
        left: 50%;
        top: 50%;
        background: #fff;
        transform: translate(-50%,-50%);
        border-radius: 0.13rem;
    }
    .plan-tit{
        font-size: 0.4rem;
        font-family: "微软雅黑";
        font-size: 0.48rem;
        color: #444444;
        line-height: 0.48rem;
        margin-top:1.2rem;
        text-align: center;
        font-weight: bold;
    }
    .y-plan{
        width: 6.2rem;
        margin:0.8rem auto 0.4rem;
    }
    .plan-item{
        margin-bottom:0.4rem;
    }
    .plan-item:after{
        content: '';
        display: block;
        clear: both;
        overflow: hidden;
        height: 0;
    }
    .plan-item p{
        float: left;
        font-size: 0.36rem;
        color: #444;
        font-family: "微软雅黑";
    }
    .plan-item span{
        display: block;
        float: right;
        font-size: 0.36rem;
        color: #444;
        font-family: "微软雅黑";
    }
    .plan-button{
        width: 2.8rem;
        height: 0.88rem;
        background-image: linear-gradient(90deg, #F00D0D 0%, #FF4B17 100%);
        border-radius: 0.13rem;
        font-family: "微软雅黑";
        font-size: 0.43rem;
        line-height: 0.88rem;
        margin: 0.6rem auto;
        color:#fff;
        text-align: center;
    }
    .y-close-btn{
        width: 0.36rem;
        height: 0.36rem;
        position: absolute;
        right: 0.3rem;
        top: 0.3rem;
    }
    .y-popup {
        height: 100%;
        width: 100%;
        background: #000;
        opacity: .5;
        position: fixed;
        left: 0;
        top: 0;
    }
</style>
<div class="bill-detail-wrap">
		<div class="depository-detail-main">
			<div class="depository-list">
				<p class="bill-item depository-item">到账金额</p>
				<p class="bill-item-con depository-item-time"><?php echo number_format($amount,0,'.',',') ?>元</p>
			</div>
			<div class="depository-list depository-line">
				<p class="bill-item">借款期限</p>
				<p class="bill-item-con"><?php echo $days; ?>天x<?php echo $period; ?>期</p>
			</div>
			<div class="depository-list depository-line">
				<p class="bill-item">综合费用</p>
				<p class="bill-item-con"><?php echo $interest?>元</p>
			</div>
            <?php if (\app\commonapi\Keywords::inspectOpen() == 2 && !$is_installment): ?>
                <div class="depository-list depository-line">
                    <div class="bill-item" style="width:3.6rem;margin: 0.2rem 0;">
                        <div style="color:#444;font-size:0.32rem;margin-top:0rem;width:3.6rem;line-height:0.5rem">综合利息</div>
                        <div style="color:#999;font-size:0.32rem;width:3.6rem;line-height:0.5rem">利息按实际使用天数收取</div>
                    </div>
                    <p class="bill-item-con" style="margin-top: 0.15rem;"><?php echo $surplus_fee?>元</p>
                </div>
            <?php endif; ?>
			<div class="depository-list">
				<p class="bill-item">优惠券</p>
                <p class="bill-item-con">
                    <?php if (\app\commonapi\Keywords::inspectOpen() == 2) {
                        $fee = $surplus_fee; //进场
                    }else{
                        $fee = $interest;
                     };
                    $coupon_amount = ($fee <= $coupon_amount) ? $fee : $coupon_amount;
                    ?>
                    <?php echo empty($coupon_amount) ? '-0': '-'.$coupon_amount;?>

                    元</p>
			</div>
			<div class="bill-line depository-dline"></div>
			<div class="bill-line"></div>
			<div class="depository-repay-wrap">
				<label class="depository-repayment">请按时还款，避免产生逾期费用</label>
			</div>
			<div class="depository-list">
				<p class="bill-item depository-item">还款计划</p>
                <?php if ($is_installment): ?>
                    <span onclick="openRepayPlan()">
                        <img src="/borrow/310/images/arrow2.png" alt="" class="arrow_right" style="margin-left: 0.2rem;margin-top: 0.26rem;float: right;margin-right: -0.3rem;display: block;width: 0.5rem;">
                        <p class="bill-item-con depository-item-con">首期应还 <?php echo empty($repay_plan[0]['repay_amount'])?0: number_format($repay_plan[0]['repay_amount'],2,'.',',');?>元</p>
                    </span>
                <?php else: ?>
                    <p class="bill-item-con depository-item-con">应还金额 <?php echo empty($repay_plan[0]['repay_amount'])?0: number_format($repay_plan[0]['repay_amount'],2,'.',',');?>元</p>
                <?php endif; ?>
			</div>
			<div class="bill-line depository-interval-top"></div>
			<div class="bill-line depository-interval-bottom"></div>
			<div class="depository-list">
				<p class="bill-item depository-item">收款卡</p>
                <p class="bill-item-con depository-item-con"><?php echo empty($bank['type']) ? '':$bank['type'];?> <?php echo empty($bank['card'])?'':'('.$bank['card'].')'?></p>
			</div>
		</div>
            <button class="bill-btn" onclick="real_loan()">确认借款</button>
</div>
<div class="bill-detail-wrap" id="sencond_toast" hidden>
    <div class="bill-mask"></div>
    <div class="depository-popup">
        <p class="depository-reviewed-time"><span id="second">10s</span></p>
            <p class="popup-tips">借款审核中...</p>
            <p class="reviewed-tips">培养良好的信用习惯，可提高额度申请成功率</p>
    </div>
</div>
<div class="bill-detail-wrap" id="tongguo_toast" hidden>
    <div class="bill-mask"></div>
    <div class="depository-popup">
             <img class="bill-isok" src="/borrow/310/images/bill-ok.png" alt="">
            <p class="popup-tips">恭喜您，审核通过</p>
            <?php if( $jg_remark == 1 ):?>
                <p class="popup-txt">借款审核已通过，系统将快马加鞭为您安排放款，请耐心等待！</p> 
<!--                <p class="popup-txt">借款审核已通过，激活成功即可下款哦！</p> -->
            <?php elseif( $jg_remark == 2 ):?>
                <p class="popup-txt">借款审核已通过，系统将快马加鞭为您安排放款，请耐心等待！</p> 
            <?php endif;?>
            
    </div>
</div>
<!--弹窗1218-->
<div class="y-popup" style="display:none;"></div>
<div class="y-pop-win" style="display: none;">
    <img src="/borrow/310/images/bill-close.png" alt="" class="y-close-btn" onclick="closeRepayPlan()">
    <h3 class="plan-tit">还款计划</h3>
    <div class="y-plan">
        <?php foreach ($repay_plan as $val): ?>
            <div class="plan-item">
                <p><?php echo $val['repay_date']; ?></p>
                <span><?php echo $val['repay_amount']; ?>元</span>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="plan-button" onclick="closeRepayPlan()">确认</div>
</div>
<style>
    .help_service{
        position: absolute;
        width: 100%;
        left: 0;
        bottom: 1.81rem;
        height: 0.37rem;
        text-align: center;
    }
    .contact_service_tip{
        width: 0.40rem;
        height: 0.43rem;
        position: absolute;
        left: 3.97rem;
        top: 0;
    }
    .contact_service_text{
        height: 0.37rem;
        position: absolute;
        left:4.59rem;
        font-family: "微软雅黑";
        font-size: 0.37rem;
        color: #3D81FF;
        letter-spacing: 0;
        line-height: 0.43rem;
    }
</style>
<div class="help_service" style="z-index:-1">
    <img src="/borrow/310/images/tip.png" alt="" class="contact_service_tip">
    <a href="javascript:void(0);" onclick="doHelp('/borrow/helpcenter/list?position=11&user_id=<?php echo $user_id;?>')"><span class="contact_service_text">获取帮助</span></a>
</div>
<script>
    <?php \app\common\PLogger::getInstance('weixin','',$user_id); ?>
    <?php $json_data = \app\common\PLogger::getJson();?>
    var baseInfoss = eval( '('  + '<?php echo $json_data; ?>' + ')' );

    var amount =  '<?php echo $amount;?>';
    var period =  '<?php echo $period;?>';
    var user_id = '<?php echo $user_id;?>';
    var csrf = '<?php echo $csrf;?>';
    var coupon_id = '<?php echo $coupon_id;?>';
    var agreement = '<?php echo $agreement;?>';
    var days = '<?php echo $days;?>';
    var desc = '<?php echo $desc;?>';
    var bank_id = '<?php echo empty($bank['bank_id']) ? '' : $bank['bank_id'];?>';    
    console.log(desc);
    var is_toast;
     //确认借款
     function real_loan(){
              zhuge.track('借款确认-确认借款');
              $.ajax({
                url: "/borrow/loan/userloan",
                type: 'post',
                async: false,
                data: {_csrf:csrf,amount:amount,period:period,coupon_id:coupon_id,desc:desc,agreement:agreement,days:days,bank_id:bank_id},
                success: function (data) {
                    data = eval('(' + data + ')');
                    if (data.rsp_code != '0000') {
                        alert(data.rsp_msg);
                        window.location = data.url;
                        return false;
                    }else{
                        window.location = '/borrow/loan';
                        return false;
                    }                                     
                },
                error: function (json) {
                    alert('请十分钟后发起借款!!');
                }
            });
     }

    function doHelp(url) {
        tongji('do_help',baseInfoss);
        setTimeout(function(){
            window.location.href = url;
        },100);
    }

    function openRepayPlan() {
        $('.y-popup').show();
        $('.y-pop-win').show();
    }

    function closeRepayPlan() {
        $('.y-popup').hide();
        $('.y-pop-win').hide();
    }
</script>