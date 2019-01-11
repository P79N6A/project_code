        <script type="text/javascript">
//         	var hastouch = "ontouchstart" in window?true:false,
//         	tapstart = hastouch?"touchstart":"mousedown";
//         	tapmove = hastouch?"touchmove":"mousemove",
//         	tapend = hastouch?"touchend":"mouseup";
			$(function(){
				
				$(".click").bind("click",function(){
					var spanIndex = $(this).index();
					$("span",$(this).parent()).each(function(index, element) {
					   if(index == spanIndex){
						   $(this).addClass("check");
						   }else{
							   $(this).removeClass("check");
						   }
					});
					return false;
				});
				
				$(".click img").each(function(index, element) {
					$(this).css("border-radius","10px");
                    $(this).bind("click",function(){
						$(".click img").each(function(i, element) {
							if(index == i){
								$(this).css("border","2px solid #e74747");
								$("#check_pic").val($(this).attr("src"));
							}else{
								$(this).css("border","none");
							}
						});
					});
                });
			});
        </script>
      
        <div class="container">
        	<p class="n30 text-center content">考验友谊的时候到了，哪个才是Ta？</p>
        	<p><img src="/images/dev/border.png" width="100%" style="display:block;"/></p>
           <div class="bgff">
           	 <div class="content">
                <div class="border_bottom mb20">
                    <p class="n36">Q:<?php echo $first_question;?>？</p>
                    <ul style="margin-left:10%; list-style:upper-alpha;">
                    	<li class="click"><span class="check"><?php echo $first_array[0]['name'];?></span></li>
                        <li class="click"><span><?php echo $first_array[1]['name'];?></span></li>
                        <li class="click"><span><?php echo $first_array[2]['name'];?></span></li>
                    </ul>
                </div>
                 <div class="border_bottom mb20">
                    <p class="n36">Q:<?php echo $second_question;?>？</p>
                    <ul style="margin-left:10%; list-style:upper-alpha;">
                    	<li class="click"><span class="check"><?php echo $second_array[0]['name'];?></span></li>
                        <li class="click"><span><?php echo $second_array[1]['name'];?></span></li>
                        <li class="click"><span><?php echo $second_array[2]['name'];?></span></li>
                    </ul>
                </div>
                 <div class="border_bottom mb20">
                    <p class="n36">Q:相貌？</p>
                    <ul style="margin-left:10%; list-style:upper-alpha;">
                    	<li class="click"><img src="<?php echo $third_array[0]['url'];?>" width="30%"/></li>
                    	<li class="click"><img src="<?php echo $third_array[1]['url'];?>" width="30%"/></li>
                    	<li class="click"><img src="<?php echo $third_array[2]['url'];?>" width="30%"/></li>
                    </ul>
                </div>
            </div>   
           <div class="n30 content">
                <input type="hidden" id="user_id" value="<?php echo $user_id;?>" />
                <input type="hidden" id="loan_id" value="<?php echo $lid;?>" />
           		<input type="hidden" id="from_user_id" value="<?php echo $from_user_id;?>" />
           		<input type="hidden" id="first_answer" value="<?php echo $first_answer;?>" />
           		<input type="hidden" id="second_answer" value="<?php echo $second_answer;?>" />
           		<input type="hidden" id="third_answer" value="<?php echo $third_answer;?>" />
           		<input type="hidden" id="first_question" value="<?php echo $first_question;?>" />
           		<input type="hidden" id="second_question" value="<?php echo $second_question;?>" />
           		<input type="hidden" id="check_pic" value="" />
                <button type="button" id="auth_invest_loan" class="btn" style="width:100%">确定</button>
            </div>
       </div>
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