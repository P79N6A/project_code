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
</style>

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
        <div class="swiper-slide"><a onclick="daichao('<?php echo isset($_GET['type']) ? $_GET['type'] : ''; ?>')"><img src="/images/daichao_1126.jpg?v=12"></a></div>
        <div class="swiper-slide"><a onclick="loan('<?php echo isset($_GET['type']) ? $_GET['type'] : ''; ?>')"><img src="/292/images/banner3.png"></a></div>
        <div class="swiper-slide"><a href="javascript:void(0);" id="toloan_1" user_id_store="<?= $user_id_store; ?>"><img src="/292/images/banner1.jpg"></a></div>
        <div class="swiper-slide"><a href="javascript:void(0);" id="toloan_2" user_id_store="<?= $user_id_store; ?>"><img src="/292/images/banner2.jpg"></a></div>
    </div>
    <div class="swiper-pagination swiper-pagination1"></div>
</div>
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
        <?php if($supermarketOpen==1){?>
        <a onclick="daichaoType('<?php echo isset($_GET['type']) ? $_GET['type'] : ''; ?>')" >
            <dl>
                <dt><img src="/298/images/daichao.png"></dt>
                <dd>贷款超市</dd>
            </dl>
        </a>
        <?php } ?>
        <?php foreach ($allGoodsTypes as $k => $v): ?>
            <?php if ($v->id != 3 || (isset($_GET['type']) && $_GET['type'] == 'weixin')) { ?>
                <span class="btn" type="<?= $v->id ?>" user_id_store="<?= $user_id_store; ?>">
                    <dl>
                        <dt><img src="<?= (!empty($v->classify_img) && @fopen($v->classify_img, 'r'))?$v->classify_img:Yii::$app->params['img_url'].$v->classify_img; ?>"></dt>
                        <dd><?= $v->classify_name; ?></dd>
                    </dl>
                </span>
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
                <?php foreach ($v->goodsList as $kk => $vv) { ?>
                    <div class="swiper-slide">
                        <a onclick="shopClick('<?=$vv->id."','".$user_id_store?>')">
                            <dl class="index_scym">
                                <dt><img src="<?= isset($vv->pic->pic_url) ? \app\commonapi\ImageHandler::getUrl($vv->pic->pic_url) : ''; ?>"></dt>
                                <dd>
                                    <h3><?= $vv->goods_name; ?></h3>
                                    <span><?= $vv->goods_price; ?><em>元</em></span>
                                    <p>月供<?= $vv->instalment; ?>起</p>
                                </dd>
                            </dl>
                        </a>
                    </div>
                <?php }; ?>
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
<link rel="stylesheet" type="text/css" href="/292/css/swiper.css"/>
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


<script src="/292/js/swiper.jquery.min.js" type="text/javascript" charset="utf-8"></script>
<script>
    var user_mobile = '<?php echo $mobile; ?>';
    var mobb = '<?php echo $dc_mobile; ?>';
    var user_id = '<?php echo $user_id;?>';
    var is_app = <?php echo $is_app;?>;
    var zrys_url = '<?php echo $zrys_url;?>';


    //商城统计
    if(is_app){
        $.get('/new/st/statisticssave?type=1410&user_id='+user_id);
    }
    var swiper = new Swiper('.swiper-containert', {
        pagination: '.swiper-pt',
        slidesPerView: 5,
        centeredSlides: false,
        paginationClickable: true,
        spaceBetween: 0,
        grabCursor: true,
        el: '.swiper-pagination',
        bulletClass: 'my-bullet',
    });
    var swiper1 = new Swiper('.swiper-container1', {
        autoplay: 5000, //可选选项，自动滑动
        pagination: '.swiper-pagination1',
        paginationHide: true,
        autoplayDisableOnInteraction : false,
    });
    var swiper3 = new Swiper('.swiper-container3', {
        pagination: '.swiper-p3',
        slidesPerView: 3,
        centeredSlides: false,
        paginationClickable: true,
        spaceBetween: 10,
        grabCursor: true,
        el: '.swiper-pagination',
        bulletClass: 'my-bullet',
    });

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

</script>
<script type="text/javascript">
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
</script>




