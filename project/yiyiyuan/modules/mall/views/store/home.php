<?php
$loan_btn = '无';
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
                    <?php if ($is_zhirongyaoshi): ?>
                        <div class="swiper-slide w_swiperTop">
                            <a style="position: relative;display: block;" href="javascript:;" onclick="daichaoType()">贷款超市<img style="width: 24px;height: 13px;position: absolute;right: -6px;top: 4px;display: block;" src="/292/images/images/new.png"></a>
                        </div>
                    <?php endif; ?>
                    <?php foreach ($all_goods_types as $k => $v): ?>
                        <?php if (!in_array($v->type, [2, 3])): ?>
                            <div class="swiper-slide w_swiperTop">
                                <a onclick="shopTabClick('<?= $v->id . "','" . $user_id_encryption . "','" . 'shopTabClick_' ?>')"><?= $v->classify_name; ?></a>
                            </div>
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
                    <?php if ($is_zhirongyaoshi): ?>
                        <div class="swiper-slide"><a href="javascript:;" onclick="daichao()"><img src="/images/daichao_1126.jpg?v=12"></a></div>
                    <?php endif; ?>
                    <div class="swiper-slide"><a href="javascript:;" onclick="closeHtml()"><img src="/292/images/banner3.png"></a></div>
                    <!-- <div class="swiper-slide w_swiperTop"><a href="http://www.idpsofa.cn"><img src="/292/images/maotai.png"></a></div> -->
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
                    <?php if ($is_app): ?>
                        <?php $loan_btn = '有'; ?>
                        <div class="swiper-slide">
                            <a href="javascript:void(0);" onclick="closeHtml()">
                                <div>
                                    <img src="/298/images/jb.gif" alt="">
                                    <?php if ($has_loan_repay || $has_ious_repay): ?>
                                        <img src="/292/images/images/repay.png" style="width: 35px;height: 15px;position: absolute;top: -7.1px;right: -13px;">
                                    <?php endif; ?>
                                    <span>借款</span>
                                </div>
                            </a>
                        </div>
                    <?php endif; ?>
                    <?php if ($is_zhirongyaoshi): ?>
                        <?php $daichao_btn = '有'; ?>
                        <div class="swiper-slide">
                            <a href="javascript:void(0);" onclick="daichaoType()">
                                <div>
                                    <img src="/298/images/daichao.png" alt="">
                                    <img src="/292/images/images/new.png" style="width: 35px;height: 15px;position: absolute;top: -7.1px;right: -13px;">
                                    <span>贷款超市</span>
                                </div>
                            </a>
                        </div>
                    <?php endif; ?>
                    <?php foreach ($all_goods_types as $k => $v): ?>
                        <?php if (in_array($v->type, [2, 3])): ?>

                            <?php
                            //页面诸葛打点数据需要
                            if (!empty($v->classify_name) && ($v->classify_name == '充值中心')) {
                                $czzx_btn = '有';
                            } elseif (!empty($v->classify_name) && ($v->classify_name == '潮流腕表')) {
                                $clwb_btn = '有';
                            }
                            ?>
                            <div class="swiper-slide">
                                <a href="javascript:void(0);" onclick="openShopajax('<?php echo $v->category_id;?>','<?php echo $v->classify_name;?>')">
                                    <div>
                                        <img src="<?= \app\commonapi\ImageHandler::getUrl($v->classify_img); ?>" alt="">
                                        <img src="/340/images/zhuanshou.png" style="width: 35px;height: 15px;position: absolute;top: -7.1px;right: -13px;">
                                        <span><?= $v->classify_name; ?></span>
                                    </div>
                                </a>
                            </div>
                        <?php else: ?>
                            <?php if($v->category_id==4):?>
                                <div class="swiper-slide">
                                    <a href="/borrow/requestother">
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
                            <?php endif; ?>
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
                    <a onclick="shopTabClick('<?= $v->id . "','" . $user_id_encryption . "','" . 'shopBannerClick_'; ?>')">
                        <img src="/292/images/index_gg<?= $v->id; ?>.jpg">
                    </a>
                </div>
                <div class="w_goodsBox">
                    <div class="swiper-goodsLoop">
                        <div class="swiper-wrapper">
                            <?php foreach ($v->goodsList as $kk => $vv) { ?>
                                <div class="swiper-slide">
                                    <a onclick="openShopgoodsajax('<?= $vv->id."','".$user_id_encryption.""?>')">
                                        <img src="<?= isset($vv->pic->pic_url) ? \app\commonapi\ImageHandler::getUrl($vv->pic->pic_url) : ''; ?>" alt="">
                                        <span class="w_goodsT"><?= $vv->goods_name; ?></span>
                                        <span class="w_price"><?= $vv->goods_price; ?>元</span>
                                    </a>
                                </div>
                            <?php }; ?>
                            <div class="swiper-slide">
                                <a onclick="shopTabClick('<?= $v->id . "','" . $user_id_encryption . "','" . 'shopLookMore_' ?>')">
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

    <!--遮罩-->
    <div class="y-mask" hidden></div>
    <!--提示弹窗开始-->
    <div class="y-popup" hidden>
        <i class="y-close-btn"></i>
        <h3 class="y-popup-h3">您有一笔进行中的借款</h3>
        <p class="y-popup-p">请结清借款后再试，详情可在账单列表中查看</p>
        <button class="y-know">立即查看</button>
    </div>
    <!--提示弹窗开始-->

    <div class="y-popup-banner" hidden>
        <i class="close-banner"></i>
        <div class="y-banner-wrap">
            <img src="/borrow/350/images/banner.png" alt="">
        </div>
    </div>

    <!--首次借款引导开始-->
    <div class="y-popup-loan" hidden></div>
    <!--首次借款引导结束-->

    <div class="y-popup-credit" hidden>
        <img class="y-credit-top" src="/borrow/350/images/active.png" alt="">
        <div class="y-credit-main">
            <p class="y-credit-tit">恭喜您！</p>
            <p class="y-credit-tips">您已成为先花一亿元新品7天短贷体验幸运用户，现在申请完成评测即可享受以下特权：</p>
            <ul>
                <li>1.10元7天享乐券，请前往【优惠券】中查看；</li>
                <li>2.高通过率，高速下款；</li>
                <li>3.8月10日-8月25日发起借款并还款的用户即可获得1张20元的7天乐享券。</li>
                <li>（本活动用户仅可参加一次）</li>
            </ul>
            <button class="y-credit-btn">发起借款</button>
            <span>本活动最终解释权归先花一亿元所有</span>
            <i class="y-credit-close"></i>
        </div>
    </div>

    <!--轮播活动banner弹窗开始-->
    <div class="y-popup-slider" hidden>
        <div class="swiper-main">
            <div class="swiper-wrapper">
                <?php foreach ($tanchuan_data as $key => $value): ?>
                    <div class="swiper-slide" onclick="elasticLayer('<?php echo (isset($_GET['type']) ? $_GET['type'] : '') . "','" . $value['id'] . "','" . $value['click_url']; ?>')"><img src="<?php echo Yii::$app->params['img_url'] . $value['banner_pic_url'] ?>"></div>
                <?php endforeach; ?>
            </div>
        </div>
        <i class="y-credit-close"></i>
    </div>
    <!--轮播活动banner弹窗结束-->
</div>
<div class="alert-toast" id="cue_activating" hidden style="width: 90%;position: fixed; top: 48%;left: 5%;border-radius: 5px; z-index: 100; padding:7px 0;background:rgba(0,0,0,0.5); color: #fff;text-align: center;font-size: 14px;opacity: 0.7;height:26px;line-height: 26px; "></div>
<?php $jsUrl = Yii::$app->params['jsUrl']; ?>
<script src="<?php echo $jsUrl; ?>"></script>
<script>
<?php \app\common\PLogger::getInstance('weixin', '', $user_id); ?>
<?php $json_data = \app\common\PLogger::getJson(); ?>

                        var baseInfoss = eval('(' + '<?php echo $json_data; ?>' + ')');
                        var user_id = '<?php echo $user_id; ?>';
                        var is_app = '<?php echo $is_app; ?>';
                        var is_android = '<?php echo $is_android; ?>';
                        var csrf = '<?php echo $csrf; ?>';
                        var loan_btn = '<?php echo $loan_btn; ?>';
                        var daichao_btn = '<?php echo $daichao_btn; ?>';
                        var czzx_btn = '<?php echo $czzx_btn; ?>';
                        var clwb_btn = '<?php echo $clwb_btn; ?>';

                        //商城统计
                        if (is_app) {
                            $.get('/new/st/statisticssave?type=1410&user_id=' + user_id);
                        }

                        //关闭html
                        function closeHtml(name) {
                            var event = name || 'loan';
                            tongji(event, baseInfoss);
                            if (is_app) {
                                $.get('/new/st/statisticssave?type=1411&user_id=' + user_id);
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
                            } else {
                                window.location.href = '<?php echo Yii::$app->request->hostInfo; ?>' + '/borrow/loan';
                            }
                        }

                        //贷超
                        function daichaoType() {
                            tongji('daichao', baseInfoss);
                            var mobile = '<?php echo $mobile; ?>';
                            $.get('/new/st/statisticssave?type=1413');
                            if (is_app && mobile == '') {
                                closeHtml('daichaoType');
                            } else if (mobile == '') {
                                window.location.href = '<?php echo Yii::$app->request->hostInfo; ?>' + '/borrow/reg/login?url=/mall/store/index';
                            } else {
                                window.location.href = 'http://dc.zhirongyaoshi.com/?utm_source=loan_btn&channel=loan_btn&phone=' + mobile;
                            }
                        }

                        //banner跳贷超
                        function daichao() {
                            var mobile = '<?php echo $mobile; ?>';
                            if (is_app && mobile == '') {
                                closeHtml('daichao');
                            } else if (mobile == '') {
                                window.location.href = '<?php echo Yii::$app->request->hostInfo; ?>' + '/borrow/reg/login?url=/mall/store/index';
                            } else {
                                window.location.href = 'http://dc.zhirongyaoshi.com?channel=hbanner&phone=' + mobile;
                            }
                        }

                        //游戏
                        function playgame(type) {
                            var mobile = '<?php echo $mobile; ?>';
                            $.get('/new/st/statisticssave?type=1412');
                            if (is_app && mobile == '') {
                                closeHtml('playgame');
                            } else if (mobile == '') {
                                window.location.href = '<?php echo Yii::$app->request->hostInfo; ?>' + '/borrow/reg/login?url=/mall/store/index';
                            } else {
                                window.location.href = 'http://dc.zhirongyaoshi.com/guide/game?phone=' + mobile;
                            }
                        }

                        //订单转售商城
                        function openShopajax(category_id, classify_name) {
                            $.ajax({
                                url: '/mall/store/xhshopajax',
                                type: 'post',
                                data: {_csrf: csrf, category_id: category_id},
                                dataType: 'json',
                                success: function (msg) {
                                    console.log(msg.rsp_code);
                                    if (msg.rsp_code == '0000') {
                                        zhuge.track('进入商城', {
                                            '类别名称': classify_name,
                                            '用户状态': msg.rsp_data.credit_status
                                        }, function () {
                                            window.location.href = msg.rsp_data.url;
                                        });
                                    } else if (msg.rsp_code == '0001') { //请登录
                                        zhuge.track('进入商城', {
                                            '类别名称': classify_name,
                                            '用户状态': '未登录'
                                        });
                                        if (isiOS) {
                                            window.myObj.toPage(ios);
                                        } else if (isAndroid) {
                                            window.myObj.toPage(android, position);
                                        }
                                    } else if (msg.rsp_code == '0007') { //商城暂未开放，敬请期待
                                        zhuge.track('进入商城', {
                                            '类别名称': classify_name,
                                            '用户状态': '商城暂未开放'
                                        });
                                        $('#cue_activating').html(msg.rsp_msg);
                                        $('#cue_activating').show();
                                        setTimeout(function () {
                                            $('#cue_activating').hide();
                                        }, 3000);
                                    } else if (msg.rsp_code == '0002') { //进行中的借款
                                        zhuge.track('进入商城', {
                                            '类别名称': classify_name,
                                            '用户状态': '已存在借款'
                                        });

                                        $('.y-popup-h3').html('您有一笔进行中的借款');
                                        $('.y-popup-p').html('请结清借款后再试，详情可在账单列表中查看');
                                        $(".y-know").click(function () {
                                            tongji('check_bill', baseInfoss);
                                            setTimeout(function () {
                                                if (is_app == 1) {
                                                    if (is_android) {
                                                        window.myObj.toPage("com.business.bill.BillActivity", 1);
                                                    } else {
                                                        window.myObj.toPage("BillViewController");
                                                    }
                                                } else {
                                                    window.location.href = msg.rsp_data.url;
                                                }
                                            }, 100);
                                        });
                                        $('.y-mask').show();
                                        $('.y-popup').show();
                                    } else if (msg.rsp_code == '0003') { //待支付订单
                                        zhuge.track('进入商城', {
                                            '类别名称': classify_name,
                                            '用户状态': '已存在商城订单'
                                        });
                                        $('.y-popup-h3').html('您有一笔待支付订单');
                                        $('.y-popup-p').html('请在24小时内完成支付，剩余时间' + msg.rsp_data.time);
                                        $(".y-know").click(function () {
                                            tongji('check_wait_order', baseInfoss);
                                            setTimeout(function () {
                                                window.location.href = msg.rsp_data.url;
                                            }, 100);
                                        });
                                        $('.y-mask').show();
                                        $('.y-popup').show();
                                    } else if (msg.rsp_code == '0004') { //进行中的订单
                                        zhuge.track('进入商城', {
                                            '类别名称': classify_name,
                                            '用户状态': '已存在商城订单'
                                        });
                                        $('.y-popup-h3').html('您有一笔进行中订单');
                                        $('.y-popup-p').html('请前往订单查看订单详情');
                                        $(".y-know").click(function () {
                                            tongji('check_doing_order', baseInfoss);
                                            setTimeout(function () {
                                                window.location.href = msg.rsp_data.url;
                                            }, 100);
                                        });
                                        $('.y-mask').show();
                                        $('.y-popup').show();
                                    } else {
                                        console.log(msg.rsp_msg);
                                        return false;
                                    }
                                },
                                error: function (msg) {
                                    console.log('点击先花商城ajax请求失败' + msg)
                                }
                            });
                        }

    //商城商品弹窗
    function openShopgoodsajax(gid,user_id_encryption){
        $.ajax({
            url: '/mall/store/shopgoodsajax',
            type: 'post',
            data:{_csrf:csrf,},
            dataType: 'json',
            success: function(msg){
                console.log(msg.rsp_code);
                if(msg.rsp_code == '0000'){
                    window.location.href = '/mall/store/detail?gid='+gid+'&user_id_store='+user_id_encryption;
                }else if(msg.rsp_code == '0001'){ //请登录
                    if (isiOS) {
                        window.myObj.toPage(ios);
                    } else if (isAndroid) {
                        window.myObj.toPage(android, position);
                    }
                }else if(msg.rsp_code == '0002'){ //进行中的借款
                    $('.y-popup-h3').html('您有一笔进行中的借款');
                    $('.y-popup-p').html('请结清借款后再试，详情可在账单列表中查看');
                    $(".y-know").click(function(){
                        tongji('check_bill',baseInfoss);
                        setTimeout(function () {
                            if(is_app == 1){
                                if (is_android) {
                                    window.myObj.toPage("com.business.bill.BillActivity", 1);
                                } else {
                                    window.myObj.toPage("BillViewController");
                                }
                            }else{
                                window.location.href = msg.rsp_data.url;
                            }
                        }, 100);
                    });
                    $('.y-mask').show();
                    $('.y-popup').show();
                }else if(msg.rsp_code == '0008'){ //进行中的借款
                    $('#cue_activating').html(msg.rsp_msg);
                    $('#cue_activating').show();
                    setTimeout(function () {
                        $('#cue_activating').hide();
                    }, 3000);
                }else if(msg.rsp_code == '0004'){ //进行中的订单
                    $('.y-popup-h3').html('您有一笔进行中订单');
                    $('.y-popup-p').html('请前往订单查看订单详情');
                    $(".y-know").click(function(){
                        setTimeout(function () {
                            window.location.href = msg.rsp_data.url;
                        }, 100);
                    });
                    $('.y-mask').show();
                    $('.y-popup').show();
                }else if(msg.rsp_code == '0007'){//额度获取中
                    $('#cue_activating').html(msg.rsp_msg);
                    $('#cue_activating').show();
                    setTimeout(function () {
                        $('#cue_activating').hide();
                    }, 3000);
                }else if(msg.rsp_code == '0003'){ //待支付订单
                    $('.y-popup-h3').html('您有一笔待支付订单');
                    $('.y-popup-p').html('请在24小时内完成支付，剩余时间'+msg.rsp_data.time);
                    $(".y-know").click(function(){
                        setTimeout(function () {
                            window.location.href = msg.rsp_data.url;
                        }, 100);
                    });
                    $('.y-mask').show();
                    $('.y-popup').show();
                }else{
                    console.log(msg.rsp_msg);
                    return false;
                }
            },
            error:function(msg){
                console.log('点击商城商品ajax请求失败'+msg)
            }
        });
    }

    //商城首页点击统计
    function shopTabClick(id,user_id_store,name) {
        tongji(name+id,baseInfoss);
        setTimeout(function () {
            window.location.href="/mall/store/list?type="+id+"&user_id_store="+user_id_store;
        }, 100);
    }


    function attention() {
        window.location.href = '<?php echo Yii::$app->request->hostInfo; ?>' + '/new/activity/attention';
    }


    //首次登陆引导借款弹窗
    var popup_loan = '<?php echo $popup_loan; ?>';
    if (popup_loan) {
        $('.y-mask').show();
        $('.y-popup-loan').show();
    }

    //轮播活动弹窗
    var popup_slider = '<?php echo $popup_slider; ?>';
    if (popup_slider) {
        $('.y-mask').show();
        $('.y-popup-slider').show();
    }

    //轮播活动弹窗操作
    function elasticLayer(type, id, url) {
        if (url == 'http://mp.yaoyuefu.com/borrow/loan') {
            if (!is_app) {
                tongji('daichaoAlertDetails_weixin_' + id, baseInfoss);
                window.location.href = '/borrow/loan';
                return false;
            }
            tongji('daichaoAlertDetails_' + id, baseInfoss);
            var u = navigator.userAgent, app = navigator.appVersion;
            var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Linux') > -1;
            var isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/);
            var android = "com.business.main.MainActivity";
            var ios = "loanViewController";
            var position = "-1";
            console.log(isiOS);
            console.log(isAndroid);
            if (isiOS) {
                window.myObj.toPage(ios);
            } else if (isAndroid) {
                window.myObj.toPage(android, position);
            }
        } else {
            if (!is_app) {
                tongji('daichaoAlertDetails_weixin_' + id, baseInfoss);
                window.location.href = url + '?user_id=' + user_id;
            } else {
                if (mobb == '') {
                    closeHtml('daichaoAlertDetails_' + id);
                } else {
                    tongji('daichaoAlertDetails_' + id, baseInfoss);
                    window.location.href = url + '?user_id=' + user_id;
                }
            }
        }

    }

    $('.y-credit-close').click(function () {
        $('.y-mask').hide();
        $('.y-popup-slider').hide();
        $('.y-popup-credit').hide();
    })
    $('.y-popup-loan').click(function () {
        $('.y-mask').hide();
        $('.y-popup-loan').hide();
    });
    $('.close-banner').click(function () {
        $('.y-mask').hide();
        $('.y-popup-banner').hide();
    });
    $('.y-close-btn').click(function () {
        $('.y-mask').hide();
        $('.y-popup').hide();
    })
    $(function () {
        //诸葛埋点-商城首页
        zhuge.track('商城首页', {
            '用户ID': user_id,
        });
    })

</script>
</script>