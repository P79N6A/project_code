<img src="/sevenday/images/bannerbg.png">
<img class="edu" src="/sevenday/images/edu.jpg">
<div class="buttonyi">
    <button onclick="getCredit()" id="getcredit">获取额度</button>
</div>
<div class="Hmask" <?php if ($repayerror != 'success'): ?>hidden<?php endif; ?>></div>
<div class="dl_tcym" id="repayerror" <?php if ($repayerror != 'success'): ?>hidden<?php endif; ?>>
    <img class="tccicon" src="/sevenday/images/errorxx.png">
    <div class="tcsuccess"><img src="/sevenday/images/tcsuccess.png"></div>
    <h3 class="sqdlu">还款成功</h3>
    <p>恭喜您，借款账单还款成功！</p>
    <button class="qrbtzf" onclick="closeBox()">确认</button>
</div>
<div class="tishi_success" id="divbox" style="display: none;"><a class="tishi_text">获取额度失败</a></div>
<input type="hidden" id="csrf" value="<?php echo $csrf; ?>">
<div style="margin: 70% auto;display: flex;justify-content:center;height:16px;" onclick="docustom()">
    <img  src="/sevenday/images/kefu.png" style="height:16px;width:16px;margin-right:4px;"/>
    <span style="color: #1994f1;">联系客服</span>
</div>
<!--还款&续期弹窗开始-->
<div class="maskPop" style="display: none;"></div>
<?php if (!empty($popup)): ?>
    <div class="maskBox" style="display: none;">
        <div class="deleIconBox"><img src="/sevenday/images/deleIcon.png" alt="" class="deleIcon" onclick="closePopup()"></div>
        <?php if ($popup['status'] == 1): ?>
            <img src="/sevenday/images/succIcon.png" alt="" class="succIcon">
        <?php else: ?>
            <img src="/sevenday/images/failIcon.png" alt="" class="failIcon">
        <?php endif; ?>
        <?php if ($popup['status'] == 1): ?>
            <?php if ($popup['type'] == 1): ?>
                <p class="maskText1">还款成功</p>
                <div class="maskText3">恭喜您，借款账单还款成功！</div>
            <?php else: ?>
                <p class="maskText1">续期成功</p>
                <div class="maskText3">恭喜您，成功续期<?php echo $popup['days'];?>天</div>
                <div class="maskText4">最后还款日变更为<?php echo $popup['end_date'];?></div>
            <?php endif; ?>
        <?php else: ?>
            <?php if ($popup['type'] == 1): ?>
                <p class="maskText1">还款失败</p>
                <div class="maskText2">
                    对不起，系统未收到您的还款款项请重新还款如有疑问请联系客服
                </div>
            <?php else: ?>
                <p class="maskText1">续期失败</p>
                <div class="maskText2">
                    对不起，系统未收到您的续期款项请重新还款如有疑问请联系客服
                </div>
            <?php endif; ?>
        <?php endif; ?>
        <div class="MaskBtn" onclick="closePopup()">确认</div>
    </div>
<?php endif; ?>
<!--还款&续期弹窗结束-->
<script type="text/javascript">
    $(function () {
        zhuge.identify(<?php echo $user_id; ?>);
    });

    var popup = '<?php echo $popup['is_popup'];?>';
    if(popup == 1){
        $('.maskPop').show();
        $('.maskBox').show();
    }

    //关闭还款&续期弹窗
    function closePopup() {
        $('.maskPop').hide();
        $('.maskBox').hide();
    }

    var csrf = $('#csrf').val();
    function docustom() {
        var userId = '<?php echo empty($user->getOldUser()) ? 0 : $user->getOldUser()->user_id; ?>';
//        location.href = 'https://www.sobot.com/chat/h5/index.html?sysNum=f0af5952377b4331a3499999b77867c2&robotFlag=1&partnerId=' + userId;
        location.href = 'https://www.sobot.com/chat/h5/index.html?sysNum=f0af5952377b4331a3499999b77867c2&groupId=92d4c9365c2646aca42b25681b40f1b7&moduleType=2&partnerId=' + userId;
    }
    function getCredit() {
        $("#getcredit").attr('disabled', true);
        $.ajax({
            type: "POST",
            url: "/day/loan/getcredit",
            data: {_csrf: csrf},
            success: function (result) {
                result = eval('(' + result + ')');
                if (result.rsp_code == '0000') {
                    location.href = result.url;
                } else {
                    $("#getcredit").attr('disabled', false);
                    $('.tishi_text').html(result.rsp_msg);
                    $('#divbox').show();
                }
            }
        });
    }

    //关闭弹窗
    function closeBox() {
        $('.Hmask').hide();
        $('#repayerror').hide();
    }
</script>