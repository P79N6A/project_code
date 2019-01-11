<img src="/sevenday/images/bannerbg.png">
<?php if ($day > 0): ?>
    <div class="hluan_bg yuqi">
        <img class="bgbg_yuqi" src="/sevenday/images/bgbg_dwen.png">
        <div class="yuqiyu">
            <div class="tanhaoyuqi"><img src="/sevenday/images/yuqi.png"> 您的逾期行为已严重影响您的信用评价，请马上还款！</div>
            <h3>待还款<span> (已逾期<?php echo $day;?>天)</span></h3>
            <p><span>¥</span><?php echo sprintf('%.2f', $repayment);?></p>
            <div class="zhkrq"><span>贷后管理费</span><i><?php echo sprintf('%.2f', $chase_amount); ?>元</i></div>
            <div class="zhkrq"><span>最后还款日期</span><i><?php echo date('Y/m/d', strtotime($user_loan->end_date) - 86400); ?></i></div>
        </div>
    </div>
<?php else: ?>
    <div class="hluan_bg ">
        <img class="bgbg_dwen" src="/sevenday/images/bgbg_dwen.png">
        
        <div class="xinxiye">
            <h3>待还款<span style="color:blue;"><?php if ($user_loan->number > 0): ?>(已续期)<?php endif; ?></span></h3>
            <p><span>¥</span><?php echo sprintf('%.2f', $repayment); ?></p>
            <div class="zhkrq"><span>最后还款日期</span><i><?php echo date('Y/m/d', strtotime($user_loan->end_date) - 86400); ?></i></div>
        </div>
    </div>
<?php endif; ?>
<div class="Hmask" <?php if ($repayerror != 'error'): ?>hidden<?php endif; ?>></div>
<div class="dl_tcym" id="repayerror" <?php if ($repayerror != 'error'): ?>hidden<?php endif; ?>>
    <img class="tccicon" src="/sevenday/images/errorxx.png">
    <div class="tcsuccess"><img src="/sevenday/images/tcfale.png"></div>
    <h3 class="sqdlu">还款失败</h3>
    <p>对不起，系统未收到您的还款款项请重新<br/>还款如有疑问请联系客服</p>
    <button class="qrbtzf" onclick="closeBox()">确认</button>
</div>
<div class="buttonyi"> <button onclick="doRepay()">立即还款</button></div>
<?php if (!empty($renew)): ?>
    <div class="buttonyi"> <button onclick="doRenew()" style="background: -webkit-linear-gradient(left,#fff,#fff);"><span style="color: black;">续期还款</span></button></div>
<?php endif; ?>

<div style="margin: 45% 0 15% 0;display: flex;justify-content:center;height:16px;" onclick="docustom()">
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

    function doRepay() {
        location.href = '/day/repay/showrepay';
    }

    function doRenew() {
        location.href = '/day/repay/renew';
    }

    //关闭弹窗
    function closeBox() {
        $('.Hmask').hide();
        $('#repayerror').hide();
    }
    function docustom() {
        var userId = '<?php echo empty($user->getOldUser()) ? 0 : $user->getOldUser()->user_id; ?>';
        location.href = 'https://www.sobot.com/chat/h5/index.html?sysNum=f0af5952377b4331a3499999b77867c2&groupId=92d4c9365c2646aca42b25681b40f1b7&moduleType=2&partnerId=' + userId;
    }
</script>