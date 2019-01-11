    <div class="ttuika" style="background:#fff;">
        <div class="ttkui_img">
            <p class="jxyalc">讲信用，爱理财。</p>
            <p>就来先花一亿元投资 “<?php echo $standard_information->name;?>”</p>
            <div class="fxj_cont youan_scon">
                <div class="fxj_txt">
                    <p class="fxj_txtfirst"><span>双倍收益券</span></p>
                    <p class="fxj_txtsen">领取优惠券，获得双倍收益！</p>
                </div>
            </div>
        </div>
        <!--img src="images/shouyij.png"-->
        <div class="form_wrapper fenxyouhj"> 
            <div class="form_left">手机号</div> 
            <div class="form_right"> 
                <div class="form_content">
                    <input type="tel" placeholder="请填写手机号注册" name="mobile" id="mobile" maxlenght="11" class="phone-input">
                </div> 
            </div> 
        </div>
        <div class="free_code">
             <input type="tel" placeholder="验证码" class="yzm_input" maxlength="4" id="mobile_code">
            <button type="button" class="btn code-obtain" id="getcoupon_sendmobile">获取验证码</button>
        </div>
        <p class="red_txtxz" id="mobile_error"></p>
        <input type="hidden" name="from_user_id" id="from_user_id" value="<?php echo $from_user_id;?>" />
        <input type="hidden" name="standard_id" id="standard_id" value="<?php echo $standard_id;?>" />
        <button class="resetpay-sub" id="button_getcoupon" >立即领取</button>
    </div>
    
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