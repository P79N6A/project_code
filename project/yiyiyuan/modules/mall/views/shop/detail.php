<style>
    .swiper-pagination .swiper-pagination-bullet-active{background: #c90000;}
</style>

<div class="xqy_title">
    <div class="swiper-container ymmesage">
        <div class="swiper-wrapper ">
            <?php foreach ($goods_pics as $v){ ?>
            <div class="swiper-slide yshiimg">
                <img src="<?=isset($v->pic_url) ? \app\commonapi\ImageHandler::getUrl($v->pic_url) : ''; ?>"/>
            </div>
            <?php };?>
        </div>
        <!-- Add Pagination -->
        <div class="swiper-pagination"></div>
    </div>
    <div class="xqy_txt">
        <h3>
            <span>热销</span><?=$goods_info->goods_name; ?>
        </h3>
        <div class="xqy_txt_zmeny">￥<?=$goods_info->goods_price; ?></div>
    </div>
    <div class="xqy_zpbz"><img src="/292/images/zpbz.jpg"></div>
    <div class="xqy_choose">
        <span id="del">已选</span>
        <?php if(!empty($attr_info)){foreach ($attr_info as $k=>$v){?>
                <!--只取第一个属性-->
<!--            --><?php //foreach ($v as $kk => $val){?>
                <span style="color: black"><?=$v[0]; ?> </span>
<!--            --><?php //}; ?>
        <?php }}; ?>
        <img src="/292/images/right_jt.png">
    </div>
</div>

<div class="xqy_cpjs fqigoumai">
    <button type="submit" id="fqigoumai" <?php if( strpos($_SERVER['HTTP_USER_AGENT'], 'YYY_ANDROID') || strpos($_SERVER['HTTP_USER_AGENT'], 'yyyIOS')){ ?> style="margin-bottom: 0px;" <?php }; ?>>立即购买</button>
</div>

<div class="xqy_cpjs">
    <h3 class="xqy_cpjs_ttjs">商品介绍</h3>
    <div>
        <?php foreach ($goods_d as $v){?>
            <img src="<?=isset($v->pic_url) ? \app\commonapi\ImageHandler::getUrl($v->pic_url) : ''; ?>">
        <?php }; ?>
        <img src="/292/images/zpbz.jpg">
    </div>
</div>
<div style="clear: both; height: 100px;"></div>

<div class="Hmask" hidden></div>
<div class="ttfukfsi" hidden>
    <p>您有正在进行中的订单，无法购买新产品！</p>
    <button class="queding">确定</button>
</div>

<div class="xq_allcont" style="display: none">
    <img class="error" src="/292/images/error.png">
    <div class="xq_all_imgtxt">
        <img src="<?php echo !empty($goods_x->pic_url)?\app\commonapi\ImageHandler::getUrl($goods_x->pic_url):''; ?>">
        <div class="xq_all_txtxt">
            <h3><?=$goods_info->goods_name; ?></h3>
            <p>￥<?=$goods_info->goods_price; ?></p>
        </div>
    </div>
    <div style="height: 22rem;overflow: scroll;">
        <div class="xq_geshi">
            <?php $mark = 0;?>
            <?php if(!empty($attr_info)){foreach ($attr_info as $k=>$v){?>
                <?php $mark += 1;?>
                <div class="ggym">
                    <p><?=$k; ?></p>
                    <ul>
                        <!--只取第一个属性-->
    <!--                    --><?php //foreach ($v as $kk => $val){?>
                            <li class="cll<?=$mark; ?> cl<?=$mark; ?>" onclick="changeCl(<?=$mark; ?>,<?=$mark; ?>)"><?=$v[0]; ?> </li>
    <!--                    --><?php //}; ?>
                    </ul>
                </div>
            <?php }}; ?>
        </div>
    </div>
    <div class="lixdan">
        <input type="hidden" name="goods_name" value="<?=$goods_info->goods_name; ?>">
        <input type="hidden" name="goods_id" value="<?=$goods_info->id; ?>">
        <input type="hidden" name="goods_price" value="<?=$goods_info->goods_price; ?>">
        <input type="hidden" name="colour" value="">
        <input type="hidden" name="bb" value="">
        <input type="hidden" name="money" value="">
        <input type="hidden" name="pic_url" value="<?=$goods_x->pic_url; ?>">
        <button type="button" id="sub" class="sub" <?php if( strpos($_SERVER['HTTP_USER_AGENT'], 'YYY_ANDROID') || strpos($_SERVER['HTTP_USER_AGENT'], 'yyyIOS')){ ?> style="margin-bottom: 0px;"<?php }else{ ;?> style="margin-bottom: 20px;" <?php }; ?>>立即下单</button>
<!--            <button class="gray">已下架</button>-->
    </div>
</div>

<div class="returntop"  id="goTopBtn" hidden>
    <img src="/292/images/returntop.png">
</div>

<!-- Swiper JS -->
<link rel="stylesheet" type="text/css" href="/292/css/swiper.css"/>
<script src="/292/js/swiper.jquery.min.js" type="text/javascript" charset="utf-8"></script>

<!-- Initialize Swiper -->
<script language="javascript">

    var mySwiper = new Swiper('.swiper-container',{
        pagination : '.swiper-pagination',
        paginationHide :true,
    })

    $(function(){
        changeCl(1,1);
        changeCl(2,2);
        $('#fqigoumai').click(function(){
            $(".Hmask").fadeIn(500);
            $(".xq_allcont").fadeIn(500);
            $('body').css({
                "overflow-x":"hidden",
                "overflow-y":"hidden"
            });
        });
        $('.xqy_choose ').click(function(){
            $(".Hmask").fadeIn();
            $(".xq_allcont").fadeIn();
            $('body').css({
                "overflow-x":"hidden",
                "overflow-y":"hidden"
            });
        })
        $('.ttfukfsi  button').click(function(){
            $(".Hmask").fadeOut();
            $(".ttfukfsi").fadeOut();
        })
        $('.xq_allcont .error').click(function(){
            $(".Hmask").fadeOut();
            $(".xq_allcont").fadeOut();
            $('body').css({
                "overflow-x":"auto",
                "overflow-y":"auto"
            });
        })

    })

    var changeCl = function (id, mark) {
        $(".cll"+mark).removeClass('hovera');
        $(".cl"+id).addClass('hovera');
        var cl = $(".cl"+id).html();
        if(mark==1){
            $("input[name='colour']").val(cl)
        }else {
            $("input[name='bb']").val(cl)
        }

    }
    var changeTerm = function (term,money) {
        $(".bianjlfours").attr("src","/292/images/yes_gx.png");
        $(".bianjlfours"+term).attr("src","/292/images/no_gx.png");
        $(".youbianjlone").removeClass('chagwe');
        $(".term"+term).addClass('chagwe');
        $("input[name='term']").val(term);
        $("input[name='money']").val(money);
        $("#sub").removeClass('gray');
        $("#sub").attr('type','submit');
    }

    $("#sub").click(function() {
        var csrf = '<?php echo $csrf; ?>';
        var term = $("input[name='term']").val();
        var money = $("input[name='money']").val();
        var goods_name = $("input[name='goods_name']").val();
        var goods_id = $("input[name='goods_id']").val();
        var goods_price = $("input[name='goods_price']").val();
        var colour = $("input[name='colour']").val();
        var a_id = $("input[name='a_id']").val();
        var bb = $("input[name='bb']").val();
        var pic_url = $("input[name='pic_url']").val();
        var user_id = '<?=isset($_GET['user_id_store']) ? $_GET['user_id_store'] : '';?>';

        $.ajax({
            type: 'POST',
            url: 'ajaxconfirmation',
            data: {'user_id':user_id, '_csrf':csrf,'goods_name':goods_name,'goods_id':goods_id,'goods_price':goods_price,'colour':colour,'a_id':a_id,'bb':bb,'pic_url':pic_url},
            dataType:'json',
            success:function(json){
                if(json.code == '0000'){
                    window.location = '/mall/shop/confirmation';
                }else if(json.code == '10001'){
//                    var url = window.location.pathname+window.location.search;
                    var url = '/mall/shop';
                    window.myObj.goToLogin(url);
                    function goToLogin() {

                    }
                }else{
                    alert('网络错误')
                }
            },
            error:function(){
                alert('网络错误')
            }
        });
    });

</script>