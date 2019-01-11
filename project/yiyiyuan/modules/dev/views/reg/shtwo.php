<div class="selfmess">
    <!--div class="endmess">
            <div class="endmessimg"><img src="images/timez2.png"></div>
            <p class="reds">个人信息<br/> 200/200 </p>
            <p class="img2 reds">公司信息<br/> 200/200 </p>
            <p class="img3">信用信息<br/> 200/200 </p>
    </div-->
    <div class="selftximg">
        <div class="dbk_inpL">
            <label>单位名称</label><input placeholder="请输入名称" value="<?php echo $users->company; ?>" type="text" name="company" id="reg_company">
        </div>
        <div class="dbk_inpL">
            <label>单位电话</label><input placeholder="区号＋前台座机号码" type="text" name='telephone' id='reg_telephone' value="<?php echo $users->telephone; ?>">
        </div>
        <div class="dbk_inpL">
            <label>单位地址</label><input placeholder="请输入地址"  type="text" name="address" id="reg_address" value="<?php echo $users->address; ?>">
        </div>
    </div>
    <p class="mb20"><input type="hidden" name="realname" id="reg_realname" maxlength="10" placeholder="姓名" value="<?php echo $users->realname; ?>" class="form-control"/></p>
    <p class="mb40"><input type="hidden" name="identity" id="reg_identity" maxlength="18" is_real='0' placeholder="身份证号" value="<?php echo $users->identity; ?>" class="form-control"/></p>
    <input type="hidden" id="from_url" value="<?php echo $from; ?>" />
    <input type="hidden" id="user_id" value="<?php echo $users->user_id; ?>" />
    <div class="button"> <button id="reg_shtwo_form">下一步</button></div>
    <?php if (empty($users->school_id)): ?>
        <p style="text-align: right; margin-right: 20px; margin-top: 5px;"><a href="<?php echo $redirurl; ?>">点击跳过</a></p>
    <?php endif; ?>
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