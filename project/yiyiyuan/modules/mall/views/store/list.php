<style>
    .y-popup-banner{
        width: 332px;
        height: auto;
        height: 500px;
        position: fixed;
        top: 100px;
        left: 0;
        right: 0;
        margin: auto;
        z-index: 100;
    }
    .y-popup-banner img{
        width: 100%;
        display: block;
        margin-top: 46px;
    }
    .y-popup {
        width: 296px;
        height: 172px;
        border-radius: 8px;
        background: #fff;
        position: fixed;
        top: 0;
        left: 0;
        bottom: 0;
        right: 0;
        margin: auto;
        z-index: 100;
    }
    .y-mask {
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.6);
        position: fixed;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        margin: auto;
        z-index: 99;
    }
    .y-popup h3{
        font-size: 18px;
        color: #444;
        margin-top: 36px;
        line-height: 1;
        text-align: center;
        font-weight: bold;
    }
    .y-popup p{
        font-size: 14px;
        color: #444;
        line-height: 18px;
        width: 250px;
        text-align: center;
        margin: 18px auto 0;
        height:36px;
    }
    .y-know{
        display: block;
        border: none;
        -webkit-outline: none;
        outline: none;
        background: -webkit-linear-gradient(-90deg, #F00D0D 0%, #FF4B17 100%);
        background: linear-gradient(-90deg, #F00D0D 0%, #FF4B17 100%);
        border-radius: 5px;
        width: 110px;
        height: 40px;
        line-height: 40px;
        font-size: 16px;
        color: #fff;
        font-weight: bold;
        margin: 11px auto 0;
    }
    .y-close-btn{
        width: 20px;
        height: 20px;
        position: absolute;
        background: url(/borrow/350/images/delte.png) no-repeat;
        background-position: top center;
        background-size: 100% 100%;
        top: 10px;
        right: 15px;
    }
</style>


<div class="actvejiuyue martop">
    <div class="ydwey">
        <div class="swiper-container  navtitle">
            <div class="swiper-wrapper">
                <div class="swiper-slide"><a href="/mall/store?user_id_store=<?=$user_id_store;?>">推荐</a></div>
                <?php if($supermarketOpen==1){?>
                    <div class="swiper-slide" style="position: relative"><a class="new" onclick="daichaoTabclick('<?php echo isset($_GET['type']) ? $_GET['type'] : ''; ?>')">
                            贷款超市
                            <img src="/292/images/images/new.png" style="width: 25px;height: 14px;position: absolute;top: 3px;right: -8px;">
                        </a>
                    </div>
                <?php } ?>
                <?php foreach($allGoodsTypes as $k => $v): ?>
                    <div class="swiper-slide"><a <?php if($type == $v['id']):?> class="hover" <?php endif;?> href="/mall/store/list?type=<?=$v->id; ?>&user_id_store=<?=$user_id_store;?>"><?=$v->classify_name; ?></a></div>
                <?php endforeach; ?>
            </div>
            <div class="seiper-pagination swiper-p"></div>
        </div>
    </div>
    <div style="height: 2.6rem;"></div>

    <div><img src="/292/images/fenl_img<?=$type; ?>.jpg"></div>
    <div class="wrapper_ym ">
        <?php foreach($model as $k => $v): ?>
            <dl class="index_scym">
                <a href="javascript:void(0);" class="btn" gid="<?=$v->id; ?>" user_id_store="<?= $user_id_store;?>">
                    <dt><img src="<?=isset($v->pic->pic_url) ? \app\commonapi\ImageHandler::getUrl($v->pic->pic_url) : ''; ; ?>"></dt>
                    <dd>
                        <h3><?=$v->goods_name; ?></h3>
                        <span><?=$v->goods_price; ?><em>元</em></span>
                    </dd>
                </a>
            </dl>
        <?php endforeach; ?>
    </div>
    <span id="pageTotal" style="display: none"><?=$pageTotal; ?></span>
</div>
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
<div class="alert-toast" id="cue_activating" hidden style="width: 90%;position: fixed; top: 48%;left: 5%;border-radius: 5px; z-index: 100;background:rgba(0,0,0,0.5); color: #fff;text-align: center;font-size: 14px;opacity: 0.7;height:26px;line-height: 26px; "></div>
<div class="returntop"  id="goTopBtn" hidden>
    <img src="/292/images/returntop.png">
</div>

<link rel="stylesheet" type="text/css" href="/292/css/swiper.css"/>
<script src="/292/js/swiper.jquery.min.js" type="text/javascript" charset="utf-8"></script>
<script>
    var type = getUrlParam('type');
    var curPage = 1; //当前页码
    var pageTotal = $("#pageTotal").html();
    var user_id_store = '<?=$user_id_store;?>';
    var is_app = '<?php echo $is_app; ?>';
    var is_android = '<?php echo $is_android; ?>';
    var csrf = '<?= $csrf;?>';
    var is_login='<?= $is_login;?>';
    var is_white='<?= $is_white;?>';
    if(type > 4){
        var initialSlide = 1;
    }else{
        var initialSlide = 0;
    }

    var swiper = new Swiper('.swiper-container', {
        pagination: '.swiper-p',
        slidesPerView: 5,
        centeredSlides: false,
        paginationClickable: true,
        spaceBetween:0,
        grabCursor: true,
        el: '.swiper-pagination',
        bulletClass : 'my-bullet',
        initialSlide : initialSlide,
    });

    $(window).scroll(function(){
        totalheight = parseFloat($(window).height()) + parseFloat($(window).scrollTop());
        if($(document).height() <= totalheight){
            if(curPage*1 < pageTotal*1){
                getData(curPage*1+1, type)
            }
        }
    });
    //贷超tab点击
    function daichaoTabclick(type) {
        zhuge.track('首页-顶部导航Tab点击',{'名称':'贷款超市'});
        if (type == 'weixin') {
            tongji('daichaoTab_weixin',baseInfoss);
            window.location.href = 'http://dc.zhirongyaoshi.com?phone=' + dcMobile;
        } else {
            if (mobile == '') {
                closeHtml('daichaoTab');
            } else {
                tongji('daichaoTab',baseInfoss);
                window.location.href = 'http://dc.zhirongyaoshi.com?phone=' + mobile;
            }
        }
    }
    /**
     * 记录点击商品详情日志
     */
    $('.btn').click(function(){
        if(is_login==0){
            gotologin();
        }
        var gid = $(this).attr('gid');
        var user_id_store = $(this).attr('user_id_store');
        var goodsdetails = "goodsdetails_"+gid;
        tongji(goodsdetails);
        setTimeout(function(){
            if(is_white){
                location.href = "/mall/store/detail?gid="+gid+"&user_id_store="+user_id_store;
            }else{
                openShopgoodsajax(gid,user_id_store);
            }
        },100);
    })
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

    function tongji(event) {
        <?php \app\common\PLogger::getInstance('weixin','',$userid); ?>
        <?php $json_data = \app\common\PLogger::getJson();?>
        var baseInfoss = eval( '('  + '<?php echo $json_data; ?>' + ')' );
        baseInfoss.url = baseInfoss.url+'&event='+event;
        console.log(baseInfoss);
        var ortherInfo = {
            screen_height: window.screen.height,//分辨率高
            screen_width: window.screen.width,  //分辨率宽
            user_agent: navigator.userAgent,
            height: document.documentElement.clientHeight || document.body.clientHeight,  //网页可见区域宽
            width: document.documentElement.clientWidth || document.body.clientWidth,//网页可见区域高
        };
        var baseInfos = Object.assign(baseInfoss, ortherInfo);
        var turnForm = document.createElement("form");
        turnForm.id = "uploadImgForm";
        turnForm.name = "uploadImgForm";
        document.body.appendChild(turnForm);
        turnForm.method = 'post';
        turnForm.action = baseInfoss.log_url+'weixin';
        //创建隐藏表单
        for (var i in baseInfos) {
            var newElement = document.createElement("input");
            newElement.setAttribute("name",i);
            newElement.setAttribute("type","hidden");
            newElement.setAttribute("value",baseInfos[i]);
            turnForm.appendChild(newElement);
        }
        var iframeid = 'if' + Math.floor(Math.random( 999 )*100 + 100) + (new Date().getTime() + '').substr(5,8);
        var iframe = document.createElement('iframe');
        iframe.style.display = 'none';
        iframe.id = iframeid;
        iframe.name = iframeid;
        iframe.src = "about:blank";
        document.body.appendChild( iframe );
        turnForm.setAttribute("target",iframeid);
        turnForm.submit();
    }


    //获取数据
    function getData(page, type){
        $.ajax({
            type: 'GET',
            url: 'ajaxpage',
            data: {'type':type,'page':page},
            dataType:'json',
            async: false,
            success:function(json){
                if(json.code == '0000'){
                    curPage = json.data.page
                    var li = "";
                    var list = json.data.list;
                    $.each(list,function(index,array){ //遍历json数据列
                        li += "<dl class='index_scym'> " +
                            "<a href='/mall/store/detail?gid="+array["id"]+"&user_id_store="+user_id_store+"'>"+
                            "<dt><img src="+array['pic_url']+"></dt>" +
                            "<dd>"+
                            "<h3>"+array['goods_name']+"</h3>"+
                            "<span>"+array['goods_price']+"<em>元</em></span>"+
                            "</dd>"+
                            "</a>"+
                            "</dl>";
                    });
                    $(".wrapper_ym").append(li);
                }else{
                    alert(json.code)
                }
            },
            error:function(){
                alert("数据加载失败");
            }
        });
    }
    $('.y-credit-close').click(function(){
        $('.y-mask').hide();
        $('.y-popup-slider').hide();
        $('.y-popup-credit').hide();
    })
    $('.y-popup-loan').click(function(){
        $('.y-mask').hide();
        $('.y-popup-loan').hide();
    });
    $('.close-banner').click(function(){
        $('.y-mask').hide();
        $('.y-popup-banner').hide();
    });
    $('.y-close-btn').click(function(){
        $('.y-mask').hide();
        $('.y-popup').hide();
    })
    function getUrlParam(name) {
        var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)"); //构造一个含有目标参数的正则表达式对象
        var r = window.location.search.substr(1).match(reg); //匹配目标参数
        if (r != null) return unescape(r[2]); return null; //返回参数值
    }

    function gotologin(){
        var url = '/mall/store';
        window.myObj.goToLogin(url);
        function goToLogin() {

        }
    }
</script>