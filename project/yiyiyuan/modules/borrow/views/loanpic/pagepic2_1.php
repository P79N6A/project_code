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
$c = $color == 1 ? 'b' : 'b';

$xinhao_xingzhuang = rand(0, 1); //0是条形 1是扇形
$xinhao_num = $xinhao_xingzhuang == 0 ? rand(1, 4) : rand(1, 3);
$xinhao_first = $xinhao_xingzhuang == 1 ? 's' . $xinhao_num : 't' . $xinhao_num;
$xinhao_second = $color == 1 ? 'b' : 'b';
$xinhao_url = $xinhao_first . $xinhao_second;
$dian_array = [1, 3, 5, 7, 10];
$dian_val = $dian_array[array_rand($dian_array, 1)];
$dian_url = 'd' . $dian_val . $c;
$dingwei = rand(0, 1); //1显示定位 0隐藏定位
$chukusatus=rand(0,1);
$qianshouren=['家人','本人'];
$paychanal=['京东支付','微信支付'];
$yujitime=strtotime($loanPic->pay_time)+86400*rand(2,3);
$yujitime_fanwei=[
    '09:00-15:00',
    '15:00-19:00',
    '19:00-22:00',
];
$kuaidi=[
    '中通',
    '圆通',
    '韵达',
];
?>
<div class="toppost">
    <img src="/buypic/img/top_1.jpg">
    <span class="time"><?php echo $hours; ?>:<?php echo rand(10, 59); ?></span>
    <img class="top2" src="/buypic/img/<?php echo $xinhao_url; ?>.png">
    <?php if ($dingwei == 1): ?>
        <img class="top3" src="/buypic/img/heihei2.png">
    <?php endif; ?>
    <em class="top4"><img src="/buypic/img/<?php echo rand(2, 4); ?>G.png"></em>
    <img class="top5" src="/buypic/img/<?php echo $dian_url; ?>.png">
</div>

<div class="jdbgs">
    <div class="bgoneck">
        <img src="/buypic/img/jdbg1.jpg">
        <img class="jdicon1" src="/buypic/img/jdicon1.jpg">
        <span class="zzacku"><?php if($chukusatus==1){ echo '完成';}else{echo '等待收货';} ?></span>
        <?php if($chukusatus!=1){?>
            <div class="ptlaidi">
                <h3>普通快递</h3>
                <p>预计<?php echo date('m月d日',$yujitime);?> <?php echo $yujitime_fanwei[rand(0,2)]?>送达</p>
            </div>
        <?php }?>
    </div>
    <div class="ardess">
        <?php if($chukusatus!=1){?>
            <div class="lrftbd">
                <div class="didan">
                    <div class="didanleft">
                        <div class="leftjru">
                            <img src="/buypic/img/jdicon2.jpg">
                            <h3><?php echo '['.$cityname.'市]客户 签收人：'.$qianshouren[rand(0,1)].' 已签收 感谢使用'.$kuaidi[rand(0,2)].'速递，期待再次为您服务'; ?></h3>
                        </div>
                        <p class="leftime"><?php echo date('Y-m-d H:i:s',$yujitime-86400-60*60*(rand(1,5))-60*(rand(1,5))-(rand(1,59)));?></p>
                    </div>
                    <div class="didanright"><img src="/buypic/img/buyjt2.jpg"></div>
                </div>

                <div class="didan dizhia">
                    <div class="didanleft">
                        <div class="leftjru">
                            <img src="/buypic/img/jdiconadr.jpg">
                            <h3><?php echo $loanPic->realname?><em><?php echo substr($loanPic->mobile,0,3).'****'.substr($loanPic->mobile,-4) ?></em></h3>
                        </div>
                        <p class="leftime">地址：<?php echo $address?></p>
                    </div>
                </div>
            </div>
        <?php }else{?>
            <div class="lrftbd">
                <div class="didan">
                    <div class="didanleft">
                        <div class="leftjru">
                            <img src="/buypic/img/jdicon2.jpg">
                            <h3>感谢您在京东购物，欢迎您再次光临！</h3>
                        </div>
                    </div>
                    <div class="didanright" style="top:28px;"><img src="/buypic/img/buyjt2.jpg"></div>
                </div>
            </div>

        <?php }?>

        <div class="mlhqan">
            <img class="mlhqian" src="/buypic/img/mlhqian.png">
            <h3>卖了换钱</h3>
            <img class="buyy2" src="/buypic/img/buyjt_2.jpg">
        </div>
    </div>
    <div class="spxqg">
        <div class="jdmwjt">
            <img class="jdiconlogo" src="/buypic/img/jdiconlogo2.png">
            <h3><?php if(!empty($goods->shop)){echo $goods->shop;}else{ echo '京东自营旗舰店'; } ?></h3>
            <img class="buyjt2" src="/buypic/img/buyjt2.jpg">
        </div>
        <div class="spinxqing">
            <img class="iconspin" src="<?php echo \app\commonapi\ImageHandler::getUrl($goods->pic); ?>">
            <div class="txtcont">
                <h3><?php echo $goods->title; ?></h3>
                <p class="shulcol">数量:1 <?php echo $goods->tag; ?></p>
                <div class="moneyy">
                    <span>¥<?php echo $nums[0] . '.' . $nums[1]; ?></span>
                    <button>加购物车</button>
                </div>
            </div>
        </div>
        <div class="jiantoudw">
            <img class="jiantouy" src="/buypic/img/jiantou.png">
            <div class="bzshenji">
                <h3><i>保证升级</i><span>商品全面无忧保障</span></h3>
                <p><em>立即购买</em> <img src="/buypic/img/buyjt3.jpg"></p>
            </div>
        </div>
        <button class="linkkfu"><img src="/buypic/img/linkkfu.png"></button>
    </div>
    <!--<div class="dadsnall">
                <div class="danzibhao">
                    <div class="dgdan"><em>发票类型：</em><i>不开发票</i></div>
                </div>
    </div>-->

    <div class="dadsnall">
        <div class="danzibhao">
            <div class="dgdan"><em>订单编号：</em><i><?php echo $loanPic->order_number; ?></i> <span>复制</span></div>
            <div class="dgdan"><em>下单时间：</em><i><?php echo $loanPic->order_time; ?></i></div>
        </div>
        <div class="danzibhao">
            <div class="dgdan"><em>支付方式：</em><i><?php echo $paychanal[rand(0,1)]?></i> </div>
            <div class="dgdan"><em>支付时间：</em><i><?php echo $loanPic->pay_time; ?></i></div>
        </div>
        <div class="danzibhao">
            <div class="dgdan"><em>配送方式：</em><i>普通快递</i> </div>
            <div class="dgdan"><em>期望配送时间：</em><i><?php echo date('Y-m-d H:i:s',strtotime($loanPic->pay_time)+86400*rand(1,3));?></i></div>
        </div>
        <div class="danzibhao">
            <div class="dgdan"><em>发票类型：</em><i>电子普通快递</i> </div>
            <div class="dgdan"><em>发票抬头：</em><i>个人</i></div>
            <div class="dgdan"><em>发票内容：</em><i>商品明细</i></div>
            <img class="dzfp" src="/buypic/img/dzfp.png">
        </div>
    </div>

    <div class="lrftbd" style="position: static;margin: 10px 0;border-radius: 0;" hidden>
        <div class="didan dizhia">
            <div class="didanleft">
                <div class="leftjru">
                    <img src="/buypic/img/jdiconadr.jpg">
                    <h3>张乐琴<em>186****0731</em></h3>
                </div>
                <p class="leftime">地址：北京海淀区四环到五环之间海淀新技术大厦北四环西路65号10层1000</p>
            </div>
        </div>
    </div>

    <div class="spinzoge">
        <div class="suanqian">
            <h3>商品总额</h3>
            <span>¥<?php echo $nums[0] . '.' . $nums[1]; ?></span>
        </div>
        <div class="suanqian">
            <h3>运费</h3>
            <span>+ ¥<?php echo $yunfeis_val; ?>.00</span>
        </div>
        <!--<div class="suanqian">
            <h3>商品优惠</h3>
            <span>- ¥20.00</span>
        </div>-->
    </div>
    <div class="shifukuan">
        实付款：<span>¥<?php echo $nums[0] + $yunfei_val + $yunfeis_val; ?></span>
    </div>
    <div style="height: 60px;;"></div>
    <div class="sqgoumai">
        <img src="/buypic/img/sqgoumai.png">
    </div>


</div>
