<?php include template("header");?>

<div class="blank108"></div>
<div class="blank60"></div>
<div class="w2 clearfix">
	<div class="sideArea fl">
        <ul class="sdsMenu">
            <li class="li1"><a href="/account/personal.php">个人资料</a></li>
            <li class="li3"><a href="/account/place.php" class="cur">收货地址</a></li>
            <li class="li2"><a href="/account/reset.php">更改密码</a></li>
            <li class="li5"><a href="/account/bindingsns.php">绑定SNS账号</a></li>
        </ul>
    </div>
    <div class="mainArea fr">
    	<div class="content2">
        	<div class="mt clearfix"><strong>收货地址</strong></div>
            <div class="mc clearfix">
                <div class="material">
                	<dl class="item">
                        <dt>收货人姓名</dt>
                        <dd><?php if(!empty($aAddress['name'])){?><input type="text" id="address_name" value="<?php echo $aAddress['name']; ?>" class="text" /><?php } else { ?><input type="text" id="address_name" value="" class="text" /><?php }?><label for="address_name">请填写您的姓名</label><em id="address_name_check"></em></dd>
                    </dl>
                    <dl class="item">
                        <dt>手机号码</dt>
                        <dd><?php if(!empty($aAddress['mobile'])){?><input type="text" id="address_mobile" value="<?php echo $aAddress['mobile']; ?>" class="text" /><?php } else { ?><input type="text" id="address_mobile" value="" class="text" /><?php }?><label for="address_mobile">请填写您的手机号码</label><em id="address_mobile_check"></em></dd>
                    </dl>
                    <dl class="item">
                        <dt>固定电话</dt>
                        <dd><?php if(!empty($aAddress['phone'])){?><input type="text" id="address_phone" value="<?php echo $aAddress['phone']; ?>" class="text" /><?php } else { ?><input type="text" id="address_phone" value="" class="text" /><?php }?><label for="address_phone">请填写您的固定电话</label><em id="address_phone_check"></em></dd>
                    </dl>
                    <dl class="item">
                        <dt>省市区：</dt>
                        <dd>
                        	<div class="fl">
                                <select id="showprovince">
                                    <option value="0">请选择省份</option>
                                    <?php if(is_array($province)){foreach($province AS $index=>$one) { ?>
	                            	<option <?php if($aAddress['province_id'] == $one['id']){?>selected<?php }?> value="<?php echo $one['id']; ?>"><?php echo $one['name']; ?></option>
	                            	<?php }}?>
                                </select>
                                <select id="showcity">
                                    <option value="0">请选择城市</option>
                                    <?php if(is_array($city)){foreach($city AS $index=>$one) { ?>
	                            	<option <?php if($aAddress['city_id'] == $one['id']){?>selected<?php }?> value="<?php echo $one['id']; ?>"><?php echo $one['name']; ?></option>
	                            	<?php }}?>
                                </select>
                                <select id="showarea">
                                    <option value="0">请选择地区</option>
                                   <?php if(is_array($area)){foreach($area AS $index=>$one) { ?>
	                            	<option <?php if($aAddress['area_id'] == $one['id']){?>selected<?php }?> value="<?php echo $one['id']; ?>"><?php echo $one['name']; ?></option>
	                            	<?php }}?>
                                </select>
                            </div>
                            <em id="checkprovince"></em>
                        </dd>
                    </dl>
                    <dl class="item">
                        <dt>街道号：</dt>
                        <dd style="height:65px;">
                        	<?php if(!empty($aAddress['street'])){?><textarea id="address_street"><?php echo $aAddress['street']; ?></textarea><?php } else { ?><textarea id="address_street"></textarea><?php }?><label for="address_street">请填写街道号</label>
                        	<div class="ts" id="address_street_check"></div>
                        </dd>
                    </dl>
                    <dl class="item">
                        <dt>邮政编码：</dt>
                        <dd><?php if(!empty($aAddress['postcode'])){?><input type="text" id="address_zipcode" value="<?php echo $aAddress['postcode']; ?>" class="text" /><?php } else { ?><input type="text" id="address_zipcode" value="" class="text" /><?php }?><label for="address_zipcode">请填写邮政编码</label><em id="address_zipcode_check"></em></dd>
                    </dl>
                </div>
            </div>
            <div class="mb">
            	<input type="hidden" id="address_id" value="<?php echo $aAddress['id']; ?>" />
            	<input type="button" id="address_submit" class="btn" /><div id="address_action"></div>
            </div>
        </div>
    </div>
</div>
<div class="blank40"></div>

<?php include template("footer");?> 