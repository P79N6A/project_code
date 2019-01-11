<div class="wrap">
    <style>
        body{ background: #f3f3f3;}
        .newmystyle{width: 90%; background: #fff; overflow: hidden; padding: 1rem 5%; }
        .newmystyle .tourxiang{width: 16%; float: left; border-radius: 50%;}
        .newmystyle .leftcont h3{ font-size: 1.35rem; color: #444;}
        .newmystyle .leftcont p{ color: #c2c2c2;}
        .newmystyle .righrjtou{ width: 2%;margin-top:6%; float: right }
        .newmystyle .leftcont{float:left;margin: 0 0% 0 5%}
        .options ul li{ height: auto;border-bottom:0.5px solid #f3f3f3;}
        /*.options ul li:last-child{ border-bottom: 0;}*/
        .options ul li span{ font-size: 16px;}
        .options ul li span em{ color: #868383;}
        .message_num{ background-color: #C90000;color:#FFF; height:20px;min-width:20px;line-height:20px;text-align: center; margin-top: 11px; margin-right: 10px;border-radius: 50%;float:right;}
    </style>
    <div class="newmystyle" >
        <a href="/new/account/peral">
            <img class="tourxiang"  src="<?php
            if ($userinfo['userwx']['head']) {
                echo $userinfo['userwx']['head'];
            } else {
                echo '/images/face.png';
            }
            ?>"/>
            <div class="leftcont" >
                <h3 style="color: #444; font-size: 16px;"><?php echo $userinfo['userwx']['nickname']; ?></h3>
                <p  style="padding-top:6px;color: #444; font-size: 16px;">个人资料</p>
<!--                <p>信用额度 --><?php //echo $current_amount; ?><!--<em>元</em> | 担保额度 --><?php //echo $guarantee_amount; ?><!--<em>元</em>-->
            </div>
            <img class="righrjtou"  src="/images/account/right.png">
        </a>

    </div>
    <div style="height:1em;width: 100%;background-color: initial;"></div>
    <div class="options">
        <ul>
<!--            <a href="/new/account/coupon">-->
            <a href="/new/coupon/couponlist">
                <li>
                    <img class="firstImg" style="display: inline-block;margin-top: 13px;" src="/images/account/zhym3.png"/>
                    <span>优惠券<em>（<?php echo $couponcount; ?>张）</em></span>
                    <img class="righGo" src="/images/account/right.png">
                </li>
            </a>
        </ul>
    </div>
    <div class="options">
        <ul>
            <a href="/new/prize">
                <li>
                    <img class="firstImg" style="display: inline-block;margin-top: 15px;" src="/newdev/images/yyy302/jiangpin.png"/>
                    <span>我的奖品<em>（<?php echo $prize; ?>个）</em></span>
                    <img class="righGo" src="/images/account/right.png">
                </li>
            </a>
        </ul>
    </div>
    <div class="options">
        <ul>
            <a href="/new/bank">
                <li>
                    <img class="firstImg" style="display: inline-block;margin-top: 15px;" src="/images/account/yhk.png"/>
                    <span>银行卡<em>（<?php echo $bank; ?>张）</em></span>
                    <img class="righGo" src="/images/account/right.png">
                </li>
            </a>
        </ul>
    </div>
    <div style="height:1em;width: 100%;background-color: initial;"></div>
    <div class="options">
        <ul>
            <a href="/borrow/tradinglist/index">
                <li>
                    <img class="firstImg" style="width: 20px;" src="/images/account/card.png"/>
                    <span>借款记录</span>
                    <img class="righGo" src="/images/account/right.png">
                </li>
            </a>
        </ul>
    </div>
    <div class="options">
        <ul>
            <a href="/mall/index/goodsrecord">
                <li>
                    <img class="firstImg" style="width: 20px;" src="/images/account/mall.png"/>
                    <span>商城订单</span>
                    <img class="righGo" src="/images/account/right.png">
                </li>
            </a>
        </ul>
    </div>
    <div style="height:1em;width: 100%;background-color: initial;"></div>
    <div class="options">
        <ul>
            <a href="/new/invitation/distribute">
                <li>
                    <img class="firstImg" style="width: 20px;" src="/images/account/card3.png"/>
                    <span>邀请认证</span>
                    <img class="righGo" src="/images/account/right.png">
                </li>
            </a>
        </ul>
    </div>
    <div style="height:1em;width: 100%;background-color: initial;"></div>
    <div class="options oioioi">
        <ul>
<!--            <a href="/new/supply">
                <li>
                    <img class="firstImg" style="width: 20px;" src="/images/account/card6.png"/>
                    <span>补充资料</span>
                    <img class="righGo" src="/images/account/right.png">
                </li>
            </a>-->
            <a href="/new/message">
                <li>
                    <img class="firstImg" style="width: 20px;" src="/images/account/message.png"/>
                    <span>消息中心</span>
                    <img class="righGo" src="/images/account/right.png">
                    <?php if($unread_message_count > 0){?>
                        <i class="message_num"><?php echo $unread_message_count;?></i>
                    <?php }?>
                </li>
            </a>

        </ul>
    </div>
</div>
<?= $this->render('/layouts/footer_new', ['page' => 'person','log_user_id'=>$userinfo->user_id]) ?>