<div class="wraper">
    <?php $csrf = \Yii::$app->request->getCsrfToken(); ?>
    <input  id="_csrf" name="_csrf" type="hidden" value="<?php echo $csrf; ?>">
    <!-- 进度条 -->
    <section class="process-bar">
        <div class="prcess"></div>
        <!-- 进度条控制css样式.process-bar .prcess 的width属性20%,40%,60%....100% -->
        <span></span>
        <span></span>
        <span></span>
        <span></span>
    </section>
    <div class="p_contact_hr" style="width: auto">
        第一联系人
    </div>
    <div class="per_info">
        <div class="com_reg">
            <label>关系</label>
            <input id="relation_family"  value="" type="text" placeholder="请选择与联系人关系">
            <input name="relation_family" value="" type="hidden">
            <img src="/borrow/310/images/left_icon.png">
            <p id="family_error" hidden>*请选择与联系人关系</p>
        </div>
        <div class="com_reg">
            <label>姓名</label>
            <input type="text" id="one_name"  placeholder="请填写亲属联系人姓名" name="relatives_name" value="<?php echo isset($user_info['favorite']['relatives_name']) ? $user_info['favorite']['relatives_name'] : ''; ?>">
            <p id="name_error" hidden>*请输入真实联系人姓名</p>
        </div>
        <div class="com_reg">
            <label>电话</label>
            <input type="text" id="one_mobile" onclick="one_mobile_input()" maxlength="11" onkeyup="this.value=this.value.replace(/\D/g,'')" onafterpaste="this.value=this.value.replace(/\D/g,'')" placeholder="请填写亲属联系人电话" name="phone" value="<?php echo isset($user_info['favorite']['phone']) ? $user_info['favorite']['phone'] : ''; ?>">
            <p id="phone_error" hidden>*请输入正确的手机号</p>
        </div>
    </div>
    <div class="p_contact_hr">
        第二联系人
    </div>
    <div class="per_info">
        <div class="com_reg">
            <label>关系</label>
            <input id="relation_common" type="text" placeholder="请选择与联系人关系">
            <input name="relation_common" value="" type="hidden">
            <img src="/borrow/310/images/left_icon.png">
            <p id="common_error" hidden>*请选择与联系人关系</p>
        </div>
        <div class="com_reg">
            <label>姓名</label>
            <input type="text" id="second_name" onclick="second_name_input()" placeholder="请填写常用联系人姓名" name="contacts_name" value="<?php echo isset($user_info['favorite']['contacts_name']) ? $user_info['favorite']['contacts_name'] : ''; ?>">
            <p id="contacts_error" hidden>*请输入真实联系人姓名</p>
        </div>
        <div class="com_reg">
            <label>电话</label>
            <input type="text" id="second_mobile" onclick="second_mobile_input()" maxlength="11" onkeyup="this.value=this.value.replace(/\D/g,'')" onafterpaste="this.value=this.value.replace(/\D/g,'')" placeholder="请填写常用联系人电话" name="mobile" value="<?php echo isset($user_info['favorite']['mobile']) ? $user_info['favorite']['mobile'] : ''; ?>">
            <p id="mobile_error" hidden>*请输入正确的手机号</p>
        </div>
    </div>
    <button class="big345-button wzg-gz-btn" id="sub">提交</button>
    <p class="p_contact_txt">非紧急情况不会拨打以上电话</p>
    <span class="rz-foot-txt" hidden>获取帮助</span>
    <!--  弹窗-->
    <div class="p_con_tc maskclose" id="maskclose" hidden></div>
    <div class="p_mask_box tcclose" id="tcclose" hidden>
        <img src="/borrow/310/images/bill-close.png" alt="" class="close_mask">
        <p class="p_mask_title">温馨提示</p>
        <p class="p_mask_text">资料尚未完善，确认放弃填写？</p>
        <span class="p_resure_btn p_btn_com">放弃</span>
        <span class="p_resureOut_btn p_btn_com">继续填写</span>
    </div>
</div>
<style>
    .help_service{
        margin-top: 3.5rem;
        width: 100%;
        bottom: 1.81rem;
        height: 0.37rem;
        text-align: center;
    }
    .contact_service_tip{
        width: 0.40rem;
        height: 0.43rem;
        margin-right: 1.5rem;
    }
    .contact_service_text{
        height: 0.37rem;
        position: absolute;
        left:4.59rem;
        font-family: "微软雅黑";
        font-size: 0.37rem;
        color: #3D81FF;
        letter-spacing: 0;
        line-height: 0.43rem;
    }
</style>
<div class="help_service">
    <img src="/borrow/310/images/tip.png" alt="" class="contact_service_tip">
    <a href="/borrow/helpcenter/list?position=4&user_id=<?php echo $user_info['user_id'];?>"><span class="contact_service_text">获取帮助</span></a>
</div>
<div class="toast_tishi" id="toast_tishi" hidden>提交失败</div>
<script src="/290/js/jquery-1.10.1.min.js"></script>
<script src="/borrow/310/js/picker.js"></script>
<script>
    <?php \app\common\PLogger::getInstance('weixin','',$user_info['user_id']); ?>
        <?php $json_data = \app\common\PLogger::getJson();?>
        var baseInfoss = eval( '('  + '<?php echo $json_data; ?>' + ')' );
    $(function(){
        
        
        var family = "<?php echo !empty($user_info['favorite']) && !empty($user_info['favorite']['relation_family']) ? $user_info['favorite']['relation_family'] : 0 ?>";
        var relation_f = '<?php echo $relation_family ?>';
        var relation_family = eval("(" + relation_f + ")");
        default_family = family == 0 ? '' : relation_family[family-1];
        $('#relation_family').val(default_family);
        $("input[name='relation_family']").val(family);
        $.scrEvent({
            data: relation_family,//数据
            evEle: '#relation_family',//选择器
            title: '选择关系',//标题
            defValue: default_family,//默认值
            afterAction: function(data) {//点击确定按钮后,执行的动作
                tongji('requireinfo_contacts_one_relation',baseInfoss);
                $('#relation_family').val(data);
                $key = getKey(relation_family,data);
                $("input[name='relation_family']").val($key+1);
            }
        });
        var common = "<?php echo !empty($user_info['favorite']) && !empty($user_info['favorite']['relation_common']) ? $user_info['favorite']['relation_common'] : 0 ?>";
        var relation_c = '<?php echo $relation_common ?>';
        var relation_common = eval("(" + relation_c + ")");
        default_common =  common == 0 ? '' : relation_common[common-1];
        $('#relation_common').val(default_common);
        $("input[name='relation_common']").val(common);
        $.scrEvent({
            data: relation_common,//数据
            evEle: '#relation_common',//选择器
            title: '选择关系',//标题
            defValue: default_common,//默认值
            afterAction: function(data) {//点击确定按钮后,执行的动作
                tongji('requireinfo_contacts_second_relation',baseInfoss);
                $('#relation_common').val(data);
                $key = getKey(relation_common,data);
                $("input[name='relation_common']").val($key+1);
            }
        });
    });

    
    function getKey(relation_family,default_family) {
        var length = relation_family.length;
        var i = 0;
        for(i;i<length;i++) {
            if(relation_family[i] == default_family){
                return i;
            }
        }
    }

    $('#sub').click(function () {
        zhuge.track('联系人认证页面-提交按钮');
        tongji('requireinfo_contacts_submit',baseInfoss);
        var csrf = '<?php echo $csrf; ?>';
        var user_id = '<?php echo $user_info['user_id']; ?>';
        var contacts_name = $('input[name="contacts_name"]').val();
        var relation_family = $('input[name="relation_family"]').val();
        var relation_common = $('input[name="relation_common"]').val();
        var mobile = $('input[name="mobile"]').val();
        var relatives_name = $('input[name="relatives_name"]').val();
        var phone = $('input[name="phone"]').val();
        var reg = /^((1(([35678][0-9])|(47)))\d{8})|((0\d{2,3})\-?\d{7,8}(\-?\d{4})?)$/;
        if(relatives_name.trim().length === 0){
            $('#name_error').show();
            return false;
        }
        if (!(relation_family > 0)) {
            $('#family_error').show();
            return false;
        }
        if(phone.trim().length === 0 || !reg.test(phone.trim())){
            $('#phone_error').show();
            return false;
        }

        if (contacts_name.trim().length === 0) {
            $('#contacts_error').show();
            return false;
        }
        if(!(relation_common > 0)){
            $('#common_error').show();
            return false;
        }
        if(mobile.trim().length === 0 || !reg.test(mobile.trim())){
            $('#mobile_error').show();
            return false;
        }

        $.post("/borrow/userauth/savecontacts", {_csrf:csrf,  relation_family: relation_family, relation_common: relation_common, user_id: user_id, contacts_name: contacts_name, mobile: mobile, relatives_name: relatives_name, phone: phone}, function (data) {
            var data = eval("(" + data + ")");
            //alert(data.res_code);return false;
            if (data.res_code == '0') {
                zhuge.identify(user_id, {
                    '联系人信息已认证': 1,  // 0表示false，1表示ture，下同
                });
                $('#show_text').hide();
                $('#show_text').html('');
                if(data.res_data.current_url == ""){
                    window.location = '/borrow/loan';
                    return false;
                }
                var location_href = "<?php echo $redirect_info; ?>";
                window.location = location_href;
            } else if (data.res_code == '4') {
                $('#maskclose').show();
                $('#tcclose').show();
                return false;
            } else {
                $('#toast_tishi').html('提交失败');
                $('#toast_tishi').show();
                hideDiv('toast_tishi');
                return false;
            }
        });
    });

    $('.close_mask').click(function (){
        $('#maskclose').hide();
        $('#tcclose').hide();
    });

    $('.p_resure_btn').click(function (){
        zhuge.track('退出认证-放弃');
        tongji('requireinfo_contacts_cancel',baseInfoss);
        setTimeout(function(){
              window.location = '/borrow/userinfo/requireinfo';
          },100);
        
    });

    $('.p_resureOut_btn').click(function (){
        zhuge.track('退出认证-继续填写');
         tongji('requireinfo_contacts_no_cancel',baseInfoss);
        $('#maskclose').hide();
        $('#tcclose').hide();
    });

    //2秒隐藏上传成功提示框
    function hideDiv(id) {
        var obj = $("#" + id);
        setTimeout(function () {
            obj.hide();
        }, 2000);

    }

//    function one_mobile_input(){
//       tongji('requireinfo_contacts_one_mobile_input');
//    }
//    function second_name_input(){
//       tongji('requireinfo_contacts_second_name_input');
//    }
//    function second_mobile_input(){
//       tongji('requireinfo_contacts_second_mobile_input');
//    }
    
    $('#one_name').bind({ 
          blur:function(){
              tongji('requireinfo_contacts_one_name_input');
          }
    });
   
    $('#one_mobile').bind({ 
          blur:function(){
                tongji('requireinfo_contacts_one_mobile_input');
          }
    });
    $('#second_name').bind({ 
          blur:function(){
                tongji('requireinfo_contacts_second_name_input');
          }
    });
    $('#second_mobile').bind({ 
          blur:function(){
                tongji('requireinfo_contacts_second_mobile_input');
          }
    });
</script>
<script>
    function tongji(event) {
        <?php \app\common\PLogger::getInstance('weixin','',$user_id); ?>
        <?php $json_data = \app\common\PLogger::getJson();?>
        var baseInfoss = eval( '('  + '<?php echo $json_data; ?>' + ')' );
        baseInfoss.url = baseInfoss.url+'&event='+event;
        // console.log(baseInfoss);
        var ortherInfo = {
            screen_height: window.screen.height,//分辨率高
            screen_width: window.screen.width,  //分辨率宽
            user_agent: navigator.userAgent,
            height: document.documentElement.clientHeight || document.body.clientHeight,  //网页可见区域宽
            width: document.documentElement.clientWidth || document.body.clientWidth,//网页可见区域高
        };
        var baseInfos = Object.assign(baseInfoss, ortherInfo);

        var turnForm = document.createElement("form");
        turnForm.id = "uploadImgForm";
        turnForm.name = "uploadImgForm";
        document.body.appendChild(turnForm);
        turnForm.method = 'post';
        turnForm.action = baseInfoss.log_url+'weixin';
        //创建隐藏表单
        for (var i in baseInfos) {
            var newElement = document.createElement("input");
            newElement.setAttribute("name",i);
            newElement.setAttribute("type","hidden");
            newElement.setAttribute("value",baseInfos[i]);
            turnForm.appendChild(newElement);
        }
        var iframeid = 'if' + Math.floor(Math.random( 999 )*100 + 100) + (new Date().getTime() + '').substr(5,8);
        var iframe = document.createElement('iframe');
        iframe.style.display = 'none';
        iframe.id = iframeid;
        iframe.name = iframeid;
        iframe.src = "about:blank";
        document.body.appendChild( iframe );
        turnForm.setAttribute("target",iframeid);
        turnForm.submit();
    }
</script>
