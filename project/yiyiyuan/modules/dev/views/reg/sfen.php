<div class="Hcontainer">
    <div class="main mt20">
        <p>选择你的身份</p>
        <div class="text-center mt40">
            <img src="/images/staff_un.png" width="30%" id="regtypeshehui">
            <!--<img src="/images/staff.png" width="30%" id="regtypeshehui"> -->
            <p class="mt20 n26">我是上班族</p>
        </div>
        <div class="text-center mt70">
            <img src="/images/student_un.png" width="30%" id="regtypestudent">
            <!-- <img src="images/student.png" width="30%"> -->
            <p class="mt20 n26">我是大学生</p>
        </div>
        <p class="text-center grey4 mt40 n26">※ 若不慎填错，请联系客服修改哦！</p>
        <input type="hidden" name="user_type" id="user_type" value="">
    </div>

    <!-- 弹层 -->
    <div class="Hmask" style="display:none;" id='luozhs'></div>
    <!-- 上班族，大学生选择 -->
    <div class="xhb_layer text-center" id="identity" style="top:18%;display: none;">
        <img src="/images/staff.png" width="40%" class="mt30">
        <!-- <img src="images/student.png" width="40%" class="mt30"> --> 
        <p class="mt40 n30">我是<span class="red">上班族</span></p>
        <p class="text-center grey4 mt20 n26">不慎填错，联系客服</p>
        <div class="mt40">
            <div class="col-xs-6">
                <button type="button" class="btn2 mt20" id='qx'>取消</button>
            </div>
            <div class="col-xs-6">
                <button type="submit" class="btn mt20" id='qd'>确定</button>
            </div>
        </div>
    </div>

    <!-- 弹层2 -->
    <div class="Hmask" style="display:none;" id='luozh'></div>
    <!-- 上班族，大学生选择 -->
    <div class="xhb_layer text-center" id="identitys" style="top:18%;display: none;">
        <img src="/images/staff.png" width="40%" class="mt30">
        <!-- <img src="images/student.png" width="40%" class="mt30"> --> 
        <p class="mt40 n30">我是<span class="red">学生族</span></p>
        <p class="text-center grey4 mt20 n26">不慎填错，联系客服</p>
        <div class="mt40">
            <div class="col-xs-6">
                <button type="button" class="btn2 mt20" id='qxs'>取消</button>
            </div>
            <div class="col-xs-6">
                <button type="submit" class="btn mt20" id='qds'>确定</button>
            </div>
        </div>
    </div>

    <!-- 注册成功 -->
    <div class="xhb_layer" id="succ" style="display: none;">
        <p class="grey2 n30 text-center"><img src="/images/icon_valid3.png" width="24" style="margin-right: 5px;">注册成功</p>
        <div class="main n26">
            <p>Hi,<?php echo $userinfo->nickname; ?>! 先花一亿元为您提供以下服务：</p> 
            <p>1.投资理财——投资先花宝、投资标的、投资熟人</p>
            <p>2.应急借钱——好友借款、担保借款</p>
            <p class="mt20">●先来抽个奖吧</p>
            <button class="btn_red" id='lzh'>完成</button>
        </div>
    </div>
</div>
<script>
    $('#qx').click(function () {
        //alert(111);
        $('.Hmask').hide();
        $('#identity').hide();
    });

    $('#qxs').click(function () {
        $('.Hmask').hide();
        $('#identitys').hide();
    });

    $('.Hmask').click(function () {
        $('.Hmask').hide();
        $('#identitys').hide();
        $('#succ').hide();
    });

    $('.Hmask').click(function () {
        $('.Hmask').hide();
        $('#identity').hide();
        $('#succ').hide();
    });

    $('#qd').click(function () {
        var user_type = $('#user_type').val();
        $.post("/dev/reg/lzh", {user_type: user_type}, function (result) {
            var data = eval("(" + result + ")");
            if (data.ret == '1') {
                //window.location ="/dev/activity/aa";
                //$('.Hmask').hide();
                $('#identity').hide();
                $('#succ').show();
            }
        });

    });

    $('#qds').click(function () {
        var user_type = $('#user_type').val();
        //alert(user_type);
        $.post("/dev/reg/lzh", {user_type: user_type}, function (result) {
            var data = eval("(" + result + ")");
            if (data.ret == '1') {
                // window.location ="/dev/activity/aa";
                //$('.Hmask').hide();
                $('#identitys').hide();
                $('#succ').show();
            }
        });
    });

    $('#lzh').click(function () {
        window.location = "/dev/activity/aa";
    });
</script>