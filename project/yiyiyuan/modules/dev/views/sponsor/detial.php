
    <div class="Hcontainer">
        <div class="dbr_head">
            <img src="/images/sxed_head.jpg" width="100%">
            <div class="col-xs-6">
                <p class="n26 grey2 bold">预计收益：<img src="/images/icon_ques3.png" class="icon_ques3"></p>
                <p class="n26 grey2"><span class="n50 red"><?php echo number_format($user_info['amount']*0.01,2,'.','');?></span> 元</p>
            </div>
        </div>
        <div class="border_bottom_1"></div>
        <div class="main bWhite overflow border_bottom_grey2">
            <div class="col-xs-12 red n26 nPad mb10 bold">借款人信息</div>
            <div class="col-xs-6 mb10">姓名：<span><?php echo $user_info['user']['realname'];?></span></div>
            <div class="col-xs-6 mb10">性别：<span><?php echo $sex;?></span></div>
            <div class="col-xs-6 mb10">年龄：<span><?php echo $nl;?></span>岁</div>
            <div class="col-xs-6 mb10">学历：<span><?php echo $xueli?></span></div>
            <div class="col-xs-12 nPad">学校：<span><?php echo $user_info['user']['school'];?></span></div>
        </div>
        <div class="main bWhite overflow">
            <div class="col-xs-10 n36 grey2 bold tel">电话：<a class="aColor"><?php echo $user_info['user']['mobile'];?></a></div>
            <div class="col-xs-2 nPad border_left_grey" style="padding:5px 0;"><a href="tel:<?php echo $user_info['user']['mobile'];?>"><img src="/images/icon_tel.png" width="50%" class="float-right icon_tel" style="max-width:52px;"></a></div>
        </div>
        <div class="main bWhite overflow jkxx border_bottom_grey2" style="margin-top:11px;">
            <div class="col-xs-12 red n26 nPad mb10 bold">借款信息</div>
            <div class="col-xs-4 nPad">
                <p class="redLight2 n26 float-left mb10">周期期限</p>
                <span class="text-center float-left"><?php echo $user_info['days'];?>天</span>
            </div>
            <div class="col-xs-4 nPad">
                <p class="redLight2 n26 float-left mb10" style="margin-left:10%;">年化收益</p>
                <span class="text-center float-left" style="margin-left:10%;"><?php echo round(0.01/$user_info['days']*365*100,1);?>%</span>
            </div>
            <div class="col-xs-4 nPad">
                <p class="redLight2 n26 float-right mb10">熟人关系</p>
                <span class="text-center float-right">同校担保</span>
            </div>              
        </div>
        <div class="main bWhite overflow">
                <p class="text-center n26 grey2">借款金额： <span class="red n60"><?php echo number_format($user_info['amount'],2,'.','');?></span> 元</p>
                <div class="col-xs-12 red n26 nPad bold mt10">借款用途</div>
                <div class="col-xs-12 n26 nPad"><?php echo $user_info['desc'];?></div>
        </div>
        <div class="bPurple overflow padtb10 dbr_nav">
            <ul>
                <li class="dbr_btn btn_a" id='hrefuse'>拒绝他</li>
               <?php if($dstatus == 7):?>
                 <li class="dbr_btn btn_b" id='hinvest1'>投资他</li>
			   <?php else:?>
				<?php if($status == 2):?>
                <li class="dbr_btn btn_b" id='hinvest'>投资他</li>
				<?php else:?>
                <a href="/dev/sponsor/hinvest?loan_id=<?php echo $user_info['loan_id']; ?>" class="dbr_btn btn_b" id='tinvest'>投资他</a>
				<?php endif;?>
			  <?php endif;?>
            </ul>
        </div>

        <!-- 黑色遮罩 -->
        <div class="Hmask" style="display: none;"></div>
        <!-- 弹层1,余额不足 -->
        <div class="layer_border text-center" style="display: none;" id='invest'>
            <p class="n30 mb30">您的担保额度不足，无法进行投资。</p>
            <div class="border_top_2">
                <a href="javascript:;" class="n30 boder_right_1" id='hback'><span class="cor">返回</span></a>
                <a href="/dev/guarantoraccount/index" class="n30 red"><span class="red">查看担保中借款</span></a>
				<!--到担保人账户页-->
	           </div>
        </div>
        <!-- 弹层2,拒绝理由 -->
        <div class="layer_border refuse1" style="top:20%;display: none;">
		   <!--<form action='/dev/sponsor/reason' method='POST'>-->
            <div class="padlr625">
                <p class="n30 mb20 bold">请选择拒绝理由</p>
                <div class="border_top_2 overflow padtb10 n26">
                    <div class="float-left mb15">
                        <input type="radio" name="discount" checked="" id="radio-1" class="regular-radio" value='和他不熟'>
                        <label for="radio-1" style="float:left;margin-right: 5px;"></label>
                        <p style="float:left;margin-top: -1px;">和他不熟</p> 
                    </div>
                    <div class="float-right mb15">
                        <input type="radio" name="discount" id="radio-2" class="regular-radio" value='协商取消'>
                        <label for="radio-2" style="float:left;margin-right: 5px;"></label>
                        <p style="float:left;margin-top: -1px;">协商取消</p>
                    </div>
                    <div class="clearfix"></div>
                    <div class="float-left mb15">
                        <input type="radio" name="discount" id="radio-3" class="regular-radio" value='存在虚假信息'>
                        <label for="radio-3" style="float:left;margin-right: 5px;"></label>
                        <p style="float:left;margin-top: -1px;">存在虚假信息</p> 
                    </div>
                    <div class="float-right mb15">
                        <input type="radio" name="discount" id="radio-4" class="regular-radio" value='额度不足'>
                        <label for="radio-4" style="float:left;margin-right: 5px;"></label>
                        <p style="float:left;margin-top: -1px;">额度不足</p>
                    </div>
                    <div class="clearfix"></div>
                    <div class="">
                        <input type="radio" name="discount" id="radio-5" class="regular-radio" value='其他'>
                        <label for="radio-5" style="float:left;margin-right: 5px;"></label>
                        <p style="float:left;margin-top: -1px;">其他</p>
                        <input type="text" class="qt_inp" name='hreason' id='hh' style="display:none;" maxlength=25>
						<input type='hidden' id='loan_id' value='<?php echo $user_info['loan_id'];?>'/>
						<input type='hidden' id='mobile' value='<?php echo $user_info['user']['mobile'];?>'/>
                    </div>
                </div>
            </div>
            
            <div class="border_top_2 text-center mt20">
                <button type="submit" class="n30 red" style="width:100%;background:none;" id='reason_login'>提交</button>
            </div>
			<!--</form>-->
        </div>
        <!-- 弹层3，预计收益 -->
        <div class="xhb_layer pad" style="display: none;">
            <img src="/images/icon_wt.png" style="width:30%;position: absolute;top:-84px;left:-5px;width:100px;">
            <p class="n28 mt40"><span class="red">预计收益：</span>借款人成功还款后，收益到账。</p>
            <button class="btn_red">朕知道了</button>
        </div>
		<!-- 弹层4，提交成功 -->
        <div class="layer_border text-center succ" style="display: none;" id="succ">
            <p class="n30 mb30">提交成功！</p>
        </div>

		<!-- 弹层5,暂时无法投资 -->
        <div class="layer_border text-center" style="display: none;" id="zswftz">
            <p class="n30 mb30">担保中借款发生逾期，暂时无法投资。</p>
            <div class="border_top_2">
                <a href="javascript:;" class="n30 boder_right_1" id='zback'><span class="cor">返回</span></a>
                <a href="/dev/guarantoraccount/index" class="n30 red"><span class="red">查看担保中借款</span></a>
            </div>
        </div>

   </div>
<script>
$(window).load(function(){
    var lineH = $('.icon_tel').parent().parent().height();
    $('.tel').css('lineHeight',lineH+10+'px');
});
$(function(){
    $('.layer_border label').each(function(){
        $(this).click(function(){
            //点击相对应的radio变为checked
            $('input[type="radio"]').prop('checked',false);
            $('input[type="radio"]').removeAttr('checked');
            $(this).siblings('input[type="radio"]').prop('checked',true);
            $(this).siblings('input[type="radio"]').attr('checked','checked');
			$('.qt_inp').hide();
        });
    });

	$('.layer_border label:last').click(function(){
        $('.qt_inp').show();
    });

    $('.Hmask').click(function(){
        $('.layer_border').hide();
        $('.xhb_layer').hide();
        $('.Hmask').hide();
    });
    $('.icon_ques3').click(function(){
        $('.Hmask').show();
        $('.xhb_layer').show();
    });
    $('.btn_red').click(function(){
        $('.xhb_layer').hide();
        $('.Hmask').hide();
    });
});

$('#hinvest').click(function(){
   $('.Hmask').show();
   $('#invest').show();
});

$('#hinvest1').click(function(){
   $('.Hmask').show();
   $('#zswftz').show();
});

$('#hback').click(function(){
	$('.Hmask').hide();
    $('#invest').hide();
});

$('#zback').click(function(){
	$('.Hmask').hide();
    $('#zswftz').hide();
});

$('#hrefuse').click(function(){
	$('.Hmask').show();
    $('.refuse1').show();
});

/*$('#tinvest').click(function(){
   var loan_id = $('#loan_id').val();

   $.post("/dev/sponsor/Hinvest",{loan_id:loan_id},function(result){
	//var data = eval("("+ result + ")" );
	location.href="/dev/sponsor/hinvest";
 });
   //location.href="/dev/sponsor/hinvest";
});*/

$('#reason_login').click(function(){
  var reason = $('input[type="radio"]:checked').val();
  var loan_id = $('#loan_id').val();
  var mobile = $('#mobile').val();
  var type = 1;
  //alert(loan_id);exit;
  if(reason=='其他'){
	  reason = $('#hh').val();
	  type = 2;
	  if( reason == ''||reason==null){
		  alert('理由不能为空');
		  return false;
	  }
  }
 
 //alert(reason);
 $.post("/dev/sponsor/reason",{reason:reason,loan_id:loan_id,mobile:mobile,type:type},function(result){
	var data = eval("("+ result + ")" );
	//alert(data);
	if( data.ret == '1' ){
      
      $('.refuse1').hide();
	  $('.succ').css('display','block');
	  setTimeout(function(){
        location.href="/dev/sponsor/index";
	  },2000);
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

    wx.ready(function() {
        wx.hideOptionMenu();
    });
</script>