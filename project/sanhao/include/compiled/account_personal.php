<?php include template("header");?>

<div class="blank108"></div>
<div class="blank60"></div>
<div class="w2 clearfix">
	<div class="sideArea fl">
        <ul class="sdsMenu">
            <li class="li1"><a href="/account/personal.php" class="cur">个人资料</a></li>
            <li class="li3"><a href="/account/place.php">收货地址</a></li>
            <li class="li2"><a href="/account/reset.php">更改密码</a></li>
            <li class="li5"><a href="/account/bindingsns.php">绑定SNS账号</a></li>
        </ul>
    </div>
    <div class="mainArea fr">
    	<div class="content">
        	<div class="mt clearfix"><strong>个人资料</strong></div>
            <div class="mc clearfix">
            	<div class="avatar">
                	<dl class="item">
                    	<dt>头像图片</dt>
                    	<dd id="scImg"><?php if($aField['headerurl'] == ''){?><img src="/static/images/i-pic.png" alt="" /><?php } else { ?><img src="<?php echo $aField['headerurl']; ?>" alt="" /><?php }?></dd>
                    </dl>
                    <dl class="item2">
                    	<dt><span></span><input type="file" id="imgFile" doing="0" name="filename" class="file" onchange="upfile();" /></dt>
                    	<?php if($aField['headerurl'] == ''){?><input type="hidden" name="artname" value="" id="artname" /><?php } else { ?><input type="hidden" name="artname" value="<?php echo $aField['headerurl']; ?>" id="artname" /><?php }?>
                        <dd><em id="upload_pic_error"></em></dd>
                        <input type="hidden" id="personal_head_error">
                    </dl>
                    <dl class="item3">
                    	<dt>建议:</dt>
                        <dd>使用不小于100*100像素的图片，</dd>
						<dd>支持jpg、jpeg、png格式。</dd>
                        <dd>不超过5M。</dd>
                    </dl>
                </div>
                <div class="account">
                	<dl class="item">
                    	<dt>账号 :</dt>
                        <dd><?php if($aField['type'] == 1){?><?php echo $aField['email']; ?><?php } else { ?><?php echo $aField['mobile']; ?><?php }?></dd>
                    </dl>
                </div>
                <div class="material">
                	<dl class="item">
                        <dt>昵称</dt>
                        <dd><?php if($aField['nickname'] == ''){?><input type="text" name="nickname" id="personal_nickname" class="text" /><?php } else { ?><input type="text" name="nickname" id="personal_nickname" value="<?php echo $aField['nickname']; ?>" class="text" /><?php }?><label for="personal_nickname">请输入您的昵称</label><em id="personal_nickname_check"></em></dd>
                        <input type="hidden" id="nickname_hidden" value="<?php echo $aField['nickname']; ?>">
                        <input type="hidden" id="personal_nickname_error">
                    </dl>
                    <?php if($aField['type'] == 2){?>
                    <dl class="item">
                        <dt>邮箱</dt>
                        <dd><?php if($aField['email'] == ''){?><input type="text" name="email" id="personal_email" class="text" /><?php } else { ?><input type="text" name="email" id="personal_email" value="<?php echo $aField['email']; ?>" class="text" /><?php }?><label for="personal_email">请输入您常用的电子邮箱</label><em id="personal_email_check"></em></dd>
                        <input type="hidden" id="personal_email_error">
                    </dl>
                    <?php }?>
                    <?php if($aField['type'] == 1){?>
                    <dl class="item">
                        <dt>手机号</dt>
                        <dd><?php if($aField['mobile'] == ''){?><input type="text" name="mobile" id="personal_mobile" class="text" /><?php } else { ?><input type="text" name="mobile" id="personal_mobile" value="<?php echo $aField['mobile']; ?>" class="text" /><?php }?><label for="personal_mobile">请输入您常用的手机号码</label><em id="personal_mobile_check"></em></dd>
                        <input type="hidden" id="personal_mobile_error">
                    </dl>
                    <?php }?>
                    <dl class="item">
                        <dt>QQ号</dt>
                        <dd><?php if($aField['qq'] == ''){?><input type="text" name="qq" id="personal_qq" class="text" /><?php } else { ?><input type="text" name="qq" id="personal_qq" value="<?php echo $aField['qq']; ?>" class="text" /><?php }?><label for="personal_qq">请输入您常用的qq号码</label><em id="personal_qq_check"></em></dd>
                        <input type="hidden" id="personal_qq_error">
                        <span>记得对您输入的QQ号进行<a href="http://wp.qq.com/consult.html" target="_blank">授权</a>喔！确保买家随时可以联系您！！！</span>
                    </dl>
                    <dl class="item">
                        <dt>个人网站</dt>
                        <dd><?php if($aField['website'] == ''){?><input type="text" name="website" id="personal_website" class="text" /><?php } else { ?><input type="text" name="website" id="personal_website" value="<?php echo $aField['website']; ?>" class="text" /><?php }?><label for="personal_website">请输入您的个人网站</label><em id="personal_website_check"></em></dd>
                        <input type="hidden" id="personal_website_error">
                    </dl>
                    <dl class="item profile">
                        <dt>个人简介</dt>
                        <dd><?php if($aField['website'] == ''){?><textarea id="personal_description" maxlength="140"></textarea><?php } else { ?><textarea id="personal_description" maxlength="140"><?php echo $aField['description']; ?></textarea><?php }?><em class="prompt"></em></dd>
                    </dl>
                </div>
            </div>
            <div class="mb">
            	<div class="renew clearfix">
            		<input type="hidden" id="uid" value="<?php echo $login_user_id; ?>">
            		<input type="hidden" id="user_type" value="<?php echo $aField['type']; ?>">
                	<input type="button" id="personal_submit" class="btn" /><div id="submit_action"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="blank40"></div>

<?php include template("footer");?>