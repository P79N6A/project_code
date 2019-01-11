<?php include template("header");?>



<div class="blank108"></div>

<div class="blank60"></div>

<div class="w2 clearfix">

	<div class="ordersDetail clearfix">

    	<div class="ordersTop"><strong>购买仅需3步</strong></div>

    	<div class="orderslMain">

        	<div class="step ss2"></div>

            <div class="pinfo">

                <div class="zfmt">

                	<ul class="clearfix">

                        <li class="pname">商品名称</li>

                        <li class="pprice">单价</li>

                        <li class="pamount">数量</li>

                        <li class="ptotal">总额</li>

                    </ul>

                </div>

                <div class="zfmc">

                	<ul class="clearfix">

                        <li class="pname"><?php echo $product['pname']; ?></li>

                        <li class="pprice"><?php echo $order['price']; ?></li>

                        <li class="pamount"><?php echo $order['quantity']; ?></li>

						<li class="ptotal"><?php echo $order['origin']; ?><br /><?php if($order['express_price'] != ''){?>(含运费<?php echo $order['express_price']; ?>)<?php }?></li>

                    </ul>

                </div>   

            </div>

            <div class="pinfo">

            	<dl class="item">

                	<dt>配送信息：</dt>

                    <dd>

                    	<p><?php echo $order['realname']; ?>，<?php if($order['mobile'] != ''){?><?php echo $order['mobile']; ?><?php } else { ?><?php echo $order['phone']; ?><?php }?></p>

                        <p><?php echo $useraddress; ?>，<?php echo $order['postcode']; ?></p>

                    </dd>

                </dl> 

                <div class="line"></div>

                <dl class="item">

                	<dt>买家留言：</dt>

                    <dd>

                    	<p><?php echo $order['remark']; ?></p>

                    </dd>

                </dl>

            </div>

            <!--<div class="pback"><a href="/order/modify.php?id=<?php echo $order['id']; ?>">返回修改订单信息</a></div>-->

            <div class="pdefray">需支付：<span>￥<?php echo $order['origin']; ?></span></div>

            <form action="/order/pay.php" method="post" sid="<?php echo $order['id']; ?>" id="order-pay-form">

            <div class="paybox">

            	<div class="payt">

                	<strong>支付方式</strong>

                </div>

                <div class="payc">

					<div class="jiaotongyinhang">

                        <ul>

                        	<li><input type="radio" name="paytype" checked="checked" id="check_yeepay" class="zffs" value="yeepay"/><label for="check_yeepay"><img src="/static/images/yeepaylogo.jpg" alt="" /></label></li>
							<li><input type="radio" name="paytype" id="check_alipay" class="zffs" value="alipay"/><label for="check_alipay"><img src="/static/images/alipay.gif" alt="" /></label></li>
							<li><input type="radio" name="paytype" id="check-BOCOM" class="zffs" value="BOCOM"/><label for="check-BOCOM"><img src="/static/images/bk1.jpg" alt="" /></label></li>
                        </ul>

                    </div>



                </div>

            </div>

            <div class="correct">

            	<div class="fore2">应付总额：<span>￥<?php echo $order['origin']; ?></span></div>

            	<input type="hidden" name="order_id" value="<?php echo $order['id']; ?>" />

				<input type="hidden" name="product_id" value="<?php echo $order['pid']; ?>" />

				<input type="hidden" name="quantity" value="<?php echo $order['quantity']; ?>" />

				<input type="hidden" name="address" value="<?php echo $useraddress; ?>" />

				<input type="hidden" name="express" value="<?php echo $order['express_price']; ?>" />

				<input type="hidden" name="remark" value="<?php echo $order['remark']; ?>" />

                <div class="fore3"><input type="submit" value="" id="order-pay-button" class="buybtn" /></div>

            </div>

            </form>

        </div>

        <div class="ordersSide">

       		<div class="rest">

            	<div class="rmt"><strong>请放心购买</strong></div>

            	<dl class="item">

                	<dt><img src="/static/images/pic10.png" alt="" /></dt>

                    <dd>采用网银和第三方平台支付，最大限度保证您支付安全。</dd>

                </dl>

                <dl class="item">

                	<dt><img src="/static/images/pic11.png" alt="" /></dt>

                    <dd>为了支付便捷，建议Windows用户使用IE浏览器支付。</dd>

                </dl>

                <div class="rbox">

                	<h3>需要帮助?</h3>

                    <p>没有网银如何购买？</p>

                    <p>您也可以让朋友代您购买，也 可使用支付通刷卡器进行购买，无需开通网上银行。 了解支付通刷卡器</p>

                    <p>购买支付通刷卡器，请点击<a href="http://user.icardpay.com/user/html/registe/zhifutongzhongduan.jsp">了解刷卡支付</a></p><br />

                    <p>网上银行扣款后，三好网订单仍显示"未付款"怎么办？</p><br />

                    <p>可能是由于银行的数据没有即时传输，请您不要担心，稍后刷新页面查看。如较长时间仍显示未付款，可联系三好网客服</p>

                    <p>(010-57793692)为您解决。</p>

                    <h3>网上银行支付失败怎么办？</h3>

                    <p>如有由于网络中断，或页面过期、超时、错误等问题导致支付失败，请先确认是否已经扣款，如未扣款可尝试再支付一次。或者，您可以联系您的银行或支付平台获得帮助。</p>

                </div>

            </div>

        </div>

    </div>

</div>

<div class="blank40"></div>

<script type="text/javascript">

$(function(){

	$(".zffs:radio").change(function(){

		if($("#jxk").attr("checked")){

			$(".jxkinner").show();

		}else{

			$(".jxkinner").hide();

		}

	});

});

</script>

<?php include template("footer");?>

