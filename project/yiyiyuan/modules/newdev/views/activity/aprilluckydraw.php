<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, minimal-ui;">
    <meta name="format-detection" content="telephone=no">
    <title></title>   
    
    <link rel="stylesheet" href="/css/demo.css" type="text/css" />
<link rel="stylesheet" type="text/css" href="/css/style.css">
<link rel="stylesheet" type="text/css" href="/css/reset.css"/>
    <link rel="stylesheet" type="text/css" href="/css/inv.css"/>

<script type="text/javascript" src="/js/jquery.min.js"></script>
<script type="text/javascript" src="/js/awardRotate.js"></script>
<script type="text/javascript" src="/js/scroll.js"></script>
<style type="text/css">
    
        html,body{width:100%;height:100%; 
background-image: -webkit-linear-gradient(0deg, #a21e32,#c12a41); 
 font-family: "Microsoft YaHei"; position: relative;}
a{color:#000}
.btn,.btn:hover.btn:visited{
	color:#ffffff;
	background-color:#fd5b78;
	margin:10px auto;
	display:block;
	text-align: center;
	width:240px;
	height:40px;
	line-height:40px;
	border:1px solid #DE3163;
	border-radius:4px;
	cursor:pointer;
}
.btn:active{
	background-color:#e46084;
}
.btn1,.btn1:hover.btn1:visited{
	color:#ffffff;
	background-color:#008573;
	margin:10px auto;
	display:block;
	text-align: center;
	width:240px;
	height:40px;
	line-height:40px;
	border:1px solid #00755E;
	border-radius:4px;
	cursor:pointer;
}
.btn1:active{
	background-color:#00755E;
}
.rotate_wrap{
	width: 90%;
	max-width: 300px;
	height: 300px;
	margin: 0px auto;
	position:relative;

}
.bg_img{
	width:100%;
	position:absolute;
	top:0;
	left:0;
}
.cont_img{
        position: absolute;
    width: 15%;
    top: 37%;
    left: 43%;
}
.rotate_origin{
	transform-origin: 50% 50%;
	-ms-transform-origin:50% 50%;
	-webkit-transform-origin:50% 50%;
	-moz-transform-origin:50% 50%;
	-o-transform-origin:50% 50%;
}
.rotate_origin1{
	transform-origin: 50% 85%;
	-ms-transform-origin:50% 85%;
	-webkit-transform-origin:50% 85%;
	-moz-transform-origin:50% 85%;
	-o-transform-origin:50% 85%;
}
.overfloat{
	position:fixed;
	top:0;
	left:0;
	width:100%;
	height:100%;
	background-color:rgba(1,1,1,0.5);
	z-index:9;
	display:none;
}
.overfloat_cont{
	position:fixed;
	top:50%;
	left:50%;
	width:318px;
	height:160px;
	margin-left:-159px;
	margin-top:-80px;
	border-radius: 4px;
	text-align:center;
	font-size:20px;
	color:#000000;
	line-height:30px;
	background-color:#ffffff;
}
.top_space{
	display:block;
	margin-top:20px;
}
.font_red{
	color:#fd5b78;
}
.ajax_hide{
	height:0;
	width:0;
	position:absolute;
	top:0;
	left:0;
	overflow:hidden;
}
.banner_wrap{
	position:fixed;
	font-size:0;
	bottom:0;
	left:0;
	width:100%;
}
.banner_wrap img{
	width:100%;
}

/*圣诞活动页面*/
.xqcjact img{display: block;}
.txtmage{ color: #fff; margin: 0px 13%; font-family: "黑体"; padding-bottom: 40px; position: relative;}
.txtmage h3{ font-size: 1.15rem; margin:25px 0 10px;}
.txtmage div{ font-size: 0.9rem; margin: 0 5%;}
.lwulp{position: absolute;right: -15%;width: 40%; bottom: 0px;}
.xqcjact .sycjcs{ color: #fff; text-align: center;    margin-top: -10px; }
.xqcjact .sycjcs a{border-bottom:0.5px solid #fff; color: #fff; font-size: 0.8rem;}
.xqcj2{	background: url(/images/activity/aprilluckydraw_xqcj2.jpg) no-repeat; background-size: 100%; padding-top: 20px;}

/*弹出层*/
.Hmask { width: 100%;height: 100%;background: rgba(0,0,0,.7);position: fixed;top: 0;left: 0; z-index: 100;}
.tanchuceng{ position: fixed;top: 20%;left: 0%;border-radius: 5px;z-index: 100;background: #fff; width: 80%; margin: 0 10%;    padding-bottom: 15px;}
.tanchuceng h3{ width: 100%;background-image: -webkit-linear-gradient(0deg, #901c2c,#ed6f4e); border-radius: 5px 5px  0 0; }
.tanchuceng h3 span{color: #fff; display: block;  padding: 12px 20px; font-size: 1.2rem;}
.tanchuceng p{ text-align: center; padding: 18px 12% 10px; font-size: 1rem;}
.tanchuceng p.gxinin{text-align: left;}
.tanchuceng button{background-image: -webkit-linear-gradient(0deg, #dd2f4b,#ed6f4e); width: 40%; margin: 0 30%; color: #fff; padding: 5px 0;}
.tanchuceng .error{  display: block; position: absolute; right: 5%; top: 15px;}

.quan{width:100%;height:100%;background:rgba(0,0,0,0.5);position:absolute;top:0px;z-index:99;display:none;}
.quan1{width:300px;height:200px;background:#fff;margin:0 auto;margin-top:300px; border-radius:10px;border:4px solid #666;}
.qian2{float:left;height:50px;width:196px;line-height:50px;text-align:center;font-size:18px;margin-top:50px; background:#fff;border:2px solid #fff100;margin-left:50px; }
.queding{float:left;width:70px;height:24px;background:#fff;border:2px solid #ccc;border-radius:5px;margin-top:50px;margin-left:115px;text-align:center;line-height:24px; cursor:pointer;}
.dis{color:#000;font-size:100px;font-weight:bold;position:absolute;left:50%;margin-left:-90px;top:100px;line-height:30px;}
.xianjin{width:280px;height:50px;background:#fff;position:absolute;border-radius:10px;top:248px;left:200px;line-height:50px;text-align:center;}
.jjc{width:500px;height:500px;background:url(/images/activity/paoma1.gif);background-size:100%100%;position:absolute;top:-250px;left:500%;font-size:35px;line-height:700px;text-align:center;color:#fff;}

.turntable-bg .pointer {
  width: 74px;
  height: 88px;
  position: absolute;
  /*border:1px solid red;*/
  left: 188px;
  top: 156px;
  margin-left: -39px;
  margin-top: -50px;
  z-index: 8;
}

</style>
<script type="text/javascript">
$(function (){
    
        var user_id = $('input[name="user_id"]').val();
        var friend_loan_num = $('input[name="friend_loan_num"]').val();
        var coupon_num = $('input[name="coupon_num"]').val();
        //优惠券发放
        $('.send_coupon').click(function(){
            var val = $(this).attr('data_val');
            var cls = "coupon_"+val;
            console.log(val);
            $.post("/new/activity/sendcouponday",{user_id: user_id,val:val,days:60},function(res){
                $("."+cls).css('display','none');
                $(".Hmask").css('display','none');
                $.post("/new/activity/subtractionactivenum",{user_id: user_id},function(res){
                    window.location.href="/new/activity/aprilluckydraw?user_id="+user_id;
                });
            });
        })
        //再来一次（不减次数）
        $('.add_num').click(function(){
            $.post("/new/activity/againnum",{user_id: user_id},function(res){
                window.location.href="/new/activity/aprilluckydraw?user_id="+user_id;
            });
        })
		 
	var rotateTimeOut = function (){
		$('#rotate').rotate({
			angle:0,
			animateTo:2160,
			duration:8000,
			callback:function (){
				alert('网络超时，请检查您的网络设置！');
			}
		});
	};
	var bRotate = false;

	var rotateFn = function (awards, angles, txt){
		bRotate = !bRotate;
		$('#rotate').stopRotate();
		$('#rotate').rotate({
			angle:0,
			animateTo:angles+1800,
			duration:8000,
			callback:function (){
                                if(txt == 0){//谢谢
//                                    alert("谢谢");
                                    $('.no_coupon').show();
                                    $(".Hmask").show();
                                }else if(txt == 1){
                                    //京东卡
                                    alert("网络问题，请重试");
                                }else if(txt == 2){
//                                    alert("20元券");
                                    $('.coupon_20').show();
                                    $(".Hmask").show();
                                }else if(txt == 3){
//                                    alert("再试一次");
                                    $('.again').show();
                                    $(".Hmask").show();
                                }else if(txt == 4){
//                                    alert("500M");
                                    alert("网络问题，请重试");
                                }else if(txt == 5){
//                                    alert("10元券");
                                    $('.coupon_10').show();
                                    $('.Hmask').show();
                                }else if(txt == 6){
//                                    alert("全额免息券");
                                    alert("网络问题，请重试");
                                }else if(txt == 7){
//                                    alert("5元券");
                                    $('.coupon_5').show();
                                    $('.Hmask').show();
                                };
//				alert("￥"+txt);
				bRotate = !bRotate;
			}
		})
	};
	
	$('.pointer').click(function (){
                //点击前校验
                var err_code = $('input[name="err_code"]').val();
                var luckydraw_num = $('input[name="luckydraw_num"]').val();
                if(err_code == 0001){
                    //@TODO 登陆判断 须打开
                                    $(".no_log").show();
                                    $(".Hmask").show();
                                    return false;
                }
                if(luckydraw_num == 0){
                                    $(".no_num").show();
                                    $(".Hmask").show();
                                    return false;
                }
                
		var a=[0,1,2,3,4,5,6,7];
		if(bRotate)return;
		 
		var item = rnd(friend_loan_num,coupon_num);
		switch (item) {

			case 0:          
				//var angle = [26, 88, 137, 185, 235, 287, 337];
				rotateFn(0, 337, a[0]);
				var ss=Number($("#xianjin").val());
				var cc=ss+a[0];
				$("#xianjin").val(cc);
				setTimeout(function(){
				$(".xianjin").html(cc);
				},8000);
				$(".qian2").html(cc);
				
				break;
			case 1:
				//var angle = [88, 137, 185, 235, 287];
				
				rotateFn(1, 26, a[1]);
				var ss=Number($("#xianjin").val());
				var cc=ss+a[1];
				$("#xianjin").val(cc);
				setTimeout(function(){
				$(".xianjin").html(cc);
				},8000);
				$(".qian2").html(cc);
				break;
			case 2:
				//var angle = [137, 185, 235, 287];
				rotateFn(2, 110, a[2]);
				var ss=Number($("#xianjin").val());
				var cc=ss+a[2];
				$("#xianjin").val(cc);
				setTimeout(function(){
				$(".xianjin").html(cc);
				},8000);

				$(".qian2").html(cc);
				break;
			case 3:
				//var angle = [137, 185, 235, 287];
				rotateFn(3, 155, a[3]);
				var ss=Number($("#xianjin").val());
				var cc=ss+a[3];
				$("#xianjin").val(cc);
				setTimeout(function(){
				$(".xianjin").html(cc);
				},8000);
				$(".qian2").html(cc);
				break;
			case 4:
				//var angle = [185, 235, 287];
				rotateFn(4, 287, a[4]);
				var ss=Number($("#xianjin").val());
				var cc=ss+a[4];
				$("#xianjin").val(cc);
				setTimeout(function(){
				$(".xianjin").html(cc);
				},8000);
				$(".qian2").html(cc);
				break;
			case 5:
			
				//var angle = [185, 235, 287];
				rotateFn(5, 195, a[5]);
				var ss=Number($("#xianjin").val());
				var cc=ss+a[5];
				$("#xianjin").val(cc);
				setTimeout(function(){
				$(".xianjin").html(cc);
				},8000);
				$(".qian2").html(cc);
				break;
			case 6:
				//var angle = [235, 287];
				rotateFn(6, 235, a[6]);
				var ss=Number($("#xianjin").val());
				var cc=ss+a[6];
				$("#xianjin").val(cc);
					setTimeout(function(){
				$(".xianjin").html(cc);
				},8000);
				$(".qian2").html(cc);
				break;
                        case 7:
				//var angle = [235, 287];
				rotateFn(7, 65, a[7]);
				var ss=Number($("#xianjin").val());
				var cc=ss+a[7];
				$("#xianjin").val(cc);
					setTimeout(function(){
				$(".xianjin").html(cc);
				},8000);
				$(".qian2").html(cc);
				break;
		}

		//console.log(item);
	});
        //点击确定按钮(没有抽奖次数)
        $(".cannot").click(function(){
            $(".Hmask").css('display','none');
            $(".no_num").css('display','none');
            $(".no_log").css('display','none');
        });
        //点击确定按钮(未登陆)
        $(".cannot_log").click(function(){
            $(".Hmask").css('display','none');
            $(".no_num").css('display','none');
            $(".no_log").css('display','none');
            window.location.href="/new/reg/loginloan";
        });
        //未中奖(弹层小时，次数减1)
        $(".no_coupon_sure").click(function(){
            $(".no_coupon").css('display','none');
            $(".Hmask").css('display','none');
            $.post("/new/activity/subtractionactivenum",{user_id: user_id},function(res){
                window.location.href="/new/activity/aprilluckydraw?user_id="+user_id;
            });
        });

});
function rnd(friend_loan_num, coupon_num){
//	return Math.floor(Math.random()*(m-n+1)+n)
//        var id:int;
        var id = 0;
        var random=Math.random();
        console.log(random);
        if(random < 0.57)
            id = 0;
         else if(random < 0.87)
             if(friend_loan_num >= 1){
                 id = 0
             }else{
                id = 3;
             }
         else if(random < 0.97)
             if(coupon_num >= 1){
                 id = 0
             }else{
                id = 7;
             }
         else if(random < 0.99)
             if(coupon_num >= 1){
                 id = 0
             }else{
                id = 5;
             }
         else if(random < 1)
             if(coupon_num >= 1){
                 id = 0
             }else{
                id = 2;
             }
        console.log(id);
        return id;
};
</script>

    
</head>
<body>

<div class="xqcjact">
        <input type='hidden' name = 'err_code' value="<?=$err_code?>">
        <input type='hidden' name = 'luckydraw_num' value="<?=$luckydraw_num?>">
        <input type='hidden' name = 'user_id' value="<?=$user_id?>">
        <input type='hidden' name = 'friend_loan_num' value="<?=$friend_loan_num?>">
        <input type='hidden' name = 'coupon_num' value="<?=$coupon_num?>">
	<img src="/images/activity/aprilluckydraw_xqcj1.jpg">
	<!--<img src="images/xqcj2.jpg">-->
		<div class="xqcj2">
			<div class="turntable-bg"> 
			  <div class="pointer"><img src="/images/activity/aprilluckydraw_img2.png" alt="pointer" style="width:100%;height:100%;"></div>
			  <div class="rotate" ><img id="rotate" src="/images/activity/aprilluckydraw_img1.png" alt="turntable" style="width:100%;height:100%;"/></div>
			</div>
			
			
			<script type="text/javascript">
				$(document).ready(function(){
				$('.list_lh li:even').addClass('lieven');
			});
			$(function(){
				$("div.list_lh").myScroll({
					speed:40, //数值越大，速度越慢
					rowHeight:68 //li的高度
				});
			});
			</script>
			
		</div>
	<p class="sycjcs"><a>剩余抽奖次数：<?=$luckydraw_num?>次</a></p>
	<div class="txtmage">
		<h3>活动规则：</h3>
		<div >
			<p>1、活动日期：2018年4月12日——4月26日</p>
			<p>2、在活动期间，用户在先花一亿元平台成功借款即可获得3次抽奖机会</p>
			<p>3、在活动期间，用户在先花一亿元平台成功还款即可获得3次抽奖机会</p>
		</div>
		<h3>活动奖品：</h3>
		<div>
			<p>1、再抽一次：可以获得一次抽奖机会</p>
			<p>2、50元京东卡：可在京东购物时减免50元</p>
			<p>3、10元红包券:在借款时使用，可抵扣相应的金额，有效日期60天</p>
			<p>4、20元红包券:在借款时使用，可抵扣相应的金额，有效日期60天</p>
			<p>5、500M移动流量包:可直接使用兑换500M移动流量</p>
			<p>6、全额免息券:可以免去全额利息</p>
		</div>
		<h3>奖品发放：</h3>
		<div>
			<p>1、红包券与全额免息券发放形式为：中奖之后5分钟内自动发放到用户账户中。</p>
			<p>2、500M移动流量包发放形式：中奖后请直接根据提示收入手机号，500M流量将会在3日之内自动充值到用户所填写的手机号中。</p>
			<p>3、京东卡实物奖品发放形式：在APP及微信公众号内参与活动，领取奖品请填写收货地址等信息，活动结束后核实中奖信息后，由平台统一邮寄，活动结束5个工作日后不再补录客户信息，如遇个人拒接电话等原因导致不能取得联系，无法安排奖品发放，将视为主动放弃活动奖品。</p>
		</div>
		<div class="lwulp"><img src="/images/activity/aprilluckydraw_lwulp.png"></div>
	</div>
	
</div>
<!--弹窗-->
<div class="Hmask" hidden></div>

<div class="tanchuceng no_log" hidden>
	<a class="error cannot"><img src="/images/activity/aprilluckydraw_error.png"></a>
	<h3><span>消息提醒</span></h3>
	<p class="gxinin">亲，您暂未登陆，请先登陆</p>
        <button class="cannot_log">确定</button>
</div>

<div class="tanchuceng no_num" hidden>
	<a class="error cannot"><img src="/images/activity/aprilluckydraw_error.png"></a>
	<h3><span>消息提醒</span></h3>
	<p class="gxinin">亲，您暂无抽奖次数，借款或还款均可获得3次抽奖次数</p>
	<button class="cannot">确定</button>
</div>

<div class="tanchuceng no_coupon" hidden>
	<a class="error no_coupon_sure"><img src="/images/activity/aprilluckydraw_error.png"></a>
	<h3><span>很遗憾</span></h3>
	<p>很遗憾，您未中奖</p>
	<button class = "no_coupon_sure">确定</button>
</div>

<div class="tanchuceng coupon_5" hidden>
	<a class="error send_coupon" data_val = "5"><img src="/images/activity/aprilluckydraw_error.png"></a>
	<h3><span>恭喜您</span></h3>
	<p class="gxinin">恭喜您，抽中5元红包券，将在5分钟内发放到您的账户中</p>
	<button class = "send_coupon" data_val = "5">确定</button>
</div>

<div class="tanchuceng coupon_10" hidden>
	<a class="error send_coupon"  data_val = "10"><img src="/images/activity/aprilluckydraw_error.png"></a>
	<h3><span>恭喜您</span></h3>
	<p class="gxinin">恭喜您，抽中10元红包券，将在5分钟内发放到您的账户中</p>
	<button class = "send_coupon" data_val = "10">确定</button>
</div>
<div class="tanchuceng coupon_20" hidden>
	<a class="error send_coupon"   data_val = "20"><img src="/images/activity/aprilluckydraw_error.png"></a>
	<h3><span>恭喜您</span></h3>
	<p class="gxinin">恭喜您，抽中20元红包券，将在5分钟内发放到您的账户中</p>
	<button class = "send_coupon" data_val = "20">确定</button>
</div>

<div class="tanchuceng again" hidden>
	<a class="error add_num"><img src="/images/activity/aprilluckydraw_error.png"></a>
	<h3><span>恭喜您</span></h3>
	<p>再来一次，祝您好运</p>
	<button class = "add_num">确定</button>
</div>


</body>
</html>


