<div class="bill-nav footer">
    <a href="javascript:void(0);" class="nav-one" onclick="doLoan()">
        <?php if ($page == 'loan') : ?>
            <img src="/borrow/310/images/bill-repay-active.png" alt="">
            <p class="active">借款</p>
        <?php else: ?>
            <img src="/borrow/310/images/bill-repay.png" alt="">
            <p>借款</p>
        <?php endif; ?>
    </a>
    <a href="javascript:void(0);" class="nav-one" onclick="doBill()">
        <?php if ($page == 'bill') : ?>
            <img src="/borrow/310/images/bill-bill-active.png" alt="">
            <p class="active">账单</p>
        <?php else: ?>
            <img src="/borrow/310/images/bill-bill.png" alt="">
            <p >账单</p>
        <?php endif; ?>
    </a>
    <a href="javascript:void(0);" class="nav-one" onclick="doMall()">
        <?php if ($page == 'mall') : ?>
            <img src="/borrow/310/images/bill-store-active.png" alt="">
            <p class="active">商城</p>
        <?php else: ?>
            <img src="/borrow/310/images/bill-store.png" alt="">
            <p>商城</p>
        <?php endif; ?>
    </a>
    <a href="javascript:void(0);" class="nav-one" onclick="doAccount()">
        <?php if ($page == 'person') : ?>
            <img src="/borrow/310/images/bill-mine-active.png" alt="">
            <p class="active">我的</p>
        <?php else: ?>
            <img src="/borrow/310/images/bill-mine.png" alt="">
            <p>我的</p>
        <?php endif; ?>
    </a>
</div>
<script>
    <?php \app\common\PLogger::getInstance('weixin','',isset($log_user_id)?$log_user_id:''); ?>
    <?php $json_data = \app\common\PLogger::getJson();?>
    var baseInfoss = eval( '('  + '<?php echo $json_data; ?>' + ')' );

    function doLoan() {
        if(typeof tongji == 'function'){
            tongji('do_loan_page',baseInfoss);
        }
        setTimeout(function () {
            window.location.href = '/borrow/loan/index';
        }, 1000);
    }

    function doBill() {
        if(typeof tongji == 'function'){
            tongji('do_bill_page',baseInfoss);
        }
        setTimeout(function () {
            window.location.href = '/borrow/billlist/index';
        }, 1000);
    }

    function doMall() {
        if(typeof tongji == 'function'){
            tongji('do_mall_page',baseInfoss);
        }
        setTimeout(function () {
            window.location.href = '/mall/store/index';
        }, 1000);
    }

    function doAccount() {
        if(typeof tongji == 'function'){
            tongji('do_account_page',baseInfoss);
        }
        setTimeout(function () {
            window.location.href = '/borrow/account';
        }, 1000);
    }
</script>
