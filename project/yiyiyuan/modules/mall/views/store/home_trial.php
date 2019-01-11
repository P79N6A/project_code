<div class="w_home">
    <div class="w_homeTop">
        <!--顶部导航开始-->
        <div class="w_topBanner" style="overflow: hidden;">
            <div class="swiper-containert">
                <div class="swiper-wrapper">
                    <div class="swiper-slide w_swiperTop"><a href="javascript:;" class="swiper-active">推荐</a></div>
                    <?php foreach ($all_goods_types as $k => $v): ?>
                        <?php if (!in_array($v->type,[2,3])): ?>
                        <div class="swiper-slide w_swiperTop"><a href="/mall/store/list?type=<?= $v->id; ?>&user_id_store=<?= $user_id_encryption; ?>"><?= $v->classify_name; ?></a></div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <!--顶部导航结束-->
        <!--banner开始-->
        <div class="w_topSlide" style="overflow: hidden;">
            <div class="swiper-conLoop">
                <div class="swiper-wrapper">
                    <div class="swiper-slide w_swiperTop"><a href="/mall/store/detail?gid=1432&user_id_store=<?= $user_id_encryption; ?>"><img src="/292/images/banner1.jpg"></a></div>
                    <div class="swiper-slide w_swiperTop"><a href="/mall/store/detail?gid=945&user_id_store=<?= $user_id_encryption; ?>"><img src="/292/images/banner2.jpg"></a></div>
                </div>
            </div>
        </div>
        <!--banner结束-->
        <div class="advertisement">
            <div><img src="/borrow/350/images/zhengpin.png" alt=""><span>正品保证</span></div>
            <div><img src="/borrow/350/images/zhengpin.png" alt=""><span>极速发货</span></div>
            <div><img src="/borrow/350/images/zhengpin.png" alt=""><span>全场包邮</span></div>
        </div>
        <!--中间导航开始-->
        <div class="w_centerLoop">
            <div class="swiper-centerLoop">
                <div class="swiper-wrapper">
                    <?php foreach ($all_goods_types as $k => $v): ?>
                        <?php if (!in_array($v->type,[2,3])): ?>
                            <div class="swiper-slide">
                                <a href="/mall/store/list?type=<?= $v->id; ?>&user_id_store=<?= $user_id_encryption; ?>">
                                    <div>
                                        <img src="<?= \app\commonapi\ImageHandler::getUrl($v->classify_img); ?>" alt="">
                                        <?php if (in_array($v->type,[2,3])): ?>
                                            <img src="/340/images/zhuanshou.png" style="width: 30px;height: 11px;position: absolute;top: -0.1px;right: -8px;">
                                        <?php endif; ?>
                                        <span><?= $v->classify_name; ?></span>
                                    </div>
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <!--中间导航结束-->
    </div>
    <!--商品开始-->
    <?php foreach ($tj_goods_types as $k => $v): ?>
        <div class="w_homeCon">
            <div class="w_contGoods">
                <div class="w_goodsTop">
                    <div class="W_goodsTitle">
                        <span></span>
                        <div><?php echo $v->classify_name; ?></div>
                        <span></span>
                    </div>
                </div>
                <div class="goodsImg">
                    <a href="/mall/store/list?type=<?= $v->id; ?>&user_id_store=<?= $user_id_encryption; ?>">
                        <img src="/292/images/index_gg<?= $v->id; ?>.jpg">
                    </a>
                </div>
                <div class="w_goodsBox">
                    <div class="swiper-goodsLoop">
                        <div class="swiper-wrapper">
                            <?php foreach ($v->goodsList as $kk => $vv) { ?>
                                <div class="swiper-slide">
                                    <a href="/mall/store/detail?gid=<?= $vv->id; ?>&user_id_store=<?= $user_id_encryption; ?>">
                                        <img src="<?= isset($vv->pic->pic_url) ? \app\commonapi\ImageHandler::getUrl($vv->pic->pic_url) : ''; ?>" alt="">
                                        <span class="w_goodsT"><?= $vv->goods_name; ?></span>
                                        <span class="w_price"><?= $vv->goods_price; ?>元</span>
                                    </a>
                                </div>
                            <?php }; ?>
                            <div class="swiper-slide">
                                <a href="/mall/store/list?type=<?= $v->id; ?>&user_id_store=<?= $user_id_encryption; ?>">
                                    <img src="/borrow/350/images/w-more.png" alt="" class="w_more">
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    <!--商品结束-->
</div>
<?php $jsUrl = Yii::$app->params['jsUrl']; ?>
<script src="<?php echo $jsUrl; ?>"></script>
<script>
    var user_id = '<?php echo $user_id;?>';
    var is_app = '<?php echo $is_app;?>';
    
    //商城统计
    if(is_app){
        $.get('/new/st/statisticssave?type=1410&user_id='+user_id);
    }
    //诸葛埋点-商城首页
    $(function(){
        zhuge.track('商城首页', {
            '用户ID': user_id,
        });
    })

</script>