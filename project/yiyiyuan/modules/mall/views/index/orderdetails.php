<div class="xq_allcont truexqy">
    <div class="shwtg"><?php if($orderInfo->status == 0){ ?>待审核<?php }else{ ?>审核未通过<?php } ?></div>
    <div class="add_dizhi">
        <ul class="list-address">
            <li class="address_cont1"><img class="address" src="/292/images/address.png"></li>
            <li class="address_cont2 padall">
                <p class="padall">
                    <span class="name"><em><?=$orderInfo->address->receive_name; ?></em> <i><?=$orderInfo->address->receive_mobile; ?></i></span>
                    <span class="num"></span>
                </p>
                <p><?=$orderInfo->address->address_detail; ?></p>
            </li>
        </ul>
    </div>
    <div style="clear: both;height: 1px; background: #e1e1e1;"></div>
    <div class="xq_all_imgtxt newtjia">
        <img src="<?= !empty($goodsContent['pic_url']) ? \app\commonapi\ImageHandler::getUrl($goodsContent['pic_url']) : ''; ?>">
        <div class="xq_all_txtxt">
            <h3><?=$goodsContent['goods_name']; ?></h3>
            <div><span><?=$goodsContent['colour']; ?></span><span><?=$goodsContent['edition']; ?></span></div>
            <p>￥<?=$goodsContent['money']; ?></p>
        </div>
    </div>

    <div class="xq_geshi">
        <div class="ggym">
            <p>分期付款</p>
            <div class="youbianjl  ">
                <div class="youbianjlone chagwe">
                    <span class="bianjlone fontsze">￥<?=$orderInfo->money; ?> × <?=$orderInfo->terms; ?>期 <em>（包含手续费）</em></span>
                </div>
            </div>
        </div>
    </div>
    <div class="dbhxtime">
        <p>订单编号：<em><?=$orderInfo->order_id; ?></em></p>
        <p>下单时间：<em><?=$orderInfo->create_time; ?></em></p>
    </div>

</div>