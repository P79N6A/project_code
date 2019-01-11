<div class="actvete">
    <div class="bannerimg" >
        <img src="/news/images/activity/newforce/tebanner.jpg">
    </div>
    <div class="buttxt">
        <div class="contnewym"><img src="/news/images/activity/newforce/contnewym.png"></div>
        <input class="srphone" type="text" placeholder="输入手机号">
        <span id="tishi" style="color: #ff0000;margin-left: 20px; font-size: large;display: none"></span>
        <button class="dwlhbao"> 领取免还款特权</button>
    </div>

</div>
<script>
    $(function(){
        $('.tancymia .tcerror').click(function(){
            $('#overDivs').hide();
            $('.tancymia img').hide();
        });
    });

    $(".srphone").blur(function(){
        var myreg = /^1[34578]\d{9}$/;
        if(!myreg.test($(".srphone").val()))
        {
            $("#tishi").show();
            $("#tishi").html("*请输入正确的手机号");
            return false;
        }else{
            $("#tishi").hide();
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
                url: "/new/activity/newforceadd",
                data: {phone:phone,from_code:from_code},
                success: function(data){
                    var a = data.split(":");
                    var phone = a[1];
                    var msg = a[0];
                    if(msg==1){
                        window.location.href="/new/activity/newforceapp?phone="+phone;
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