<!--<div style="clear:both;height: 60px;"></div>
<div class="mMenu">
    <ul class="fCf">
        <li class="item-1"> <a href="/borrow/loan"><i class="icon"></i> <em>借款</em></a> </li>
        <li class="item-2 active"> <a href="/mall/index?type=weixin"><i class="icon"></i> <em>商城</em></a> </li>
        <li class="item-3"> <a href="/new/account"><i class="icon"></i> <em>我</em></a> </li>
    </ul>
</div>-->
    <style>
        .footer{
            display: flex;
            align-items: center;
            justify-content: space-around;
            font-size: 12px;
            width: 100%;
            height: 47px;
            position: fixed;
            left: 0;
            bottom: 0;
            background: #ffffff;
            z-index: 999;
            color:#ccc;
        }
        .nav-one{
            text-align: center; 
            color: #ccc;
        }
        .nav-one p{
            /*color: #ccc;*/
        }
        .nav-one img{
            width: 22.1px;
        }
        .active{
            color: #f00d0d;
        }
        .hr{
            height: 56px;
            visibility: hidden;
        }
    </style>
    <div class="hr"></div>

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
<script src="/newdev/js/log.js" type="text/javascript" charset="utf-8"></script> 
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