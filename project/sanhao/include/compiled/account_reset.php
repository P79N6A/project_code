<?php include template("header");?>

<div class="blank108"></div>
<div class="blank60"></div>
<div class="w2 clearfix">
	<div class="sideArea fl">
        <ul class="sdsMenu">
            <li class="li1"><a href="/account/personal.php">个人资料</a></li>
            <li class="li3"><a href="/account/place.php">收货地址</a></li>
            <li class="li2"><a href="/account/reset.php" class="cur">更改密码</a></li>
            <li class="li5"><a href="/account/bindingsns.php">绑定SNS账号</a></li>
        </ul>
    </div>
    <div class="mainArea fr">
    	<div class="content">
        	<div class="mt clearfix"><strong>更改密码</strong></div>
            <div class="mc clearfix">
            	<div class="changepsw">
                	<dl class="item">
                        <dt>新密码</dt>
                        <dd><input type="password" id="ret_password" class="text" /><em id="reset_password_check"></em></dd>
                    </dl>
                    <dl class="item">
                        <dt>确认新密码</dt>
                        <dd><input type="password" id="ret_repassword" class="text" /><em id="reset_repassword_check"></em></dd>
                    </dl>
                </div>
            </div>
            <div class="mb">
            	<div class="save clearfix">
            		<input type="hidden" id="uid" value="<?php echo $login_user_id; ?>">
                	<input type="button" id="retpassword_submit" class="btn" /><div id="retpassword_action"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="blank40"></div>

<?php include template("footer");?> 
