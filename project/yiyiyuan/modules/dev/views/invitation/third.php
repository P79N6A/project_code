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
    <div class="mDati mDati-2">
        <ul class="number">
            <li class="item1 item1-2"><i></i></li>
            <li class="item2 item2-2"><i></i></li>
            <li class="item3 item3-3"><i></i></li>
        </ul>
        <div class="title"><strong>● Ta的相貌是？</strong><span>关系铁不铁,就看回答咯</span></div>
        <ul class="list2">
            <li class="item1" url="<?php echo $third_array[0]['url']; ?>"><img src="<?php echo $third_array[0]['url']; ?>" class="avatar" alt="" /><span class="mask"><i class="icon"></i></span></li>
            <li class="item2" url="<?php echo $third_array[1]['url']; ?>"><img src="<?php echo $third_array[1]['url']; ?>" class="avatar" alt="" /><span class="mask"><i class="icon"></i></span></li>
            <li class="item3" url="<?php echo $third_array[2]['url']; ?>"><img src="<?php echo $third_array[2]['url']; ?>" class="avatar" alt="" /><span class="mask"><i class="icon"></i></span></li>
        </ul>
        <input type="hidden" id="third_answer" value="<?php echo $third_answer; ?>" />
        <input type="hidden" id="wid" value="<?php echo $userinfowx['user_id']; ?>" />
        <a  href="javascript:void(0);"class="close"></a> </div>
</div>
<script>
    $(".list2 li").bind("click", function () {
        var spanIndex = $(this).index();
        $("li", $(this).parent()).each(function (index, element) {
            if (index == spanIndex) {
                $(this).addClass("valid");
                var click_answer = $(this).attr("url");
                var third = $("#third_answer").val();
                var wid = $("#wid").val();
                if (click_answer == third)
                {
                    //点击正确答案,则跳转到第二个页面
                    $.post("/dev/invitation/successsave", {userid: wid}, function (result) {

                        var data = eval("(" + result + ")");
                        if (data.ret == '0')
                        {
                            window.location = '/dev/invitation/success?userid=' + wid;
                        } else if (data.ret == '2') {
                            //已做过认证
                            window.location = '/dev/invitation/inputmobile?userid=' + wid;
                        } else if (data.ret == '1')
                        {
                            window.location = '/dev/invitation/fail?userid=' + wid;
                        }
                    });
                }
                else
                {
                    //点击错误答案
                    $.post("/dev/invitation/thirdsave", {userid: wid}, function (data) {
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