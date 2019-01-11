<?php
$nums = explode('.', (string) $goods->price);
$gongyi = rand(0, 1);
$gongyi_val = rand(1, 5) / 100;
$tui_val = rand(7, 9);
$yunfei = 0; //运费险
$yunfei_val = $yunfei == 1 ? rand(2, 4) : 0;
$yunfeis = rand(1, 10); //运费
$yunfeis_val = $yunfeis > 5 ? rand(5, 15) : 0;
$h = rand(8, 22);
$hours = $h < 10 ? '0' . $h : $h;
$color = rand(0, 1); //0白色，1黑色
$c = $color == 1 ? 'b' : 'w';

$xinhao_xingzhuang = rand(0, 1); //0是条形 1是扇形
$xinhao_num = $xinhao_xingzhuang == 0 ? rand(1, 4) : rand(1, 3);
$xinhao_first = $xinhao_xingzhuang == 1 ? 's' . $xinhao_num : 't' . $xinhao_num;
$xinhao_second = $color == 1 ? 'b' : 'w';
$xinhao_url = $xinhao_first . $xinhao_second;
$dian_array = [1, 3, 5, 7, 10];
$dian_val = $dian_array[array_rand($dian_array, 1)];
$dian_url = 'd' . $dian_val . $c;
$dingwei = rand(0, 1); //1显示定位 0隐藏定位
?>
<div class="toppost">
    <img src="/buypic/img/top.jpg">
    <span class="time" <?php if ($color == 1): ?>style="color: #000;"<?php endif; ?>><?php echo $hours; ?>:<?php echo rand(10, 59); ?></span>
    <?php if ($dingwei == 1): ?>
        <img class="top2" src="/buypic/img/top2<?php if ($color == 1): ?>b<?php endif; ?>.png">
    <?php endif; ?>
    <img class="top3" src="/buypic/img/<?php echo $xinhao_url; ?>.png">
    <em class="top4"><img src="/buypic/img/<?php echo rand(2, 4); ?>G<?php if ($color == 0): ?>w<?php endif; ?>.png"></em>
    <img class="top5" src="/buypic/img/<?php echo $dian_url; ?>.png">
</div>

<div class="add_dizhi">
    <ul class="list-address">
        <li class="address_cont1"><img src="/buypic/img/address.jpg"></li>
        <li class="address_cont2">
            <p>
                <span class="name"><em><?php echo $user->realname; ?></em></span>
                <span class="num">86-<?php echo $user->mobile; ?></span>
            </p>
            <p class="hbeis"><?php echo $address; ?></p>
        </li>
    </ul>
</div>


<div class="scddan">
    <div class="yidong newddxq">
        <div class="scdgdqx">
            <h3><img class="ixcon" src="/buypic/img/ixcon.jpg"><span><?php echo $goods->shop ?> </span> <img class="buyjt" src="/buypic/img/buyjt2.jpg"></h3>
        </div>
        <div class="twenbm">
            <div class="yidongimg">
                <img src="<?php echo $goods->pic; ?>">
            </div>
            <div class="yidongtxtx">
                <h4><?php echo $goods->title; ?></h4>
                <p class="zgydog"><?php echo $goods->tag; ?></p>
                <div class="gyiz">
                    <?php if ($gongyi == 1): ?>
                        <a>公益捐赠<?php echo $gongyi_val; ?>元</a>
                    <?php endif; ?>
                    <a><?php echo $tui_val; ?>天退货</a></div>
            </div>
            <div class="moneybuy">
                <p class="qianleft">￥<em><?php echo $nums[0] . '.' . $nums[1]; ?></em></p>
                <p class="chengy1">️×1</p>
            </div>
        </div>
        <div class="tkuan"><a>退款</a></div>
        <div class="poeihui"><span>运费险</span>
            <em>确认收获前退货可理赔</em>
            <i>￥<?php echo $yunfei_val; ?>.00×1</i>

        </div>
        <div class="yunfeix">
            <span class="yfxan1">运费险</span>
            <span class="yfxan2">卖家赠送</span>
        </div>
        <div class="yunfeix">
            <span class="yfxan1">运费</span>
            <span class="yfxan2">￥<?php echo $yunfeis_val; ?>.00</span>
        </div>
        <div class="yunfeix sfukxm">
            <span class="yfxan1">实付款 (含运费)</span>
            <span class="yfxan2 redred"><em>￥</em><?php echo $nums[0] + $yunfei_val + $yunfeis_val; ?><em>.<?php echo $nums[1]; ?></em></span>
        </div>
    </div>
</div>


<div class="ddanmeg">
    <h4><em></em><a>订单信息</a></h4>
    <div class="dgdan"><em>订单编号:</em><?php echo $loanPic->order_number; ?> <span>复制</span></s></div>
    <div class="dgdan"><em>支付宝交易号:</em><?php echo $loanPic->trade_number; ?></div>
    <div class="dgdan"><em>创建时间:</em><?php echo $loanPic->order_time; ?></div>
    <div class="dgdan"><em>付款时间:</em><?php echo $loanPic->pay_time; ?></div>
    <div class="buttonbottom">
        <img src="/buypic/img/buttonbottom.jpg">
        <button class="/buypic/imgimg1"></button>
        <button class="/buypic/imgimg2"></button>
    </div>
</div>