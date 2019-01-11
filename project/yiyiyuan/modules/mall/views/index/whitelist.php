<?php \app\common\PLogger::getInstance('weixin', '', $user_id); ?>
<?php $json_data = \app\common\PLogger::getJson(); ?>
  <div class="swiper-container nav-tab">
    <div class="swiper-wrapper">
      <div class="swiper-slide"><a class="hover">推荐</a></div>
        <?php foreach ($allGoodsTypes as $k => $v): ?>
            <div class="swiper-slide"><a onclick="shopTabClick('<?= $v->id."','".$user_id_store."','".'shopTabClick_'?>')"><?= $v->classify_name; ?></a></div>
        <?php endforeach; ?>
    </div>
  </div>
  <div class="place"></div>

  <!-- 轮播图 -->
  <div class="swiper-container roll">
    <div class="swiper-wrapper">
        <div class="swiper-slide"><a onclick="toloan('<?=$user_id_store."','".'1432'."','".'0'?>')"><img src="/292/images/banner1.jpg"></a></div>
        <div class="swiper-slide"><a onclick="toloan('<?=$user_id_store."','".'945'."','".'1'?>')"><img src="/292/images/banner2.jpg"></a></div>
    </div>
      <div class="swiper-pagination"></div>
  </div>
  <!-- 热门推荐 -->
  <div class="hot">
    <img class="hot-banner tips" src="/292/images/images/index_zpsh.jpg">
    <div class="swiper-container hot-list-tab hot-t">
      <div class="swiper-wrapper">
          <?php foreach ($allGoodsTypes as $k => $v): ?>
        <div class="swiper-slide btn" type="<?= $v->id ?>" user_id_store="<?= $user_id_store; ?>">
            <dl>
                <dt><img src="<?= $v->classify_img; ?>"></dt>
                <dd><?= $v->classify_name; ?></dd>
            </dl>
        </div>
          <?php endforeach; ?>

      </div>
    </div>
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
                <span class="time">月供<?= $vv->instalment; ?><em>元</em></span>
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
<script src="/290/js/jquery-1.10.1.min.js"></script>
<script>
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
  /* tab */
  var hot = new Swiper('.hot-t', {
    slidesPerView: 5,
    centeredSlides: false,
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

      var isIos = !!navigator.userAgent.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/);

      function isIphoneX(){
          return /iphone/gi.test(navigator.userAgent) && (screen.height == 812 && screen.width == 375)
      }

      if(!!+is_app && isIos){
          var a =$('.place').height();
          $('.nav-tab').css('top',64* window.devicePixelRatio+'px');
          $('.place').css('height',64* window.devicePixelRatio+a+'px');
          if(isIphoneX()){
              console.log(true)
              $('.nav-tab').css('top',88* window.devicePixelRatio+'px');
              $('.place').css('height',88* window.devicePixelRatio+a+'px')
          }
      }
      //商城导航栏点击统计
      function shopTabClick(id,user_id_store,name) {
          tongji(name+id,baseInfoss);
          window.location.href="/mall/index/list?type="+id+"&user_id_store="+user_id_store;
      }

      //商品点击
      function shopClick(gid,user_id_store) {
          tongji('shopClick_'+gid,baseInfoss);
          window.location.href="/mall/index/detail?gid="+gid+"&user_id_store="+user_id_store;
      }

      /**
       * 记录用户点击轮播图行为日志
       */
      function toloan(user_id_store,gid,index) {
          zhuge.track('首页-banner点击',{'位置':0,'名称':index});
          tongji('toloan_'+gid,baseInfoss);
          setTimeout(function () {
              window.location.href="/mall/index/detail?gid="+gid+"&user_id_store="+user_id_store;
          }, 100);
      }

      //记录用户行为事件
      $('.btn').click(function () {
          var id = $(this).attr('type');
          var user_id_store = $(this).attr('user_id_store');
          var str = 'clicktype_' + id;
          tongji(str,baseInfoss);
          setTimeout(function () {
              location.href = "/mall/index/list?type=" + id + "&user_id_store=" + user_id_store;
          }, 100);
      })

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
  </script>
