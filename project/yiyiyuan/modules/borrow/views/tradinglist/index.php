
<ul class="payout_record">
    <?php foreach($loan_list as $key=>$value){?>
    <li class="payout_record_text">
        <span class="payout_num">借款金额 <?php echo sprintf("%.2f",$value['amount']) ?>元</span>
        <span class="payout_time"><?php echo $value['create_time'] ?></span>
        <?php if($value['status']==9){ ?>
        <a href="javascript:void(0);" onclick="go_loan_index()">
            <span class="pay_state">待还款</span>
        <?php }elseif($value['status']==11){ ?>
            <a href="javascript:void(0);" onclick="go_loan_index()">
                <span class="pay_state">待还款确认</span>
        <?php }elseif($value['status']==8){?>
        <a href="/borrow/tradinglist/detail?loan_id=<?php echo $value['loan_id']?>">
            <span class="pay_state pay_state_back">已还款</span>
        <?php }elseif(in_array($value['status'],[12,13])){?>
            <a href="javascript:void(0);" onclick="go_loan_index()">
            <span class="pay_state pay_state_overdue">已逾期</span>
        <?php }elseif(in_array($value['status'],[3,7,23])){?>
        <a href="/borrow/tradinglist/detail?loan_id=<?php echo $value['loan_id']?>">
            <span class="pay_state pay_state_back">已驳回</span>
        <?php }elseif($value['settle_type']==3){?>
            <a href="javascript:void(0);" onclick="go_loan_index()">
            <span class="pay_state pay_state_going">已续期</span>
        <?php }elseif($value['status']==18 || $value['status']==19){?>
            <a href="javascript:void(0);" onclick="go_loan_index()">
            <span class="pay_state">待提现</span>
        <?php }elseif($value['status']==21){?>
             <a href="javascript:void(0);" onclick="go_loan_index()">
             <span class="pay_state">待激活</span>
        <?php }elseif($value['status']==22){?>
            <a href="javascript:void(0);" onclick="go_loan_index()">
            <span class="pay_state">放款中</span>
        <?php } ?>
        <img src="/borrow/310/images/arrow.png" alt="" class="arrow"></a>
    </li>
    <?php }?>
</ul>
<script type="text/javascript">
    var payout_record_text = document.getElementsByClassName('payout_record_text');
    payout_record_text[payout_record_text.length-1].style.border = 'none';
    var isApp = <?php
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'YYY_ANDROID') || strpos($_SERVER['HTTP_USER_AGENT'], 'yyyIOS')) {
            echo 1;  //app端
        } else {
            echo 2;  //h5端
        }
        ?>;
    function go_loan_index() {
        if (isApp == 1) {
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
        } else {
            window.location.href = '/borrow/loan/index';
        }
    }
</script>
