<script>
    $(function() {
        $('.icon_Rem').click(function() {
            $(this).siblings('input').prop('value', '');
        });

        $(".button button").click(function() {
            $('.Hmask').show();
            $('.layer_border').show();
        });
        //点击关闭按钮
        $('.layer_border .border_top').click(function() {
            $('.Hmask').hide();
            $('.layer_border').hide();
        });

    });
</script>
<div class="jdall">
    <div class="jdyyy studtyimg hengyames">
        <div class="dbk_inpL">
            <label>行业</label>  
            <select  name="industry" class="select" id="industry">
                <option value="0">请选择</option>
                <?php foreach ($industry as $key => $value): ?>
                    <option <?php echo!empty($user_extend) && $user_extend->industry == $key ? 'selected="selected"' : ""; ?> value="<?php echo $key; ?>"><?php echo $value[0]; ?></option>
                <?php endforeach; ?>
            </select>
            <img src="/images/dibujtou.png" class="dibujtou">
        </div>
        <div class="dbk_inpL">
            <label>职业</label>
            <select name="profession" id="profession">
                <option value="0">请选择</option>
                <?php foreach ($profession as $key => $value): ?>
                    <option <?php echo!empty($user_extend) && $user_extend->profession == $key ? 'selected="selected"' : ""; ?> value="<?php echo $key; ?>"><?php echo $value[0]; ?></option>
                <?php endforeach; ?>
            </select>
            <img src="/images/dibujtou.png" class="dibujtou">
        </div>
        <div class="dbk_inpL">
            <label>职务级别</label>
            <select name="position" id="position">
                <option value="0">请选择</option>
                <?php foreach ($position as $key => $value): ?>
                    <option <?php echo!empty($user_extend) && $user_extend->position == $key ? 'selected="selected"' : ""; ?> value="<?php echo $key; ?>"><?php echo $value[0]; ?></option>
                <?php endforeach; ?>
            </select>
            <img src="/images/dibujtou.png" class="dibujtou">
        </div>
        <div class="dbk_inpL">
            <label>月收入</label>
            <select name="income" id="income">
                <option <?php echo!empty($user_extend) && $user_extend->income == "2000以下" ? 'selected="selected"' : ""; ?>>2000以下</option>
                <option <?php echo!empty($user_extend) && $user_extend->income == "2000-2999" ? 'selected="selected"' : ""; ?>>2000-2999</option>
                <option <?php echo!empty($user_extend) && $user_extend->income == "3000-3999" ? 'selected="selected"' : ""; ?>>3000-3999</option>
                <option <?php echo!empty($user_extend) && $user_extend->income == "4000-4999" ? 'selected="selected"' : ""; ?>>4000-4999</option>
                <option <?php echo!empty($user_extend) && $user_extend->income == "5000以上" ? 'selected="selected"' : ""; ?>>5000以上</option>
            </select>
            <img src="/images/dibujtou.png" class="dibujtou">
        </div>

    </div>
    <div class="jdyyy danweimes">
        <div class="dbk_inpL">
            <label>单位名称</label><input value="<?php echo!empty($user_extend) ? $user_extend->company : ""; ?>" name="company" placeholder="如：先花信息技术（北京）有限公司" type="text">
            <img src="/images/icon_remove.png" class="icon_Rem">
            <input type="hidden" name="user_id" value="<?php echo $users->user_id; ?>" >
            <input type="hidden" name="from_url" value="<?php echo $from; ?>" >
        </div>
        <div class="dbk_inpL">
            <label>单位电话</label><input value="<?php echo!empty($user_extend) ? $user_extend->telephone : ""; ?>" name="telephone" placeholder="" type="text">
            <img src="/images/icon_remove.png" class="icon_Rem">
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
            <label>详细地址</label><input value="<?php echo!empty($user_extend) ? $user_extend->company_address : ""; ?>" name="address" placeholder="如：西城区新街口外大街28号" type="text">
            <img src="/images/icon_remove.png" class="icon_Rem">
        </div>

    </div>
    <div class="jdyyy danweimes">
        <div class="dbk_inpL">
            <label>邮箱</label><input value="<?php echo!empty($user_extend) ? $user_extend->email : ""; ?>" name="email" placeholder="如：xianhuahua@163.com" type="text">
            <img src="/images/icon_remove.png" class="icon_Rem">
        </div>
    </div>
    <!-- <div class="tsmes">*手机号错误</div> -->
    <div class="button"> <button id="reg_shtwo_form">提交</button></div>

</div>

<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
    $(function() {

        $(".selectList").each(function() {
            var areaJson = <?php echo $list; ?>;
            var default_code = "<?php echo!empty($user_extend) ? $user_extend->company_area : 0; ?>";
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
                }
                ;
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
    })
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
    wx.ready(function() {
        wx.hideOptionMenu();
    });
</script>