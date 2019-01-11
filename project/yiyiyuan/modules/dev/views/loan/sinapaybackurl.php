<div class="successmima">
    <?php if ($succ == 1): ?>
        <img src="/images/successcg.png">
        <button class="wancgend">完成</button>
    <?php else: ?>
        <img src="/images/fasesb.png">
        <button class="wancgend">重新设置</button>
    <?php endif; ?>
</div>
<script>
    var mark = <?php echo $succ; ?>;
    var type = "<?php echo $type; ?>";
    var user_id = <?php echo $user_id; ?>;
    $(".wancgend").click(function () {
        if (mark == 1) {
            if (type == "ios") {
                okmake();
            } else if (type == "android") {
                myObj.fun1FromAndroid("1");
            } else {
                location.href = "/dev/loan";
            }
        } else {
            if (type == "ios") {
                retrymake();
            } else if (type == "android") {
                myObj.fun1FromAndroid("2");
            } else {
                $.post("/dev/loan/sina", {user_id: user_id}, function (result) {
                    var data = eval("(" + result + ")");
                    if (data.code == 0) {
                        location.href = data.url;
                    } else {
                        alert("暂时无法激活，请联系先花客服");
                    }
                });
            }
        }
    });
    function okmake() {
//        alert("fff");
    }
    function retrymake() {

    }
</script>