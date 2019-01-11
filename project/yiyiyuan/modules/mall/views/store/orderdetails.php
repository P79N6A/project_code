<div class="order-wrap">
    <?php if($is_goloan && (in_array($user_credit_data['user_credit_status'],[4,5]))):?>
        <!-- 去借款 -->
        <div class="y-go-loan" onclick="closeHtml()">
            <p class="get-money">您已获得<span><?= $user_credit_data['order_amount']?>元</span>额度借款额度</p>
            <p class="loan-money">戳这里立即拿钱<span>去借款</span></p>
        </div>
    <?php endif;?>
    <?php if($user_credit_data['user_credit_status']==3):?>
        <!-- 订单正在审核中 -->
        <div class="y-order-state">
            <i class="state1"></i>
            <p class="in-review">您的分期订单正在审核中</p>
        </div>
    <?php else:?>
        <!-- 订单审核未通过 -->
        <div class="y-order-state">
            <i class="state2"></i>
            <p class="no-pass">您的分期订单审核未通过</p>
        </div>
    <?php endif;?>
    <div class="y-order-address">
        <i></i>
        <div class="order-address">
            <p class="order-name">
                <span><?=$orderInfo->address->receive_name; ?></span>
                <span><?=$orderInfo->address->receive_mobile; ?></span>
            </p>
            <p class="address-message"><?=$orderInfo->address->address_detail; ?></p>
        </div>
        <div class="clear"></div>
    </div>
    <div class="order-goods">
        <img src="<?= !empty($goodsContent['pic_url']) ? \app\commonapi\ImageHandler::getUrl($goodsContent['pic_url']) : ''; ?>" alt="">
        <div class="y-goods-right">
            <p><?=$goodsContent['goods_name']; ?></p>
            <p><span><?=empty($goodsContent['colour']) ? '': $goodsContent['colour']; ?></span><span><?=empty($goodsContent['bb']) ? '': $goodsContent['bb']; ?></span></p>
            <p class="y-money"><i>¥</i> <?=$goodsContent['money']; ?></p>
        </div>
    </div>
    <div class="y-scheme">
        <div class="scheme-main">
            <p>分期方案</p>
            <span><?=$orderInfo->term_days; ?>天×<?=$orderInfo->terms; ?>期</span>
        </div>
    </div>
    <div class="y-scheme-detail">
        <ul>
            <?php foreach($terms_data as $value):?>
            <li>
                <p><?= date('Y年m月d日',strtotime($value['days']))?></p>
                <span>￥<?=$value['single_money']?>元</span>
            </li>
            <?php endforeach;?>
        </ul>
    </div>
    <div class="y-order-num">
        <p>订单编号 <?=$orderInfo->order_id; ?></p>
        <p>付款时间 <?=$orderInfo->create_time; ?></p>
    </div>
</div>
<script>
    var is_app = '<?php echo $is_app;?>';
    //关闭html
    function closeHtml() {
        if(is_app){
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
            window.location.href = '<?php echo Yii::$app->request->hostInfo;?>'+'/borrow/loan';
        }
    }

    //重写返回按钮
//    pushHistory();
//    var bool=false;
//    setTimeout(function(){
//        bool=true;
//    },500);
//    window.addEventListener("popstate", function(e) {
//        if(bool){
//            //根据自己的需求返回到不同页面
//            setTimeout(function(){
//               window.location.href='/mall/store';
//            },1000);
//        }
//        pushHistory();
//    }, false);
//    function pushHistory() {
//        var state = {
//            url: "#"
//        };
//        window.history.pushState(state,  "#");
//    }
</script>