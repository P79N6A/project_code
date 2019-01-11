<div class="xq_allcont truexqy">
    <div class="shwtg">
        <?php if($orderInfo->status == 0){ ?>
            待支付
        <?php }elseif (in_array($orderInfo->status,[1,2])){ ?>
            待收货
        <?php }elseif($orderInfo->status == 3){ ?>
            已完成
        <?php } ?>
    </div>
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
            <p>订单金额： <span style="float: right">￥<?=$goodsContent['money']; ?></span></p>
        </div>
    </div>

    <div class="dbhxtime">
        <p>订单编号：<em><?=$orderInfo->order_id; ?></em></p>
        <p>下单时间：<em><?=$orderInfo->create_time; ?></em></p>
    </div>

    <?php if(in_array($orderInfo->status,[0,2])){ ?>
        <div class="xq_geshi" style="position: fixed; width: 100%; background: #fff; bottom: 3.5rem;border-top: 13px #f3f3f3 solid;">
            <div class="ggym">
                <button style="background: #fff;border: 1px solid #444;color: #444;padding: 3px 10px;border-radius: 5px;position: relative;left: 75%;">
                    <?php if($orderInfo->status == 0){ ?>
                        <span id = 'toPay'>去支付</span>
                    <?php }elseif ($orderInfo->status == 2){ ?>
                        <span id = 'get'>确认收货</span>
                    <?php } ?>
                </button>
            </div>
        </div>
    <?php } ?>
</div>
<script type="text/javascript">
    $(function(){
        var orderId = '<?=$orderInfo->order_id;?>';
        var money = '<?=$goodsContent['money'];?>';
        var csrf = '<?php echo $_csrf; ?>';
        var bank_count = <?php echo $bank_count; ?>;
        $("#get").click(function() {
            $("#get").attr("disabled", "disabled");
            $.post("/mall/shop/getadd", {_csrf:csrf, orderId: orderId}, function(result) {
                var data = eval("(" + result + ")");
                if (data.res_code == 0) {
                    var location_href = "/mall/shop/orderdetails?order_id="+orderId;
                    window.location = location_href;
                } else {
                    alert(data.res_data);
                    $("#get").removeAttr("disabled");
                    return false;
                }
            });
        });

        $("#toPay").click(function() {
            if(bank_count == 0 || bank_count < 0){
                alert('请先到“我”添加您的银行卡！')
                return false;
            }
            $("#toPay").attr("disabled", "disabled");
            var location_href = href="/mall/shop/repaychoose?order_id="+orderId;
            window.location = location_href;
        });

    });
</script>