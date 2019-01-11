<div class="selfmess">
    <div class="selftximg">
        <div class="dbk_inpL">
            <label>单位名称</label><input placeholder="请输入名称" type="text" value="<?php echo $userinfo->company; ?>" readonly="readonly" name="company" id="reg_company">
        </div>
        <div class="dbk_inpL">
            <label>单位电话</label><input placeholder="区号＋前台座机号码" type="text" name='mobile' readonly="readonly" value="<?php echo $userinfo->telephone; ?>" id='reg_mobile'>
        </div>
        <div class="dbk_inpL">
            <label>公司地址</label>
            <input type="text" name='mobile' readonly="readonly" value="<?php echo htmlspecialchars($userinfo->address); ?>" id='reg_position'>
        </div>
    </div>
</div>
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