<script>
    $(function() {
        var height = $('.pic_bg').height();
        $('.container').css('height', height);
    });
    $(window).load(function() {
        var height = $('.pic_bg').height();
        $('.container').css('height', height);
    });

</script>

<form action="/dev/compensation/luckdraw" method="post" id="form" enctype="multipart/form-data">
    <div class="container">
        <img src="/images/july/qixi.png" width="100%" class="pic_bg">
        <div class="file-div">
            <img src="/images/july/showPic.jpg" id="chooseImage" >
        </div>
        <div class="btn-div">
            <img src="/images/july/btn1.png" class="btn1">
        </div>
        <input type="hidden" name="serverid" value="" id="reg_serverid">
        <input type="hidden" name="user_type" value="0">
    </div>
    <!-- 开奖弹层 -->
    <div class="mask" style="display: none;"></div>
    <div class="form" style="display: none;">
        <img src="/images/july/tree.png">
        <input type="text" name="mobile" class="phoneNum" placeholder="联系方式">
        <input type="text" name="code" class="code" placeholder="验证码">
        <button class="codeBtn" id="code">获取验证码</button>
        <div class="clearfix"></div>
        <a href="javascript:void(0);" class="link_1">去开奖</a>
    </div>
</form>

<script>
    $('.link_1').click(function() {
        $.ajax({
            type: "POST",
            dataType: "json",
            url: '/dev/compensation/onesave',
            data: $('#form').serialize(), // 你的formid
            async: false,
            error: function(data) {
                return false;
            },
            success: function(data) {
                if (data.ret == 0) {
                    $("#form").submit();
                } else if (data.ret == 2) {
                    alert('验证码错误');
                } else {
                    alert('注册失败，请重新操作');
                }
            }
        });
        return false;
    });
//    $(".subtn").on('click', function() {
//        alert(111);
//        var pic_repay1 = $('#chooseImage').attr('src');
////        console.dir(pic_repay1);
//        if (pic_repay1 == '/images/july/showPic.jpg')
//        {
//            alert("请添加恩爱双人照");
//            return false;
//        }
//        $(this).attr('disabled', true);
//        $.ajax({
//            type: "POST",
//            dataType: "json",
//            url: "/dev/compensation/openid",
//            async: false,
//            success: function(data) {
//                if (data.code == 0) {
//                    $('.mask').hide();
//                    $('.form').hide();
//                    $("#form").submit();
//                } else {
//                    $('.mask').show();
//                    $('.form').show();
//                }
//            }
//        });
//    });
    var codeBtn = document.getElementById('code');
    var timer = null;
    var time = 60;
    var s = time + 1;
    codeBtn.onclick = function() {
        var mobile = $('input[name="mobile"]').val();
        if (mobile.length == 0 || !_mobileRex.test(mobile)) {
            alert('请输入正确格式的手机号!');
            return false;
        } else {
            countDown();
            timer = setInterval(countDown, 1000);
            codeBtn.disabled = true;
            var mark = false;
            $.ajax({
                type: "POST",
                dataType: "json",
                url: '/dev/compensation/sendmobile',
                data: $('#form').serialize(), // 你的formid
                async: false,
                success: function(data) {
                    if (data.ret == 1) {
                        alert('该手机号已经注册');
                    } else if (data.ret == 2) {
                        alert('每天只能发6次短信');
                    }
                }
            });
            return mark;
        }
    };
    function countDown() {
        s--;
        codeBtn.innerHTML = s + '秒后重新获取';
        if (s == 0) {
            clearInterval(timer);
            codeBtn.disabled = false;
            s = time + 1;
            codeBtn.innerHTML = '重新获取验证码';
        }
    }
</script>

<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
    var sub = 0;
    wx.config({
        debug: false,
        appId: '<?php echo $jsinfo['appid']; ?>',
        timestamp: <?php echo $jsinfo['timestamp']; ?>,
        nonceStr: '<?php echo $jsinfo['nonceStr']; ?>',
        signature: '<?php echo $jsinfo['signature']; ?>',
        jsApiList: [
            'chooseImage',
            'previewImage',
            'uploadImage',
            'downloadImage',
            'hideOptionMenu'
        ]
    });
    wx.ready(function() {
        wx.hideOptionMenu();
        // config信息验证后会执行ready方法，所有接口调用都必须在config接口获得结果之后，config是一个客户端的异步操作，所以如果需要在页面加载时就调用相关接口，则须把相关接口放在ready函数中调用来确保正确执行。对于用户触发时才调用的接口，则可以直接调用，不需要放在ready函数中。
    });
    wx.error(function(res) {
        alert(res.errMsg);
    });


    var images = {
        localId: [],
        serverId: []
    };
    document.querySelector('#chooseImage').onclick = function() {
        wx.chooseImage({
            success: function(res) {
                images.localId = res.localIds;
                $("#chooseImage").attr("src", res.localIds[0]);
                var upload = function() {
                    wx.uploadImage({
                        localId: images.localId[0],
                        success: function(ret) {
                            var serverId = ret.serverId; // 返回图片的服务器端ID
                            $("#reg_serverid").val(serverId);
                            $('.btn1').attr('src', '/images/july/btn7.png');
                            sub = 1;
                        }
                    });
                };
                upload();
            }
        });
    };
    document.querySelector('.btn1').onclick = function() {
        if (sub == 0) {
            wx.chooseImage({
                success: function(res) {
                    images.localId = res.localIds;
                    $("#chooseImage").attr("src", res.localIds[0]);
                    var upload = function() {
                        wx.uploadImage({
                            localId: images.localId[0],
                            success: function(ret) {
                                var serverId = ret.serverId; // 返回图片的服务器端ID
                                $("#reg_serverid").val(serverId);
                                $('.btn1').attr('src', '/images/july/btn7.png');
                                sub = 1;
                            }
                        });
                    };
                    upload();
                }
            });
        } else {
            var pic_repay1 = $('#chooseImage').attr('src');
//        console.dir(pic_repay1);
            if (pic_repay1 == '/images/july/showPic.jpg')
            {
                alert("请添加恩爱双人照");
                return false;
            }
            $(this).attr('disabled', true);
            $.ajax({
                type: "POST",
                dataType: "json",
                url: "/dev/compensation/openid",
                async: false,
                success: function(data) {
                    if (data.code == 0) {
                        $('.mask').hide();
                        $('.form').hide();
                        $("#form").submit();
                    } else {
                        $('.mask').show();
                        $('.form').show();
                    }
                }
            });
        }
    };
</script>