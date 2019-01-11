<img src="/sevenday/images/bannerbg.png">
<?php if ($money < 500000): ?>
    <img class="edu" src="/sevenday/images/fkuan.jpg">
<?php else : ?>
    <img class="edu" src="/sevenday/images/outmoney.png">
<?php endif; ?>
<div style="margin: 78% 0 15% 0;display: flex;justify-content:center;height:16px;" onclick="docustom()">
    <img  src="/sevenday/images/kefu.png" style="height:16px;width:16px;margin-right:4px;"/>
    <span style="color: #1994f1;">联系客服</span>
</div>
<script type="text/javascript">
    function docustom() {
        var userId = '<?php echo empty($user->getOldUser()) ? 0 : $user->getOldUser()->user_id; ?>';
        location.href = 'https://www.sobot.com/chat/h5/index.html?sysNum=f0af5952377b4331a3499999b77867c2&groupId=92d4c9365c2646aca42b25681b40f1b7&moduleType=2&partnerId=' + userId;
    }
</script>