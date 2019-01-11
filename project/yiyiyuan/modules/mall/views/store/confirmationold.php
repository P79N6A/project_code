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
                <span><?=$colour; ?></span><span><?=$bb; ?></span>
            </div>
            <p>￥<?=$goods_price;?></p>
        </div>
    </div>

    <div class="xq_geshi">
        <div class="ggym">
            <p>订单金额： <span style="float: right">￥<?=$goods_price;?></span></p>
        </div>
    </div>
    <div class="lixdan truedzxq">
        <input type="hidden" name="goods_name" value="<?=$goods_name?>">
        <input type="hidden" name="goods_id" value="<?=$goods_id?>">
        <input type="hidden" name="goods_price" value="<?=$goods_price?>">
        <input type="hidden" name="colour" value="<?=$colour?>">
        <input type="hidden" name="bb" value="<?=$bb?>">
        <input type="hidden" name="user_id" value="<?=$userInfo->user_id;?>">
        <input type="hidden" name="a_id" value="<?=$address_info['id'];?>">
        <input type="hidden" name="pic_url" value="<?=$pic_url;?>">
        <input type="hidden" name="address_info" value="<?php echo empty($address_info['address'])?'' : $address_info['address']; ?>">
        <button type="submit" id="sub" class="sub" <?php if( strpos($_SERVER['HTTP_USER_AGENT'], 'YYY_ANDROID') || strpos($_SERVER['HTTP_USER_AGENT'], 'yyyIOS')){ ?> style="margin-bottom: 0px;" <?php }; ?>>立即下单</button>
        <!--<button class="gray">立即下单</button>
        <button class="gray">已下架</button>-->
    </div>
</div>

<div id="addBox" class="true_shdz" style="display: none"></div>
<script type="text/javascript">
    $(function(){
        var address = $("input[name='address_info']").val();
        if(address == ''){
            $(".add_dzhi").css('display', 'block');
            $(".list-address").css('display', 'none');
            $("#sub").attr('class','gray');
        }

        $("#sub").click(function() {
            $("#sub").attr("disabled", "disabled");
            var csrf = '<?php echo $csrf; ?>';
            var goods_name = $("input[name='goods_name']").val();
            var goods_id = $("input[name='goods_id']").val();
            var goods_price = $("input[name='goods_price']").val();
            var user_id = $("input[name='user_id']").val();
            var term = $("input[name='term']").val();
            var money = $("input[name='money']").val();
            var colour = $("input[name='colour']").val();
            var a_id = $("input[name='a_id']").val();
            var bb = $("input[name='bb']").val();
            var pic_url = $("input[name='pic_url']").val();
            if (address == ''){
                $("#addBox").html('请填写收货地址!').show(150).delay(1500).hide(150);
                setTimeout(function () {
                    $("#sub").removeAttr("disabled");
                }, 1500);
                return false;
            }

            $.post("/mall/store/preorder", {_csrf:csrf, goods_id: goods_id,user_id: user_id, colour:colour, bb:bb, goods_name:goods_name, a_id:a_id, pic_url:pic_url, goods_price:goods_price}, function(result) {
                var data = eval("(" + result + ")");
                if (data.res_code == 0) {
                    var location_href = "/mall/store/orderdetailsold?order_id="+data.res_data;
                    window.location = location_href;
                } else {
                    alert(data.res_data);
                    $("#sub").removeAttr("disabled");
                    return false;
                }
            });
        });
    });
</script>