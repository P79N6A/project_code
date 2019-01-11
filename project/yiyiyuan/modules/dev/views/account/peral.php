<?php
$url = '/dev/account/peral?user_id=' . $userinfo['user_id'];
?>
<div class="newzilaio">
    <div class="newmyzo1"><img src="/images/newmyzo1.png"></div>
    <div class="newself">
        <a href='<?php echo '/dev/reg/personals?url=' . $url . '&user_id=' . $userinfo['user_id']; ?>'>
            <div class="dbk_inpL">
                <label>实名认证</label>
                <p class="allws <?php echo $pinfo == '修改' ? 'yellow' : ''; ?>"><?php echo $pinfo; ?></p>
            </div>
        </a>
        <a href='/dev/reg/company?url=<?php echo $url; ?>&user_id=<?php echo $userinfo['user_id']; ?>'>
            <div class="dbk_inpL">
                <label>工作信息</label>
                <p class="allws <?php echo $cinfo == '修改' ? 'yellow' : ''; ?>"><?php echo $cinfo; ?></p>
            </div>
        </a>
        <a href='<?php echo ($userinfo->status == 1 || $userinfo->status == 4) ? '/dev/reg/pic?url=' . $url . '&user_id=' . $userinfo['user_id'] : ''; ?>'>
            <div class="dbk_inpL">
                <label>持证自拍照</label>
                <p class="allws <?php echo $userinfo->status == 3 || $userinfo->status == 2 ? 'blue' : ($userinfo->status == 4 ? 'reds' : ''); ?>"><?php echo $userinfo->status == 3 ? '已认证' : ($userinfo->status == 4 ? '未通过' : ($userinfo->status == 2 ? '审核中' : '未认证')); ?></p>
            </div>
        </a>
        <a href='/dev/reg/contacts?user_id=<?php echo $userinfo['user_id']; ?>'>
            <div class="dbk_inpL">
                <label>联系人信息</label>
                <p class="allws <?php echo $contacts == 1 ? 'yellow' : ''; ?>"><?php echo $contacts == 2 ? '未认证' : '修改'; ?></p>
            </div>
        </a>
        <?php if ($userinfo['status'] == 3): ?>
<!--            <a href="--><?php //echo $juli == 1 ? '/dev/account/juxinli?user_id=' . $userinfo['user_id'] . '&url=' . $url : '#'; ?><!--">-->
            <a href="<?php echo $juli == 1 ? '/new/mobileauth/phoneauth?from=2' : '#'; ?>">
            <?php else: ?>
                <a href="javascript:{$('.Hmask').show();$('.duihsucc2').show();}">
                <?php endif; ?>
                <div class="dbk_inpL">
                    <label>手机号认证</label>
                    <p class="allws <?php echo $juli == 1 ? '' : 'blue'; ?>"><?php echo $juli == 1 ? '未认证' : '已认证'; ?></p>
                </div>
            </a>
    </div>
    <div class="newmyzo1"><img src="/images/newmyzo2.png"></div>
    <div class="newself">
        <?php if ($userinfo['status'] == 3): ?>
            <a href="<?php echo $jing == 1 ? '/dev/account/jingdong?user_id=' . $userinfo['user_id'] : '#'; ?>">
            <?php else: ?>
                <a href="javascript:{$('.Hmask').show();$('.duihsucc2').show();}">
                <?php endif; ?>
                <div class="dbk_inpL">
                    <label>京东认证</label>
                    <p class="allws <?php echo $jing == 1 ? '' : 'blue'; ?>"><?php echo $jing == 1 ? '未认证' : '已认证'; ?></p>
                </div>
            </a>
    </div>
</div>
<div class="Hmask" style="display:none;"></div>
<div class="duihsucc2" style="display:none;">
    <p class="errore"><img src="/images/closed.png"></p>
    <p class="xuhua">暂时不能填写此信息</p>
    <button type="button" class="sureyemian">确定</button>
</div>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
    $('.errore').click(function () {
        $('.duihsucc2').hide();
        $('.Hmask').hide();
    });
    $('.sureyemian').click(function () {
        $('.duihsucc2').hide();
        $('.Hmask').hide();
    });
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