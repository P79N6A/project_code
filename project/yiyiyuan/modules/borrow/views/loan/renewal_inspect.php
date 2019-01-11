<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo $this->title; ?></title>
    <script src="/borrow/335/javascript/z_scale.js"></script>
    <link rel="stylesheet" href="/borrow/335/css/reset.css">
    <link rel="stylesheet" href="/borrow/335/css/home.css">
</head>
<body>
<div class="w_homeBody">
    <p>申请中</p>
    <span>续期还款申请中，预计5分钟内完成审核！</span>
    <div class="homeBox">
        <img src="/borrow/335/images/jindu.png" alt="">
        <div class="w_text_con">
            <p>续期申请已提交</p>
            <p>2018-10-30 16:08:39</p>
            <p>申请中</p>
        </div>
    </div>
</div>
<script>
    //重写返回按钮
    pushHistory();
    var bool = false;
    setTimeout(function () {
        bool = true;
    }, 500);
    window.addEventListener("popstate", function (e) {
        if (bool) {
            setTimeout(function () {
                window.location.href = '/borrow/loan';
            }, 1000);
        }
        pushHistory();
    }, false);
    function pushHistory() {
        var state = {
            url: "#"
        };
        window.history.pushState(state, "#");
    }
</script>
</body>
</html>