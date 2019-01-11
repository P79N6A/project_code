<script type="text/javascript">
	$(function(){ $("label").inFieldLabels(); });
</script>
<div class="tLogin">
	<div class="">
        <div class="mt"></div>
        <div class="mc">
        	<h3 class="clearfix"><a href="javascript:void(0);" onclick="return X.boxClose();" class="close">X</a></h3>
            <div class="entry">
                <dl class="item">
                    <dt>账号</dt>
                    <dd><input type="text" name="username" id="dialog_username" class="text" /><label for="dialog_username">输入邮箱或手机号码</label></dd>
                </dl>
                <dl class="item">
                    <dt>密码</dt>
                    <dd><input type="password" name="password" id="dialog_password" class="text" /><label for="dialog_password">输入密码</label></dd>
                </dl>
                <div class="link"><a href="/account/repass.php">忘记密码？</a></div>
                <input type="hidden" name="product_id" id="product_id" value="<?php echo $id; ?>" />
                <input type="hidden" name="product_detail" id="product_detail" value="<?php echo $detail; ?>" />
                <div class="item"><input type="button" id="login_dialog_submit" class="btn" /></div>
                <div class="cont"><?php echo $msg; ?></div>
            </div>
            <div class="entry2">
				<dl class="extra">
                    <!--<dd><a href="#" class="qf">钱方登录</a></dd>-->
                    <dd><a href="/account/login.php?action=sinalogin" class="sina">微博登录</a></dd>
                    <!--<dd class="last"><a href="#" class="qq">QQ登录</a></dd>-->
                </dl>
                <dl class="guide">
                    <dt>还没有三好网账号？</dt>
                    <dd><a href="/account/signup.php">免费注册</a></dd>
                </dl>
            </div>
        </div>
        <div class="mb"></div>
    </div>
</div>