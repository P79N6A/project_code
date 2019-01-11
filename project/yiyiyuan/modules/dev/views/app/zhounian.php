<button id="butt">点击分享</button>
<script>
    var type = "<?php echo $from; ?>";
    $('#butt').click(function () {
        if (type == "app") {
            window.myObj.bannerShare();
        } else {
            location.href = "/dev/loan";
        }
    });
    function bannershare() {
//        alert("fff");
    }
</script>