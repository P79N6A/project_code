<?php  $loan_btn = '无';
       $daichao_btn = '无';
       $czzx_btn = '无'; //充值中心
       $clwb_btn = '无'; //潮流腕表
?>
<div class="w_home">
    <div class="w_homeTop">
        <!--顶部导航开始-->
        <div class="w_topBanner" style="overflow: hidden;">
            <div class="swiper-containert">
                <div class="swiper-wrapper">
                    <div class="swiper-slide w_swiperTop"><a href="javascript:;" class="swiper-active">推荐</a></div>
                    <?php if ((!$is_trial && !$is_white && $is_zhirongyaoshi) || ($is_android && !$is_white && $is_zhirongyaoshi)): ?>
                        <div class="swiper-slide w_swiperTop">
                            <a href="javascript:;" onclick="daichaoType()">贷款超市<img src="/292/images/images/new.png"></a>
                        </div>
                    <?php endif; ?>
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
                    <div class="swiper-slide"><a onclick="attention()"><img src="/images/banner/attention.png?v=12"></a></div>
                    <?php if ((!$is_trial && !$is_white) || ($is_android && !$is_white)): ?>
                        <?php if ($is_zhirongyaoshi): ?>
                        <div class="swiper-slide"><a href="javascript:;" onclick="daichao()"><img src="/images/daichao_1126.jpg?v=12"></a></div>
                        <?php endif; ?>
                        <div class="swiper-slide"><a href="javascript:;" onclick="closeHtml()"><img src="/292/images/banner3.png"></a></div>
                    <?php endif; ?>
                    <!--<div class="swiper-slide w_swiperTop"><a href="http://www.idpsofa.cn"><img src="/292/images/maotai.png"></a></div>-->
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
                    <?php if ((!$is_trial && !$is_white) || ($is_android && !$is_white)): ?>
                        <?php $loan_btn = '有';?>
                        <div class="swiper-slide">
                            <a href="javascript:void(0);" onclick="closeHtml()">
                                <div>
                                    <img src="/298/images/jb.gif" alt="">
                                    <span>借款</span>
                                </div>
                            </a>
                        </div>
                        <?php if ($is_zhirongyaoshi): ?>
                            <?php $daichao_btn = '有';?>
                        <div class="swiper-slide">
                            <a href="javascript:void(0);" onclick="daichaoType()">
                                <div>
                                    <img src="/298/images/daichao.png" alt="">
                                    <img src="/292/images/images/new.png" style="width: 30px;height: 11px;position: absolute;top: -0.1px;right: -8px;">
                                    <span>贷款超市</span>
                                </div>
                            </a>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php foreach ($all_goods_types as $k => $v): ?>
                        <?php if (!in_array($v->type,[2,3])): ?>
                            <?php if($v->category_id==4):?>
                                <div class="swiper-slide">
                                    <a onclick="gotologin()">
                                        <div>
                                            <img src="/340/images/xiaohuazhu.png" alt="">
<!--                                            <img src="/340/images/shop.png" style="width: 35px;height: 15px;position: absolute;top: -7.1px;right: -13px;">-->
                                            <span>特卖商城</span>
                                        </div>
                                    </a>
                                </div>
                            <?php else: ?>
                            <div class="swiper-slide">
                                <a href="/mall/store/list?type=<?= $v->id; ?>&user_id_store=<?= $user_id_encryption; ?>">
                                    <div>
                                        <img src="<?= \app\commonapi\ImageHandler::getUrl($v->classify_img); ?>" alt="">
                                        <span><?= $v->classify_name; ?></span>
                                    </div>
                                </a>
                            </div>
                            <?php endif;?>
                        <?php elseif((!$is_trial && !$is_white) || ($is_android && !$is_white)): ?>
                            <?php   //页面诸葛打点数据需要
                                    if(!empty($v->classify_name) && ($v->classify_name == '充值中心') ){
                                        $czzx_btn = '有';
                                    }elseif(!empty($v->classify_name) && ($v->classify_name == '潮流腕表') ){
                                        $clwb_btn = '有';
                                    }
                            ?>
                            <div class="swiper-slide">
                                <a href="javascript:void(0);" onclick="doNewShop('<?php echo $v->classify_name;?>')">
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
                                    <a onclick="gotologin()">
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
    <?php \app\common\PLogger::getInstance('weixin','',$user_id); ?>
    <?php $json_data = \app\common\PLogger::getJson();?>

    var baseInfoss = eval( '('  + '<?php echo $json_data; ?>' + ')' );
    var user_id = '<?php echo $user_id;?>';
    var is_app = '<?php echo $is_app;?>';
    var loan_btn = '<?php echo $loan_btn;?>';
    var daichao_btn = '<?php echo $daichao_btn;?>';
    var czzx_btn = '<?php echo $czzx_btn;?>';
    var clwb_btn = '<?php echo $clwb_btn;?>';
    
    //商城统计
    if(is_app){
        $.get('/new/st/statisticssave?type=1410&user_id='+user_id);
    }

    //关闭html
    function closeHtml(name) {
        var event = name || 'loan';
        tongji(event,baseInfoss);
        if(is_app){
            $.get('/new/st/statisticssave?type=1411&user_id='+user_id);
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
    
    function attention() {
     window.location.href = '<?php echo Yii::$app->request->hostInfo; ?>' + '/new/activity/attention';
    }

    //贷超
    function daichaoType() {
        tongji('daichao',baseInfoss);
        $.get('/new/st/statisticssave?type=1413');
        if(is_app){
            closeHtml('daichaoType');
        } else {
            window.location.href = '<?php echo Yii::$app->request->hostInfo;?>'+'/borrow/reg/login?url=/mall/store/index';
        }
    }

    //banner跳贷超
    function daichao() {
        if (is_app) {
            closeHtml('daichao');
        } else {
            window.location.href = '<?php echo Yii::$app->request->hostInfo;?>'+'/borrow/reg/login?url=/mall/store/index';
        }
    }

    //游戏
    function playgame(type) {
        $.get('/new/st/statisticssave?type=1412');
        if (is_app) {
            closeHtml('playgame');
        } else {
            window.location.href = '<?php echo Yii::$app->request->hostInfo;?>'+'/borrow/reg/login?url=/mall/store/index';
        }
    }
    
    //去新商城
    function doNewShop(classify_name) {
        zhuge.track('进入商城', {
            '类别名称': classify_name,
            '用户状态': '未登录'
        }, function(){
            if (is_app) {
                closeHtml();
            } else {
                window.location.href = '<?php echo Yii::$app->request->hostInfo;?>'+'/borrow/reg/login?url=/mall/store/index';
            }
        });
    }


    function gotologin(){
        var url = '/mall/store';
        window.myObj.goToLogin(url);
        function goToLogin() {

        }
    }

    $(function(){
        //诸葛埋点-商城首页
        zhuge.track('商城首页', {
             '用户ID': user_id,
         });
    })

    function gotologin(){
        var url = '/mall/store';
        window.myObj.goToLogin(url);
        function goToLogin() {

        }
    }
 

</script>