<?php include template("html_header");?>

<div class="header">
	<div class="w2 clearfix">
    	<h2 class="logo fl"><a href="/"><img src="/static/images/logo.png" alt="" /></a></h2>
        <div class="fr">
        	<?php if($login_user_id == '' || $login_user_id == 0){?>
        	<div class="nav"><?php if($nav == 'index'){?><a href="/" class="lk1 cur">首页</a><?php } else { ?><a href="/" class="lk1">首页</a><?php }?> <?php if($nav == 'flow'){?><a href="/help/flow.php" class="lk2 cur">玩转三好</a><?php } else { ?><a href="/help/flow.php" class="lk2">玩转三好</a><?php }?><?php if($nav == 'faqs'){?><a href="/help/faqs.php" class="lk3 cur">帮助</a><?php } else { ?><a href="/help/faqs.php" class="lk3">帮助</a><?php }?></div>
            <div class="regLogin"><a href="/account/login.php">登录</a><span>|</span><a href="/account/signup.php">注册</a></div>
            <?php } else { ?>
            <div class="nav"><?php if($nav == 'index'){?><a href="/" class="lk1 cur">首页</a><?php } else { ?><a href="/" class="lk1">首页</a><?php }?> <?php if($nav == 'list'){?><a href="/account/productlist.php" class="lk2 cur">商品列表</a><?php } else { ?><a href="/account/productlist.php" class="lk2">商品列表</a><?php }?><?php if($nav == 'order'){?><a href="/order/index.php" class="lk3 cur">订单</a><?php } else { ?><a href="/order/index.php" class="lk3">订单</a><?php }?></div>
            <div class="yesLogin">
				<div class="setup"><a href="javascript:void(0);">设置</a></div>
				<div class="uname">
					<span class="s1"><?php if($_SESSION['type'] == 1){?><?php echo mb_strimwidth($_SESSION['email'],0,12); ?><?php } else { ?><?php echo mb_strimwidth($_SESSION['mobile'],0,12); ?><?php }?></span><span class="s2"></span>
					<div class="u">
						<span class="s3"></span>
						<ul>
							<li><a href="/account/personal.php">个人资料</a></li>
							<li><a href="/account/place.php">收货地址</a></li>
							<li><a href="/account/reset.php">更改密码</a></li>
							<li><a href="/account/bindingsns.php">绑定SNS账号</a></li>
							<li><a href="/account/logout.php">退出</a></li>
						</ul>
					</div>
				</div>
			</div>
            <?php }?>
            
        </div>
    </div>
</div>
