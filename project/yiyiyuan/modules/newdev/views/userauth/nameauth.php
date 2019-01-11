<?php

use app\commonapi\ImageHandler;

$uploadurl = ImageHandler::$img_upload;
$imgurl    = ImageHandler::$img_domain;
?>
<div class="jdall">
    <div class="my_ziliao">
        <h3>拍摄并上传个人身份证</h3>

        <?php
        $fontsrc   = isset($userinfo->password) && !empty($userinfo->password->iden_url) ? $imgurl . $userinfo->password->iden_url : '/h5/images/sfz_one.png';
        $backsrc   = !empty($userinfo->pic_self) ? $imgurl . $userinfo->pic_self : '/h5/images/sfz_two.jpg';
        ?>

        <div class="sfenzeng">
            <div class="sfz_one" style="position: relative;">
                <img id="supply1" src="<?= $fontsrc ?>">
                <p>身份证正面(人像面)</p>
                <input id="upload" <?php echo $identity_valid == 1 ? 'disabled' : '' ?> name="file" capture="camera" accept="image/*" type="file" style="display: none">
                <input id="fontUrl" hidden  name="iden_url"  value='<?php echo isset($userinfo->password) && !empty($userinfo->password->iden_url) ? $userinfo->password->iden_url : '' ?>'>
                <div hidden id="fontReset" style="position:absolute;top: 35%;width: 30%;margin: 0 35%; height: auto;"><img src="/h5/images/congpai.png"></div>
            </div>
            <div class="sfz_one"  style="position: relative;">
                <img id="supply" src="<?= $backsrc ?>">
                <p>身份证反面(国徽面)</p>
                <input id="upload1" <?php echo $identity_valid == 1 && $backiden ? 'disabled' : '' ?>  name="file" capture="camera" accept="image/*" type="file" style="display: none">
                <input id="backUrl" hidden  name="pic_self" value='<?php echo!empty($userinfo->pic_self) ? $userinfo->pic_self : '' ?>'>
                <div hidden id="backReset" style="position: absolute;top: 35%;width: 30%;margin: 0 35%; height: auto;"><img src="/h5/images/congpai.png"></div>
            </div>
        </div>
        <h3 style="padding-bottom:0;">其他身份信息</h3>
    </div>
    <div class="jdyyy danweimes">
        <input type="hidden" id='isOcr' value="0" hidden>
        <input id='nation' name="nation" type="text" value="<?php echo $identity_valid == 1 && isset($userinfo->password) ? $userinfo->password->nation : '' ?>" hidden>
        <input id='iden_address' name="iden_address" type="text" value="<?php echo $identity_valid == 1 && isset($userinfo->password) ? $userinfo->password->iden_address : '' ?>" hidden>

    </div>
    <div class="jdyyy danweimes">
        <div class="dbk_inpL">
            <label>姓名</label>
            <?php if ($identity_valid == 1): ?>
                <?php echo $userinfo->realname; ?>
                <input name="realname" placeholder="姓名" type="hidden" value="<?php echo $userinfo->realname; ?>">
            <?php else: ?>
                <input name="realname" value="<?php echo!empty($userinfo->realname) ? $userinfo->realname : ''; ?>" placeholder="姓名" type="text">
                <img src="/images/icon_remove.png" class="icon_Rem">
            <?php endif; ?>
        </div>
        <div class="dbk_inpL">
            <label>身份证号</label>
            <?php if ($identity_valid == 1): ?>
                <?php echo substr($userinfo->identity, 0, 4) . "**********" . substr($userinfo->identity, 14, 4); ?>
                <input name="identity" placeholder="身份证号" type="hidden" value="<?php echo $userinfo->identity; ?>">
            <?php else: ?>
                <input name="identity" placeholder="身份证号" value="<?php echo !empty($userinfo->identity) ? $userinfo->identity: ''; ?>" type="text">
                <img src="/images/icon_remove.png" class="icon_Rem">
            <?php endif; ?>
        </div>
    </div>

    <style>
        .jdyyy .dbk_inpL .selectList select {
            width: 24%;
            font-size: 1rem;
        }
    </style>

    <div class="jdyyy danweimes">
        <div class="dbk_inpL">
            <label>常住地址</label>
            <div class="selectList">
                <select class="province">
                    <option style="">请选择</option>
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
            <label>详细地址</label><input name="home_address" placeholder="如：西城区新街口外大街28号" type="text" value="<?php echo!empty($userinfo->extend) ? $userinfo->extend->home_address : ""; ?>">
            <img src="/images/icon_remove.png" class="icon_Rem">
        </div>
    </div>

    <div class="jdyyy danweimes">
        <div class="dbk_inpL">
            <label>学历</label>
            <select name="edu">
                <option value="0">请选择</option>
                <?php foreach ($edu as $key => $val): ?>
                    <option <?php if (!empty($userinfo->extend) && $key == $userinfo->extend->edu): ?>selected="selected"<?php endif; ?> value="<?php echo $key; ?>"><?php echo $val[0] ?></option>
                <?php endforeach; ?>
            </select>
            <img src="/images/dibujtou.png" class="dibujtou">
        </div>
        <div class="dbk_inpL">
            <label>婚姻</label>
            <select name="marriage">
                <option value="0">请选择</option>
                <?php foreach ($marriage as $key => $val): ?>
                    <option <?php if (!empty($userinfo->extend) && $key == $userinfo->extend->marriage): ?>selected="selected"<?php endif; ?> value="<?php echo $key; ?>"><?php echo $val[0] ?></option>
                <?php endforeach; ?>
            </select>
            <img src="/images/dibujtou.png" class="dibujtou">
        </div>
    </div>
    <div id="errormsg" style="color: red; font-size: 12px; margin-left: 5px;"></div>
    <div class="button"> <button id="bank_form">提交</button></div>
    <!--    <div class="zyzsty" style="padding: 0 7%;color: #8a8a8a;">-->
    <!--        <input type="checkbox" checked="checked">-->
    <!--        领取意外保险 | <span style="color: #c2c2c2;">查看<a href="/new/agreeloan/wapactivity" style="color: #c2c2c2;">《活动规则》</a>及<a href="/new/agreeloan/wapsafety" style="color: #c2c2c2;">《信息安全说明》</a></span>-->
    <!--    </div>-->
</div>
<div id="fontmask" class="Hmask" hidden></div>
<div id="fontDiv" class="dl_tcym" hidden>
    <img class="tccicon" src="/h5/images/tccicon.png">
    <h3>请上传身份证反面（国徽面）</h3>
    <p class="btbuy_false">您的身份证正面照片已上传</p>
    <button id="dofontUpload">上传</button>
</div>
<div id="backmask" class="Hmask" hidden></div>
<div id="backDiv" class="dl_tcym" hidden>
    <img class="tccicon" src="/h5/images/tccicon.png">
    <h3>请上传身份证正面（人像面）</h3>
    <p class="btbuy_false">您的身份证反面照片已上传</p>
    <button id="dobackUpload">上传</button>
</div>
<div id='backSuccess' hidden class="jiebangcg">您的身份证反面（国徽面）已上传</div>
<div id='fontSuccess' hidden class="jiebangcg">您的身份证正面（人像面）已上传</div>

<div id="uploading" hidden>
    <div class="Hmask" ></div>
    <div class="shareone" >
        <p>图片上传中,请稍后</p >
    </div>
</div>
<style>
    .shareone{ position: fixed;top: 25%;width:70%;left: 15%;z-index: 100; }
    .shareone p{ width:100%; text-align: center; background: #fff;font-size:1.1rem;padding: 15px 0;border-radius: 5px;}
</style>

<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js?m=v10"></script>
<script src="/js/upload/imgupload.js?m=v10" type="text/javascript"></script>
<script type="text/javascript">
    $(function () {
        //控制删除详细地址input
        $('.icon_Rem').click(function () {
            $(this).siblings('input').prop('value', '');
        });

        //省市联动
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
            var csrf = '<?php echo $csrf; ?>';
            var marriage = $('select[name="marriage"]').val();
            var edu = $('select[name="edu"]').val();
            var district = $('.district').val();
            var realname = $('input[name="realname"]').val();
            var identity = $('input[name="identity"]').val();
            var home_address = $('input[name="home_address"]').val();
            $('#errormsg').html('');

            //edit by yjl 2018-05-16
            var iden_url = $('input[name="iden_url"]').val();
            var pic_self = $('input[name="pic_self"]').val();
            var nation = $('input[name="nation"]').val();
            var iden_address = $('input[name="iden_address"]').val();
            var isOcr = $("#isOcr").val();

            if (!iden_url) {
                $('#errormsg').html('*请上传身份证正面照片');
                return false;
            }
            if (!pic_self) {
                $('#errormsg').html('*请上传身份证反面照片');
                return false;
            }
            if (realname == '') {
                $('#errormsg').html('*请填写你的真实姓名');
                return false;
            }
            if (!checkregisteridentity(identity)) {
                $('#errormsg').html('*请填写正确的身份证号码');
                return false;
            }
            if (home_address == '') {
                $('#errormsg').html('*请填写详细地址');
                return false;
            }
            if (edu == 0) {
                $('#errormsg').html('*请选择你的学历');
                return false;
            }

            if (marriage == 0) {
                $('#errormsg').html('*请选择你的婚姻');
                return false;
            }
            $.post("/new/userauth/nameauthajax", {_csrf: csrf, userId: userId, realname: realname, identity: identity, district: district, home_address: home_address, marriage: marriage, edu: edu, iden_url: iden_url, pic_self: pic_self, iden_address: iden_address, nation: nation, ocr_api: isOcr}, function (result) {
                var data = eval("(" + result + ")");
                if (data.res_code == 0) {
                    var location_href = "<?php echo $redirect_info['nextPage'] . "?" . $redirect_info['orderinfo'] ?>";
                    window.location = location_href;
                } else {
                    $('#errormsg').html(data.res_data);
                    $("#isOcr").val('0');
                    return false;
                }
            });
        });

        //身份证正面上传js
        frontCanClick = true;
        backCanClick = true;
        $("#supply1").click(function (event) {
            event.preventDefault();
            if (!frontCanClick) {
                return false;
            }
            $("#upload").click(); //隐藏了input:file样式后，点击头像就可以本地上传
            $("#upload").on("change", function () {
                //用于释放change的统计次数
                var thatFun = arguments.callee;  //
                $(this).unbind("change", thatFun);

                var oFile = new FileReader();
                if (!this.files[0]) {
                    return false;
                }
                frontCanClick = false;
                $("#fontUrl").val('');
                $("#uploading").show();

                oFile.readAsDataURL(this.files[0]);
                oFile.onload = function (e) {
                    var text = oFile.result;
                    var html = '<div id="supply1_group"><input id="supply1_url" name="supply1[url]" type="hidden" value=""><input id="supply1_base64" name="supply1[base64]" type="hidden" value="' + text + '"><input id="supply1_file" name="supply1[file]" type="file" style="display:none;"></div>';
                    $("#supply1_group").remove();
                    $("#uploadImgForm").append(html);
                    $("#supply1").attr("src", text);
                    oUpload.save();
                }

            });

        });
        //身份证反面js
        $("#supply").click(function (eventone) {
            eventone.preventDefault();
            if (!backCanClick) {
                return false;
            }
            $("#backSuccess").hide();
            $("#upload1").click(); //隐藏了input:file样式后，点击头像就可以本地上传
            $("#upload1").on("change", function () {
                //用于释放change的统计次数
                var thatFun = arguments.callee;  //
                $(this).unbind("change", thatFun);

                var oFile = new FileReader();
                if (!this.files[0]) {
                    return false;
                }
                backCanClick = false;
                $("#backUrl").val('');
                $("#uploading").show();
                oFile.readAsDataURL(this.files[0]);
                oFile.onload = function (e) {
                    var text = oFile.result;
                    var html = '<div id="supply_group"><input id="supply_url" name="supply[url]" type="hidden" value=""><input id="supply_base64" name="supply[base64]" type="hidden" value="' + text + '"><input id="supply_file" name="supply[file]" type="file" style="display:none;"></div>';
                    $("#supply_group").remove();
                    $("#uploadImgForm1").append(html);
                    $("#supply").attr("src", text);
                    oUpload1.save();
                }

            });

        });

        var showErr = function () {
            $('#errormsg').html('*请选择常住地址');
        }

        //正面上传成功回调
        var fnAfter = function (data) {
            frontCanClick = true;
            var ok = data && parseInt(data.res_code, 10) === 0;
            var urls = data.res_data.supply1;
            var csrf = '<?php echo $csrf; ?>';
            $.post("/new/userauth/idenfontajax", {_csrf: csrf, urls: urls, type: 1}, function (result) {
                $("#uploading").hide();
                var data = eval("(" + result + ")");
                if (data.res_code == 0) {
                    $("#fontUrl").val(urls); //正面url  input赋值
                    $("#nation").val(data.res_data.nation);  //民族赋值
                    $("#iden_address").val(data.res_data.iden_address); //身份证地址赋值
                    $('input[name="realname"]').val(data.res_data.realname) //姓名赋值
                    $('input[name="identity"]').val(data.res_data.identity) //身份证赋值
                    $("#isOcr").val('1');  //设置调用过OCR
                    var backUrl = ($("#backUrl").val());  //判断反面是否上传
                    if (!backUrl) {  //反面未上传 显示上传弹层
                        $("#fontDiv").show();
                        $("#fontmask").show();
                    } else {  //反面已上传 显示正面成功弹层
                        $("#fontSuccess").show();
                        hideDiv('fontSuccess');
                    }
                    $("#fontReset").hide();
                    $('#errormsg').html('');
                } else {
                    $('#errormsg').html(data.res_data.msg);
                    $("#fontmask").hide();
                    $("#fontDiv").hide();
                    $("#fontReset").show();  //显示重拍
                    return false;
                }
            });
        }
        var fnAfter1 = function (data) {
            backCanClick = true;
            var ok = data && parseInt(data.res_code, 10) === 0;
            // 写入到本地表单中
            var urls = data.res_data.supply;
            var csrf = '<?php echo $csrf; ?>';
            $.post("/new/userauth/idenfontajax", {_csrf: csrf, urls: urls, type: 2}, function (result) {
                $("#uploading").hide();
                var data = eval("(" + result + ")");
                if (data.res_code == 0) {
                    $("#backUrl").val(urls);
                    $("#backReset").hide();
                    $("#isOcr").val('1');

                    var fontUrl = ($("#fontUrl").val());
                    if (!fontUrl) {
                        $("#backDiv").show();
                        $("#backmask").show();
                    } else {
                        $("#backSuccess").show();
                        hideDiv('backSuccess');
                    }
                    $('#errormsg').html('');
                } else {
                    $('#errormsg').html(data.res_data.msg);
                    $("#backReset").show();
                    return false;
                }
            });
        }

        ImageUpload.prototype.beforeSave = function () {
            var me = this;
            var result = false;
            var id, v;
console.dir(me);
            for (var k in me.ids) {
                id = me.ids[k];
                //alert(id);return false;
                if (document.getElementById(id + "_base64").value ||
                        document.getElementById(id + "_file").value) {
                    result = true;
                }
            }
            result = true;
            if (!result) {
                me.error("-20000", "至少上传一张图片");
            }
            return result;
        }
        /**
         * 提交到服务器
         */
        ImageUpload.prototype.save = function () {
            var me = this;
            //1 提交前
            var result = me.beforeSave();
            if (!result) {
                return false;
            }

            //2 使用 iframe 提交
            var oForm = me.oImageForm.oForm[0];
            if (!window.FileReader) {// 非html5 时
                oForm.enctype = "multipart/form-data";//enctype : 
            }

            if (me.onupload) {
                me.onupload();
            }
            iframepost(oForm, me.afterSave);
        }

        //关闭按钮点击
        $(".tccicon").click(function () {
            $("#fontDiv").hide();
            $("#fontmask").hide();
            $("#backDiv").hide();
            $("#backmask").hide();
        })

        //上传按钮点击
        $("#dobackUpload").click(function () {
            $("#backmask").hide();
            $("#backDiv").hide();
            $("#supply1").click();
        })
        $("#dofontUpload").click(function () {
            $("#fontmask").hide();
            $("#fontDiv").hide();
            $("#supply").click();
        })
        $("#fontReset").click(function () {
            $("#supply1").click();
        })
        $("#backReset").click(function () {
            $("#supply").click();
        })

        //2秒隐藏上传成功提示框
        function hideDiv(id)
        {
            var obj = $("#" + id);
            setTimeout(function () {
                obj.hide();
            }, 2000);
        }
        var oUpload = new ImageUpload({
            "formid": "uploadImgForm",
            'action': "<?= $uploadurl ?>/upload",
            "encrypt": "<?= $encrypt ?>",
            "error": showErr,
            'afterSave': fnAfter,
            'onupload': function () {
                //$("#btok").html("正在上传中");
            }
        });
        var oUpload1 = new ImageUpload({
            "formid": "uploadImgForm1",
            'action': "<?= $uploadurl ?>/upload",
            "encrypt": "<?= $encrypt ?>",
            "error": showErr,
            'afterSave': fnAfter1,
            'onupload': function () {
                //$("#btok").html("正在上传中");
            }
        });
    });
    //微信参数
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