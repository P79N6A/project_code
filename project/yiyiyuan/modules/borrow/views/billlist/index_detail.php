<?php \app\common\PLogger::getInstance('weixin', '', $user_id); ?>
<?php $json_data = \app\common\PLogger::getJson(); ?>
<script>
    var baseInfoss = eval('(' + '<?php echo $json_data; ?>' + ')');
</script>
<div class="bill-detail-wrap">
    <?php if($loan_status==3){?>
    <!-- 续期成功 -->
    <div class="bill-tips-success">
        <p>恭喜您，您的账单已成功续期，还款日延后<?php echo $renewal_day; ?>天！</p>
    </div>
    <?php } ?>
    <?php if($loan_status==2){?>
        <!-- 提示还款 -->
         <div class="bill-tips-error">
            <p>您的逾期行为已严重影响您的信用评价，请马上还款！</p>
        </div>
    <?php } ?>

    <div class="bill-detail-main">
        <div class="bill-repay-wrap">
            <label class="bill-repayment">本期应还款金额</label>
            <?php if($loan_status==2){?>
                <span class="bill-overdue">（已逾期）</span>
            <?php } ?>
        </div>
        <i class="bill-money-icon">￥</i>
        <span class="bill-money-num"><?php echo $amount; ?></span>
        <?php if($loan_status==2){?>
            <!-- 逾期展示 -->
             <span class="bill-repayment-overdue">逾期<?php echo $day;  ?>天</span>
            <input name = "repaystatus" type="hidden" value="1">
        <?php }else{?>
            <!-- 未逾期展示 -->
            <span class="bill-repayment-day">还款日<?php echo $last_day; ?></span>
            <input name = "repaystatus" type="hidden" value="2">
        <?php } ?>

        <?php if($loan_status==2):?>
        <!--  逾期 -->
        <div class="bill-line bill-line-top"></div>
        <div class="bill-line"></div>
            <?php if( $loan_type == 2 ):?>
               <div class="bill-list">
                   <p class="bill-item">本期应还本金</p>
                   <p class="bill-item-con">￥<?php echo $principal; ?></p>
               </div>
               <div class="bill-list">
                   <p class="bill-item">往期应还本金</p>
                   <p class="bill-item-con">￥<?php echo $overdue_bjamount; ?></p>
               </div>
            <?php else:?>
                <div class="bill-list">
                    <p class="bill-item">应还本金</p>
                    <p class="bill-item-con">￥<?php echo $principal; ?></p>
                </div>
            <?php endif;?>
        <div class="bill-list">
            <p class="bill-item">综合费用</p>
            <p class="bill-item-con">￥<?php echo $interest_amount; ?></p>
        </div>
        <!--逾期显示：贷后管理费  -->
        <div class="bill-list" style="border-bottom: none;">
            <p class="bill-item">贷后管理费</p>
            <p class="bill-item-con">￥<?php echo $management_amount; ?></p>
        </div>
        <div class="bill-line" style="margin-top: 0.26rem;"></div>
        <div class="bill-line"></div>
        <!--逾期显示：逾期天数  -->
            <?php if( $loan_type == 2 ):?>
            <div class="bill-list bill-list-last" onclick="window.location = '/borrow/billlist/detailterm?loan_id=<?php echo $loan_id;?>';">
                <p class="bill-item" style="font-weight:bold; font-size: 0.37rem;" >全部待还</p>
                <p class="bill-item-con" style=" font-size: 0.37rem; " ><?php echo $period_num; ?>笔账单
                <img src="/borrow/310/images/fqjt.png" alt="" style="width: 0.23rem;display: block;float: right;margin-top: 0.37rem;margin-left: 0.2rem;">
                </p>
            </div>
            <?php endif;?>
        <?php else:?>
        <div class="bill-line bill-line-top"></div>
        <div class="bill-line"></div>
        <div class="bill-list">
            <p class="bill-item">应还本金</p>
            <p class="bill-item-con">￥<?php echo $principal; ?></p>
        </div>
        <div class="bill-list">
            <p class="bill-item">综合费用</p>
            <p class="bill-item-con">￥<?php echo $interest_amount; ?></p>
        </div>
            <?php if( $loan_type == 2 ):?>
            <div class="bill-list bill-list-last" onclick="window.location = '/borrow/billlist/detailterm?loan_id=<?php echo $loan_id;?>';">
                <p class="bill-item" style="font-weight:bold; font-size: 0.37rem;" >全部待还</p>
                <p class="bill-item-con" style=" font-size: 0.37rem; " ><?php echo $period_num; ?>笔账单
                <img src="/borrow/310/images/fqjt.png" alt="" style="width: 0.23rem;display: block;float: right;margin-top: 0.37rem;margin-left: 0.2rem;">
                </p>
            </div>
            <?php endif;?>
        <?php endif; ?>

    </div>
    <button id="loan_ljhg" class="bill-btn">立即还款</button>
    <?php if($is_renew_amout['type'] != 0){ ?>
    <p class="bill-repayment-link">续期还款></p>
    <?php } ?>
    <a href="javascript:void(0);" class="bill-repayment-question" onclick="doHelp('/borrow/helpcenter/list?position=12&user_id=<?php echo $user_id;?>')">还款遇到问题？</a>
</div>
<!--<script src="/borrow/350/javascript/z_scale.js"></script>-->
<script type="text/javascript">
    var loan_id = '<?php echo $loan_id;?>';
    var csrf = '<?php echo $csrf;?>';
    var baseInfoss = eval( '('  + '<?php echo $json_data; ?>' + ')' );
    $("#loan_ljhg").click(function () {
        tongji('billdetail_ljhg', baseInfoss);
        var repaystatus = $('input[name="repaystatus"]').val();
        if(repaystatus == 1){
            zhuge.track('账单详情-点击事件', {
                '账单详情-点击事件' : "逾期还款",});
        }else {
            zhuge.track('账单详情页-立即还款按钮');
        }
        window.location ='/borrow/repay/repaychoose?loan_id=<?php echo $loan_id?>'+'&goods_bill=<?php echo $pay_goods_bill_id?>';
    });

    $(".bill-repayment-link").click(function () {
        tongji('billdetail_xqhg', baseInfoss);
        zhuge.track('账单详情页-续期还款按钮');
        <?php if ($is_renew_amout['type'] == 1): ?>
            window.location ='/new/renewal/index?loan_id=<?php echo $loan_id?>';
        <?php elseif ($is_renew_amout['type'] == 2): ?>
            window.location ='/renew/renewal/index?loan_id=<?php echo $loan_id; ?>';
        <?php elseif ($is_renew_amout['type'] == 3): ?>
            if($('.bill-repayment-link').hasClass('lock')){
                return false;
            }
            $('.bill-repayment-link').addClass('lock');
            $.ajax({
                type:"POST",
                url:"/borrow/billlist/ajax-renew",
                data:{_csrf:csrf,loan_id:loan_id},
                datatype: "json",
                success:function(data){
                    data = eval('('+data+')');
                    if(data.rsp_code == '0000'){
                        window.location ='/borrow/loan';
                        $('.bill-repayment-link').removeClass('lock');
                        return false;
                    }else{
                        alert(data.rsp_msg);
                        $('.bill-repayment-link').removeClass('lock');
                        return false;
                    }
                },
                error: function(){
                    $('.bill-repayment-link').removeClass('lock');
                    return false;
                }
            });
        <?php endif; ?>
    });

    function doHelp(url) {
        tongji('do_help',baseInfoss);
        setTimeout(function(){
            window.location.href = url;
        },100);
    }
</script>
