<?php include template("header");?>

<div class="blank108"></div>
<div class="blank60"></div>
<div class="w2 clearfix">
	<div class="sideArea fl">
        <ul class="sdsMenu">
            <li class="li1"><a href="/account/personal.php">个人资料</a></li>
            <li class="li3"><a href="/account/place.php">收货地址</a></li>
            <li class="li2"><a href="/account/reset.php">更改密码</a></li>
            <li class="li5"><a href="/account/bindingsns.php" class="cur">绑定SNS账号</a></li>
        </ul>
    </div>
    <div class="mainArea fr">
    	<div class="content">
        	<div class="mt clearfix"><strong>绑定SNS网站账号</strong></div>
            <div class="mc clearfix">
            	<div class="bangdingsns">
                	<div class="forediv1">与其他SNS网站账号绑定，可将商品动态同步发送至该网站</div>
                    <?php if($userweibo['sns_id'] == ''){?>
                    <div class="forediv3"><span><img src="/static/images/weibangdingweibo.png" alt="" /></span><a href="/account/login.php?action=sinalogin"><img src="/static/images/bangding.png" alt="" /></a></div>
                    <?php } else { ?>
                    <div class="forediv3"><span><img src="/static/images/yibangdingweibo.png" alt="" /></span><a href="javascript:void(0);" id="unbindweibo"><img src="/static/images/jiebang.png" alt="" /></a></div>
                    <?php }?>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="blank40"></div>

<?php include template("footer");?>