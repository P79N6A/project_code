<div class="huikuannone">
    <img src="/299/images/huikuannone.png">
    <p>等待中...</p>
</div>
<script type="text/javascript">
    var from = '<?php echo $from;?>';
    var page_type = '<?php echo $page_type;?>';
    function toUrl(){
        if(from == 1){
            setTimeout(function () {
                window.myObj.closeHtml();
                function closeHtml() {
                }
            });
        }else{
            if(page_type == 1){
              location.href = "/borrow/userinfo/list";
              return false;
            }
            location.href = "/borrow/userinfo/selectioninfo";
        }
    }
    timer = setInterval(function(){
        toUrl();
    }, 3000);
</script>