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
        <img src="/images/coupon/message2.png">
    </div>
    <div class="jdyyy">
        <div class="dbk_inpL">
            <label>行业</label>  
            <select  name="industry" class="select" id="industry">
                <option selected="selected">请选择</option>  
                <?php foreach ($industry as $key => $value): ?>
                    <option value="<?php echo $key; ?>"><?php echo $value[0]; ?></option>
                <?php endforeach; ?>
            </select>
            <img src="/images/coupon/dibujtou.png" class="dibujtou">
        </div>
        <div class="dbk_inpL">
            <label>职业</label>
            <select name="profession" id="profession">
                <option value="0">请选择</option>
                <?php foreach ($profession as $key => $value): ?>
                    <option value="<?php echo $key; ?>"><?php echo $value[0]; ?></option>
                <?php endforeach; ?>
            </select>
            <img src="/images/coupon/dibujtou.png" class="dibujtou">
        </div>
        <div class="dbk_inpL">
            <label>职务级别</label>
            <select name="position" id="position">
                <option value="0">请选择</option>
                <?php foreach ($position as $key => $value): ?>
                    <option value="<?php echo $key; ?>"><?php echo $value[0]; ?></option>
                <?php endforeach; ?>
            </select>
            <img src="/images/coupon/dibujtou.png" class="dibujtou">
        </div>
        <div class="dbk_inpL">
            <label>月收入</label>
            <select name="income" id="income">
                <option>2000以下</option>
                <option>2000-2999</option>
                <option>3000-3999</option>
                <option>4000-4999</option>
                <option>5000以上</option>
            </select>
            <img src="/images/coupon/dibujtou.png" class="dibujtou">
        </div>

    </div>
    <div class="jdyyy danweimes">
        <div class="dbk_inpL">
            <label>单位名称</label><input value="" name="company" placeholder="如：先花信息技术（北京）有限公司" type="text">
            <input type="hidden" name="user_id" value="<?php echo $users->user_id; ?>" >
            <img src="/images/coupon/icon_remove.png" class="icon_Rem">
        </div>
        <div class="dbk_inpL">
            <label>单位电话</label><input value="" name="telephone" placeholder="" type="text">
            <img src="/images/coupon/icon_remove.png" class="icon_Rem">
        </div>
        <div class="dbk_inpL">
            <label>单位地区</label>
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
            <label>详细地址</label><input value="" name="address" placeholder="如：西城区新街口外大街28号" type="text">
            <img src="/images/coupon/icon_remove.png" class="icon_Rem">
        </div>

    </div>
    <div class="jdyyy danweimes">
        <div class="dbk_inpL">
            <label>电子邮箱</label><input value="" name="email" placeholder="如：xianhuahua@163.com" type="text">
            <img src="/images/coupon/icon_remove.png" class="icon_Rem">
        </div>
    </div>
    <!-- <div class="tsmes">*手机号错误</div> -->
    <div class="button"> <button id="company_save">提交</button></div>

</div>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
    $(function () {
        $("#company_save").click(function () {
            var csrf = '<?php echo $csrf; ?>';
            var industry = $("#industry  option:selected").val();
            var profession = $("#profession  option:selected").val();
            var position = $("#position  option:selected").val();
            var income = $("#income  option:selected").val();
            var district = $('.district').val();
            var company = $("input[name='company']").val();
            var telephone = $("input[name='telephone']").val();
            var address = $("input[name='address']").val();
            var user_id = $("input[name='user_id']").val();
            var from_url = $("input[name='from_url']").val();
            var email = $("input[name='email']").val();

            if (company == '' || address == '' || email == '') {
                alert("请完整输入信息");
                return false;
            }
            var reg_mobile = /^1(([3578][0-9])|(47))\d{8}$/;
            var reg_phone = /^0\d{2,3}\-?\d{7,8}$/;
            if (!reg_mobile.test(telephone.trim())) {
                if (!reg_phone.test(telephone.trim())) {
                    alert("请输入正确的单位电话");
                    return false;
                }
            }
            var filter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
            if (!filter.test(email)) {
                alert('您的电子邮件格式不正确');
                return false;
            }
//            $.post("/dev/reg/companysave", {email: email, telephone: telephone, company: company, address: address, industry: industry, profession: profession, position: position, income: income, district: district, from_url: from_url, user_id: user_id}, function (result) {
//            $.post("/dev/reg/companysave", {email: email, telephone: telephone, company: company, address: address, industry: industry, profession: profession, position: position, income: income, district: district, from_url: from_url, user_id: user_id}, function (result) {
            $.post("/new/userauth/workinfoajax", {_csrf:csrf, email: $.trim(email), telephone: telephone.trim(), company: company, address: address, industry: industry, profession: profession, position: position, income: income, district: district,  user_id: user_id}, function(result) {
                var data = eval("(" + result + ")");
                if (data.res_code == 0) {
                    window.location = '/dev/coupon/verify?user_id=' + user_id;
                } else if(data.res_code == 3){
                    window.location = data.res_data.url;
                } else {
//                    $("#workinfoajax").attr('disabled', false);
                    alert(data.res_data);
                    return false;
                }
//                var data = eval("(" + result + ")");
//
//                if (data.ret == '0') {
//                    if (data.url != '') {
//                        window.location = '/dev/coupon/verify?user_id=' + user_id;
//                    } else {
//                        alert('提交失败');
//                        return false;
//                    }
//                } else if (data.ret == '2')
//                {
//                    alert('该身份证号已存在，请更换');
//                    return false;
//                } else if (data.ret == '11') {
//                    alert("请填写姓名/身份证号码");
//                    return false;
//                } else if (data.ret == '12') {
//                    alert("请输入正确的单位电话");
//                    return false;
//                }
//                else if (data.ret == '3')
//                {
//                    window.location = '/dev/account/black';
//                }
//                else if (data.ret == '4')
//                {
//                    alert('身份认证失败，请重新修改');
//                    return false;
//                }
//                else {
//                    alert('提交失败，请退出重新提交');
//                    return false;
//
//                }

            });
        });


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

    });
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