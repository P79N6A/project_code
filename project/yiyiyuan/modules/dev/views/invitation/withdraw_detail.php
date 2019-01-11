<div class="selfmess">
	<p class="selftxt">请填写以下信息进行提现</p>
	<div class="selftximg">
		<div class="dbk_inpL">
        	<label>银行卡</label><input placeholder="" id="bank_card" type="text">
    	</div>
    	<div class="dbk_inpL">
        	<label>姓名</label><input placeholder="" id="real_name" type="text">
    	</div>
    	<div class="dbk_inpL">
        	<label>身份证</label><input placeholder="" id="identity" type="text">
    	</div>
        <div class="dbk_inpL">
            <label>手机号</label><input placeholder="输入银行预留手机号" id="mobile" maxlength="11" type="text">
        </div>
        <div class="dbk_inpL">
            <label>验证码</label><input class="yzmwidth" placeholder="填写验证码" id="code" maxlength="4" type="text">
            <button class="hqyzm">获取验证码</button>
        </div>
	</div>
	<input type="hidden" id="grant_id" value="<?php echo $grant_id;?>">
	<div class="tsmes"></div>
	<div class="button"> <button id="with_draw">立即提现</button></div>
	<div class="certification">
        <div class="cert_one"><img src="/images/account/fircert.png">提现榜</div>
       	<?php if(!empty($red_packet_list)):?>
		<?php foreach ($red_packet_list as $key=>$value):?>
		<div class="cert_two">
			<img src="<?php echo $value['head'];?>">
			<div class="cert_two2"><p class="p1"><?php if(!empty($value['nickname'])):?><?php echo $value['nickname'];?><?php else:?><?php echo $value['realname'];?><?php endif;?></p><p class="p2"><?php echo date('m'.'月'.'d'.'日'.' H'.':'.'i', strtotime($value['create_time']));?></p></div>
			<div class="cert_two3"><?php echo sprintf("%.2f", $value['amount']);?>元</div>
		</div>
		<?php endforeach;?>
		<?php endif;?>
        
    </div>
</div>
<script>
var _mobileRex = /^(1(([3578][0-9])|(47)))\d{8}$/;
$("#bank_card").keyup(function(){
    var card = $(this).val();
    if (card.length > 4) {
        if (card[card.length - 1] != ' ') {
            card = card.replace(/\s+/g, "");
            if (card.length % 4 == 1) {
                var ncard = '';
                for (var n = 0; n < card.length; n++) {
                    if (n % 4 == 3)
                        ncard += card.substring(n, n + 1) + " ";
                    else
                        ncard += card.substring(n, n + 1);
                }
                $('#bank_card').val(ncard);
            }
        }
    }
});

$('.hqyzm').click(function () {
    var mobile = $("#mobile").val();

    if (mobile == '' || !(_mobileRex.test(mobile))) {
        $(".tsmes").html('请输入正确的手机号码');
        $("#mobile").focus();
        return false;
    }
    $(".hqyzm").attr('disabled', true);
    $.post("/dev/reg/onesend", {mobile: mobile}, function (result) {
        var data = eval("(" + result + ")");
        if (data.ret == '0') {
            //发送成功
            count = 60;
            countdown = setInterval(CountDown, 1000);
        } else if (data.ret == '2')
        {
            $(".tsmes").html('该手机号码已超出每日短信最大获取次数');
            $("#mobile").focus();
            $(".hqyzm").attr('disabled', false);
            return false;
        } else {
            $(".tsmes").html('该手机号已注册，请直接<a href="/dev/reg/login">登录</a>');
            $("#mobile").focus();
            $(".hqyzm").attr('disabled', false);
            return false;
        }
    });

});

$("#with_draw").click(function(){
	var bank_card = $("#bank_card").val();
	var real_name = $("#real_name").val();
	var identity = $("#identity").val();
	var mobile = $("#mobile").val();
	var code = $("#code").val();
	var grant_id = $("#grant_id").val();
	bank_card = bank_card.replace(/\s+/g, "");

	if(bank_card == '' || bank_card == undefined)
	{
		$(".tsmes").html('请填写银行卡号');
        return false;
	}

	if(real_name == '' || real_name == undefined)
	{
		$(".tsmes").html('请填写姓名');
        return false;
	}

	if (!checkregisteridentity(identity))
	{
		$(".tsmes").html('请填写正确的身份证号');
        return false;
	}

	if (mobile == '' || !(_mobileRex.test(mobile))) 
	{
		$(".tsmes").html('请填写正确的手机号码');
        return false;
	}

	if(code == '' || code == undefined)
	{
		$(".tsmes").html('请填写验证码');
        return false;
	}

	$.post("/dev/invitation/withsave", {bank_card: bank_card, real_name: real_name, identity: identity, mobile: mobile, code: code, grant_id: grant_id}, function (result) {
		 var data = eval("(" + result + ")");
		 if(data.ret == '1'){
			$(".tsmes").html('手机号已注册');
		    return false;
		 }else if(data.ret == '2'){
			 $(".tsmes").html('该微信已绑定其它的手机号');
			 return false;
		 }else if(data.ret == '3'){
			 $(".tsmes").html('验证码错误');
			 return false;
		 }else if(data.ret == '4'){
			 $(".tsmes").html('身份认证失败');
			 return false;
		 }else if(data.ret == '5'){
			 $(".tsmes").html('系统错误');
			 return false;
		 }else if(data.ret == '6'){
			 $(".tsmes").html('该银行卡已绑定');
			 return false;
		 }else if(data.ret == '7'){
			 $(".tsmes").html('银行卡号错误');
			 return false;
		 }else if(data.ret == '8'){
			 $(".tsmes").html('红包已失效');
			 return false;
		 }else if(data.ret == '9'){
			 $(".tsmes").html('提现失败');
			 return false;
		 }else if(data.ret == '10'){
			 $(".tsmes").html('红包已失效');
			 return false;
		 }else if(data.ret == '11'){
			 $(".tsmes").html('身份证号已存在');
			 return false;
		 }else{
			 window.location = '/dev/invitation/withdrawsuccess?order_id='+data.order_id;
		 }
	});
});

</script>
<script>
var checkregisteridentity = function (idcard) {
    var area = {11: "北京", 12: "天津", 13: "河北", 14: "山西", 15: "内蒙古", 21: "辽宁", 22: "吉林", 23: "黑龙江", 31: "上海", 32: "江苏", 33: "浙江", 34: "安徽", 35: "福建", 36: "江西", 37: "山东", 41: "河南", 42: "湖北", 43: "湖南", 44: "广东", 45: "广西", 46: "海南", 50: "重庆", 51: "四川", 52: "贵州", 53: "云南", 54: "西藏", 61: "陕西", 62: "甘肃", 63: "青海", 64: "宁夏", 65: "新疆", 71: "台湾", 81: "香港", 82: "澳门", 91: "国外"}
    var idcard, Y, JYM;
    var S, M;
    var idcard_array = new Array();
    idcard_array = idcard.split("");
    //地区检验 
    if (area[parseInt(idcard.substr(0, 2))] == null)
        return false;
    //身份号码位数及格式检验 
    switch (idcard.length) {
        case 15:
            if ((parseInt(idcard.substr(6, 2)) + 1900) % 4 == 0 || ((parseInt(idcard.substr(6, 2)) + 1900) % 100 == 0 && (parseInt(idcard.substr(6, 2)) + 1900) % 4 == 0)) {
                ereg = /^[1-9][0-9]{5}[0-9]{2}((01|03|05|07|08|10|12)(0[1-9]|[1-2][0-9]|3[0-1])|(04|06|09|11)(0[1-9]|[1-2][0-9]|30)|02(0[1-9]|[1-2][0-9]))[0-9]{3}$/; //测试出生日期的合法性 
            } else {
                ereg = /^[1-9][0-9]{5}[0-9]{2}((01|03|05|07|08|10|12)(0[1-9]|[1-2][0-9]|3[0-1])|(04|06|09|11)(0[1-9]|[1-2][0-9]|30)|02(0[1-9]|1[0-9]|2[0-8]))[0-9]{3}$/; //测试出生日期的合法性 
            }
            if (ereg.test(idcard))
                return true;
            else
                return false;
            break;
        case 18:
            //18位身份号码检测 
            //出生日期的合法性检查 
            //闰年月日:((01|03|05|07|08|10|12)(0[1-9]|[1-2][0-9]|3[0-1])|(04|06|09|11)(0[1-9]|[1-2][0-9]|30)|02(0[1-9]|[1-2][0-9])) 
            //平年月日:((01|03|05|07|08|10|12)(0[1-9]|[1-2][0-9]|3[0-1])|(04|06|09|11)(0[1-9]|[1-2][0-9]|30)|02(0[1-9]|1[0-9]|2[0-8])) 
            if (parseInt(idcard.substr(6, 4)) % 4 == 0 || (parseInt(idcard.substr(6, 4)) % 100 == 0 && parseInt(idcard.substr(6, 4)) % 4 == 0)) {
                ereg = /^[1-9][0-9]{5}19[0-9]{2}((01|03|05|07|08|10|12)(0[1-9]|[1-2][0-9]|3[0-1])|(04|06|09|11)(0[1-9]|[1-2][0-9]|30)|02(0[1-9]|[1-2][0-9]))[0-9]{3}[0-9|x|X]$/i; //闰年出生日期的合法性正则表达式 
            } else {
                ereg = /^[1-9][0-9]{5}19[0-9]{2}((01|03|05|07|08|10|12)(0[1-9]|[1-2][0-9]|3[0-1])|(04|06|09|11)(0[1-9]|[1-2][0-9]|30)|02(0[1-9]|1[0-9]|2[0-8]))[0-9]{3}[0-9|x|X]$/i; //平年出生日期的合法性正则表达式 
            }
            if (ereg.test(idcard)) {//测试出生日期的合法性 
                //计算校验位 
                S = (parseInt(idcard_array[0]) + parseInt(idcard_array[10])) * 7
                        + (parseInt(idcard_array[1]) + parseInt(idcard_array[11])) * 9
                        + (parseInt(idcard_array[2]) + parseInt(idcard_array[12])) * 10
                        + (parseInt(idcard_array[3]) + parseInt(idcard_array[13])) * 5
                        + (parseInt(idcard_array[4]) + parseInt(idcard_array[14])) * 8
                        + (parseInt(idcard_array[5]) + parseInt(idcard_array[15])) * 4
                        + (parseInt(idcard_array[6]) + parseInt(idcard_array[16])) * 2
                        + parseInt(idcard_array[7]) * 1
                        + parseInt(idcard_array[8]) * 6
                        + parseInt(idcard_array[9]) * 3;
                Y = S % 11;
                M = "F";
                JYM = "10X98765432";
                M = JYM.substr(Y, 1); //判断校验位 
                if (M == idcard_array[17])
                    return true; //检测ID的校验位 
                else
                    return false;
            }
            else
                return false;
            break;
        default:
            return false;
            break;
    }
};
var CountDown = function () {

    $(".hqyzm").attr("disabled", true).addClass('dis');
    $(".hqyzm").html("重新获取 ( " + count + " ) ");
    if (count <= 0) {
        $(".hqyzm").html("获取验证码").removeAttr("disabled").removeClass('dis');
        clearInterval(countdown);
    }
    count--;
};
</script>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
wx.config({
	debug: false,
	appId: '<?php echo $jsinfo['appid'];?>',
	timestamp: <?php echo $jsinfo['timestamp'];?>,
	nonceStr: '<?php echo $jsinfo['nonceStr'];?>',
	signature: '<?php echo $jsinfo['signature'];?>',
	jsApiList: [
		'hideOptionMenu'
	  ]
  });
  
  wx.ready(function(){
	  wx.hideOptionMenu();
	});  
</script>