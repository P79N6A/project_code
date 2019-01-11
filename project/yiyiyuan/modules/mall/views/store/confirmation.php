<style>
    .xq_allcont{
        margin-bottom:56px;
    }
    .xq_allcont .lixdan.truedzxq{
        bottom: 56px !important;
    }
    .w_subBtn{
        height:49px !important;
    }
    .w_profit_list{
        overflow-y: scroll;
        -webkit-overflow-scrolling:touch;
    }
    .w_subBtn{
        position: static;
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
    .w_btn{
        padding-bottom: 70px;
        margin-top:10px;
    }
    /*.xq_allcont .lixdan.truedzxq{*/
        /*position: fixed !important;;*/
    /*}*/
    .profit-box{
        border-top: 10px solid #f3f3f3;
        margin-bottom: 134px;
    }
</style>
<div class="xq_allcont truexqy">
    <div class="add_dizhi">
        <a href="/mall/store/editaddress?user_id=<?=$userInfo->user_id;?>"><img src="/292/images/add_dzhi.jpg" class="add_dzhi" style="display:none;"></a>
        <a href="/mall/store/editaddress?user_id=<?=$userInfo->user_id;?>&address_id=<?=$address_info['id'];?>">
            <ul class="list-address">
                <li class="address_cont1"><img src="/292/images/address.png"></li>
                <li class="address_cont2">
                    <p>
                        <span class="name">收件人：<em><?=$address_info['receive_name'];?></em></span>
                        <span class="num"><?=$address_info['receive_mobile'];?></span>
                    </p>
                    <p>收货地址：<?php echo empty($address_info['address'])?'' : $address_info['address']; ?></p>
                </li>
                <li class="address_cont3"><img src="/292/images/right_jt.png"></li>
            </ul>
        </a>
    </div>
    <img src="/292/images/true_border.jpg">
    <div class="xq_all_imgtxt">
        <img src="<?php echo !empty($pic_url)?\app\commonapi\ImageHandler::getUrl($pic_url):''; ?>">
        <div class="xq_all_txtxt">
            <h3><?=$goods_name?></h3>
            <div>
                <?php if(!empty($shuxing)): ?>
                    <?php foreach (json_decode($shuxing) as $k=>$v): ?>
                        <?php if($k == 0): ?>
                            <input type="hidden" name="colour" value="<?=$v?>">
                        <?php else: ?>
                            <input type="hidden" name="bb" value="<?=$v?>">
                        <?php endif;?>
                        <span><?php  echo $v;?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
                <span><?=$colour; ?></span><span><?=$bb; ?></span>
            </div>
            <p>￥<?=$goods_price;?></p>
        </div>
    </div>
    <!-- start -->

    <div class="w_profit">
        <span>分期方案</span>
        <span><?php echo $days; ?>天×<?php echo $terms; ?>期</span>
    </div>
    <div class="w_profit_lis">
        <span style="margin-left: 20px;"><?=date_format(date_create($goodtermModel['0']['days']),"Y年m月d日")?></span><span style="margin-right:20px;">￥<?=$goodtermModel['0']['single_money']?>元</span>
    </div>
    <div class="w_profit_list">
        <?php if(!empty($goodtermModel)): ?>
        <?php foreach ($goodtermModel as $k=>$v): ?>
            <?php if($k != 0): ?>
                <div class="w_profit_lis">
                    <span><?=date_format(date_create($v['days']),"Y年m月d日")?></span><span>￥<?=$v['single_money']?>元</span>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <div class="w_btn" data="0"><span>查看全部</span><img src="/292/images/shouqi.png" alt=""></div>
    <div class="profit-box">
        <div class="w_profit" style="margin-top: 0;border-bottom:none;">
            <p style="float: left;margin-left:110px;line-height: 50px;font-size: 14px;color: #444;">共一件商品</p>
            <?php $y=explode(".",$goods_price);?>
            <p style="float: left;margin-left:22px;line-height: 50px;color: #444;"><span style="font-size:14px;">小计：</span><span style="font-weight:bold;font-size:18px;float:none;margin-right:0">￥<?=$y[0];?></span><span style="font-weight:bold;font-size:14px;">.<?php echo $y[1]; ?></span></p>
        </div>
    </div>
    <div class="lixdan truedzxq" style="height: 49px;">
        <div class="w_subBtn" style="position: fixed;bottom: 56px;">
            <div class="w_subBtn_L">
                <span>合计：</span>
                <span>￥<em class="w_totalMon"><?=$goods_price;?></em></span>
            </div>
            <div class="w_subBtn_R" id="sub">
                提交分期订单
            </div>
        </div>
        <!--<button class="gray">立即下单</button>
        <button class="gray">已下架</button>-->
    </div>
</div>
<div><input type="hidden" name="goods_name" value="<?=$goods_name?>">
    <input type="hidden" name="goods_id" value="<?=$goods_id?>">
    <input type="hidden" name="goods_price" value="<?=$goods_price?>">
    <input type="hidden" name="user_id" value="<?=$userInfo->user_id;?>">
    <input type="hidden" name="a_id" value="<?=$address_info['id'];?>">
    <input type="hidden" name="pic_url" value="<?=$pic_url;?>">
    <input type="hidden" name="terms" value="<?=$terms;?>">
    <input type="hidden" name="days" value="<?=$days;?>">
    <input type="hidden" name="shuxing" value="<?=$shuxing?>">
    <input type="hidden" name="address_info" value="<?php echo empty($address_info['address'])?'' : $address_info['address']; ?>">
</div>
<div id="addBox" class="true_shdz" style="display: none"></div>
<div class="y-wintips" hidden>请勿频繁提交商品订单，建议您明天再试</div>
<script>
    (function () {
        var timer = null;
        timer = setTimeout(function () {
            clearTimeout(timer);
            $('.lixdan').css({
                'position':'fixed',
                'bottom':'56px'
            })
        },500)

    })();
</script>
<script type="text/javascript">
    var black_box = _fmOpt.getinfo();//获取同盾指纹
    $(function(){
        var address = $("input[name='address_info']").val();
        if(address == ''){
            $(".add_dzhi").css('display', 'block');
            $(".list-address").css('display', 'none');
//            $("#sub").attr('class','gray');
        }

        $("#sub").click(function() {
            var attrinfo = $(this).parent().find('.w_subBtn_R');
            attrinfo.attr('id',"");
            var csrf = '<?php echo $csrf; ?>';
            var goods_name = $("input[name='goods_name']").val();
            var goods_id = $("input[name='goods_id']").val();
            var goods_price = $("input[name='goods_price']").val();
            var user_id = $("input[name='user_id']").val();
            var terms = $("input[name='terms']").val();
            var days = $("input[name='days']").val();
            var money = $("input[name='money']").val();
            var colour = $("input[name='colour']").val();
            var a_id = $("input[name='a_id']").val();
            var bb = $("input[name='bb']").val();
            if(!bb){
                bb = "";
            };
            if(!colour){
                colour = "";
            }
            var pic_url = $("input[name='pic_url']").val();
            if (address == ''){
                $("#addBox").html('请填写收货地址!').show(150).delay(1500).hide(150);
                setTimeout(function () {
                    attrinfo.attr('id',"sub");
                }, 1500);
                return false;
            }
            $.post("/mall/store/preorderterm", {_csrf:csrf, goods_id: goods_id,user_id: user_id, colour:colour, bb:bb, goods_name:goods_name, a_id:a_id, pic_url:pic_url, goods_price:goods_price,terms:terms,days:days,black_box:black_box}, function(result) {
                var data = eval("(" + result + ")");
//                console.log(data);return false;
                if (data.res_code == 0) {
                    var location_href = "/mall/store/ordersuccess?order_id="+data.res_data;
                    window.location = location_href;
                } else {
                    attrinfo.attr('id',"sub");
                    $('.y-wintips').show();
                    $('.y-wintips').delay(5000).hide(0);
                    return false;
                }
            });
        });
    });
    $(".w_btn").click(function(){
        var onOff = $(this).attr('data');
        $(".w_profit_list").animate({height:'toggle',opacity:'toggle'});
        if(onOff == '1'){
            $(this).attr('data','0');
            $(this).find('span').html('查看全部');
            $(this).find('img').css('transform','rotate(180deg)');
        };
        if(onOff == '0'){
            $(this).attr('data','1');
            $(this).find('span').html('收起');
            $(this).find('img').css('transform','rotate(0deg)');

        };
    });
</script>
<!--<script src="//cdn.jsdelivr.net/npm/eruda"></script>-->
<!--<script>eruda.init();</script>-->