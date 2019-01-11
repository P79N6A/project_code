<div class="qzhgoodfri">
    <ul class="fCf">
        <li class="item-1"> <a href="#" class="actve"><i class="icon"></i> <em>1度好友</em></a> </li>
        <li class="item-2"> <a href="/new/friends/second"><i class="icon"></i> <em>2度好友</em></a> </li>
        <li class="item-3"> <a href="/new/friends/phonecontact"><i class="icon"></i> <em class="phonecolor">手机通讯录</em></a> </li>
    </ul>
</div>
<?php if (!empty($fuser)): ?>
    <div class="frere_content">
        <div class="frecot_left">
            <?php foreach ($fuser as $key => $val): ?>
                <dl>
                    <dt>
                    <?php if (!empty($val->openid) && !empty($val->userwx) && @fopen($val->userwx->head, 'r')): ?>
                        <img src="<?php echo $val->userwx->head; ?>">
                    <?php else: ?>
                        <?php echo mb_substr($val->realname, 0, 1,"UTF-8"); ?>
                    <?php endif; ?>
                    </dt>
                    <dd>
                        <p class="left_ddp"><span class="txt_name"><?php echo $val->realname; ?></span>
                            <span class="frered">
                                <?php if (!empty($val->company)): ?><i><?php echo mb_substr($val->company, 0, 14, 'UTF-8'); ?></i><?php else:?>&nbsp;<?php endif; ?>
                            </span>
                        </p>
                    </dd>
                </dl>
            <?php endforeach; ?>
        </div>
    </div> 
<?php else: ?>
    <div class="nonefriend">
        <img class="tuone" src="/images/nonefriend.png">
        <img class="tutwo" src="/images/nonefriend2.png" onclick="window.location = '/new/invitation/index'">
    </div>
<?php endif; ?>
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