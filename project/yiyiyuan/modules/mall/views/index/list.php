<div class="actvejiuyue martop">
    <div class="ydwey">
        <div class="swiper-container  navtitle">
            <div class="swiper-wrapper">
                <div class="swiper-slide"><a href="/mall/index?user_id_store=<?=$user_id_store;?>">推荐</a></div>
                <?php if($supermarketOpen==1){?>
                    <div class="swiper-slide" style="position: relative"><a class="new" onclick="daichaoTabclick('<?php echo isset($_GET['type']) ? $_GET['type'] : ''; ?>')">
                            贷款超市
                            <img src="/292/images/images/new.png" style="width: 25px;height: 14px;position: absolute;top: 3px;right: -8px;">
                        </a>
                    </div>
                <?php } ?>
                <?php foreach($allGoodsTypes as $k => $v): ?>
                    <div class="swiper-slide"><a <?php if($type == $v['id']):?> class="hover" <?php endif;?> href="/mall/index/list?type=<?=$v->id; ?>&user_id_store=<?=$user_id_store;?>"><?=$v->classify_name; ?></a></div>
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
       var gid = $(this).attr('gid');
       var user_id_store = $(this).attr('user_id_store');
       var goodsdetails = "goodsdetails_"+gid;
       tongji(goodsdetails);
        setTimeout(function(){
            location.href = "/mall/index/detail?gid="+gid+"&user_id_store="+user_id_store;
        },100);
    })

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
                            "<a href='/mall/index/detail?gid="+array["id"]+"&user_id_store="+user_id_store+"'>"+
                            "<dt><img src="+array['pic_url']+"></dt>" +
                            "<dd>"+
                            "<h3>"+array['goods_name']+"</h3>"+
                            "<span>"+array['goods_price']+"<em>元</em></span>"+
                            "<p>月供"+array['instalment']+"起</p>"+
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

    function getUrlParam(name) {
        var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)"); //构造一个含有目标参数的正则表达式对象
        var r = window.location.search.substr(1).match(reg); //匹配目标参数
        if (r != null) return unescape(r[2]); return null; //返回参数值
    }
</script>
