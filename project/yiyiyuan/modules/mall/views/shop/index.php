<style>
    .swiper-pagination  .swiper-pagination-bullet{background: #f3f3f3; opacity: 1;}
    .swiper-pagination .swiper-pagination-bullet-active{background: #c90000;}
</style>
<div class="ydwey">
    <div class="swiper-container swiper-containert  navtitle">
        <div class="swiper-wrapper">
            <div class="swiper-slide"><a class="hover">推荐</a></div>
            <?php foreach ($allGoodsTypes as $k => $v): ?>
                <div class="swiper-slide"><a href="/mall/shop/list?type=<?= $v->id; ?>&user_id_store=<?= $user_id_store; ?>"><?= $v->classify_name; ?></a></div>
            <?php endforeach; ?>
        </div>
        <div class="seiper-pagination swiper-ptitt"></div>
    </div>
</div>
<div style="height: 2.6rem;"></div>

<div class="swiper-container swiper-container1">
    <div class="swiper-wrapper">
        <div class="swiper-slide"><a onclick="attention()"><img src="/images/banner/attention.png?v=12"></a></div>
        <?php if (!strpos($_SERVER['HTTP_USER_AGENT'], '3.5.0') && $showLoan == 1) { ?>
            <div class="swiper-slide"><a onclick="daichao()"><img src="/images/daichao_1126.jpg?v=12"></a></div>
        <?php } ?>
        <?php if (!strpos($_SERVER['HTTP_USER_AGENT'], '3.5.0') && $showLoan == 1) { ?>
            <div class="swiper-slide"><a onclick="loan()"><img src="/292/images/banner3.png"></a></div>
        <?php } ?>
        <div class="swiper-slide"><a href="/mall/shop/detail?gid=1432&user_id_store=<?= $user_id_store; ?>"><img src="/292/images/banner1.jpg"></a></div>
        <div class="swiper-slide"> <a href="/mall/shop/detail?gid=945&user_id_store=<?= $user_id_store; ?>"><img src="/292/images/banner2.jpg"></a></div>
    </div>
    <div class="swiper-pagination swiper-pagination1"></div>
</div>
<!--审核与白名单shop/index-->
<div class="index_nav">
    <img src="/292/images/index_zpsh.jpg">
    <div class="navmes">
        <?php if (!strpos($_SERVER['HTTP_USER_AGENT'], '3.5.0') && $showLoan == 1) { ?>
            <a  onclick="closeHtml()" >
                <dl>
                    <dt><img src="/298/images/jb.gif"></dt>
                    <dd>借款</dd>
                </dl>
            </a>
            <a onclick="daichaoType('<?php echo isset($_GET['type']) ? $_GET['type'] : ''; ?>')" >
                <dl>
                    <dt><img src="/298/images/daichao.png"></dt>
                    <dd>贷款超市</dd>
                </dl>
            </a>
        <?php } ?>

        <?php foreach ($allGoodsTypes as $k => $v): ?>
            <?php if ($v->id != 3) { ?>
                <a href="/mall/shop/list?type=<?= $v->id; ?>&user_id_store=<?= $user_id_store; ?>">
                    <dl>
                        <dt><img src="<?= (!empty($v->classify_img) && @fopen($v->classify_img, 'r'))?$v->classify_img:Yii::$app->params['img_url'].$v->classify_img; ?>"></dt>
                        <dd><?= $v->classify_name; ?></dd>
                    </dl>
                </a>
            <?php } elseif (($v->id == 3 && strpos($_SERVER['HTTP_USER_AGENT'], '3.5.0')) || ($v->id == 3 && $showLoan == 0)) { ?>
                <a href="/mall/shop/list?type=<?= $v->id; ?>&user_id_store=<?= $user_id_store; ?>">
                    <dl>
                        <dt><img src="<?= (!empty($v->classify_img) && @fopen($v->classify_img, 'r'))?$v->classify_img:Yii::$app->params['img_url'].$v->classify_img; ?>"></dt>
                        <dd><?= $v->classify_name; ?></dd>
                    </dl>
                </a>
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
            <a href="/mall/shop/list?type=<?= $v->id; ?>&user_id_store=<?= $user_id_store; ?>">
                <img src="/292/images/index_gg<?= $v->id; ?>.jpg">
            </a>
        </div>
        <div class="swiper-container   swiper-container3">
            <div class="swiper-wrapper">
                <?php foreach ($v->goodsList as $kk => $vv) { ?>
                    <div class="swiper-slide">
                        <a href="/mall/shop/detail?gid=<?= $vv->id; ?>&user_id_store=<?= $user_id_store; ?>">
                            <dl class="index_scym">
                                <dt><img src="<?= isset($vv->pic->pic_url) ? \app\commonapi\ImageHandler::getUrl($vv->pic->pic_url) : ''; ?>"></dt>
                                <dd>
                                    <h3><?= $vv->goods_name; ?></h3>
                                    <span><?= $vv->goods_price; ?><em>元</em></span>
                                </dd>
                            </dl>
                        </a>
                    </div>
                <?php }; ?>
                <div class="swiper-slide">
                    <a href="/mall/shop/list?type=<?= $v->id; ?>&user_id_store=<?= $user_id_store; ?>">
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
<link rel="stylesheet" type="text/css" href="/292/css/swiper.css"/>
<?php $jsUrl = Yii::$app->params['jsUrl']; ?>
<script src="<?php echo $jsUrl; ?>"></script>
<script src="/292/js/swiper.jquery.min.js" type="text/javascript" charset="utf-8"></script>
<script>
            var user_id = '<?php echo $user_id;?>';
            var is_app = <?php echo $is_app;?>
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

            function daichaoType(type) {
                var mobile = '<?php echo $mobile; ?>';
                $.get('/new/st/statisticssave?type=1413');
                if (type == 'weixin') {
                    tongji('daichaoType_weixin');
                    window.location.href = 'http://dc.zhirongyaoshi.com/?utm_source=loan_btn&channel=loan_btn&phone=' + mobile;
                } else {
                    if (mobile == '') {
                        closeHtml('daichaoType');
                    } else {
                        window.location.href = 'http://dc.zhirongyaoshi.com/?utm_source=loan_btn&channel=loan_btn&phone=' + mobile;
                    }
                }
            }

            function tongji(event) {
<?php \app\common\PLogger::getInstance('weixin', '', $user_id_store); ?>
<?php $json_data = \app\common\PLogger::getJson(); ?>
                var baseInfoss = eval('(' + '<?php echo $json_data; ?>' + ')');
                baseInfoss.url = baseInfoss.url + '&event=' + event;
                console.log(baseInfoss);
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

            function loan() {
                closeHtml();
            }
            function attention() {
                window.location.href = '<?php echo Yii::$app->request->hostInfo;?>'+'/new/activity/attention';
            }
            function daichao() {
                var mobile = '<?php echo $mobile; ?>';
                if (mobile == '') {
                    closeHtml();
                } else {
                    window.location.href = 'http://dc.zhirongyaoshi.com?channel=hbanner&phone=' + mobile;
                }
            }

            function playgame(type) {
                var mobile = '<?php echo $mobile; ?>';
                $.get('/new/st/statisticssave?type=1412');

                if (mobile == '') {
                    closeHtml('playgame');
                } else {
                    window.location.href = 'http://dc.zhirongyaoshi.com/guide/game?phone=' + mobile;
                }
            }
</script>


