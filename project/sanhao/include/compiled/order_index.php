<?php include template("header");?>

<div class="blank108"></div>
<div class="blank60"></div>
<div class="w2 clearfix">
	<div class="ordersDetail clearfix">
    	<div class="ordersTop"><strong>订单</strong></div>
        <div class="myTrading">
        	<div class="omt1"><a href="/order/index.php" class="cur">我出售的</a><a href="/order/index.php?action=buy" class="">我购买的</a></div>
            <div class="omt2">
                <ul>
                    <li class="pname">商品</li>
                    <li class="pprice">单价</li>
                    <li class="pamount">数量</li>
                    <li class="pseller">买家</li>
                    <li class="zprice">金额</li>
                    <li class="pstatus">订单状态</li>
                    <li class="poperat">操作</li>
                </ul>
            </div>
            <div class="omc">
            <?php if(is_array($aorderlist)){foreach($aorderlist AS $index=>$one) { ?>
                <ul>
                    <li class="pname">
                        <p><a href="/account/productdetail.php?id=<?php echo $one['pid']; ?>"><img src="/<?php echo $one['image']; ?>" alt="<?php echo $one['productname']; ?>" /></a><a href="/account/productdetail.php?id=<?php echo $one['pid']; ?>"><?php echo $one['productname']; ?></a></p>
                    </li>
                    <li class="pprice">￥<?php echo $one['price']; ?></li>
                    <li class="pamount"><?php echo $one['quantity']; ?></li>
                    <li class="pseller"><?php echo $one['buyer']; ?></li>
                    <li class="zprice">
                    <!-- { if $one['paytype'] == 'jxk' } -->
                        <p>￥<?php echo $one['tatolmoney']; ?></p>
                        <p><?php if($one['express'] == 'y'){?>（含运费<?php echo $one['express_price']; ?>,手续费￥<?php echo $one['charge']; ?>）<?php } else { ?>免运费,手续费￥<?php echo $one['charge']; ?><?php }?></p>
                    <!-- { else } -->
                    	<p>￥<?php echo $one['tatolmoney']; ?></p>
                        <p><?php if($one['express'] == 'y'){?>（含运费<?php echo $one['express_price']; ?>）<?php } else { ?>免运费<?php }?></p>
                    <!-- { /if } -->
                    </li>
                    <li class="pstatus">
                    	<p><?php if($one['state'] == 'pay'){?>已付款<?php } else { ?>交易成功<?php }?></p>
                        <p><a href="/order/sellerdetail.php?id=<?php echo $one['id']; ?>">订单详情</a></p>
                    </li>
                    <li class="poperat"><?php if($one['state'] == 'pay'){?><a href="/order/sellerdetail.php?id=<?php echo $one['id']; ?>"><img src="/static/images/btn13.png" /></a><?php }?></li>
                </ul>
            <?php }}?>    
            </div>
       <?php if($count > 20){?>
        <?php echo $pagestring; ?>
        <?php }?>
            
        </div>
    </div>
</div>
<div class="blank40"></div>

<?php include template("footer");?>