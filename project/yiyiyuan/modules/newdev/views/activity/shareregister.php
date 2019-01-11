<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title></title>
    <link rel="stylesheet" type="text/css" href="/css/dev/activityrest.css"/>
    <link rel="stylesheet" type="text/css" href="/css/dev/activityindex.css"/>
    <script src="/js/dev/activityjs.js"></script>
    <script>
        $(function(){
            $('.tancymia .tcerror').click(function(){
                $('#overDivs').hide();
                $('.tancymia img').hide();
            });
        })
    </script>
</head>

<body>
<div class="actvete">
    <div class="bannerimg" >
        <img src="/images/dev/tebanner.jpg">
    </div>

    <div class="buttxt">
        <div class="mestishi" style="margin-bottom: 0;">
            <p><?php echo !empty($username)?$username:'花二哥'?></p>
            <h3>送你198元免息券</h3>
        </div>
        <p class="tishimes">立即注册登陆先花一亿元APP即可激活</p>
        <input class="srphone" type="text" placeholder="输入手机号" style="color:#fff; font-size: 1.2rem;">
        <span id="tishi" style="color: #ff0000;margin-left: 20px; font-size: large;display: none"></span>
        <button class="dwlhbao"> 领取免息券</button>

        <div class="bottombanner"><img src="/images/dev/bottombanner.png"></div>
    </div>

</div>




</body>
</html>
<script>
    $(".srphone").blur(function(){
        var myreg = /^1[34578]\d{9}$/;
        if(!myreg.test($(".srphone").val()))
        {
            $("#tishi").show();
            $("#tishi").html("*请输入正确的手机号");
            return false;
        }
    });

    $(".dwlhbao").click(function(){
        var myreg = /^1[34578]\d{9}$/;
        var phone = $(".srphone").val();
        if(!myreg.test(phone))
        {
            $("#tishi").show();
            $("#tishi").html("*请输入正确的手机号");
            return false;
        }else{
            var from_code = GetQueryString("from_code");
                $.ajax({
                    type: "POST",
                    url: "/new/activity/add",
                    data: {phone:phone,from_code:from_code},
                    success: function(data){
                        var a = data.split(":");
                        var phone = a[1];
                        var msg = a[0];
                        if(msg==1){
                            window.location.href="/new/activity/shareapp?phone="+phone;
                        }else if(msg == 2){
                            $("#tishi").show();
                            $("#tishi").html("*该手机号已接受过邀请");
                        }else{
                            $("#tishi").show();
                            $("#tishi").html("*请输入正确的手机号");
                        }

                    }
                });
            }
    });
    function GetQueryString(name)
    {
        var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
        var r = window.location.search.substr(1).match(reg);
        if(r!=null)return  unescape(r[2]); return null;
    }
</script>