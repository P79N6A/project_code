
<style>
    .borrow-box{
        display: inline-block;
        position: relative;
    }
    .borrow-box div{
        width: 100%;
        text-align: center;
    }
    .borrow span{
        height:auto;
    }
    .w_bannerImg{
        display: block;
        margin: 0 auto !important;
    }
    .borrow-box img{
        display: block;
        margin: auto;
    }
</style>

<?php \app\common\PLogger::getInstance('weixin', '', $user_id); ?>
<?php $json_data = \app\common\PLogger::getJson(); ?>
  <div class="swiper-container nav-tab">
    <div class="swiper-wrapper">
      <div class="swiper-slide"><a class="hover">推荐</a></div>
        <?php if($supermarketOpen==1){?>
            <div class="swiper-slide"><a class="new"  onclick="daichaoTabclick('<?php echo isset($_GET['type']) ? $_GET['type'] : ''; ?>')">
                    贷款超市
                    <img src="/292/images/images/new.png">
                </a>
            </div>
        <?php } ?>
        <?php foreach ($allGoodsTypes as $k => $v): ?>
            <div class="swiper-slide"><a onclick="shopTabClick('<?= $v->id."','".$user_id_store."','".'shopTabClick_'?>')"><?= $v->classify_name; ?></a></div>
        <?php endforeach; ?>
    </div>
  </div>
  <div class="place"></div>

  <!-- 轮播图 -->
  <div class="swiper-container roll">
    <div class="swiper-wrapper">
        <div class="swiper-slide"><a onclick="attention()"><img src="/images/banner/attention.png?v=12"></a></div>
        <div class="swiper-slide"><a onclick="daichao('<?php echo isset($_GET['type']) ? $_GET['type'] : ''; ?>')"><img src="/images/daichao_1126.jpg?v=12"></a></div>
        <div class="swiper-slide"><a onclick="loan('<?php echo isset($_GET['type']) ? $_GET['type'] : ''; ?>')"><img src="/292/images/banner3.png"></a></div>
    </div>
      <div class="swiper-pagination"></div>
  </div>
<!--未登录trialindex-->
<!-- 借款/贷款超市 -->
<div class="borrow">
    
    <?php if($shop_switch  ):?>
        <div onclick="closeHtml()">
            <div class="borrow-box">
                <img src="/292/images/images/borrow.png">
                <div>
                    <h4>借款</h4>
                    <p>低息借贷</p>
                </div>
                
            </div>
        </div>
        <span></span>
        <div onclick="daichaoType('<?php echo isset($_GET['type']) ? $_GET['type'] : ''; ?>')" >
            <div class="borrow-box">
                <img src="/292/images/images/new.png" style="width: 0.640rem;height: 0.347rem;position: absolute;top: -0.1rem;right: 0;">
                <img src="/292/images/images/super.png">
                <div style="position: relative">
                    <h4>贷款超市</h4>
                    <p>额度任您选</p>
                </div>
                
            </div>
        </div>
        <span></span>
        <div onclick="openShop()">
            <div class="borrow-box">
                <img src="/340/images/zhuanshou.png" style="width: 0.640rem;height: 0.347rem;position: absolute;top: -0.1rem;right: 0;">
                <img src="/340/images/shop.png">
                <div style="position: relative">
                    <h4>先花商城</h4>
                    <p>转售换钱</p>
                </div>
                
            </div>
        </div>
    <?php else:?>
        <div onclick="closeHtml()">
        <div class="borrow-box">
            <div>
                <h4>借款</h4>
                <p>低息借贷 快速借款</p>
            </div>
            <img src="/292/images/images/borrow.png">
        </div>
    </div>
    <span></span>
    <div onclick="daichaoType('<?php echo isset($_GET['type']) ? $_GET['type'] : ''; ?>')" >
        <div class="borrow-box">
            <div style="position: relative">
                <img src="/292/images/images/new.png" style="width: 0.640rem;height: 0.347rem;position: absolute;top: -0.1rem;right: 0;">
                <h4>贷款超市</h4>
                <p>填完资料直接拿钱！</p>
            </div>
            <img src="/292/images/images/super.png">
        </div>
    </div>
    <?php endif;?>
</div>

  <?php foreach ($tjGoodsTypes as $k => $v): ?>
      <!-- 手机推荐 -->
      <div class="hot phone">
          <div class="title-box">
              <span></span>
              <b><?php echo $v->classify_name; ?></b>
              <span></span>
          </div>
          <div class="banner">
              <a onclick="shopTabClick('<?= $v->id."','".$user_id_store."','".'shopBannerClick_';?>')">
                  <img class="hot-banner" src="/292/images/index_gg<?= $v->id; ?>.jpg">
              </a>
          </div>
          <div class="swiper-container hot-list-tab phone-list-tab">
              <div class="swiper-wrapper">
                  <?php foreach ($v->goodsList as $kk => $vv) { ?>
                      <div class="swiper-slide">
              <span onclick="shopClick('<?=$vv->id."','".$user_id_store?>')">
                <img src="<?= isset($vv->pic->pic_url) ? \app\commonapi\ImageHandler::getUrl($vv->pic->pic_url) : ''; ?>">
                <p><?= $vv->goods_name; ?></p>
                <span class="yuan"><?= $vv->goods_price; ?>元</span>
                <span class="time"></span>
              </span>
                      </div>
                  <?php }; ?>

                  <div class="swiper-slide">
                      <a onclick="shopTabClick('<?=$v->id."','".$user_id_store."','".'shopLookMore_'?>')">
                          <div class="more">
                              <img src="/292/images/more.png">
                          </div>
                      </a>
                  </div>
              </div>
              <div class="swiper-pagination"></div>
          </div>
      </div>
  <?php endforeach; ?>
<!--      先花商城弹窗-->
<div class="mask" id="alert_shade" hidden style="height: 100%;width: 100%;background: rgba(0, 0, 0, .6);position: fixed;left: 0;top: 0;z-index: 800;"></div>
<div class="tccontents" id="toast_cg_intime"hidden  style=" position: fixed;top: 28%;left: 10%;z-index: 999;background: #fff;width: 80%;height:4.5rem;
    border-radius: 0.2rem;"> 
    <img src="/borrow/310/images/bill-close.png" style="width:0.35rem;height: 0.35rem;position: absolute;right: 0.3rem;top: 0.2rem;" onclick="close_toast()" >
    <p class="mask_title" style=" padding-top: 0.55rem;text-align: center;font-size: 0.45rem;font-weight: bold;"></p>  
    <p class="mask_text" style="width: 80%;margin-left: 10%;text-align: center;font-size: 0.4rem;"></p>
    <span class="add_btn go_pwd_list"  id="wait_order" 
    style="background-image: linear-gradient(-90deg, #F00D0D 0%, #FF4B17 100%);border-radius: 0.2rem;    margin-left: -1.5rem;height: 0.75rem;width: 3rem;position: absolute;left: 50%;margin-top: 0.6rem;text-align: center;padding-top: 0.3rem;font-size: 0.4rem;color: #FFFFFF;line-height: 0.45rem;" >
    立即查看</span>
</div>
<!--    商城暂未开放，敬请期待-->
<div class="alert-toast" id="cue_activating"hidden  style="width: 90%;position: fixed; top: 48%;left: 5%;border-radius: 0.1rem; z-index: 100; padding:0.1rem 0;background:rgba(0,0,0,0.5); color: #fff;text-align: center;font-size: 0.4rem;opacity: 0.7;height:0.7rem; ">
商城暂未开放，敬请期待
</div>
<script src="/290/js/jquery-1.10.1.min.js"></script>
<script src="/292/js/swiper.min.js"></script>
<script>
var csrf = '<?php echo $csrf;?>';
  /* 导航栏 */
  var navTab = new Swiper('.nav-tab', {
    slidesPerView: 5,
    centeredSlides: false,
  });
  /* 轮播图 */
  var roll = new Swiper('.roll', {
    centeredSlides: true,
    loop: true,
    autoplay: {
      delay: 2500,
      disableOnInteraction: false,
    },
      pagination: {
          el: '.swiper-pagination',
      },
  });

  var phone = new Swiper('.phone-list-tab', {
    slidesPerView: 3,
    slidesPerGroup: 3,
    centeredSlides: false,
    pagination: {
      el: '.swiper-pagination',
      clickable: true,
    },
  });

  var alert = new Swiper('.alert-sw', {
    loop: true,
    pagination: {
      el: '.swiper-pagination',
    },
  });

  /* 关闭弹窗 */
  $('.close-btn').on('click', function() {
    $('body').css('overflow', 'visible');
    $('.alert-box').css({ display: 'none' });
  });
</script>
<script>
    var baseInfoss = eval('(' + '<?php echo $json_data; ?>' + ')');
    var mobile = '<?= $dc_mobile ?>';
    var is_app = '<?php echo $is_app;?>';
    var user_id = '<?php echo $user_id;?>';
    var dcMobile = '<?php echo $dcMobile;?>';

    var trial = '<?php echo strpos($_SERVER['HTTP_USER_AGENT'], '3.5.0'); ?>';
    if(!trial){
//        var isIos = !!navigator.userAgent.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/);
//        function isIphoneX(){
//            return /iphone/gi.test(navigator.userAgent) && (screen.height == 812 && screen.width == 375)
//        }
//
//        if(!!+is_app && isIos){
//            var a =$('.place').height();
//            $('.nav-tab').css('top',64* window.devicePixelRatio+'px');
//            $('.place').css('height',64* window.devicePixelRatio+a+'px');
//            if(isIphoneX()){
//                $('.nav-tab').css('top',88* window.devicePixelRatio+'px');
//                $('.place').css('height',88* window.devicePixelRatio+a+'px')
//            }
//        }
    }

    //商城首页点击统计
    function shopTabClick(id,user_id_store,name) {
        tongji(name+id,baseInfoss);
        window.location.href="/mall/index/list?type="+id+"&user_id_store="+user_id_store;
    }

    //商品点击
    function shopClick(gid,user_id_store) {
        tongji('shopClick_'+gid,baseInfoss);
        window.location.href="/mall/index/detail?gid="+gid+"&user_id_store="+user_id_store;
    }

    //贷款超市banner
    function daichao(type) {
        zhuge.track('首页-banner点击',{'位置':0,'名称':'贷款超市'});
        $.get('/new/st/statisticssave?type=1408');
        if (type == 'weixin') {
            tongji('daichaoBanner_weixin',baseInfoss);
            window.location.href = 'http://dc.zhirongyaoshi.com?channel=wbanner&phone=' + dcMobile;
        } else {
            if (mobile == '') {
                closeHtml('daichaoBanner',baseInfoss);
            } else {
                window.location.href = 'http://dc.zhirongyaoshi.com?channel=wbanner&phone=' + mobile;
            }

        }
    }

    //借款banner
    function loan(type) {
        zhuge.track('首页-banner点击',{'位置':1,'名称':'借款'});
        if (type == 'weixin') {
            tongji('tolocan_weixin',baseInfoss);
            window.location = '/new/loan';
        } else {
            closeHtml('tolocan');
        }
    }
    function attention() {
                window.location.href = '<?php echo Yii::$app->request->hostInfo;?>'+'/new/activity/attention';
            }

    //贷超tab点击
    function daichaoTabclick(type) {
        tongji('daichaoTabclick',baseInfoss);
        if (type == 'weixin') {
            tongji('daichaoTab_weixin',baseInfoss);
            window.location.href = 'http://dc.zhirongyaoshi.com?phone=' + dcMobile;
        } else {
            if (mobile == '') {
                closeHtml('daichaoTab',baseInfoss);
            } else {
                tongji('daichaoTab',baseInfoss);
                window.location.href = 'http://dc.zhirongyaoshi.com?phone=' + mobile;
            }
        }
    }

    //游戏banner
    function playgame(type) {
        zhuge.track('首页-banner点击',{'位置':2,'名称':'游戏玩吧'});
        $.get('/new/st/statisticssave?type=1412');
        if (type == 'weixin') {
            tongji('playgame_weixin',baseInfoss);
            window.location.href = 'http://dc.zhirongyaoshi.com/guide/game?phone=' + dcMobile;
        } else {
            if (mobile == '') {
                closeHtml('playgame',baseInfoss);
            } else {
                window.location.href = 'http://dc.zhirongyaoshi.com/guide/game?phone=' + mobile;
            }
        }
    }

    //贷超按钮点击
    function daichaoType(type) {
        $.get('/new/st/statisticssave?type=1413');
        if (type == 'weixin') {
            tongji('daichaoType_weixin',baseInfoss);
            window.location.href = 'http://dc.zhirongyaoshi.com/?utm_source=loan_btn&phone=' + dcMobile;
        } else {
            if (mobile == '') {
                closeHtml('daichaoType',baseInfoss);
            } else {
                window.location.href = 'http://dc.zhirongyaoshi.com/?utm_source=loan_btn&phone=' + mobile;
            }
        }
    }

    function closeHtml(name) {
        var event = name || 'loan';
        tongji(event,baseInfoss);
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
        tongji('shop_click',baseInfoss);
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
                }else if(msg.rsp_code == '0002'){ //进行中的借款 alert_shade toast_cg_intime wait_order doing_order doing_loan check_order_loan
                    $('#alert_shade').show();
                    $('#toast_cg_intime').show();
                    $("#wait_order").show();
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
    function toPage() {}
   //取消蒙层 alert_shade toast_cg_intime       
   function close_toast() { 
      $('#alert_shade').hide();
      $('#toast_cg_intime').hide();
//      $('#wait_order').hide();
   }
</script>
