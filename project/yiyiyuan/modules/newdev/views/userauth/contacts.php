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
    .form-group .buymeg .seltxt{
        font-size: 1.2rem;
    }
    .form-group .Bortitle{
        width:15%;
    }
    .form-group{
        margin-bottom:0px;
        font-size:1.2rem;
        color:#444;
    }
    .selcol{
        color:#aaa;
    }
    .optcol{
        color:#444;
    }
    .jdyyy.ximgxtel .dbk_inpL input {
        line-height: 27px;
        width: 85%;
    }
</style>
<div class="jdall">
        <?php $csrf = \Yii::$app->request->getCsrfToken(); ?>
        <input  id="_csrf" name="_csrf" type="hidden" value="<?php echo $csrf; ?>">
        <input name ="orderinfo" type = "hidden" value="<?php echo $orderinfo ?>">
	<div class="jdyyy ximgxtel">
        <div class="dbk_inpL">
            <label>姓名</label>
            <input type="text" placeholder="请填写亲属联系人姓名" name="relatives_name" value="<?php echo isset($userinfo['favorite']['relatives_name']) ? $userinfo['favorite']['relatives_name'] : ''; ?>">
        </div>
       
        <!--修改样式及点击 START-->
        <div class="form-group dbk_inpL">
            <label>关系</label>
            <div class="buymeg">
                <select class="seltxt selcol" id="relation_family" name="relation_family">
                    <div>
                        <option class="optcol" value="0">请选择亲属联系人关系</option>
                        <option class="optcol" value="1" <?php echo!empty($userinfo['favorite']) && $userinfo['favorite']['relation_family'] == 1 ? 'selected="selected"' : ''; ?>>父母</option>
                        <option class="optcol" value="2" <?php echo!empty($userinfo['favorite']) && $userinfo['favorite']['relation_family'] == 2 ? 'selected="selected"' : ''; ?>>配偶</option>

                    </div>
                </select>
            </div>
        </div>
        <!---->
        <div class="dbk_inpL">
            <label>电话</label>
            <input type="text" maxlength="11" onkeyup="this.value=this.value.replace(/\D/g,'')" onafterpaste="this.value=this.value.replace(/\D/g,'')" placeholder="请填写亲属联系人电话" name="phone" value="<?php echo isset($userinfo['favorite']['phone']) ? $userinfo['favorite']['phone'] : ''; ?>">
        </div>
	</div>

    <div class="jdyyy ximgxtel">
         <div class="dbk_inpL">
            <label>姓名</label>
            <input type="text" placeholder="请填写常用联系人姓名" name="contacts_name" value="<?php echo isset($userinfo['favorite']['contacts_name']) ? $userinfo['favorite']['contacts_name'] : ''; ?>">
        </div>
        <!--修改样式及点击 START-->
        <div class="form-group dbk_inpL">
            <label>关系</label>
            <div class="buymeg">
                <select class="seltxt selcol" id="relation_common" name="relation_common">
                    <div>
                        <option class="optcol" value="0">请选择常用联系人关系</option>
                        <option class="optcol" value="1" <?php echo!empty($userinfo['favorite']) && $userinfo['favorite']['relation_common'] == 1 ? 'selected="selected"' : ''; ?>>朋友</option>
                        <option class="optcol" value="2" <?php echo!empty($userinfo['favorite']) && $userinfo['favorite']['relation_common'] == 2 ? 'selected="selected"' : ''; ?>>同事</option>
                        <option class="optcol" value="3" <?php echo!empty($userinfo['favorite']) && $userinfo['favorite']['relation_common'] == 3 ? 'selected="selected"' : ''; ?>>兄弟</option>
                        <option class="optcol" value="4" <?php echo!empty($userinfo['favorite']) && $userinfo['favorite']['relation_common'] == 4 ? 'selected="selected"' : ''; ?>>姐妹</option>
                        <option class="optcol" value="5" <?php echo!empty($userinfo['favorite']) && $userinfo['favorite']['relation_common'] == 5 ? 'selected="selected"' : ''; ?>>其他</option>
                    </div>
                </select>
            </div>
        </div>
        <!---->
        <div class="dbk_inpL">
            <label>电话</label>
            <input type="text" maxlength="11" onkeyup="this.value=this.value.replace(/\D/g,'')" onafterpaste="this.value=this.value.replace(/\D/g,'')" placeholder="请填写常用联系人电话" name="mobile" value="<?php echo isset($userinfo['favorite']['mobile']) ? $userinfo['favorite']['mobile'] : ''; ?>">
        </div>
    </div>
    
	<div class="tsmes" id="show_text"></div> 
	<div class="button"> <button id="sub">提交</button></div>
	
</div>

<script>
    //亲属联系人及家庭联系人关系样式控制
    $('#relation_family').change(function(){
        var relation_family_chk = $("#relation_family  option:selected").val();
        if(relation_family_chk != 0){
            $('#relation_family').removeClass('selcol');
        }else{
            $('#relation_family').addClass('selcol');
        }
    })
    $('#relation_common').change(function(){
        var relation_common_chk = $("#relation_common  option:selected").val();
        if(relation_common_chk != 0){
            $('#relation_common').removeClass('selcol');
        }else{
            $('#relation_common').addClass('selcol');
        }
    })
    var user_id = <?php echo $userinfo['user_id']; ?>;
    var csrf = $("#_csrf").val(); 
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
        var orderinfo = $('input[name="orderinfo"]').val();
        var reg = /^((1(([35678][0-9])|(47)))\d{8})|((0\d{2,3})\-?\d{7,8}(\-?\d{4})?)$/;
        if(relatives_name.trim().length === 0){
            $('#show_text').html('*请填写亲属联系人姓名');
            $('#show_text').show();
            return false;
        }
        
        if (!(relation_family > 0)) {
            $('#show_text').html('*请选择您与亲属联系人的关系!');
            $('#show_text').show();
            return false;
        }
        if(phone.trim().length === 0){
            $('#show_text').html('*请填写亲属联系人电话');
            $('#show_text').show();
            return false;
        }
        if(!reg.test(phone.trim())){
            $('#show_text').html('*请填写正确的亲属联系人电话');
            $('#show_text').show();
            return false;
        }
        if (contacts_name.trim().length === 0) {
            $('#show_text').html('*请填写常用联系人姓名');
            $('#show_text').show();
            return false;
        }
        if(!(relation_common > 0)){
            $('#show_text').html('*请选择您与常用联系人的关系');
            $('#show_text').show();
            return false;
        }
        if(mobile.trim().length === 0){
            $('#show_text').html('*请填写常用联系人电话');
            $('#show_text').show();
            return false;
        }
        if (!reg.test(mobile.trim())) {
            $('#show_text').html('*请填写正确的常用联系人电话');
            $('#show_text').show();
            return false;
        }
        $.post("/new/userauth/savecontacts", {_csrf:csrf, orderinfo:orderinfo, relation_family: relation_family, relation_common: relation_common, user_id: user_id, contacts_name: contacts_name, mobile: mobile, relatives_name: relatives_name, phone: phone}, function (data) {
            var data = eval("(" + data + ")");
            if (data.res_code == '0') {
                $('#show_text').hide();
                $('#show_text').html('');
                if(data.res_data.current_url == "" || data.res_data.orderinfo == ""){
                    window.location = '/new/loan';
                    return false;
                }
                var curl = data.res_data.current_url;
                if(curl.indexOf("banktype") > 0 ){
                    var location_href = data.res_data.current_url +'&orderinfo='+ data.res_data.orderinfo;
                }else{
                    var location_href = data.res_data.current_url +'?orderinfo='+ data.res_data.orderinfo;
                }
                window.location = location_href;
            } else if (data.res_code == '4') {
                $('#show_text').html('*数据没有更改,请更新之后提交');
                $('#show_text').show();
                return false;
            } else {
                $('#show_text').html('*提交失败');
                $('#show_text').show();
                return false;
            }
        });
    });
</script>