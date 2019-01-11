<div class="actvejiuyue martop">
    <div class="ydwey">
        <div class="swiper-container  navtitle">
            <div class="swiper-wrapper">
                <div class="swiper-slide"><a href="/mall/shop?user_id_store=<?=$user_id_store;?>">推荐</a></div>
                <?php foreach($allGoodsTypes as $k => $v): ?>
                    <div class="swiper-slide"><a <?php if($type == $v['id']):?> class="hover" <?php endif;?> href="/mall/shop/list?type=<?=$v->id; ?>&user_id_store=<?=$user_id_store;?>"><?=$v->classify_name; ?></a></div>
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
                <a href="/mall/shop/detail?gid=<?=$v->id; ?>&user_id_store=<?=$user_id_store;?>">
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
                            "<a href='/mall/shop/detail?gid="+array["id"]+"&user_id_store="+user_id_store+"'>"+
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

    function getUrlParam(name) {
        var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)"); //构造一个含有目标参数的正则表达式对象
        var r = window.location.search.substr(1).match(reg); //匹配目标参数
        if (r != null) return unescape(r[2]); return null; //返回参数值
    }
</script>
