<div class="hkcguy">
    <img class="click" src="/images/click.png">
    <p>您的购买操作已经提交，实际购买情况正在确认中，如有疑问请咨询先花一亿元微信客服：<span>先花一亿元</span></p>
    <img src="/images/bxgoumai.png">
</div>
<script type="text/javascript">
//    window.onload=function(){
//}
<?php if ($source == 1): ?>
        setTimeout(function () {
            window.location.href = '/new/loan';
        }, 3000);
<?php else: ?>
        function clos() {
            window.myObj.closeHtml();
            function closeHtml() {
            }
        }
        setTimeout("clos();");
<?php endif; ?>
</script>