<div class="qzhgoodfri">
    <ul class="fCf">
        <li class="item-1"> <a href="/new/friends/first" ><i class="icon"></i> <em>1度好友</em></a> </li>
        <li class="item-2"> <a href="/new/friends/second" ><i class="icon"></i> <em>2度好友</em></a> </li>
        <li class="item-3"> <a href="#" class="actvess"><i class="icon"></i> <em class="phonecolor">手机通讯录</em></a> </li>
    </ul>
</div>
<?php if (!empty($friend)): ?>
    <div class="frere_content">
        <div class="frecot_left">
            <?php foreach ($friend as $key => $val): ?>
                <dl>
                    <dt>
                    <?php if (!empty($val->head)): ?>
                        <img src="<?php echo!empty($val->head) ? $val->head : '/images/txing1.png'; ?>">
                    <?php else: ?>
                        <?php echo mb_substr($val->name, 0, 1, "UTF-8"); ?>
                    <?php endif; ?>
                    </dt>
                    <dd>
                        <p class="mayname"><?php echo $val->name; ?></p>
                        <p class="mayphone"><?php echo str_replace('-', '', $val->phone); ?></p>
                    </dd>
                </dl>
            <?php endforeach; ?>
        </div>
    </div> 
<?php else: ?>    
    <div class="nonefriend">
        <img class="tuone" src="/images/nonefriend.png">
        <p>快去下载<em>先花一亿元</em>APP同步通讯录好友吧！ </p>
        <p>和好友一起玩转一亿元！</p>
        <button type="button" onclick="javascript:window.location = '/dev/ds/down'">立即下载</button>
    </div>
<?php endif; ?>
<div style="position: fixed;bottom: 55px;right: 5%;">
    <a href="/new/invitation/index">
        <img src="/images/txunlu.png" style="width:55px; height:50px; ">
    </a>
</div>
<?= $this->render('/layouts/_page', ['page' => 'friends']) ?>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
            wx.config({
                debug: false,
                appId: '<?php echo $jsinfo['appid']; ?>',
                timestamp: <?php echo $jsinfo['timestamp']; ?>,
                nonceStr: '<?php echo $jsinfo['nonceStr']; ?>',
                signature: '<?php echo $jsinfo['signature']; ?>',
                jsApiList: [
                    'hideOptionMenu'
                ]
            });

            wx.ready(function () {
                wx.hideOptionMenu();
            });
</script>