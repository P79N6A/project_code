<div class="Hcontainer nP">
    <div class="main">
        <div class="border1 jcbd">
            <ul>
                <li>
                    <div class="col-xs-3 text-right n26 grey2">姓名</div>
                    <div class="col-xs-8 n26 grey4"><?php echo $userbank->user->realname; ?></div>
                </li>
                <li>
                    <div class="col-xs-3 text-right n26 grey2">银行卡号</div>
                    <div class="col-xs-8 n26 grey4"><?php echo substr($userbank->card, 0, 4) . '*******' . substr($userbank->card, strlen($userbank->card) - 4, 4); ?></div>
                </li>
                <li>
                    <div class="col-xs-3 text-right n26 grey2">身份证号</div>
                    <div class="col-xs-8 n26 grey4"><?php echo substr($userbank->user->identity, 0, 4) . '**********' . substr($userbank->user->identity, strlen($userbank->user->identity) - 4, 4); ?></div>
                </li>
                <li>
                    <div class="col-xs-3 text-right n26 grey2">手机号码</div>
                    <div class="col-xs-8 n26 grey4"><?php echo substr($userbank->user->mobile, 0, 3) . '********'; ?></div>
                </li>
                <?php if ($userbank->type == 1): ?>
                    <li>
                        <div class="col-xs-3 text-right n26 grey2">有效期</div>
                        <div class="col-xs-8 n26 grey4"><?php echo substr($userbank->validate, 0, 2) . '月' . substr($userbank->validate, 2) . '年'; ?></div>
                    </li>
                    <li>
                        <div class="col-xs-3 text-right n26 grey2">卡验证码</div>
                        <div class="col-xs-8 n26 grey4"><?php echo $userbank->cvv2; ?> </div>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
        <button class="btn mt40" style="width:100%;" onclick="tishi()">解除绑定</button>
    </div>
    <div class="Hmask" style="display: none;"></div>
    <div class="layer_border overflow noBorder" style="display: none;">
        <p class="n28 mb30 padlr625">确定解绑该银行卡？</p>
        <div class="border_top_2 nPad overflow">
            <a href="javascript:{$('.Hmask').toggle();$('.layer_border').toggle();};" class="n30 boder_right_1 text-center"><span class="grey2">取消</span></a>
            <a href="javascript:delbank();" class="n30 red text-center bRed"><span class="white ">确定</span></a>
        </div>
    </div>
</div>
<script>
    function tishi() {
        $('.Hmask').toggle();
        $('.layer_border').toggle();
    }
    function delbank() {
        $('.Hmask').toggle();
        $('.layer_border').toggle();
        $.ajax({
            type: "POST",
            dataType: "json",
            url: "/dev/bank/delcard?id=<?php echo $userbank->id; ?>",
            async: false,
            error: function(data) {
            },
            success: function(data) {
                if (data.code == '0') {
                    alert(data.message);
                    location.href = '/dev/bank';
                } else if (data.code == '2') {
                    alert(data.message);
                } else {
                    alert(data.message);
                    location.href = '/dev/bank';
                }
            }
        });
    }
</script>