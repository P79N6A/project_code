<?php include template("header");?>

<div class="blank108"></div>
<div class="blank60"></div>
<div class="w2 clearfix">
	<div class="ordersDetail clearfix">
    	<div class="ordersTop"><strong>订单详情</strong></div>
        <div class="pback2"><a href="/order/index.php?action=buy">返回"订单"</a></div>
        <div class="ordersArea1">
        	<?php if($order['state'] == 'unpay'){?>
        	<div class="clearfix">当前订单状态：<span>未付款</span></div>
            <div class="clearfix">未付款的商品不会锁定库存，请及时付款，不然就被抢光啦！</div>
            <?php if($diff_time > 0 || $order['paytype'] == 'jxk'){?>
            <div class="clearfix"><?php if(($order['state'] == 'unpay' && $order['paytype'] != 'jxk')){?><a href="/order/check.php?id=<?php echo $order['id']; ?>" id="order_detail_pay"><img src="/static/images/btn12.png" alt="" /></a>&nbsp;&nbsp;&nbsp;&nbsp;<span id="order_single_lasttime" curtime="<?php echo $now; ?>000" diff="<?php echo $diff_time; ?>000" class="o">剩余时间</span><span id="order_single_difftime" class="o">00:<?php echo $left_minute; ?>:<?php echo $left_time; ?></span>&nbsp;&nbsp;&nbsp;&nbsp;<!--<a href="javascript:void(0);" oid="<?php echo $order['id']; ?>" class="delorder">删除订单</a>--><?php }?></div>
            <?php } else { ?>
            <div class="clearfix"><span>订单失效</span>&nbsp;&nbsp;&nbsp;&nbsp;<!--<a href="javascript:void(0);" oid="<?php echo $order['id']; ?>" class="delorder">删除订单</a>--></div>
            <?php }?>
            <?php } else if($order['state'] == 'pay') { ?>   
        	<div class="clearfix">当前订单状态：<span>已付款</span></div>
            <div class="clearfix">等待卖家发货。</div>
            <?php } else { ?>            
        	<div class="clearfix">当前订单状态：<span>交易成功</span></div>
            <div class="clearfix">卖家已发货。</div>
            <?php }?>
            
        </div>
        <div class="ordersArea2">
        	<div class="dt">物流信息</div>
            <div class="dd">
            	<?php if($order['state'] == 'complete'){?>
            	<div class="clearfix">快递公司：<?php echo $order['express_name']; ?></div>
                <div class="clearfix">物流单号：<?php echo $order['express_id']; ?></div>
            	<?php } else { ?>
            	<div class="clearfix">尚无物流信息</div>
            	<?php }?>
            </div>
        </div>
        <div class="ordersArea2">
        	<div class="dt">订单信息</div>
            <div class="dd">
            	<div class="clearfix">订单编号：<?php echo $order['pay_id']; ?></div>
                <div class="clearfix">下单时间：<?php echo $order['createdate']; ?></div>
            </div>
        </div>
        <div class="ordersArea2">
        	<div class="dt">配送信息</div>
            <div class="dd">
            	<div class="clearfix">收货人：<?php echo $order['realname']; ?></div>
                <div class="clearfix">联系电话：<?php if(!empty($order['mobile'])){?><?php echo $order['mobile']; ?><?php } else { ?><?php echo $order['phone']; ?><?php }?></div>
                <div class="clearfix">地址： <?php echo $useraddress; ?>，<?php echo $order['postcode']; ?></div>
            </div>
        </div>
        <div class="ordersArea2">
        	<div class="dt">买家留言</div>
            <div class="dd">
            	<div class="clearfix"><?php if(!empty($order['remark'])){?><?php echo $order['remark']; ?><?php } else { ?>无<?php }?></div>
            </div>
        </div>
        <div class="ordersArea3">
        	<div class="dt">商品信息</div>
            <div class="dd">
            	<div class="omt">
                	<ul>
                    	<li class="pname">商品</li>
                        <li class="pprice">单价</li>
                        <li class="pamount">数量</li>
                        <li class="pseller">卖家</li>
                        <li class="zprice">金额</li>
                    </ul>
                </div>
                <div class="omc">
                	<ul>
                    	<li class="pname">
                        	<p><?php echo $product['pname']; ?></p>
                            <p><span><?php echo $order['property']; ?></span></p>
                        </li>
                        <li class="pprice">￥<?php echo $order['price']; ?></li>
                        <li class="pamount"><?php echo $order['quantity']; ?></li>
                        <li class="pseller"><?php echo $user['saler']; ?></li>
                        <li class="zprice">
                        	<p>￥<?php echo $order['origin']; ?></p>
                            <p><?php if($order['express'] == 'y'){?>（含运费<?php echo $order['express_price']; ?>）<?php } else { ?>免运费<?php }?></p>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="blank40"></div>
<?php if($diff_time > 0){?>
<script>
setTimeout('getorderdetaillasttime()',1000);
</script>
<?php }?>
<?php include template("footer");?>