<div class="jdall">
    <div class="jdyyy danweimes">
        <div class="dbk_inpL">
            <label>收货人  &nbsp;&nbsp;</label><input placeholder="姓名" type="text" name="user_name" maxlength="20" value="<?php echo empty($address_info->receive_name)?'' : $address_info->receive_name; ?>">
        </div>
        <div class="dbk_inpL">
            <label>联系电话</label><input placeholder="联系电话" type="text"  name="mobile" maxlength="11" onkeyup="this.value=this.value.replace(/\D/g,'')" value="<?php echo empty($address_info->receive_mobile)?'' : $address_info->receive_mobile; ?>">
        </div>
        <div class="dbk_inpL">
            <label>收货地址</label>
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
        <div class="dbk_inpL" style="height: 80px;">
            <label>详细地址</label><input placeholder="如：西城区新街口外大街28号" type="text" name="address" maxlength="50" value="<?php echo empty($address_info->address_detail)?'' : $address_info->address_detail; ?>">
        </div>
    </div>
    <div class="xq_allcont">
        <div class="lixdan truedzxq">
            <input type="hidden" name="user_id" value="<?=$user_id; ?>">
            <input type="hidden" name="address_id" value="<?php echo empty($address_info->id)?'':$address_info->id; ?>">
            <button id="addressajax" <?php if( strpos($_SERVER['HTTP_USER_AGENT'], 'YYY_ANDROID') || strpos($_SERVER['HTTP_USER_AGENT'], 'yyyIOS')){ ?> style="margin-bottom: 0px;" <?php }; ?>>保存并使用</button>
            <!--<button class="gray">立即下单</button>
            <button class="gray">已下架</button>-->
        </div>
    </div>
</div>

<div class="true_shdz" hidden></div>

<script type="text/javascript">
    $(function(){
        //省市联动
        $(".selectList").each(function() {
            var areaJson = <?php echo $list; ?>;
            var default_code = "<?php echo!empty($address_info) ? $address_info['area_code'] : 0; ?>";
            var temp_html;
            var oProvince = $(this).find(".province");
            var oCity = $(this).find(".city");
            var oDistrict = $(this).find(".district");
            //初始化省
            var province = function() {
                var code = default_code.substr(0, 2);
                $.each(areaJson, function(i, province) {
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
            var city = function() {
                temp_html = "";
                var n = oProvince.get(0).selectedIndex;
                var code = default_code.substr(0, 4);
                $.each(areaJson[n].area, function(i, city) {
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
            var district = function() {
                temp_html = "";
                var m = oProvince.get(0).selectedIndex;
                var n = oCity.get(0).selectedIndex;
                if (typeof (areaJson[m].area[n].area) == "undefined") {
                    oDistrict.css("display", "none");
                } else {
                    oDistrict.css("display", "inline");
                    $.each(areaJson[m].area[n].area, function(i, district) {
                        if (district.code == default_code) {
                            temp_html += "<option selected='selected' value='" + district.code + "'>" + district.name + "</option>";
                        } else {
                            temp_html += "<option value='" + district.code + "'>" + district.name + "</option>";
                        }
                    });
                    oDistrict.html(temp_html);
                };
            };
            //选择省改变市
            oProvince.change(function() {
                city();
            });
            //选择市改变县
            oCity.change(function() {
                district();
            });
            province();
        });

        //提交保存收货地址
        $("#addressajax").click(function() {
            $("#addressajax").attr("disabled", "disabled");
            var csrf = '<?php echo $csrf; ?>';
            var district = $('.district').val();
            var address_id = $("input[name='address_id']").val();
            var user_name= $("input[name='user_name']").val().replace(/\s+/g, "");
            var mobile= $("input[name='mobile']").val().replace(/\s+/g, "");
            var address= $("input[name='address']").val().replace(/(^\s*)|(\s*$)/g,"");
            var user_id = $("input[name='user_id']").val();
            var reg_phone =  /^1(([35678][0-9])|(47))\d{8}$/;
            if (user_name == '') {
                $(".true_shdz").html('请填写收件人!').show(150).delay(1500).hide(150);
                setTimeout(function () {
                    $("#addressajax").removeAttr("disabled");
                }, 1500);
                return false;
            }
            if (mobile == '') {
                $(".true_shdz").html('请填写手机号!').show(150).delay(1500).hide(150);
                setTimeout(function () {
                    $("#addressajax").removeAttr("disabled");
                }, 1500);
                return false;
            }
            if (district == 0) {
                $(".true_shdz").html('请选择省市区!').show(150).delay(1500).hide(150);
                setTimeout(function () {
                    $("#addressajax").removeAttr("disabled");
                }, 1500);
                return false;
            }
            if (address == '') {
                $(".true_shdz").html('请填写详细地址!').show(150).delay(1500).hide(150);
                setTimeout(function () {
                    $("#addressajax").removeAttr("disabled");
                }, 1500);
                return false;
            }
            if (!reg_phone.test(mobile)) {
                $(".true_shdz").html('请输入正确的手机号!').show(150).delay(1500).hide(150);
                setTimeout(function () {
                    $("#addressajax").removeAttr("disabled");
                }, 1500);
                return false;
            }
            $("#addressajax").attr('disabled', true);
            $.post("/mall/shop/addressajax", {_csrf:csrf, mobile: mobile, address: address, district: district,  user_id: user_id, user_name: user_name, address_id:address_id}, function(result) {
                var data = eval("(" + result + ")");
                if (data.res_code == 0) {
                    var location_href = "/mall/shop/confirmation";
                    window.location = location_href;
                } else {
                    alert(data.res_data);
                    $("#addressajax").removeAttr("disabled");
                    return false;
                }
            });
        });
    });
</script>