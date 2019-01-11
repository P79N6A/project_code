<style>
    .swiper-pagination .swiper-pagination-bullet-active{background: #c90000;}
    /* loading */
    .loadingBox{
        height: 121px;
        width: 121px;
        opacity: 0.6;
        background: #000000;
        border-radius: 0.27rem;
        position:absolute;
        left:0;
        top: -175px;
        bottom: 0;
        right: 0;
        margin: auto;
        z-index: 100;
    }
    .loadingIcon{
        width: 58px;
        height: 58px;
        margin:18px auto 10px;
        display: block;
        animation: rotation 1.5s linear infinite;
        animation: rotation 1.5s linear infinite;
        -moz-animation: rotation 1.5s linear infinite;
        -webkit-animation: rotation 1.5s linear infinite;
        -o-animation: rotation 1.5s linear infinite;
    }
    @-webkit-keyframes rotation{
        from {-webkit-transform: rotate(0deg);}
        to {-webkit-transform: rotate(360deg);}
    }
    .loadingText{
        font-family:"微软雅黑";
        font-size: 14px;
        color: #FFFFFF;
        letter-spacing: 2px;
        line-height: 14px;
        width: 100%;
        text-align: center;
    }
    .y-closewin{
        background: url("/borrow/310/images/closewin.png") no-repeat;
        width: 16px;
        height: 16px;
        background-size: 100%;
        background-position: top center;
        position: absolute;
        right: 10px;
        top:10px;
    }
    .ttfukfsi1{
        width: 296px;
        height: 178px;
        position: fixed;
        top: 0;
        right: 0;
        left: 0;
        bottom: 0;
        margin: auto;
        border-radius: 5px;
        z-index: 100;
        background: #fff;
    }
    .ttfukfsi1 h3{
        font-size: 18px;
        font-weight: bold;
        color: #444;
        line-height: 1;
        margin-top:36px;
    }
    .ttfukfsi1 p{
        margin: 0 auto;
        font-size: 14px;
        color: #444444;
        line-height: 18px;
        width: 254px;
        text-align: left;
        margin-top: 10px;
    }
    .y-go-write{
        font-size: 16px;
        color: #FFFFFF;
        line-height: 16px;
        background: -webkit-linear-gradient(90deg, #F00D0D 0%, #FF4B17 100%);
        background: -moz-linear-gradient(90deg, #F00D0D 0%, #FF4B17 100%);
        background: linear-gradient(90deg, #F00D0D 0%, #FF4B17 100%);
        border-radius: 5px;
        width: 118px;
        height: 40px;
        line-height: 40px;
        margin: 16px auto 0;
        text-align: center;
    }
    .y-wintips{
        opacity: 0.7;
        background: #000000;
        border-radius: 7px;
        font-size: 16px;
        color: #FFFFFF;
        line-height: 34px;
        text-align: center;
        width: 307px;
        height: 34px;
        position:fixed;
        bottom: 118px;
        left: 0;
        right: 0;
        margin: auto;
        z-index: 101;
    }
    .y-active{
        text-align: center;
        font-size: 14px;
        color: #FFFFFF;
        line-height: 25px;
        background: -webkit-linear-gradient(90deg, #F00E0E 0%, #FE4A16 100%) !important;
        background: linear-gradient(90deg, #F00E0E 0%, #FE4A16 100%) !important;
        border-radius: 4px !important;
        padding: 2px 6px !important;
        margin-bottom: 10px;
    }
    .y-ordinary{
        text-align: center;
        font-size: 14px;
        color: #444444;
        line-height: 25px;
        background: #EFEFEF;
        border-radius: 4px;
        padding: 2px 6px;
        margin-bottom:10px;
    }
    .fqfa li{
        float: left;
        margin-right: 17px !important;
    }
    .xq_allcont .lixdan button {
        width: 100%;
        height:49px;
        background: -webkit-linear-gradient(90deg, #F00E0E 0%, #FE4A16 100%);
        background: linear-gradient(90deg, #F00E0E 0%, #FE4A16 100%);
        font-size: 18px;
        color: #FFFFFF;
        line-height: 49px;
        margin: 0;
        border-radius: 0;
        padding: 0;
    }
    .fqigoumai button{
        width: 100%;
        height:49px;
        background: -webkit-linear-gradient(90deg, #F00E0E 0%, #FE4A16 100%);
        background: linear-gradient(90deg, #F00E0E 0%, #FE4A16 100%);
        font-size: 18px;
        color: #FFFFFF;
        line-height: 49px;
        margin: 0;
        border-radius: 0;
        padding: 0;
    }
    .xqy_cpjs.fqigoumai{
        bottom: 56px !important;
    }
    .xqy_cpjs button{
        /*bottom: 56px !important;*/
        position: static;
    }
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
        <div class="xqy_txt_yuefu"></em>
            <!--            <span>支持3/6/9/12期</span>-->
        </div>
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
    <button type="submit" class="clickpurchase" id="fqigoumai" <?php if( strpos($_SERVER['HTTP_USER_AGENT'], 'YYY_ANDROID') || strpos($_SERVER['HTTP_USER_AGENT'], 'yyyIOS')){ ?> style="margin-bottom: 0px;" <?php }; ?>>立即下单</button>
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

<!--原始弹框-->
<div class="Hmask" hidden></div>
<div class="ttfukfsi" hidden>
    <p>您有正在进行中的订单，无法购买新产品！</p>
    <button class="queding">确定</button>
</div>
<!--新增弹窗1211-->
<div class="ttfukfsi1" hidden>
    <i class="y-closewin"></i>
    <h3 style="text-align: center;">温馨提示</h3>
    <p>您还未完成资料填写，请完成个人资料后提交分期订单！</p>
    <div class="y-go-write" onclick="ziliao()">去填写</div>
</div>
<!--新增提示1211-->
<div class="y-wintips" hidden>请勿频繁提交商品订单，建议您明天再试</div>

<div class="xq_allcont" style="display: none;<?php if( strpos($_SERVER['HTTP_USER_AGENT'], 'YYY_ANDROID') || strpos($_SERVER['HTTP_USER_AGENT'], 'yyyIOS')){ ?><?php }else{ ;?>bottom:50px;<?php }; ?>" >
    <img class="error" src="/292/images/error.png">
    <div class="xq_all_imgtxt">
        <img src="<?php echo !empty($goods_x->pic_url)?\app\commonapi\ImageHandler::getUrl($goods_x->pic_url):''; ?>">
        <div class="xq_all_txtxt">
            <h3><?=$goods_info->goods_name; ?></h3>
            <p>￥<?=$goods_info->goods_price; ?></p>
        </div>
    </div>

    <div style="height: 13rem;overflow: scroll;">
        <div class="xq_geshi">
            <?php $mark = 0;?>
            <?php if(!empty($attr_info)){
                $shuxingarr = array();
                foreach ($attr_info as $k=>$v){
                    $shuxingarr[] = $v[0];
                    ?>
                <?php $mark += 1;?>
                <div class="ggym">
                    <p><?=$k; ?></p>
                    <ul>
                        <!--只取第一个属性-->
                        <!--                    --><?php //foreach ($v as $kk => $val){?>
                        <li class="y-active" onclick="changeCl(<?=$mark; ?>,<?=$mark; ?>)"><?=$v[0]; ?> </li>
                        <!--                    --><?php //}; ?>
                    </ul>
                </div>
            <?php }}; ?>
            <p>分期方案</p>
            <ul class="fqfa">
                <li class="y-active  checknumber"  days ="30" number ="1">30天X1期</li>
                <li class="y-ordinary checknumber" days ="30" number ="3">30天X3期</li>
                <li class="y-ordinary checknumber" days = "30" number = "6">30天X6期</li>
                <li class="y-ordinary checknumber" days = "30" number = "9">30天X9期</li>
                <li class="y-ordinary checknumber" days = "56" number = "1">56天X1期</li>
            </ul>
        </div>
    </div>
    <div class="lixdan">
        <input type="hidden" name="goods_name" value="<?=$goods_info->goods_name; ?>">
        <input type="hidden" name="goods_id" value="<?=$goods_info->id; ?>">
        <input type="hidden" name="goods_price" value="<?=$goods_info->goods_price; ?>">
<!--        <input type="hidden" name="colour" value="">-->
<!--        <input type="hidden" name="bb" value="">-->
        <input type="hidden" name="term" value="">
        <input type="hidden" name="money" value="">
        <input type="hidden" name="shuxing" value = '<?php echo json_encode($shuxingarr); ?>'>
        <input type="hidden" name="pic_url" value="<?=$goods_x->pic_url; ?>">
        <button type="submit" id="sub" class="sub" <?php if( strpos($_SERVER['HTTP_USER_AGENT'], 'YYY_ANDROID') || strpos($_SERVER['HTTP_USER_AGENT'], 'yyyIOS')){ ?> style="margin-bottom: 0px;"<?php }else{ ;?> style="margin-bottom: 20px;" <?php }; ?>>立即下单</button>
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

<?php if( strpos($_SERVER['HTTP_USER_AGENT'], 'YYY_ANDROID') || strpos($_SERVER['HTTP_USER_AGENT'], 'yyyIOS')): ?>
    <input type="hidden" value="1" id="is_app">
<?php else: ?>
    <input type="hidden" value="2" id="is_app">
<?php endif; ?>
<div class="loadingBox" hidden>
    <img src="/borrow/310/images/loading.png" class="loadingIcon">
    <p class="loadingText">前往借款中</p>
</div>
<script language="javascript">
    var is_app = '<?php echo $is_app;?>';
    var mySwiper = new Swiper('.swiper-container',{
        pagination : '.swiper-pagination',
        paginationHide :true,
    })
    $('.checknumber').click(function() {
        $(this).siblings().removeClass('y-active');
        $(this).siblings().addClass('y-ordinary');
        $(this).removeClass('y-ordinary');
        $(this).addClass('y-active');
    })

    $(function(){
        changeCl(1,1);
        changeCl(2,2);
        $('#fqigoumai').click(function(){
            $(".Hmask").show();
            $(".xq_allcont").show();
            $('body').css({
                "overflow-x":"hidden",
                "overflow-y":"hidden"
            });
        });
        $('.xqy_choose ').click(function(){
            $(".Hmask").hide();
            $(".xq_allcont").hide();
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

    $(".y-closewin").click(function(){
        $(".Hmask").fadeOut();
        $(".ttfukfsi1").fadeOut();
    })
    var is_app = $("#is_app").val();
    $("#sub").click(function() {
        tongji('immediately_order');
        $(".Hmask").hide();
        $(".xq_allcont").hide();
        $('body').css({
            "overflow-x":"auto",
            "overflow-y":"auto"
        });

//        $('.loadingBox').show();
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
        var user_id = '<?php echo $user_id; ?>';
        var number = $('.fqfa').find('.y-active').attr('number');
        var days = $('.fqfa').find('.y-active').attr('days');
        var shuxing = $("input[name='shuxing']").val();
        if(!shuxing) {shuxing = "";}
//        console.log(number);return false;
        /**
         setTimeout(function () {
            if(is_app == 1){
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
                return false;
            }else{
                window.location = '/borrow/loan?type=1';
                return false;
            }

        }, 1500);
         **/
        $.ajax({
            type: 'POST',
            url: '/mall/store/ajaxconfirmation',
            data: {'shuxing':shuxing,'user_id':user_id, '_csrf':csrf,'goods_name':goods_name,'goods_id':goods_id,'goods_price':goods_price,'colour':colour,'a_id':a_id,'bb':bb,'pic_url':pic_url,'terms':number,'days':days},
            dataType:'json',
            success:function(json){
                console.log(json);
                if(json.code == '0000'){
                    window.location = '/mall/store/confirmation';
                }else if(json.code == '10001'){
//                    var url = window.location.pathname+window.location.search;
                    var url = '/mall/store';
                        window.location = url;
                }else if(json.code == '10002'){
                    $('.loadingBox').hide();
                    $(".Hmask").show();
                    $(".ttfukfsi1").show();
                }else if(json.code == '10003'){
                        $('.loadingBox').hide();
                        $('.y-wintips').show();
                        $('.y-wintips').delay(5000).hide(0);
                        return false;
                }else{
                    $('.loadingBox').hide();
                    alert('暂不可购买')
                }
            },
            error:function(){
                $('.loadingBox').hide();
                alert('暂不可购买')
            }
        });
    });

    /**
     * 分期购买埋点事件
     */
    $('.clickpurchase').click(function(){
        tongji('clickpurchase');
    })

    function tongji(event) {
        <?php \app\common\PLogger::getInstance('weixin','',$user_id); ?>
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

    function ziliao(){
        if(is_app == 1){
            var u = navigator.userAgent, app = navigator.appVersion;
            var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Linux') > -1;
            var isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/);
            var android_requiredata = "com.business.userinfo.UserInfoActivity.class";
            var ios_requiredata = "UserInfoViewController";
            var position_requiredata = "11";
            if (isiOS) {
                window.myObj.toPage(ios_requiredata);
            } else if (isAndroid) {
                window.myObj.toPage(android_requiredata, position_requiredata);
            }
            return false;
        }else{
            window.location = '/borrow/userinfo/requireinfo?source_mall=1';
            return false;
        }
    }
</script>