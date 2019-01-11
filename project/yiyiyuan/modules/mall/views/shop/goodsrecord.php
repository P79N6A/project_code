<?php if($goods_list){ ?>
    <?php foreach ($goods_list as $k=>$v): ?>
        <div class="xq_allcont truexqy martop10">
            <a href="/mall/shop/orderdetails?order_id=<?=$v['order_id'];?>">
                <div class="xq_all_imgtxt newtjia">
                    <div class="timeztai">
                        <span><?=$v['create_time']; ?></span>
                        <em>
                            <?php if($v['status'] == 0){ ?>
                                待支付
                            <?php }elseif (in_array($v['status'],[1,2])){ ?>
                                待收货
                            <?php }elseif($v['status'] == 3){ ?>
                                已完成
                            <?php } ?>
                        </em>
                    </div>
                    <img src="<?=$v['pic_url']; ?>">
                    <div class="xq_all_txtxt wdya60">
                        <h3><?=$v['goods_name']; ?></h3>
                        <div><span><?=!empty($v['colour']) ? $v['colour'] : ''; ?></span><span><?=!empty($v['edition']) ? $v['edition'] : ''; ?></span></div>
                        <p>￥<?=$v['goods_money']; ?></p>
                    </div>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
<?php }else{ ?>
    <div class="huikuannone">
        <img src="/292/images/huikuannone.png">
        <p>您还没有购买记录哦！</p>
    </div>
<?php } ?>