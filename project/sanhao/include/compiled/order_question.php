<?php include template("header");?>

<div class="blank108"></div>
<div class="blank60"></div>
<div class="w2 clearfix">
	<div class="ordersDetail clearfix">
    	  <div class="orderspro">
        	<a href="javascript:void(0);" class="close" id="order_question_close">关闭</a>
        	<h3><span>付款过程中发生意外，银行未成功扣款！</span></h3>
            <div class="kbdiv">
            	<h4>付款遇到问题了？先看看是不是以下的原因造成的。</h4>
                <ul>
                    <li>
                        <dl>
                            <dt>所需支付金额超过了银行支付限额。</dt>
                            <dd>建议您登录网上银行提高上限额度，或使用<a href="http://www.icardpay.com/infcenter/cardpay/" target="_blank">支付通刷卡支付</a>，还不知道什么是刷卡支付？<a href="http://www.icardpay.com/infcenter/cardpay/" target="_blank">点此了解刷卡支付</a></dd>
                        </dl>
                    </li>
                    <li>
                        <dl>
                            <dt>支付通或网银页面显示错误或者空白</dt>
                            <dd>部分网银对不同的浏览器的兼容性有限，导致无法正常支付，建议您使用IE浏览器进行支付操作。</dd>
                        </dl>
                    </li>
                    <li>
                        <dl>
                            <dt>网上银行已扣款，三好网订单仍显示"未付款"</dt>
                            <dd>可能由于银行的数据没有即时传输，请不要担心，稍后刷新页面查看。如较长时间仍显示未付款，可联系三好网客服（010-57793692）为您解决。</dd>
                        </dl>
                    </li>
                </ul>
            </div>
        </div>
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
            <!--<div class="pback"><a href="#">返回修改订单信息</a></div>-->
            <div class="pdefray">应付总额：<span>￥<?php echo $order['origin']; ?></span></div>
            <form action="/order/pay.php" method="post" sid="<?php echo $order['id']; ?>" id="order-pay-form">
            <div class="paybox">
            	<div class="payt">
                	<strong>支付方式</strong>
                </div>
                <div class="payc">
                    <div class="zhifutong">
                        <ul>
                        	<li><input type="radio" name="paytype" checked="checked" id="check_icardpay" value="icardpay" class="zffs" /><label for="zft"><img src="/static/images/zft.jpg" alt="" /></label><span>支持大部分网银支付，且无需开通网银即可在互联网上以银行卡刷卡方式进行支付，<br />更加安全、便捷！<a href="http://www.icardpay.com/infcenter/cardpay/" target="_blank">了解刷卡支付</a></span></li>
                        </ul>
                    </div>
                    <!--<div class="jiaotongyinhang">
                        <ul>
                        	<li><input type="radio" name="paytype" id="jtyh" class="zffs" value="BOCOM"/><label for="jtyh"><img src="/static/images/jtyh.png" alt="" /></label></li>
                        </ul>
                    </div>-->
                    <div class="jiaoxiangka">
                        <ul>
                        	<li><input type="radio" name="paytype" id="jxk" value='jxk' class="zffs" /><label for="jxk"><img src="/static/images/jxk.jpg" alt="" /></label><span>交享卡支付仅支持"掌芯宝手机刷卡器"支付。</span></li>
                        </ul>
                        <div class="jxkinner clearfix">
                        	<dl class="sjhm clearfix">
                                <dt>手机号码：</dt>
                                <dd><input type="text" class="text" name="jxk_pay_mobile" id="jxk_pay_mobile"  maxlength="11"/><label for="jxk_pay_mobile">请输入正确手机号</label><em id="jxk_pay_mobile_error"></em>
                                	<p>*手机号是用户登录掌芯宝APP客户端的账号</p>
                                </dd>
                            </dl>
                            <div class="zhushi">
                            	<p class="txt">注：什么是掌芯宝手机刷卡器？掌芯宝手机刷卡器是一款通过音频口插入智能手机连接，支持刷卡的智能手机外接设备，使用户可以安全、便捷的完成基于刷卡的各类支付交易服务。</p>
                                <p class="img"><img src="/static/images/zhu1.jpg" alt="" /><img src="/static/images/zhu2.jpg" alt="" /></p>
                            </div>
                            <div class="erweima">
                            	<p class="txt">请先下载：</p>
                                <p class="img"><img src="/static/images/erweima.jpg" alt="" /></p>
                                <p class="txt2">欢迎致电客服热线：<br /><strong>400-668-6689</strong></p>
                            </div>
                        </div>
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