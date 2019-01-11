<div class="wrap">

    <!--
    <div class="content">
        <a href="/dev/account/peral?user_id=<?php echo $userinfo['user_id']; ?>">
            <div class="myziliao">我的资料〉</div>
        </a>
        <p class="content1">
            <a href="/dev/account/peral?user_id=<?php echo $userinfo['user_id']; ?>">
                <img src="<?php
                if ($userinfo['userwx']['head']) {
                    echo $userinfo['userwx']['head'];
                } else {
                    echo '/images/face.png';
                }
                ?>">
            </a>
        </p>
        <p class="content2"><?php echo $userinfo['userwx']['nickname']; ?></p>
        <p class="content3"><span>当前可借款额度</span></p>
        <p class="content4"><?php echo sprintf("%.2f", $userinfo->getUserLoanAmount($userinfo)); ?><em>元</em></p>

    </div>
    -->
    <!--
    <div class="tags">

        <section class="left">
            <a href="/dev/guarantee">
                <div><img src="/images/account/zhym1.png">担保卡</div>
                <p><?php echo $totalGuarant; ?>点</p>
            </a>
        </section>

        <section class="center">
            <a href="/dev/bank">
                <div><img src="/images/account/yhk.png">银行卡</div>
                //<p><?php echo sprintf("%.2f", $totalIncome); ?>点</p>
                <p><?php echo $bank; ?>张</p>
            </a>
        </section>
        <section class="right">
            <a href="/dev/account/coupon">
                <div><img src="/images/account/zhym3.png">优惠券</div>
                <p><?php echo $couponcount; ?>张</p>
            </a>
        </section>

    </div>
    <div style="height:1em;width: 100%;background-color: initial;"></div>
     -->

    <style>
        .newmystyle{width: 90%; background: #fff; overflow: hidden; padding: 1rem 5%; }
        .newmystyle .tourxiang{width: 16%; float: left; border-radius: 50%;}
        .newmystyle .leftcont{ float: left; margin:2% 5% 0;}
        .newmystyle .leftcont h3{ font-size: 1.35rem; color: #444;}
        .newmystyle .leftcont p{ color: #c2c2c2;}
        .newmystyle .righrjtou{ width: 3%;margin-top:5.5%; float: right }
    </style>

    <div class="newmystyle" >
        <a href="/dev/account/peral?user_id=<?php echo $userinfo['user_id']; ?>">
            <img class="tourxiang"  src="<?php
            if ($userinfo['userwx']['head']) {
                echo $userinfo['userwx']['head'];
            } else {
                echo '/images/face.png';
            }
            ?>"/>
            <div class="leftcont" >
                <h3><?php echo $userinfo['userwx']['nickname']; ?></h3>
                <p>当前可用额度 <?php echo sprintf("%.2f", $userinfo->getUserLoanAmount($userinfo)); ?><em>元</em></p >
            </div>
            <img class="righrjtou"  src="/images/account/right.png">
        </a>
    </div>
    <div style="height:1em;width: 100%;background-color: initial;"></div>
    <div class="options">
        <ul>
            <a href="/dev/account/coupon">
                <li>
                    <img class="firstImg" src="/images/account/zhym3.png"/>
                    <span>优惠券（<?php echo $couponcount; ?>张）</span>
                    <img class="righGo" src="/images/account/right.png">
                </li>
            </a>
        </ul>
    </div>
    <!--<div style="height:1em;width: 100%;background-color: initial;"></div>-->
    <div class="options">
        <ul>
            <a href="/dev/bank">
                <li>
                    <img class="firstImg" src="/images/account/yhk.png"/>
                    <span>银行卡（<?php echo $bank; ?>张）</span>
                    <img class="righGo" src="/images/account/right.png">
                </li>
            </a>
        </ul>
    </div>
    <div style="height:1em;width: 100%;background-color: initial;"></div>
    <div class="options">
        <ul>
            <!--<a href="/dev/loan/loanlist">-->
            <a href="/new/loanrecord/loanrecord">
                <li>
                    <img class="firstImg" src="/images/account/card.png"/>
                    <span>借款记录</span>
                    <img class="righGo" src="/images/account/right.png">
                </li>
            </a>
        </ul>
    </div>
    <div style="height:1em;width: 100%;background-color: initial;"></div>
    <div class="options">
        <ul>
            <a href="/dev/invitation/index">
                <li>
                    <img class="firstImg" src="/images/account/card3.png"/>
                    <span>邀请认证</span>
                    <img class="righGo" src="/images/account/right.png">
                </li>
            </a>
        </ul>
    </div>
    <div style="height:1em;width: 100%;background-color: initial;"></div>
    <div class="options oioioi">
        <ul>
            <a href="/dev/supply">
                <li>
                    <img class="firstImg" src="/images/account/card6.png"/>
                    <span>补充资料</span>
                    <img class="righGo" src="/images/account/right.png">
                </li>
            </a>

        </ul>
    </div>
</div>
<?= $this->render('/layouts/_page', ['page' => 'friends']) ?>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
            wx.config({
                debug: false,
                appId: '<?php echo $jsinfo['appid']; ?>',
                timestamp: <?php echo $jsinfo['timestamp']; ?>,
                nonceStr: '<?php echo $jsinfo['nonceStr']; ?>',
                signature: '<?php echo $jsinfo['signature']; ?>',
                jsApiList: [
                    'hideOptionMenu'
                ]
            });

            wx.ready(function () {
                wx.hideOptionMenu();
            });
</script>