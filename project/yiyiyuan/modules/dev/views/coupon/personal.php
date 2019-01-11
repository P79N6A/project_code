<script>
    $(function () {
        $('.icon_Rem').click(function () {
            $(this).siblings('input').prop('value', '');
        });

        $(".button button").click(function () {
            $('.Hmask').show();
            $('.layer_border').show();
        });
        //点击关闭按钮
        $('.layer_border .border_top').click(function () {
            $('.Hmask').hide();
            $('.layer_border').hide();
        });

    });
</script>
<style>
    .jdyyy .dbk_inpL .selectList select{
        width: 20%;
    }
</style>
<div class="jdall">
    <div>
        <img src="/images/coupon/message1.png">
    </div>    
    <div class="jdyyy">
        <div class="dbk_inpL">
            <label>姓名</label><input name="realname" placeholder="姓名" type="text">
            <img src="/images/coupon/icon_remove.png" class="icon_Rem">
        </div>
        <div class="dbk_inpL">
            <label>身份证号</label><input name="identity" placeholder="身份证号" type="text">
            <img src="/images/coupon/icon_remove.png" class="icon_Rem">
        </div>
    </div>
    <div class="jdyyy">
        <div class="dbk_inpL">
            <label>常住地址</label>
            <div class="selectList">
                <select class="province">
                    <option>请选择</option>
                </select>
                <select class="city">
                    <option>请选择</option>
                </select>
                <select class="district">
                    <option>请选择</option>
                </select>
            </div>
        </div>
        <div class="dbk_inpL">
            <label>详细地址</label><input name="home_address" placeholder="如：西城区新街口外大街28号" type="text" value="">
            <img src="/images/coupon/icon_remove.png" class="icon_Rem">
        </div>
    </div>
    <div class="jdyyy">
        <div class="dbk_inpL">
            <label>学历</label>
            <select name="edu">
                <?php foreach ($edu as $key => $val): ?>
                    <option value="<?php echo $key; ?>"><?php echo $val[0] ?></option>
                <?php endforeach; ?>
            </select>
            <img src="/images/coupon/dibujtou.png" class="dibujtou">
        </div>
        <div class="dbk_inpL">
            <label>婚姻</label>
            <select name="marriage">
                <?php foreach ($marriage as $key => $val): ?>
                    <option  value="<?php echo $key; ?>"><?php echo $val[0] ?></option>
                <?php endforeach; ?>
            </select>
            <img src="/images/coupon/dibujtou.png" class="dibujtou">
        </div>

    </div>
    <!-- <div class="tsmes">*手机号错误</div> -->
    <div class="button"> <button id="bank_form">提交</button></div>

</div>
<script>
    $(function () {
        var userId = "<?php echo $userinfo->user_id; ?>";
        $(".selectList").each(function () {
            var areaJson = <?php echo $list; ?>;
            var default_code = "<?php echo!empty($user_extend) ? $user_extend->home_area : 0; ?>";
            var temp_html = "";
            var oProvince = $(this).find(".province");
            var oCity = $(this).find(".city");
            var oDistrict = $(this).find(".district");
            //初始化省 
            var province = function () {
                var code = default_code.substr(0, 2);
                $.each(areaJson, function (i, province) {
                    if (province.code == code) {
                        temp_html += "<option selected='selected' value='" + province.code + "'>" + province.name + "</option>";
                    } else {
                        temp_html += "<option value='" + province.code + "'>" + province.name + "</option>";
                    }
                });
                oProvince.html(temp_html);
                city();
            };
            //赋值市 
            var city = function () {
                temp_html = "";
                var n = oProvince.get(0).selectedIndex;
                var code = default_code.substr(0, 4);
                $.each(areaJson[n].area, function (i, city) {
                    if (city.code == code) {
                        temp_html += "<option selected='selected' value='" + city.code + "'>" + city.name + "</option>";
                    } else {
                        temp_html += "<option value='" + city.code + "'>" + city.name + "</option>";
                    }
                });
                oCity.html(temp_html);
                district();
            };
            //赋值县 
            var district = function () {
                temp_html = "";
                var m = oProvince.get(0).selectedIndex;
                var n = oCity.get(0).selectedIndex;
                if (typeof (areaJson[m].area[n].area) == "undefined") {
                    oDistrict.css("display", "none");
                } else {
                    oDistrict.css("display", "inline");
                    $.each(areaJson[m].area[n].area, function (i, district) {
                        if (district.code == default_code) {
                            temp_html += "<option selected='selected' value='" + district.code + "'>" + district.name + "</option>";
                        } else {
                            temp_html += "<option value='" + district.code + "'>" + district.name + "</option>";
                        }
                    });
                    oDistrict.html(temp_html);
                }
                ;
            };
            //选择省改变市 
            oProvince.change(function () {
                city();
            });
            //选择市改变县 
            oCity.change(function () {
                district();
            });
            province();
        });

        $("#bank_form").click(function () {
            var marriage = $('select[name="marriage"]').val();
            var edu = $('select[name="edu"]').val();
            var district = $('.district').val();
            var realname = $('input[name="realname"]').val();
            var identity = $('input[name="identity"]').val();
            var home_address = $('input[name="home_address"]').val();
            if (realname == '') {
                $('#errormsg').html('*请选择你的真实姓名');
                return false;
            }
            $('#errormsg').html('');
            if (!checkregisteridentity(identity)) {
                $('#errormsg').html('*请填写正确的身份证号码');
                return false;
            }
            $('#errormsg').html('');
            if (home_address == '') {
                $('#errormsg').html('*请填写详细地址');
                return false;
            }
            $.post("/dev/reg/namesaves", {userId: userId, realname: realname, identity: identity, district: district, home_address: home_address, marriage: marriage, edu: edu}, function (result) {
                var data = eval("(" + result + ")");
                if (data.ret == '0') {
                    window.location = '/dev/coupon/company?user_id=' + userId;
                } else if (data.ret == '3') {
                    alert('身份证号码已经存在！');
                    return false;
                } else if (data.ret == '11') {
                    alert('身份证号码与姓名不匹配');
                    return false;
                }
                else if (data.ret == '1')
                {
                    window.location = '/dev/reg/login';
                    return false;
                } else if (data.ret == '2') {
                    alert('系统错误');
                    return false;
                }
            });
        });

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