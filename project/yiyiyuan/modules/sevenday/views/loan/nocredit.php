<img src="/sevenday/images/bannerbg.png">
<img class="edu" src="/sevenday/images/noneed.jpg">
<div class="buttonyi" onclick="doGuide()">
    <button>试试其他产品</button>
</div>
<input type="hidden" id="guide_url" value="<?php echo $guide_url; ?>">
<script type="text/javascript">
    var guide_url = $('#guide_url').val();
    function doGuide() {
        location.href = guide_url;
    }
</script>