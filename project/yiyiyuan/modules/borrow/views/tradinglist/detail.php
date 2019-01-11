<style>
    .w_xieyi{
        position: fixed;
        left: 0;
        bottom: 7%;
        width: 100%;
        height: 1rem;
        text-align: center;
        line-height: 1rem;
        font-size: 0.35rem;
    }
    .w_xieyi span:nth-of-type(1){
        color: #000;
    }
    .w_xieyi span:nth-of-type(2){
        color:#261CDC;
    }
</style>
<div class="reset_payout">
    <div class="payout_count">
        <span class="deatil_title">借款金额</span>
        <span class="list_num"><?php echo sprintf("%.2f", $loan_info['amount'])?></span>
    </div>
    <?php if(in_array($loan_info['business_type'],[5,6,11]) && isset($loan_info->goodsbills[0])): ?>
    <div class="payout_count">
        <span class="deatil_title">申请期限</span>
        <span class="list_num"><?php echo $loan_info->goodsbills[0]['days'] ?>天X<?php echo $loan_info->goodsbills[0]['number'] ?>期</span>
    </div>
    <?php endif; ?>
    <div class="apply_date" <?php if($loan_info['status']!=7){ echo "style='border: none;'"; } ?>>
    <?php if($loan_info['status']==8){ ?>
        <span  class="deatil_title">还款日期</span>
        <span class="list_num"><?php echo $repay_time ?> </span>
    <?php }else{ ?>
    <span  class="deatil_title">申请日期</span>
        <span class="list_num"><?php echo date('Y-m-d',strtotime($loan_info['create_time']))?></span>
    <?php } ?>

    </div>
    <?php if($loan_info['status']==7){ ?>
    <div class="back_reason" style="display: flex;justify-content: center;justify-content: space-between;align-items: center;height: auto;padding: 0.3rem 0;">
        <span class="deatil_title" style="margin:0">驳回理由</span>
        <span class="list_num" style="max-width: 6.2rem; line-height: 1em;"><?php echo $desc ?></span>
    </div>
    <?php } ?>
</div>
<div class="reset_payout_btn">
    重新发起借款
</div>
<div class="w_xieyi">
<?php if ($contract_show): ?>
<span>查看</span><span onclick="doContract_url('<?php echo $contract_url;?>')">《居间服务及借款协议（四方）》</span>
<?php endif; ?>
</div>

<script type="text/javascript">
    var isApp = <?php
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'YYY_ANDROID') || strpos($_SERVER['HTTP_USER_AGENT'], 'yyyIOS')) {
            echo 1;  //app端
        } else {
            echo 2;  //h5端
        }
        ?>;
    $('.reset_payout_btn').click(function(){
        zhuge.track('借款首页', {
            '来源': '侧导航借款记录再借一笔按钮',
            '状态': '额度已过期',
        });
        //关闭html
        if(isApp == 1){
            var u = navigator.userAgent, app = navigator.appVersion;
            var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Linux') > -1;
            var isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/);
            var android = "com.business.main.MainActivity";
            var ios = "loanViewController";
            var position = "-1";
            if (isiOS) {
                window.myObj.toPage(ios);
            } else if (isAndroid) {
                window.myObj.toPage(android, position);
            }
        }else{
            window.location.href='/borrow/loan/index';
        }
    })
    $(function(){
        var status=<?php echo $loan_info['status'] ?>;
        if(status==8){
            $('.reset_payout_btn').text('');
            $('.reset_payout_btn').text('再借一笔');
        }

    });

    function doContract_url(url) {
        window.location.href=url;
    }
</script>

