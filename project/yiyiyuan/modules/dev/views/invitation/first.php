<div class="fInvitation">
    <div class="fInvfirst helpmone">
        <p class="help1">您的好友<?php echo!empty($userwx) ? '“' . $userwx->nickname . '”' : ''; ?>邀请您做三道测试题 </p>
        <p class="help2"><em>全部答对</em>即可获得<em>66元</em>优惠券红包</p>
        <img src="/images/account/firstimg2.png">
    </div>
    <div class="button"> <button>开始测试</button></div>
    <div class="certification">
        <div class="cert_one"><img src="/images/account/fircert.png">雷锋榜</div>
        <div class="cert_two">
            <img src="/images/account/firtoux.png">
            <div class="cert_two2"><p class="p1">马云</p><p class="p2">02月09日  21:39</p></div>
            <div class="cert_two3">34点</div>
        </div>
        <div class="cert_two">
            <img src="/images/account/firtoux.png">
            <div class="cert_two2"><p class="p1">马云</p><p class="p2">02月09日  21:39</p></div>
            <div class="cert_two3">34点</div>
        </div>
    </div>
    <div id="overDiv"></div>
    <div class="mDatiMask">
        <div class="mDati">
            <ul class="number">
                <li class="item1 item1-3"><i></i></li>
                <li class="item2 item2-1"><i></i></li>
                <li class="item3 item3-1"><i></i></li>
            </ul>
            <div class="title"><strong>● Ta的名字是？</strong><span>关系铁不铁,就看回答咯</span></div>
            <ul class="list">
                <?php foreach ($first_array as $key => $value): ?>
                    <li class="first_click" name="<?php echo $first_array[$key]['name']; ?>"><?php echo $first_array[$key]['name']; ?><i class="icon"></i></li>
                <?php endforeach; ?>
            </ul>
            <input type="hidden" id="first_answer" value="<?php echo $first_answer; ?>" />
            <input type="hidden" id="wid" value="<?php echo $userinfowx['user_id']; ?>" />
            <input type="hidden" id="array_key" value="<?php echo $array_key; ?>" />
            <a href="javascript:void(0);" class="close"></a> 
        </div>
    </div>
</div>
<script>
    $(".first_click").bind("click", function () {

        var spanIndex = $(this).index();
        $("li", $(this).parent()).each(function (index, element) {
            if (index == spanIndex) {
                $(this).addClass("valid");
                var click_answer = $(this).attr("name");

                var first = $("#first_answer").val();
                var wid = $("#wid").val();
                var array_key = $("#array_key").val();
                if (click_answer == first)
                {
                    //点击正确答案,则跳转到第二个页面
                    window.location = '/dev/invitation/second?userid=' + wid + '&key=' + array_key;
                }
                else
                {
                    //点击错误答案
                    $.post("/dev/invitation/firstsave", {userid: wid, array_key: array_key}, function (data) {
                        window.location = '/dev/invitation/fail?userid=' + wid;
                    });
                }
            } else {
                $(this).removeClass("valid");
            }
        });
        return false;
    });
</script>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
    wx.config({
        debug: false,
        appId: '<?php echo $jsinfo['appid']; ?>',
        timestamp: <?php echo $jsinfo['timestamp']; ?>,
        nonceStr: '<?php echo $jsinfo['nonceStr']; ?>',
        signature: '<?php echo $jsinfo['signature']; ?>',
        jsApiList: [
            'hideOptionMenu'
        ]
    });

    wx.ready(function () {
        wx.hideOptionMenu();
    });
</script>