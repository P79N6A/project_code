<img src="/sevenday/images/bannerbg.png">
<img class="edu" src="/sevenday/images/tedu.jpg">
<div class="buttonyi" onclick="doConfirm()"> <button>立即借款</button></div>
<input type="hidden" id="csrf" value="<?php echo $csrf; ?>">
<div style="margin: 55% auto;display: flex;justify-content:center;height:16px;" onclick="docustom()">
    <img  src="/sevenday/images/kefu.png" style="height:16px;width:16px;margin-right:4px;"/>
    <span style="color: #1994f1;">联系客服</span>
</div>
<script type="text/javascript">
    var csrf = $('#csrf').val();
    function doConfirm() {
        zhuge.track('发起借款', {
            '金额': 500,
            '周期': 7,
            '理由': '个人消费',
            '优惠券': '无',
        });
        location.href = '/day/loan/confirm';
    }

    function docustom() {
        var userId = '<?php echo empty($user->getOldUser()) ? 0 : $user->getOldUser()->user_id; ?>';
        location.href = 'https://www.sobot.com/chat/h5/index.html?sysNum=f0af5952377b4331a3499999b77867c2&groupId=92d4c9365c2646aca42b25681b40f1b7&moduleType=2&partnerId=' + userId;
    }
</script>