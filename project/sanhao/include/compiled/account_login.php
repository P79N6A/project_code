<?php include template("header");?>

<div class="blank108"></div>
<div class="blank60"></div>
<div class="cRegLogin">
    <div class="mt"></div>
    <div class="mc">
        <h3><strong>登录</strong></h3>
        <div class="entry clearfix">
        <form action="" method="POST" id="registerForm">
            <div class="fl">
                <dl class="item">
                    <dt>账号</dt>
                    <dd><input type="text" name="username" id="loginusername" class="text" /><label for="loginusername">输入手机号码</label></dd>
                </dl>
                <dl class="item">
                    <dt>密码</dt>
                    <dd><input type="password" name="password" id="loginpassword" class="text" /><label for="loginpassword">输入密码</label></dd>
                </dl>
                <div class="link"><!--<a href="/account/repass.php">忘记密码？</a>--></div>

                <div class="item"><input type="submit" id="login_submit" value="" class="btn" /></div>
                <div class="cont"><?php echo $msg; ?></div>
                <div style="padding-left: 100px">会员充值</div>
                <div>*注册会员每月5元，充值会员即可享受最新资讯信息</div>
                <div style="padding-left: 50px">如：苹果冰点换屏价198元起</div>
                <!--<div class="item zft"><a href="#" class="zftlg"><img src="/static/images/zft.jpg" alt="" /></a><a href="#" class="zftdl">使用支付通账户登录</a></div>-->
            </div>
         </form>
            <div class="fr">
                <dl class="guide">
                    <dt>还没有三好网账号？</dt>
                    <dd><a href="/account/signup.php">免费注册</a></dd>
                </dl>
                <dl class="extra">
                    <dt>使用合作网站登录</dt>
                    <!--<dd><a href="#" class="zft">支付通登录</a></dd>-->
                    <dd><a href="/account/login.php?action=sinalogin" class="sina">微博登录</a></dd>
                </dl>
            </div>
        </div>
    </div>
    <div class="mb"></div>
</div>
<div class="blank120"></div>

<?php include template("footer");?>
