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
<div class="jdall">
    <div class="jdyyy ximgxtel">
        <div class="dbk_inpL">
            <label>姓名</label><input type="text" placeholder="输入常用联系人姓名" name="relatives_name" value="<?php echo isset($fav['relatives_name']) ? $fav['relatives_name'] : ''; ?>">
            <img src="/images/icon_remove.png" class="icon_Rem">
        </div>

        <div class="dbk_inpL">
            <label>关系</label>
            <select id="relation_family" name="relation_family">
                <option value="0">请选择</option>
                <option value="1" <?php echo!empty($fav) && $fav['relation_family'] == 1 ? 'selected="selected"' : ''; ?>>父母</option>
                <option value="2" <?php echo!empty($fav) && $fav['relation_family'] == 2 ? 'selected="selected"' : ''; ?>>配偶</option>
            </select>
            <img src="/images/dibujtou.png" class="dibujtou">
        </div>
        <div class="dbk_inpL">
            <label>电话</label><input type="text" placeholder="输入亲属联系人电话号码" name="phone" value="<?php echo isset($fav['phone']) ? $fav['phone'] : ''; ?>">
            <img src="/images/icon_remove.png" class="icon_Rem">
        </div>
    </div>

    <div class="jdyyy ximgxtel">
        <div class="dbk_inpL">
            <label>姓名</label><input type="text" placeholder="输入常用联系人姓名" name="contacts_name" value="<?php echo isset($fav['contacts_name']) ? $fav['contacts_name'] : ''; ?>">
            <img src="/images/icon_remove.png" class="icon_Rem">
        </div>

        <div class="dbk_inpL">
            <label>关系</label>
            <select id="relation_common" name="relation_common">
                <option value="0">请选择</option>
                <option value="1" <?php echo!empty($fav) && $fav['relation_common'] == 1 ? 'selected="selected"' : ''; ?>>朋友</option>
                <option value="2" <?php echo!empty($fav) && $fav['relation_common'] == 2 ? 'selected="selected"' : ''; ?>>同事</option>
                <option value="3" <?php echo!empty($fav) && $fav['relation_common'] == 3 ? 'selected="selected"' : ''; ?>>兄弟</option>
                <option value="4" <?php echo!empty($fav) && $fav['relation_common'] == 4 ? 'selected="selected"' : ''; ?>>姐妹</option>
                <option value="5" <?php echo!empty($fav) && $fav['relation_common'] == 5 ? 'selected="selected"' : ''; ?>>其他</option>
            </select>
            <img src="/images/dibujtou.png" class="dibujtou">
        </div>
        <div class="dbk_inpL">
            <label>电话</label><input type="text" placeholder="输入联系人电话号码" name="mobile" value="<?php echo isset($fav['mobile']) ? $fav['mobile'] : ''; ?>">
            <img src="/images/icon_remove.png" class="icon_Rem">
        </div>
    </div>

    <div class="tsmes" id="show_text"></div> 
    <div class="button"> <button id="sub">提交</button></div>

</div>


<script>
    var user_id = <?php echo $user_id; ?>;
    $('input[name="contacts_name"]').blur(function () {
        var contacts_name = $(this).val();
        if (contacts_name.trim().length !== 0) {
            $(".contacts_name").css('display', '');
        } else {
            $(".contacts_name").css('display', 'none');
        }
    });
    $('input[name="mobile"]').blur(function () {
        var mobile = $(this).val();
        if (mobile.trim().length !== 0) {
            $(".mobile").css('display', '');
        } else {
            $(".mobile").css('display', 'none');
        }
    });
    $('input[name="relatives_name"]').blur(function () {
        var relatives_name = $(this).val();
        if (relatives_name.trim().length !== 0) {
            $(".relatives_name").css('display', '');
        } else {
            $(".relatives_name").css('display', 'none');
        }
    });
    $('input[name="phone"]').blur(function () {
        var phone = $(this).val();
        if (phone.trim().length !== 0) {
            $(".phone").css('display', '');
        } else {
            $(".phone").css('display', 'none');
        }
    });
    $(".contacts_name").click(function () {
        $('input[name="contacts_name"]').val("");
        $('input[name="contacts_name"]').focus();
    });
    $(".mobile").click(function () {
        $('input[name="mobile"]').val("");
        $('input[name="mobile"]').focus();
    });
    $(".relatives_name").click(function () {
        $('input[name="relatives_name"]').val("");
        $('input[name="relatives_name"]').focus();
    });
    $(".phone").click(function () {
        $('input[name="phone"]').val("");
        $('input[name="phone"]').focus();
    });
    $('#sub').click(function () {
        var contacts_name = $('input[name="contacts_name"]').val();
        var relation_family = $("#relation_family  option:selected").val();
        var relation_common = $("#relation_common  option:selected").val();
        var mobile = $('input[name="mobile"]').val();
        var relatives_name = $('input[name="relatives_name"]').val();
        var phone = $('input[name="phone"]').val();
        if (contacts_name.trim().length === 0 || relatives_name.trim().length === 0) {
            alert('请输入相关信息');
            return false;
        }
        var reg = /^((1(([3578][0-9])|(47)))\d{8})|((0\d{2,3})\-?\d{7,8}(\-?\d{4})?)$/;
        if (!reg.test(mobile.trim()) || !reg.test(phone.trim())) {
            alert('请输入正确的手机或者座机号码!');
            return false;
        }
        if (!(relation_family > 0) || !(relation_common > 0)) {
            alert('请选择亲属朋友关系!');
            return false;
        }
        $.post("/dev/reg/savecontacts", {relation_family: relation_family, relation_common: relation_common, user_id: user_id, contacts_name: contacts_name, mobile: mobile, relatives_name: relatives_name, phone: phone}, function (data) {
            ;
            var data = eval("(" + data + ")");
            if (data.code == '0') {
                $('#show_text').hide();
                $('#show_text').html('');
                window.location = data.url;
            } else if (data.code == '4') {
                $('#show_text').html('数据没有更改,请更新之后提交');
                $('#show_text').show();
                return false;
            } else {
                $('#show_text').html('提交失败');
                $('#show_text').show();
                return false;
            }
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