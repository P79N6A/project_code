<!--<div class="mMenu">
    <ul class="fCf">
        <?php if( $page == 'loan'){?><li class="item-2 active"><?php }else{?><li class="item-2"><?php }?> <a href="https://open.weixin.qq.com/connect/oauth2/authorize?appid=<?php echo Yii::$app->params['AppID'];?>&redirect_uri=<?php echo Yii::$app->params['app_url'];?>/dev/loan&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect"><i class="icon"></i> <em>借款</em></a> </li>
        <?php if( $page == 'friends'){?><li class="item-1 active"><?php }else{?><li class="item-1"><?php }?> <a href="https://open.weixin.qq.com/connect/oauth2/authorize?appid=<?php echo Yii::$app->params['AppID'];?>&redirect_uri=<?php echo Yii::$app->params['app_url'];?>/dev/friends/first&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect"><i class="icon"></i> <em>商城</em></a> </li>
        <?php if( $page == 'account'){?><li class="item-3 active"><?php }else{?><li class="item-3"><?php }?> <a href="https://open.weixin.qq.com/connect/oauth2/authorize?appid=<?php echo Yii::$app->params['AppID'];?>&redirect_uri=<?php echo Yii::$app->params['app_url'];?>/dev/account&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect"><i class="icon"></i> <em>我</em></a> </li>
    </ul>

</div>-->

    <style>
        .footer{
            display: flex;
            align-items: center;
            justify-content: space-around;
            font-size: 12px;
            width: 100%;
            height: 56px;
            position: fixed;
            left: 0;
            bottom: 0;
            background: #ffffff;
            z-index: 999;
            color:#999;
        }
        .nav-one{
            text-align: center; 
        }
        .nav-one p{
            
        }
        .nav-one img{
            width: 24px;
        }
        .active{
            color: #c90000;
        }
        .hr{
            height: 56px;
            visibility: hidden;
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
    <a href="/new/account" class="nav-one">
        <?php if ($page == 'person') : ?>
            <img src="/borrow/310/images/bill-mine-active.png" alt="">
            <p class="active">我的</p>
        <?php else: ?>
            <img src="/borrow/310/images/bill-mine.png" alt="">
            <p>我的</p>
        <?php endif; ?>
    </a>
</div>        