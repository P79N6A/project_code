    <style>
        .footer { display: -webkit-box; display: -ms-flexbox; display: flex; -webkit-box-align: center; -ms-flex-align: center; align-items: center; -ms-flex-pack: distribute; justify-content: space-around; font-size: 0.320rem; width: 100%; height: 1.493rem; position: fixed; left: 0; bottom: 0; background: #ffffff; z-index: 999; color: #999; }

        .nav-one { text-align: center; }

        .nav-one img { width: 0.640rem; }

        .active { color: #c90000; }

        .hr { height: 1.493rem; visibility: hidden; }
        .footer a{
            font-size: 0.32rem;
            color: #999;
        }
    </style>
    <div class="hr"></div>

     <div class="bill-nav footer">
    <a href="/borrow/loan/index" class="nav-one">
        <?php if ($page == 'loan') : ?>
            <img src="/borrow/310/images/bill-repay-active.png" alt="">
            <p class="active">借款</p>
        <?php else: ?>
            <img src="/borrow/310/images/bill-repay.png" alt="">
            <p>借款</p>
        <?php endif; ?>
    </a>
    <a href="/borrow/billlist/index" class="nav-one">
        <?php if ($page == 'bill') : ?>
            <img src="/borrow/310/images/bill-bill-active.png" alt="">
            <p class="active">账单</p>
        <?php else: ?>
            <img src="/borrow/310/images/bill-bill.png" alt="">
            <p >账单</p>
        <?php endif; ?>
    </a>
    <a href="/mall/store/index" class="nav-one">
        <?php if ($page == 'mall') : ?>
            <img src="/borrow/310/images/bill-store-active.png" alt="">
            <p class="active">商城</p>
        <?php else: ?>
            <img src="/borrow/310/images/bill-store.png" alt="">
            <p>商城</p>
        <?php endif; ?>
    </a>
    <a href="/borrow/account" class="nav-one">
        <?php if ($page == 'person') : ?>
            <img src="/borrow/310/images/bill-mine-active.png" alt="">
            <p class="active">我的</p>
        <?php else: ?>
            <img src="/borrow/310/images/bill-mine.png" alt="">
            <p>我的</p>
        <?php endif; ?>
    </a>
</div>