<?php include template("header");?>

<div class="blank108"></div>
<div class="blank60"></div>
<div class="w2 clearfix">
	<div class="ordersDetail clearfix">
    	<div class="ordersTop"><strong>购买仅需3步</strong></div>
    	<div class="orderslMain">
        	<div class="step ss1"></div>
            <div class="plist">
            	<div class="pmt clearfix">
                	<ul>
                    	<li class="pname">商品名称</li>
                        <li class="pamount">数量</li>
                        <li class="pprice">价格</li>
                        <li class="pattribute">属性</li>
                    </ul>
                </div>
                <div class="pmc clearfix">
                	<ul>
                    	<li class="pname">
                        	<a href="/account/productdetail.php?id=<?php echo $product['id']; ?>"><?php echo $product['pname']; ?></a>
                        </li>
                        <li class="pamount">
                        	<div class="wrap-input">
                                <a href="javascript:void(0);" id="leftLessen">-</a>
                                <input type="text" name="buynum" id="buy_product_num" class="text" />
                                <a href="javascript:void(0);" id="rightAdd">+</a>
                            </div>
                            <div class="store-prompt" id="product_prompt"></div>
                        </li>
                        <li class="pprice" id="product_price"><?php echo $product['price']; ?></li>
                        <li class="pattribute" id="product_property"></li>
                    </ul>
                </div>
            </div>
            <div class="consignee">
                <div class="entry clearfix">
                    <dl class="item">
                        <dt>收货人：</dt>
                        <dd><input type="text" id="address_name" value="<?php echo $address['name']; ?>" class="text" /><label for="address_name">收货人姓名</label><em id="address_name_check"></em></dd>
                    </dl>
                    <dl class="item">
                        <dt>手机号码：</dt>
                        <dd><input type="text" id="address_mobile" value="<?php echo $address['mobile']; ?>" class="text" /><label for="address_mobile">手机号码</label><em id="address_mobile_check"></em></dd>
                    </dl>
                    <dl class="item">
                        <dt>固定电话：</dt>
                        <dd><input type="text" id="address_phone" value="<?php echo $address['phone']; ?>" class="text" /><label for="address_phone">固定电话</label><em id="address_phone_check"></em></dd>
                    </dl>
                    <dl class="item">
                        <dt>省市区：</dt>
                        <dd>
                        	<div class="fl">
                                <select id="showprovince">
                                    <option value="0">请选择省份</option>
                                    <?php if(is_array($province)){foreach($province AS $index=>$one) { ?>
	                            	<option <?php if($address['province_id'] == $one['id']){?>selected<?php }?> value="<?php echo $one['id']; ?>"><?php echo $one['name']; ?></option>
	                            	<?php }}?>
                                </select>
                                <select id="showcity">
                                    <option value="0">请选择城市</option>
                                    <?php if(is_array($city)){foreach($city AS $index=>$one) { ?>
	                            	<option <?php if($address['city_id'] == $one['id']){?>selected<?php }?> value="<?php echo $one['id']; ?>"><?php echo $one['name']; ?></option>
	                            	<?php }}?>
                                </select>
                                <select id="showarea">
                                   <option value="0">请选择地区</option>
                                   <?php if(is_array($area)){foreach($area AS $index=>$one) { ?>
	                            	<option <?php if($address['area_id'] == $one['id']){?>selected<?php }?> value="<?php echo $one['id']; ?>"><?php echo $one['name']; ?></option>
	                            	<?php }}?>
                                </select>
                            </div>
                            <em id="checkprovince"></em>
                        </dd>
                    </dl>
                    <dl class="item">
                        <dt>街道号：</dt>
                        <dd style="height:65px;">
                        	<textarea id="address_street"><?php echo $address['street']; ?></textarea><label for="address_street">街道号</label>
                        	<div class="ts" id="address_street_check"></div>
                        </dd>
                    </dl>
                    <dl class="item">
                        <dt>邮政编码：</dt>
                        <dd><input type="text" id="address_zipcode" value="<?php echo $address['postcode']; ?>" class="text" /><label for="address_zipcode">邮政编码</label><em id="address_zipcode_check"></em></dd>
                    </dl>
                </div>
            </div>
            <div class="message">
            	<dl class="item">
                    <dt>买家留言：</dt>
                    <dd style="height:65px;">
                        <textarea id="address_buyer" maxlength="200"></textarea><label for="address_buyer">选填，少于200字</label>
                        <div class="ts" id="buyer_prompt"></div>
                    </dd>
                </dl>
            </div>
            <div class="confirm">
            	<?php if($product['express_price'] != ''){?>
            	<div class="fore1">运费：<?php echo $product['express_price']; ?></div>
            	<?php } else { ?>
            	<div class="fore1">运费：0:00</div>
            	<?php }?> 
                <div class="fore2">应付总额：<span id="total_price"></span></div>
                <input type="hidden" id="product_id" value="<?php echo $product['id']; ?>">
                 <input type="hidden" id="address_id" value="<?php echo $address['id']; ?>">
                 <input type="hidden" id="saler_id" value="<?php echo $product['uid']; ?>">
                 <input type="hidden" id="order_id" value="">
                <div class="fore3"><input type="button" id="product_buy_submit" class="buybtn" /></div>
            </div>
        </div>
        <div class="ordersSide">
       		<div class="pbuy">
        		<div class="img"><a href="/account/productdetail.php?id=<?php echo $product['id']; ?>"><img src="/<?php echo $productimage['image']; ?>" alt="" /></a></div>
            	<div class="name"><a href="/account/productdetail.php?id=<?php echo $product['id']; ?>"><?php echo $product['pname']; ?></a></div>
                <div class="pselect">
                <?php if(count($property) == 2){?>
                    <dl class="item">
                        <dt><?php echo $property[0]['name']; ?></dt>
                        <dd><select class="select" id="product_property_first">
                        <?php if(is_array($property[0]['size'])){foreach($property[0]['size'] AS $key=>$value) { ?>
                        <?php if($value != ''){?>
	                    <option <?php if($first_property == $value){?>selected<?php }?> value="<?php echo $value; ?>"><?php echo $value; ?></option>
	                    <?php }?>
                    	<?php }}?>
                        </select></dd>
                    </dl>
                    <dl class="item">
                        <dt><?php echo $property[1]['name']; ?></dt>
                        <dd><select class="select" id="product_property_second">
                        <?php if(is_array($property[1]['size'])){foreach($property[1]['size'] AS $key=>$value) { ?>
                        <?php if($value != ''){?>
	                    <option <?php if($second_property == $value){?>selected<?php }?> value="<?php echo $value; ?>"><?php echo $value; ?></option>
	                    <?php }?>
                    	<?php }}?>
                        </select></dd>
                    </dl>
                <?php }?>
                <?php if((count($property) == 1)){?>
                      <dl class="item">
                        <dt><?php echo $property[0]['name']; ?></dt>
                        <dd><select class="select" id="product_property_first">
                        <?php if(is_array($property[0]['size'])){foreach($property[0]['size'] AS $key=>$value) { ?>
                        <?php if($value != ''){?>
	                    <option <?php if($first_property == $value){?>selected<?php }?> value="<?php echo $value; ?>"><?php echo $value; ?></option>
	                    <?php }?>
                    	<?php }}?>
                        </select></dd>
                    </dl> 
                <?php }?>
                </div>
                <div class="ppri">￥<?php echo $product['price']; ?></div>
            </div>
            <div class="pbuyt">
                <dl class="item">
                    <dt>运费</dt>
                    <?php if($product['express_price'] != ''){?>
                    <dd id="product_express">￥<?php echo $product['express_price']; ?></dd>
                    <?php } else { ?>
                    <dd id="product_express">￥0:00</dd>
                 	<?php }?>  
                </dl>
                <dl class="item">
                    <dt>库存</dt>
                    <?php if($product['max_number'] != ''){?>
                    <dd id="product_max_num" num="<?php echo $product['max_number']-$product['sale_number']; ?>"><?php echo $product['max_number']-$product['sale_number']; ?></dd>
                    <?php } else { ?>
                    <dd id="product_max_num" num="不限">不限</dd>
                    <?php }?>
                </dl>
                <dl class="item">
                    <?php if($product['end_time'] != ''){?>
                	<?php if($product['end_time'] > $now){?>
                    <dt id="lasttime" curtime="<?php echo $now; ?>000" diff="<?php echo $diff_time; ?>000">剩余时间</dt>
                    <?php if($left_day>0){?>
                    <dd id="difftime"><?php echo $left_day; ?>天<?php echo $left_hour; ?>时<?php echo $left_minute; ?>分<?php echo $left_time; ?>秒</dd>
                    <?php } else { ?>
                    <dd id="difftime"><?php echo $left_hour; ?>时<?php echo $left_minute; ?>分<?php echo $left_time; ?>秒</dd>
                    <?php }?>
                    <?php } else { ?>
                    <dt>剩余时间</dt>
                    <dd>已结束</dd>
                    <?php }?>
                    <?php }?>
                </dl>
            </div>
        </div>
    </div>
</div>
<div class="blank40"></div>
<script>
setTimeout('getproductlasttime()',1000);
</script>
<script>
showbuyproductinfo();
</script>
<?php include template("footer");?>