<style>
    .swiper-pagination  .swiper-pagination-bullet{background: #f3f3f3; opacity: 1;}
    .swiper-pagination .swiper-pagination-bullet-active{background: #c90000;}

    p { margin: 0; }
    .mask {
        height: 100%;
        width: 100%;
        background: rgba(0, 0, 0, .6);
        position: fixed;
        left: 0;
        top: 0;
        z-index: 800;
    }
    .tccontent {
        position: fixed;
        top: 16%;
        left: 10%;
        z-index: 999;
        background: #fff;
        width: 80%;
        border-radius: 8px;
    }
    .tccontent img{
        width:100%;
        margin-top:-3rem;

    }
    .tccontent .ac_title{
        color: #FF1111;
        font-weight: 700;
        font-size: 1.2rem;
        text-align: center;
        margin-top:0.4rem;
    }
    .tccontent p:nth-of-type(n+2){
        font-size: .9rem;
        line-height: 1.8rem;
    }
    .tccontent p:nth-of-type(2){
        font-weight: 700;
        margin-bottom: 5px;
    }
    .ac_text{
        padding:0 24px;
    }
    .ac_text button{
        width: 80%;
        height: 3rem;
        border-radius: 1.5rem;
        outline: none;
        border:0;
        background-image: linear-gradient(-90deg, #FF4B17 0%, #F00D0D 100%);
        font-size: 1.2rem;
        color: #fff;
        display: table;
        margin:0.6rem auto;
    }
    .close{
        position: absolute;
        bottom: -3rem;
        left:50%;
        margin-left: -1.2rem;

    }
    .alert-box{
        width: 100%;
        height: 100%;
        position: fixed;
        z-index: 10000;
        top: 0;
        left: 0;
        border-top: 1px solid rgba(0, 0, 0, 0);
        background: rgba(0, 0, 0, 0.5);
        padding-top: 3rem;
        opacity: 0;
        /*display: none;*/
    }

    .alert-sw{
        width:276px;
        height:373px !important;
        position: fixed !important;
        z-index: 10001 !important;
        top: 12%;
        left: 50%;
        margin-left: -138px !important;
        background: rgba(0,0,0,0);
        /*display: none;*/
    }
    .
    .alert-sw img{
        width: 90%;
    }
    .alert-box .close-btn {
        height: 34px;
        width: 34px;
        margin: 130% auto 0;
    }

    .swiper-pagination-bullet-active{
        background: #f00d0d !important;
    }
</style>

<?php \app\common\PLogger::getInstance('weixin', '', $user_id); ?>
<?php $json_data = \app\common\PLogger::getJson(); ?>
<div class="ydwey">
    <div class="swiper-container swiper-containert  navtitle">
        <div class="swiper-wrapper">
            <div class="swiper-slide"><a class="hover">推荐</a></div>
            <?php foreach ($allGoodsTypes as $k => $v): ?>
                <div class="swiper-slide"><a onclick="shopTabClick('<?= $v->id."','".$user_id_store."','".'shopTabClick_'?>')"><?= $v->classify_name; ?></a></div>
            <?php endforeach; ?>
        </div>
        <div class="seiper-pagination swiper-ptitt"></div>
    </div>
</div>
<div style="height: 2.6rem;"></div>
<div class="swiper-container swiper-container1">
    <div class="swiper-wrapper">
        <div class="swiper-slide"><a onclick="attention()"><img src="/images/banner/attention.png?v=12"></a></div>
        <?php if($supermarketOpen==1){?>
        <div class="swiper-slide"><a onclick="daichao('<?php echo isset($_GET['type']) ? $_GET['type'] : ''; ?>')"><img src="/images/daichao_1126.jpg?v=12"></a></div>
        <?php } ?>
        <div class="swiper-slide"><a onclick="loan('<?php echo isset($_GET['type']) ? $_GET['type'] : ''; ?>')"><img src="/292/images/banner3.png"></a></div>
<!--        <div class="swiper-slide"><a onclick="shareMoney()"><img src="/borrow/311/images/activity-banner2.png"></a></div>-->

<!--        <div class="swiper-slide"><a onclick="collage()"><img src="/borrow/311/images/activity2-banner.png"></a></div>-->

<!--        <div class="swiper-slide"><a onclick="activity3()"><img src="/borrow/activity3/images/750-360banner.png"></a></div>-->

        <div class="swiper-slide"><a href="javascript:void(0);" id="toloan_1" user_id_store="<?= $user_id_store; ?>"><img src="/292/images/banner1.jpg"></a></div>
        <div class="swiper-slide"><a href="javascript:void(0);" id="toloan_2" user_id_store="<?= $user_id_store; ?>"><img src="/292/images/banner2.jpg"></a></div>
    </div>
    <div class="swiper-pagination swiper-pagination1"></div>
</div>
<!--贷超关闭页面indexs-->
<div class="index_nav">
    <img src="/292/images/index_zpsh.jpg">
    <div class="navmes">
        <?php if (isset($_GET['type']) && $_GET['type'] == 'weixin') { ?>
        <?php } else { ?>
            <?php if ($hasRepayingLoan != 1 && $hasIousing != 1) { ?>
                <a onclick="closeHtml()" >
                    <dl>
                        <dt><img src="/298/images/jb.gif"></dt>
                        <dd>借款</dd>
                    </dl>
                </a>
            <?php } else if ((!empty($userLoanInfo) && !empty($userLoanInfo->cgRemit) && $userLoanInfo->cgRemit->remit_status == 'SUCCESS') || (!empty($userLoanInfo) && $userLoanInfo->settle_type==3) ) { ?>
                <a href="javascript:void(0);"onclick="closeHtml()">
                    <dl style="position: relative;">
                        <dt><img src="/298/images/jb.gif"></dt>
                        <?php if (in_array($userLoanInfo->business_type,[1,4,5,6])): ?>
                            <dd style="position: absolute;color: #fff;top: 0; font-size: 8px;right: 0;background: #c90000; padding: 2px 5px;border-radius: 10px;">待还款</dd>
                        <?php endif; ?>
                        <dd>借款</dd>
                    </dl>
                </a>
            <?php } else { ?>
                <a onclick="closeHtml()" >
                    <dl>
                        <dt><img src="/298/images/jb.gif"></dt>
                        <dd>借款</dd>
                    </dl>
                </a>
            <?php } ?>
        <?php } ?>
        
        <?php if($shop_switch):?>
            <a onclick="openShop()" >
                    <dl>
                        <dt style="position: relative;"><img src="/340/images/shop.png">
                            <img src="/340/images/zhuanshou.png" style="width: 30px;height: 11px;position: absolute;top: -0.1px;right: -8px;">
                        </dt>
                        <dd>先花商城</dd>
                    </dl>
            </a>
        <?php endif;?>
        <?php foreach ($allGoodsTypes as $k => $v): ?>
            <?php if ($v->id != 3 || (isset($_GET['type']) && $_GET['type'] == 'weixin')) { ?>
                <?php if($k==1 && $shop_switch):?>
                <?php else:?>
                <span class="btn" type="<?= $v->id ?>" user_id_store="<?= $user_id_store; ?>">
                    <dl>
                        <dt><img src="<?= (!empty($v->classify_img) && @fopen($v->classify_img, 'r'))?$v->classify_img:Yii::$app->params['img_url'].$v->classify_img; ?>"></dt>
                        <dd><?= $v->classify_name; ?></dd>
                    </dl>
                </span>
                <?php endif;?>
                
            <?php } ?>
        <?php endforeach; ?>
    </div>
</div>
<?php foreach ($tjGoodsTypes as $k => $v): ?>
    <div class="actvejiuyue">
        <div class="certifn">
            <div class="bortop"></div>
            <h3><?php echo $v->classify_name; ?></h3>
        </div>
        <div>
            <a onclick="shopTabClick('<?= $v->id."','".$user_id_store."','".'shopBannerClick_';?>')">
                <img src="/292/images/index_gg<?= $v->id; ?>.jpg">
            </a>
        </div>
        <div class="swiper-container   swiper-container3">
            <div class="swiper-wrapper">
                <?php foreach ($v->goodsList as $kk => $vv) : ?>
                    <div class="swiper-slide">
                        <a onclick="shopClick('<?=$vv->id."','".$user_id_store?>')">
                            <dl class="index_scym">
                                <dt><img src="<?= isset($vv->pic->pic_url) ? \app\commonapi\ImageHandler::getUrl($vv->pic->pic_url) : ''; ?>"></dt>
                                <dd>
                                    <h3><?= $vv->goods_name; ?></h3>
                                    <span><?= $vv->goods_price; ?><em>元</em></span>
                                    <p></p>
                                </dd>
                            </dl>
                        </a>
                    </div>
                <?php endforeach; ?>
                <div class="swiper-slide">
                    <a onclick="shopTabClick('<?=$v->id."','".$user_id_store."','".'shopLookMore_'?>')">
                        <dl class="index_scym" style="margin-top: 25px;">
                            <dt><img src="/292/images/more.png" style="height: 10.7rem;border:0.5px solid #e1e1e1;"></dt>
                        </dl>
                    </a>
                </div>
            </div>
            <div class="seiper-pagination swiper-p3"></div>
        </div>
    </div>
<?php endforeach; ?>
<div class="returntop"  id="goTopBtn" hidden>
    <img src="/292/images/returntop.png">
</div>
<?php
if (!empty($user_id_store) && $isShow) {
    ;
    ?>
    <div class="Hmasks inow"></div>
    <div class="tccym inow">
        <img src="/298/images/tccym.png">
        <button id="inow"></button>
    </div>
<?php } ?>
<!-- 活动弹窗 -->

<div class="Hmask" hidden></div>
<div class="erwma" hidden >
    <img id="act_img" src="/newdev/images/yyy302/allgg_img.png">
    <a class="error"  ><img src="/newdev/images/fiveactivity/errorer.png"></a>
</div>
<style type="text/css">
    /*分期首页弹窗*/
    .Hmask { width: 100%;height: 100%;background: rgba(0,0,0,.6); position: fixed;top: 0; left: 0;}
    .erwma{position: fixed;top: 20%;left: 5%;z-index: 100; width: 90%;}
    .erwma .error{top: -3.3rem;position: absolute;right: 0; width: 10%; height: 2.3rem;}
</style>
<!-- 活动弹窗 -->
<!--<link rel="stylesheet" type="text/css" href="/292/css/swiper.css"/>-->
<?php $jsUrl = Yii::$app->params['jsUrl']; ?>
<script src="<?php echo $jsUrl; ?>"></script>

<!--七天借款弹层-->
<div class="mask" id="alert1" style="display: none"></div>
<div class="tccontent" id="alert2" style="display: none">
    <img src="/292/images/active.png" alt="">
    <div class="ac_text">
        <p class="ac_title">恭喜您！</p>
        <p>您已成为先花一亿元新品7天短贷体验幸运用户，现在申请完成评测即可享受以下特权：</p>
        <p>1.10元7天享乐券，请前往【优惠券】中查看；</p>
        <p>2.高通过率，高速下款；</p>
        <p>3.8月10日-8月25日发起借款并还款的用户即可获得1张20元的7天享乐券。</p>
        <p>(本活动用户仅可参加一次)</p>
        <button onclick="sevenLoanClick('<?php echo isset($_GET['type']) ? $_GET['type'] : ''; ?>')">发起借款</button>
        <p style="font-size: 0.8rem; text-align: center;color: #A3A2A2;margin-bottom: 0.6rem">本活动最终解释权归先花一亿元所有</p>
        <img class="close" onclick="sevenCloseClick()" style="width:2.4rem;" src="/292/images/close.png" alt="">
    </div>
</div>
<!--      先花商城弹窗-->
<div class="mask" id="alert_shade" hidden style="height: 100%;width: 100%;background: rgba(0, 0, 0, .6);position: fixed;left: 0;top: 0;z-index: 800;"></div>
<div class="tccontents" id="toast_cg_intime" hidden style=" position: fixed;top: 28%;left: 10%;z-index: 999;background: #fff;width: 80%;height:165px;
    border-radius: 8px;"> 
    <img src="/borrow/310/images/bill-close.png" style="width:15px;height: 15px;position: absolute;right: 15px;top: 10px;" onclick="close_toast()" >
    <p class="mask_title" style=" padding-top: 30px;text-align: center;font-size: 16px;font-weight: bold;"></p>  
    <p class="mask_text" style="width: 80%;margin-left: 10%;text-align: center;"></p>
    <span class="add_btn go_pwd_list"  id="wait_order" 
    style="background-image: linear-gradient(-90deg, #F00D0D 0%, #FF4B17 100%);border-radius: 5px;height: 40px;width: 100px;position: absolute;left: 50%;    margin-left: -50px;margin-top: 15px;text-align: center;padding-top: 17px;font-size: 14px;color: #FFFFFF;
    line-height: 4px;" >立即查看</span>
</div> 
<!-- 弹窗 -->
<div class="alert-box" style="display: none;">
    <img class="close-btn" src="/292/images/images/alert-close.png">
</div>

<div class="swiper-container alert-sw" style="display: none;" >
    <div class="swiper-wrapper">
        <?php foreach ($tanchuan_data as $key=>$value): ?>
            <div class="swiper-slide" onclick="elasticLayer('<?php echo (isset($_GET['type']) ? $_GET['type'] : '')."','".$value['id']."','".$value['click_url']; ?>')"><img src="<?php echo Yii::$app->params['img_url'].$value['banner_pic_url'] ?>"></div>
        <?php endforeach; ?>
        <div class="swiper-pagination"></div>
    </div>
</div>

<!--    商城暂未开放，敬请期待-->
<div class="alert-toast" id="cue_activating" hidden style="width: 90%;position: fixed; top: 48%;left: 5%;border-radius: 5px; z-index: 100; padding:7px 0;background:rgba(0,0,0,0.5); color: #fff;text-align: center;font-size: 14px;opacity: 0.7;height:40px; ">
</div>
<!--<script src="/292/js/swiper.min.js"></script>
<script src="/292/js/jquery.min.js"></script>-->
<script>
    var swiper = new Swiper('.swiper-containert', {
        slidesPerView: 5,
        spaceBetween: 0,
        grabCursor: true,
        bulletClass: 'my-bullet',
        observer:true,//修改swiper自己或子元素时，自动初始化swiper
        observeParents:true//修改swiper的父元素时，自动初始化swiper
    });
    console.log(123);
    
    var swiper1 = new Swiper('.swiper-container1', {
        loop: true,
        autoplay: {
        delay: 2000 //1秒切换一次
        },
        pagination: '.swiper-pagination1',
        paginationHide: true,
        observer:true,//修改swiper自己或子元素时，自动初始化swiper
        observeParents:true//修改swiper的父元素时，自动初始化swiper
    });
    var swiper3 = new Swiper('.swiper-container3', {
        pagination: '.swiper-p3',
        slidesPerView: 3,
        centeredSlides: false,
        paginationClickable: true,
        spaceBetween: 10,
        grabCursor: true,
        bulletClass: 'my-bullet',
        observer:true,//修改swiper自己或子元素时，自动初始化swiper
        observeParents:true//修改swiper的父元素时，自动初始化swiper
    });
</script>
<script>
    var is_display = '<?php echo $is_display;?>';
    var user_id = '<?=$user_id?>';
    if(is_display==1){
        $('.alert-box').css({
            display:'block',
            opacity: 1
        })
        $('.alert-sw').css({
            display:'block',
            opacity: 1
        })
        $('body').css('overflow','hidden')
    }else {
        $('.alert-box').css('display','none');
        $('.alert-sw').css('display','none')
        $('body').css('overflow','visible')
    }
    var type = '<?php echo isset($_GET['type']) ? $_GET['type'] : '';?>';
    // $('body').css('overflow','hidden')
    // var vConsole = new VConsole()

    var alert = new Swiper('.alert-sw', {
        loop: true,
        pagination: {
            el: '.swiper-pagination',
        },
    });
    /* 关闭弹窗 */
    $('.close-btn').on('click', function() {
        $('.alert-box').css('display','none');
        $('.alert-sw').css('display','none')
         $('body').css('overflow','visible')
    });

    //弹层
    function elasticLayer(type,id,url) {
        if(url=='http://mp.yaoyuefu.com/borrow/loan'){
            if(type=='weixin'){
                tongji('daichaoAlertDetails_weixin_'+id,baseInfoss);
                window.location.href = '/borrow/loan';
                return false;
            }
            tongji('daichaoAlertDetails_'+id,baseInfoss);
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
        }else{
            if(type == 'weixin') {
                tongji('daichaoAlertDetails_weixin_'+id,baseInfoss);
                window.location.href = url+'?user_id='+user_id;
            } else {
                if (mobb == '') {
                    closeHtml('daichaoAlertDetails_'+id);
                } else {
                    tongji('daichaoAlertDetails_'+id,baseInfoss);
                    window.location.href = url+'?user_id='+user_id;
                }
            }
        }

    }
</script>
<!--<script src="/292/js/swiper.jquery.min.js" type="text/javascript" charset="utf-8"></script>-->
<script>

    var baseInfoss = eval('(' + '<?php echo $json_data; ?>' + ')');
    var user_mobile = '<?php echo $mobile; ?>';
    var mobb = '<?php echo $dc_mobile; ?>';
    var user_id = '<?php echo $user_id;?>';
    var is_app = <?php echo $is_app;?>;
    var zrys_url = '<?php echo $zrys_url;?>';
    var csrf = '<?php echo $csrf;?>';
    var type = '<?=$type?>';

    var is_display = '<?php echo $is_display;?>';
    if(is_display==1){
    //                $('#alert1').show();
    //                $('#alert2').show();
    }

    //商城统计
    if(is_app){
        $.get('/new/st/statisticssave?type=1410&user_id='+user_id);
    }

    function closeHtml(name) {
        var event = name || 'loan';
        tongji(event);
        if(is_app){
            $.get('/new/st/statisticssave?type=1411&user_id='+user_id);
        }
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
        //function toPage() {}
    }

    //商城首页点击统计
    function shopTabClick(id,user_id_store,name) {
        tongji(name+id);
        window.location.href="/mall/index/list?type="+id+"&user_id_store="+user_id_store;
    }

            //商品点击
            function shopClick(gid,user_id_store) {
                tongji('shopClick_'+gid);
                window.location.href="/mall/index/detail?gid="+gid+"&user_id_store="+user_id_store;
            }

            //七天banner点击
            function sevenBannerClick(type){
                $.get('/new/st/statisticssave?type=1416&user_id='+user_id);
                if (type == 'weixin') {
                    window.location = '/borrow/loan';
                } else {
                    closeHtml('sevenBannerClick');
                }
            }
            //七天弹层关闭点击
            function sevenCloseClick()
            {
                $.get('/new/st/statisticssave?type=1415&user_id='+user_id);
                $('#alert1').hide();
                $('#alert2').hide();
            }
            //七天弹层发起借款点击
            function sevenLoanClick(type)
            {
                $.get('/new/st/statisticssave?type=1414&user_id='+user_id);
                if (type == 'weixin') {
                    window.location = '/borrow/loan';
                } else {
                    closeHtml('sevenLoanClick');
                }
            }

            function openHtml(url) {
                tongji('toRepay');
                window.myObj.openHTML5(url);
                function openHTML5() {

                }
            }

            $('#inow').click(function () {
                $('.inow').hide();
            })

            /**
             * 记录用户点击轮播图行为日志
             */
            $('#toloan_2').click(function () {
                var gid = 945;
                var user_id_store = $(this).attr('user_id_store');
                tongji('toloan_2');
                setTimeout(function () {
                    location.href = "/mall/index/detail?gid=" + gid + "&user_id_store=" + user_id_store;
                }, 100);
            })

            $('#toloan_1').click(function () {
                var gid = 1432;
                var user_id_store = $(this).attr('user_id_store');
                tongji('toloan_1');
                setTimeout(function () {
                    location.href = "/mall/index/detail?gid=" + gid + "&user_id_store=" + user_id_store;
                }, 100);
            })


            function loan(type) {
                if (type == 'weixin') {
                    tongji('tolocan_weixin');
                    window.location = '/new/loan';
                } else {
                    closeHtml('tolocan');
                }
            }
            function attention() {
                window.location.href = '<?php echo Yii::$app->request->hostInfo;?>'+'/new/activity/attention';
            }

            function daichao(type) {
                $.get('/new/st/statisticssave?type=1408');

                if (type == 'weixin') {
                    tongji('daichao_weixin');
                    window.location.href = 'http://dc.zhirongyaoshi.com?channel=wbanner&phone=' + mobb;
                } else {
                    if (mobb == '') {
                        closeHtml('daichao');
                    } else {
                        window.location.href = 'http://dc.zhirongyaoshi.com?channel=wbanner&phone=' + mobb;
                    }

                }
            }

            function daichaoType(type) {
                $.get('/new/st/statisticssave?type=1413');
                if (type == 'weixin') {
                    tongji('daichaoType_weixin');
                    window.location.href = 'http://dc.zhirongyaoshi.com/?utm_source=loan_btn&channel=loan_btn&phone=' + mobb;
                } else {
                    if (mobb == '') {
                        closeHtml('daichaoType');
                    } else {
                        window.location.href = 'http://dc.zhirongyaoshi.com/?utm_source=loan_btn&channel=loan_btn&phone=' + mobb;
                    }
                }
            }

            function playgame(type) {
                $.get('/new/st/statisticssave?type=1412');

                if (type == 'weixin') {
                    tongji('playgame_weixin');
                    window.location.href = zrys_url+'guide/game?phone=' + mobb;
                } else {
                    if (mobb == '') {
                        closeHtml('playgame');
                    } else {
                        window.location.href = zrys_url+'guide/game?phone=' + mobb;
                    }

                }
            }

            //分享赚钱banner
            function shareMoney() {
                var url = '/borrow/pressuretestactivity/index';
                zhuge.track('首页-banner点击',{'位置':1,'名称':'分享赚钱'});
                $.get('/new/st/statisticssave?type=1438');
                if (type == 'weixin') {
                    tongji('shareMoney_weixin');
                    window.location.href = url;
                } else {
                    if (mobb == '') {
                        closeHtml('shareMoney');
                    } else {
                        tongji('shareMoney');
                        window.location.href = url;
                    }
                }
            }

            //拼团
            function collage() {
                var url = '/borrow/collageactivity';
                zhuge.track('首页-banner点击',{'位置':2,'名称':'拼团活动'});
                $.get('/new/st/statisticssave?type=1443&user_id='+user_id);
                if (type == 'weixin') {
                    tongji('collage_weixin');
                    window.location.href = url;
                } else {
                    if (mobb == '') {
                        closeHtml('collage');
                    } else {
                        tongji('collage');
                        window.location.href = url;
                    }
                }
            }

    //购买优惠券
    function activity3() {
        var url = '/borrow/purchasecardsactivity/';
        zhuge.track('首页-banner点击',{'位置':3,'名称':'购买优惠券活动'});
        $.get('/new/st/statisticssave?type=1447&user_id='+user_id);
        if (type == 'weixin') {
            tongji('activity3_weixin');
            window.location.href = url;
        } else {
            if (mobb == '') {
                closeHtml('activity3');
            } else {
                tongji('activity3');
                window.location.href = url;
            }
        }
    }

            //记录用户行为事件
            $('.btn').click(function () {
                var id = $(this).attr('type');
                var user_id_store = $(this).attr('user_id_store');
                var str = 'clicktype_' + id;
                tongji(str);
                setTimeout(function () {
                    location.href = "/mall/index/list?type=" + id + "&user_id_store=" + user_id_store;
                }, 100);
            })

            function tongji(event) {
                <?php \app\common\PLogger::getInstance('weixin', '', $encodeUserId); ?>
                <?php $json_data = \app\common\PLogger::getJson(); ?>
                var baseInfoss = eval('(' + '<?php echo $json_data; ?>' + ')');
                baseInfoss.url = baseInfoss.url + '&event=' + event;
                // console.log(baseInfoss);
                var ortherInfo = {
                    screen_height: window.screen.height, //分辨率高
                    screen_width: window.screen.width, //分辨率宽
                    user_agent: navigator.userAgent,
                    height: document.documentElement.clientHeight || document.body.clientHeight, //网页可见区域宽
                    width: document.documentElement.clientWidth || document.body.clientWidth, //网页可见区域高
                };
                var baseInfos = Object.assign(baseInfoss, ortherInfo);

                var turnForm = document.createElement("form");
                turnForm.id = "uploadImgForm";
                turnForm.name = "uploadImgForm";
                document.body.appendChild(turnForm);
                turnForm.method = 'post';
                turnForm.action = baseInfoss.log_url + 'weixin';
                //创建隐藏表单
                for (var i in baseInfos) {
                    var newElement = document.createElement("input");
                    newElement.setAttribute("name", i);
                    newElement.setAttribute("type", "hidden");
                    newElement.setAttribute("value", baseInfos[i]);
                    turnForm.appendChild(newElement);
                }
                var iframeid = 'if' + Math.floor(Math.random(999) * 100 + 100) + (new Date().getTime() + '').substr(5, 8);
                var iframe = document.createElement('iframe');
                iframe.style.display = 'none';
                iframe.id = iframeid;
                iframe.name = iframeid;
                iframe.src = "about:blank";
                document.body.appendChild(iframe);
                turnForm.setAttribute("target", iframeid);
                turnForm.submit();
            }

            //Object.assign兼容问题
            if (typeof Object.assign != 'function') {
                Object.defineProperty(Object, "assign", {
                    value: function assign(target, varArgs) {
                        'use strict';
                        if (target == null) {
                            throw new TypeError('Cannot convert undefined or null to object');
                        }
                        var to = Object(target);
                        for (var index = 1; index < arguments.length; index++) {
                            var nextSource = arguments[index];
                            if (nextSource != null) {
                                for (var nextKey in nextSource) {
                                    if (Object.prototype.hasOwnProperty.call(nextSource, nextKey)) {
                                        to[nextKey] = nextSource[nextKey];
                                    }
                                }
                            }
                        }
                        return to;
                    },
                    writable: true,
                    configurable: true
                });
            }
    //活动弹窗
    $(function () {
        
        var activity_remark = '<?php echo $activity_show_remark; ?>';
        var activity_img_url = '<?php echo $img_url . $activity_img_url; ?>';
        var activity_id = '<?php echo $activity_id; ?>';
        var user_mobile = '<?php echo $mobile; ?>';
        if (activity_remark == 1) {
            $('.Hmask').show();
            $('.erwma').show();
            $('#act_img').attr('src', activity_img_url);
        }

        //取消遮罩层
        $('.error').click(function () {
            var str_close = 'close_activity_banner_' + activity_id;
            tongji(str_close);
            $('.Hmask').hide();
            $('.erwma').hide();

        });

        $('#act_img').click(function () {
            var str_enter = 'enter_activity_banner_' + activity_id;
            tongji(str_enter);
            setTimeout(function () {
                window.location.href = "/new/lottery?activity_id=" + activity_id + "&mobile=" + user_mobile;
            }, 100);
        });
    })
    var u = navigator.userAgent, app = navigator.appVersion;
    var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Linux') > -1;
    var isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/);
    var android = "com.business.main.MainActivity";
    var ios = "loanViewController";
    var position = "-1";
    var isApp = <?php
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'YYY_ANDROID') || strpos($_SERVER['HTTP_USER_AGENT'], 'yyyIOS')) {
            echo 1;
        } else {
            echo 2;
        }
        ?>;
    var user_id_store_app = '<?php echo $user_id_store; ?>';    
    function openShop(){
        tongji('shop_click');
        setTimeout(function () {
                openShopajax();
            }, 100);
    }
    
    function openShopajax(){
        $.ajax({
            url: '/mall/index/xhshopajax',
            type: 'post',
            data:{_csrf:csrf,isapp:isApp,user_id_store_app:user_id_store_app},
            dataType: 'json',
            success: function(msg){
                console.log(msg.rsp_code);
                if(msg.rsp_code == '0000'){
                    window.location.href = msg.rsp_data.url;
                }else if(msg.rsp_code == '0001'){ //请登录
                    if (isiOS) {
                        window.myObj.toPage(ios);
                    } else if (isAndroid) {
                        window.myObj.toPage(android, position);
                    } 
                }else if(msg.rsp_code == '0007'){ //商城暂未开放，敬请期待
                   $('#cue_activating').html(msg.rsp_msg);
                   $('#cue_activating').show();
                   setTimeout(function () {
                        $('#cue_activating').hide();
                    }, 3000); 
                }else if(msg.rsp_code == '0002'){ //进行中的借款 
                    $('#alert_shade').show();
                    $('#toast_cg_intime').show();
                    $('.mask_title').html('您有一笔进行中的借款');
                    $('.mask_text').html('请结清借款后再试，详情可在账单列表中查看');
                    $("#wait_order").click(function(){
                        tongji('check_bill',baseInfoss);
                        setTimeout(function () {
                           if(isApp == 1){
                                if (isiOS) {
                                    window.myObj.toPage("BillViewController");
                                } else if (isAndroid) {
                                    window.myObj.toPage("com.business.bill.BillActivity", 1);
                                } 
                            }else{
                                window.location.href = msg.rsp_data.url;
                            }
                        }, 100); 
                        
                    });
//                    $('#doing_loan').show(); 
                }else if(msg.rsp_code == '0003'){ //待支付订单
                    $('#alert_shade').show();
                    $('#toast_cg_intime').show();
                    $('.mask_title').html('您有一笔待支付订单');
                    $('.mask_text').html('请在24小时内完成支付，剩余时间'+msg.rsp_data.time);
                    $("#wait_order").click(function(){
                        tongji('check_wait_order',baseInfoss);
                        setTimeout(function () {
                            window.location.href = msg.rsp_data.url;
                        }, 100); 
                    });
//                    $('#wait_order').show();
                }else if(msg.rsp_code == '0004'){ //进行中的订单
                    $('#alert_shade').show();
                    $('#toast_cg_intime').show();
                    $('.mask_title').html('您有一笔进行中订单');
                    $('.mask_text').html('请前往订单查看订单详情');
                    $("#wait_order").click(function(){
                        tongji('check_doing_order',baseInfoss);
                        setTimeout(function () {
                            window.location.href = msg.rsp_data.url;
                        }, 100); 
                    });
                }else{
                    console.log(msg.rsp_msg);
                    return false;
                }
            },
            error:function(msg){
             console.log('点击先花商城ajax请求失败'+msg)
            }
           });
    }
    
    //取消蒙层 alert_shade toast_cg_intime       
   function close_toast() { 
      $('#alert_shade').hide();
      $('#toast_cg_intime').hide();
 
   }
   
   
</script>




