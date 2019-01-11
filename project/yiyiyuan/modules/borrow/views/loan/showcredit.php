<style>
    a{text-decoration:none;}
    .alert { width: 100vw; height: 100vh; background: rgba(0, 0, 0, 0.4); display: -webkit-box; display: -ms-flexbox; display: flex; -webkit-box-align: center; -ms-flex-align: center; align-items: center; -webkit-box-pack: center; -ms-flex-pack: center; justify-content: center;z-index: 9;position: fixed;top: 0;left: 0; }
    .alert-box { width: 7.89rem; background: #ffffff; border-radius: 0.13rem; position: relative; padding: 0 0.40rem; -webkit-box-sizing: border-box; box-sizing: border-box; }
    .alert-title { font-size: 0.48rem; color: #444444; font-weight: 700; margin: 0.75rem auto 0.40rem; text-align: center; }
    .alert-tips { font-size: 0.37rem; color: #999999; padding: 0.13rem 0.53rem; -webkit-box-sizing: border-box; box-sizing: border-box; }
    .alert-tips-content { background: #f7f7f7; border-radius: 0.13rem; width: 100%; padding: 0.19rem 0.53rem; -webkit-box-sizing: border-box; box-sizing: border-box; margin-bottom: 0.27rem; font-size: 0.37rem; color: #444444; }
    .alert-btn { font-size: 0.43rem; color: #ffffff; background-image: -webkit-gradient(linear, left top, right top, from(#f00d0d), to(#ff4b17)); background-image: -o-linear-gradient(left, #f00d0d 0%, #ff4b17 100%); background-image: linear-gradient(90deg, #f00d0d 0%, #ff4b17 100%); border-radius: 0.13rem; width: 3.15rem; height: 1.07rem; margin: 0.72rem auto 0.40rem; text-align: center; line-height: 1.07rem; }
    body{
        height:25.2rem;
    }
    .cash_date{
        opacity: 0.8;
        font-family: "微软雅黑";
        font-size: 0.37rem;
        color: #999999;
        letter-spacing: 0;
        line-height: 0.37rem;
        position: absolute;
        right: 0.27rem;
        top: 0.27rem;
    }
</style>
<?php
if(isset($_GET['type'])){
    $urlType = 1;
}else{
    $urlType = 2;
} ?>
<div class="payout_wrap" style="height:20rem;position:relative;margin-bottom: 1rem;">

    <!-- 1:未测评;2已测评不可借;3:评测中;4:已测评可借未购买;6:已过期;-->
    <?php if ($user_credit_status == 3): ?>
        <div class="payout_main_top">
            <div class="main_text">
                <img src="/borrow/310/images/getPay.png" alt="" class="getPayIcon">
                <span class="getPayTitle">额度获取中</span>
                <span class="getPayText">预计5分钟内即可完成</span>
            </div>
            <div class="payout_identification">
                <img src="/borrow/310/images/identificationBar2.png" class="identificationBar">
                <p class="stepNumOne">个人资料安全认证</p>
                <p class="stepNumOneDate"><?php echo $userinfo_data_time; ?>完成</p>
                <p class="stepNumTwo">资料审核中</p>
                <p class="stepNumTwoText">您可以通过完善更多信息加速审核速度！</p>
                <p class="stepNumThree">申请借款</p>
            </div>
            <?php if ($audit_status == 2): ?>
            <div class="checkBtn" onclick="jumpSelection()" style="top:8.5rem">
                    加快审核
                </div>
            <?php endif; ?>
            <div class="aboutBtn">
                点击<a href="javascript:;" class="linkBtn" >“<span style="border-bottom: 1px solid #F00D0D;padding: 5px;">关注</span>”</a>公众号，每周五有新福利！
            </div>
        </div>
    <?php elseif ($user_credit_status == 4 || $user_credit_status == 5): ?>
        <div class="payout_main_top">
            <span class="identification_title">恭喜您，额度审批通过</span>
            <?php if ($jg_remark == 1): ?>
                <?php if ($user_credit_status == 4): ?>
                    <span class="identificationText">额度待激活，激活后立即下款</span>
                <?php elseif ($user_credit_status == 5): ?>
                    <span class="identificationText">额度已激活，请尽快完成借款</span>
                <?php endif; ?>   
            <?php endif; ?>
            <div class="count_identification">
                <div class="cash_identification">
                    <span class="cash_title">可借现金额度</span>
                    <span class="cash_date">可借期限：<?php echo $can_max_days?>天/<?php echo $period;?>期</span>
                    <span class="cash_count"><span style="font-size: 0.8rem;">￥</span><?php echo $can_max_money; ?></span>
                </div>
                <p class="count_time">额度有效期至<span class="red"><?php echo date('Y/m/d H:i', strtotime($invalid_time)); ?></span>，剩余<span class="red"><?php echo strtotime($invalid_time) - time() > 0 ? ceil((strtotime($invalid_time) - time()) / 3600) : 0; ?>小时</span></p>
                <p class="count_time_txt">超出有效期，需要重新申请</p>
            </div>
            <?php if ($user_credit_status == 4): ?>
                <div class="test_active" style="top:8rem;" id="evaluation_activation" onclick="buySiganl()" >测评激活</div>
                <div  class="immediate_active" style="top:8rem;" id="redirct_activation" onclick="direct_activation()" >直接激活</div>
                   <div class="alert-box" id="cue_activating" style="width: 90%;display: none; position: fixed; top: 64%;left: 5%;border-radius: 5px; z-index: 100; padding:10px 0;background:rgba(0,0,0,0.5); color: #fff;text-align: center;font-size: 0.4rem; ">
                    您的激活申请正在处理中，请耐心等待
                </div>
                <div class="poppay_mask" id="toast_mask"  style="position: fixed;top: 0;left: 0;z-index: 1;" hidden></div>
                <div id="toast" hidden style="height: 4.75rem;width: 7.89rem;position: fixed; top: 16%;left: 10%;border-radius: 0.13rem;; z-index: 9999;background: #fff; padding-bottom: 20px; " id="tanceng" >
                    <img src="/borrow/310/images/bill-close.png" alt="" id="reject_activation1" class="close_mask">
                    <h3 style="text-align: center;font-size: 0.5rem;padding-top: 0.9rem;color: #444;font-weight: bold;">温馨提示</h3>
                    <p style="line-height: 0.55rem;padding-bottom: 25px; text-align: left; font-size: 0.43rem; color: #444; margin: .6rem 5% .2rem;">你已激活失败两次，三次激活失败将导致额度激活失败，是否确认激活？</p >
                    <button style="width: 40%;height: 1rem; color: #c2c2c2; background: #fff; border:1px solid #A4A4A4; font-size: 0.43rem;  margin-left:6%;padding: 8px 0;border-radius: 20px;" id="confirm_activation">确认激活</button>
                    <button style="width: 40%;height: 1rem; background-image: linear-gradient(-90deg, #F00D0D 0%, #FF4B17 100%); border:1px solid #c90000; font-size: 0.43rem; color: #fff; margin: 0 6% 0 5%;padding: 8px 0;border-radius: 20px;" id="reject_activation" >取消</button>
               </div>
            <?php elseif ($user_credit_status == 5 ): ?>
                <div class="payout_Btn" id="go_loan">立即借款</div>
            <?php endif; ?>          

        </div>
        <!--        申请条件-->
        <div class="bannerApply"  >
            <div class="bannerTitle">
                <span>申请条件</span>
            </div>
            <div class="bannerImg"></div>
        </div>
        <div class="bannerMethods">
            <div class="bannerTitle">
                <span>逾期处理办法</span>
            </div>
            <div class="bannerImg"></div>
        </div>
    <?php elseif ($user_credit_status == 6): ?>
        <div class="payout_main_top">
            <span class="identification_title">您的借款额度已失效</span>
            <!--            <span class="again_apply">请重新获取</span>-->
            <p class="count_time_txt font_color" style="top: 1.6rem;left: 0.35rem;color:#FED4C9 !important;">超出额度有效期<?php echo date('Y/m/d H:i', strtotime($invalid_time)); ?>，需要重新申请</p>
            <div class="count_identification" style="height: 4.73rem;">
                <div class="cash_identification">
                    <span class="cash_title"  style="color: #000">申请额度</span>
                    <span class="cash_count"  style="color: #000"><span style="font-size: 0.8rem;">￥</span><span id="moneyTitle"><?php echo $can_max_money; ?></span></span>
                    <span class="w_change_cash_title" id="w_title_txt"  style="color: #000">更改额度</span>
                    <img src="/borrow/310/images/jiantou_ss.png" class="w_jiantou">
                </div>
            </div>
            <div class="cash_home_title" style="top:6.1rem">

                <div class="qixianone">
                    <img src="/borrow/310/images/jihua_ss.png" class="w_jihua_ss">
                    <span class="w_title_txt">借款期限</span>
                </div>
                <div class="qixianc">
                    <span class="w_title_txt" id="qixian" >30天x3期</span>
                    <img src="/borrow/310/images/jiantou_ss.png" class="jiantou_ss">
                </div>
            </div>
            <div class="payout_Btn" id="credit_valid" onclick="getcanloan(1)">
                重新获取额度
            </div>
        </div>
        <!--        申请条件-->
        <div class="bannerApply" style="top:9.7rem">
            <div class="bannerTitle">
                <span>申请条件</span>
            </div>
            <div class="bannerImg"></div>
        </div>
        <div class="bannerMethods" style="top:13.5rem">
            <div class="bannerTitle">
                <span>逾期处理办法</span>
            </div>
            <div class="bannerImg"></div>
        </div>
    <?php elseif ($user_credit_status == 2): ?>
         
             <?php if ($audit_status == 3): ?>
               <div class="payout_main_top">
                <span class="identification_title">很遗憾，您的额度审批未通过</span>
                <span class="again_apply" style="color:#FED4C9"> 评分不足</span>
                 <span class="tip_xin" style="color:#FED4C9">您可以24小时后重试或完善更多信息重新获取额度</span>
                <div class="count_identification" style=" height: 4.73rem;">
                    <div class="cash_identification">
                        <span class="cash_title font_color">申请额度</span>
                        <span class="cash_count font_color"><span style="font-size: 0.8rem;">￥</span><span id="moneyTitle"><?php echo $can_max_money; ?></span></span>
                        <span class="w_change_cash_title" id="w_title_txt_no" style="#999" >更改额度</span>
                        <img src="/borrow/310/images/jiantou.png" class="w_jiantou">
                    </div>
                </div>
                <div class="cash_home_title">
                    <div class="qixianone" >
                        <img src="/borrow/310/images/jihua.png" class="w_jihua_ss">
                        <span class="w_title_txt" style="color: #999999;">借款期限</span>
                    </div>
                    <div class="qixianc" >
                        <span class="w_title_txt" id="qixian_no" style="color: #999999;" >30天x3期</span> 
                       <img src="/borrow/310/images/jiantou.png" class="jiantou_ss">
                    </div>
                </div>
                <div class="checkBtn rejeck_wszl" onclick="jumpSelection()" style="top:7.5rem">完善资料</div>
            <?php elseif ($audit_status == 4 || $audit_status == 0): ?>
                <div class="payout_main_top">
                <span class="identification_title">很遗憾，您的额度审批未通过</span>
                <span class="again_apply" style="color:#FED4C9">评分不足</span>
                <span class="tip_xin" style="color:#FED4C9">您可以24小时后重试或完善更多信息重新获取额度</span>
                <div class="count_identification" style="height: 4.73rem;">
                    <div class="cash_identification">
                        <span class="cash_title" style="color:#444">申请额度</span>
                        <span class=" cash_count"><span style="font-size: 0.8rem;">￥</span><span id="moneyTitle"><?php echo $can_max_money; ?></span></span>
                        <span class="w_change_cash_title" id="w_title_txt" style="color:#444" >更改额度</span>
                        <img src="/borrow/310/images/jiantou.png" class="w_jiantou">
                    </div>
                </div>
                <div class="cash_home_title">
                    <div class="qixianone">
                        <img src="/borrow/310/images/jihua_ss.png" class="w_jihua_ss">
                        <span class="w_title_txt" >借款期限</span>
                    </div>
                    <div class="qixianc" >
                        <span class="w_title_txt" id="qixian" >30天x3期</span> 
                       <img src="/borrow/310/images/jiantou.png" class="jiantou_ss">
                    </div>
                </div>
                <?php if($audit_status == 0):?>
                    <div class="checkBtn rejeck_cxhq" id="credit_reject" onclick="getcanloan_false()" style="top:7.5rem">重新获取额度</div>
                <?php else:?>
                    <div class="checkBtn rejeck_cxhq" id="credit_reject" onclick="getcanloan(2)" style="top:7.5rem">重新获取额度</div>
                <?php endif;?>
                
            <?php elseif($audit_status == 5):?> 
                <div class="payout_main_top">
                <span class="identification_title">很遗憾，您的额度审批未通过</span>
                <span class="again_apply" style="color:#FED4C9"> 评分不足</span>
                <span class="tip_xin" style="color:#FED4C9">您可以24小时后重试或完善更多信息重新获取额度</span>
                <div class="count_identification" style="height: 4.73rem;">
                    <div class="cash_identification">
                        <span class="cash_title" style="color:#444">申请额度</span>
                        <span class=" cash_count"><span style="font-size: 0.8rem;">￥</span><span id="moneyTitle"><?php echo $can_max_money; ?></span></span>
                        <span class="w_change_cash_title" id="w_title_txt" style="color:#444" >更改额度</span>
                        <img src="/borrow/310/images/jiantou.png" class="w_jiantou">
                    </div>
                </div>
                <div class="cash_home_title">
                    <div class="qixianone">
                        <img src="/borrow/310/images/jihua_ss.png" class="w_jihua_ss">
                        <span class="w_title_txt" >借款期限</span>
                    </div>
                    <div class="qixianc" >
                        <span class="w_title_txt" id="qixian" >30天x3期</span> 
                       <img src="/borrow/310/images/jiantou.png" class="jiantou_ss">
                    </div>
                </div>
                <div class="checkBtn rejeck_cxhq" id="credit_reject" onclick="getcanloan(2)" style="width: 4.2rem;top:7.5rem;left:0.40rem">重新获取额度</div>
                <div class="checkBtn rejeck_wszl" onclick="jumpSelection()" style="width: 4.2rem;top:7.5rem;left:5.4rem">完善资料</div>
                
            <?php endif; ?>
                
            <?php if ($audit_status == 3 || $audit_status == 4 || $audit_status == 5 ): ?>
                <div class="aboutBtn">
                    点击<a href="javascript:;" class="linkBtn">“<span style="border-bottom: 1px solid #F00D0D;padding: 5px;">关注</span>”</a>公众号，获取最快审批攻略
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($reject_data) && !empty($reject_data['is_reject'] && $reject_data['is_reject'] == 1)): ?>
            <div class="alert">
                <div class="alert-box">
                    <div class="alert-title">获取额度失败</div>
                    <div class="alert-tips">失败时间</div>
                    <div class="alert-tips-content"><?php echo $reject_data['reject_data'][0]; ?></div>
                    <div class="alert-tips">失败理由</div>
                    <div class="alert-tips-content"><?php echo $reject_data['reject_data'][1]; ?></div>
                    <!-- 我知道了 -->
                    <!-- <div class="alert-btn">我知道了</div> -->
                    <!-- 换家试试 -->
                    <a href="<?php echo $reject_data['guide_url']; ?>"><div class="alert-btn">换家试试</div></a>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    <div class="help_service" style="bottom:-0.5rem;">
        <img src="/borrow/310/images/tip.png" alt="" class="contact_service_tip">
        <a href="javascript:void(0);" onclick="doHelp('/borrow/helpcenter?user_id=<?php echo $user_id; ?>')"><span class="contact_service_text">获取帮助</span></a>
    </div>
</div>
<!--    存管弹窗-->
<div class="poppay_mask" id="toast_cg1"  style="position: fixed;top: 0;left: 0;z-index: 1;" hidden ></div>
<div class="mask_box" id="toast_cg_card" style="top: 38%;z-index: 2;" hidden>
    <img src="/borrow/310/images/bill-close.png" alt="" onclick="close_toast()" class="close_mask">
    <p class="mask_title">温馨提示</p>
    <p class="mask_text" >很抱歉，您未绑定存管银行卡无法发起借款，请立即绑定</p>
    <span class="add_btn go_pwd_list" id="go_card">立即绑卡</span>
</div>
<div class="mask_box" id="toast_cg_pwd" style="top: 38%;z-index: 2;" hidden>
    <img src="/borrow/310/images/bill-close.png" alt="" onclick="close_toast()" class="close_mask">
    <p class="mask_title">温馨提示</p>  
    <p class="mask_text">很抱歉，您未绑定存管银行卡无法发起借款，请立即绑定,绑卡前需先设置交易密码</p>
    <span class="add_btn go_pwd_list">立即设置</span>
</div>
<div class="mask_box" id="toast_cg_intime" style="top: 38%;z-index: 2;" hidden>
    <img src="/borrow/310/images/bill-close.png" alt="" onclick="close_toast()" class="close_mask">
    <p class="mask_title">温馨提示</p>  
    <p class="mask_text">由于您的操作授权已失效，请重新授权</p>
    <span class="add_btn go_pwd_list">重新授权</span>
</div>
<div class="mask_box" id="open_account" style="top: 38%;z-index: 2;" hidden>
    <img src="/borrow/310/images/bill-close.png" alt=""  onclick="close_toast()" class="close_mask">
    <p class="mask_title">温馨提示</p>  
    <p class="mask_text">本平台现已接入存管系统，为了您的账户安全，请开通存管账户</p>
    <p style="position: absolute;padding-right: 0.2rem;left: 0.53rem;top: 2.8rem;color: #F00D0D;font-size: 0.3rem;">提示：请使用您在一亿元开户身份证进行存管开户</p>
    <span class="add_btn go_pwd_list" style="top:3.45rem;">立即开户</span>
</div>
<div class="mask_box" id="idcard_diff" style="top: 38%;z-index: 2;" hidden>
    <img src="/borrow/310/images/bill-close.png" alt=""  onclick="close_toast()" class="close_mask">
    <p class="mask_title">温馨提示</p>  
    <p class="mask_text">很抱歉，由于您一亿元开户身份证与存管开户身份证不符，开户失败！暂不可发起借款！</p>
    <span class="add_btn go_idcard" style="top:3.45rem;">确定</span>
</div>

<!--存管卡导致提现失败弹窗-->
<!--<div class="poppay_mask" id="toast"  style="position: fixed;top: 0;left: 0;z-index: 1; "  ></div>-->
<div class="mask_box" id="toast_tixian_fail" style="top: 38%;z-index: 2;height:5rem;" hidden >
    <img src="/borrow/310/images/bill-close.png" onclick="close_toast()" class="close_mask" style="width: 0.35rem;height: 0.35rem;">
    <p class="mask_title" style="top:0.8rem;">温馨提示</p>  
    <?php if($card_tixian_fail == 1):?>
    <p class="mask_text" style="left:0.5rem; top:1.6rem;">很抱歉，由于您绑定的存管卡暂不支持提现，导致借款驳回，请更换存管卡后重新发起借款</p>
    <span class="add_btn" onclick="unbankcard()" style="top:3.5rem;">立即更换</span>
    <?php elseif( $card_tixian_fail == 0 && $card_unband_fail==1 ):?>
    <p class="mask_text" style="left:0.5rm; top:1.6rem;">由于当前银行卡无法解绑，暂无法更换卡片，请联系客服解决</p>
    <span class="add_btn" onclick="window.location.href = 'https://www.sobot.com/chat/h5/index.html?sysNum=f0af5952377b4331a3499999b77867c2&robotFlag=1&partnerId=<?php echo $user_id;?>'" style="top:3.2rem;">联系客服</span>
    <?php endif;?>
</div>
<!--超过5天未提现-->
<div class="mask_box" id="toast_over_tixian" style="top: 38%;z-index: 2;height:7rem;" hidden >
    <img src="/borrow/310/images/bill-close.png" onclick="close_toast()" class="close_mask" style="width: 0.35rem;height: 0.35rem;">
    <p class="mask_title" style="top:0.8rem;">借款失效</p>  
    <p class="mask_text" style="left:1rem; top:1.6rem;">时间</p>
    <div class="mask_text" style=" top:2.2rem;line-height: 1rem; background:#F7F7F7;padding-left: 0.5rem;width:80%;border-radius:0.2rem;height:1rem" >2017-09-30 13:00:00</div>
    <p class="mask_text" style="left:1rem; top:3.5rem;">理由</p>
    <div class="mask_text" style=" top:4.1rem;line-height: 1rem; background:#F7F7F7;padding-left: 0.5rem; width:80%;border-radius:0.2rem;height:1rem">超过5天未提现</div>
    <span class="add_btn" onclick="close_toast()" style="top:5.5rem;">我知道了</span>
</div>
<div class="toast_tishi" id="xtfmang_false" style="top: 63%;background-color: #444;opacity:0.9;border-radius: 0.2rem;font-size: 0.3rem; " hidden >暂不可发起申请，请24小时后重试</div>
<div class="toast_tishi" id="xtfmang" style="top: 63%;" hidden>复制成功</div>
<?= $this->render('/layouts/footer', ['page' => 'loan', 'log_user_id' => $user_id]) ?>
<script src="/js/clipboard.min.js?v=10001" type="text/javascript"></script>
<script src="/290/js/jquery-1.10.1.min.js"></script>
<script src="/borrow/310/js/picker.js"></script>
<script>
<?php \app\common\PLogger::getInstance('weixin', '', $user_id); ?>
<?php $json_data = \app\common\PLogger::getJson(); ?>
        var baseInfoss = eval('(' + '<?php echo $json_data; ?>' + ')');
        var user_id = '<?php echo $user_info->user_id; ?>';
        var csrf = '<?php echo $csrf; ?>';
        var direct_activation_url = '<?php echo $direct_activation_url; ?>';
        var activation_btn_status = '<?php echo $activation_btn_status; ?>';
        var mobile = '<?php echo $user_info->mobile; ?>';
        var req_id = '<?php echo $req_id; ?>';
        var evaluation_activation_channel = '<?php echo $evaluation_activation_channel; ?>';
        var youxin_down_url = '<?php echo $youxin_down_url; ?>';
        var yxl_authentication_url = '<?php echo $yxl_authentication_url; ?>';
        var redict_activation_num = '<?php echo $redict_activation_num; ?>';
        var audit_status = '<?php echo $audit_status; ?>';
        var user_credit_status = "<?php echo $user_credit_status ?>";
        var error_id = '<?php echo $error_id; ?>';
        var card_tixian_fail = '<?php echo $card_tixian_fail; ?>';
        var card_unband_fail = '<?php echo $card_unband_fail; ?>';
        var fivedayover = '<?php echo $fivedayover; ?>';
        var clipboard = new Clipboard('.linkBtn', {
            text: function () {
                $('#xtfmang').show();
                $("#xtfmang").text('复制成功！');
                setTimeout(function () {
                    $("#xtfmang").hide();
                    $('#xtfmang').text('');
                }, 1000);
                return "xianhuayyy";
            }
        });
        clipboard.on('success', function (e) {
        });
/*页面诸葛打点start*/
        var urlType = "<?php echo $urlType; ?>";
        var typeInfo = "商城首页借款按钮";
        var creStaInfo = "额度获取中";
        if(urlType == 1){
            typeInfo = "商城首页借款购买按钮";
        }
        //查询状态
        if(user_credit_status == 4){
            creStaInfo = "额度待激活成功";
        }else if(user_credit_status == 5){
            creStaInfo = "额度已激活";
        }else if(user_credit_status == 6){
            creStaInfo = "额度已过期";
        }else if(user_credit_status == 2){
            creStaInfo = "审核驳回";
        }
        zhuge.track('借款首页', {
            '来源': typeInfo,
            '状态': creStaInfo,
        });
/*页面诸葛打点end*/

        $(function(){
            if(card_tixian_fail == 1 || (card_tixian_fail == 0 && card_unband_fail==1 ) ){
                $.ajax({
                    url: "/borrow/loan/tixianajax",
                    type: 'post',
                    async: false,
                    data: {_csrf: csrf,error_id:error_id},
                    success: function (json) {
                        json = eval('(' + json + ')');
                        if(json.res_code == '0000'){
                            $('#toast_cg1').show();
                            $('#toast_tixian_fail').show();
                        }
                    },
                    error: function (json) {
                        console.log('请求失败');
                    }
                });
            }
            if( card_tixian_fail == 0 && card_unband_fail==0 && fivedayover==1 ){
                $.ajax({
                    url: "/borrow/loan/tixianajax",
                    type: 'post',
                    async: false,
                    data: {_csrf: csrf,error_id:error_id},
                    success: function (json) {
                        json = eval('(' + json + ')');
                        if(json.res_code == '0000'){
                            $('#toast_cg1').show();
                            $('#toast_over_tixian').show();
                        }
                    },
                    error: function (json) {
                        console.log('请求失败');
                    }
                });
            }
        })
        if(user_credit_status == 2){
            var zl = "无";
            var cxhq = "无";
            if(audit_status == 3){
                zl = "有";
            }else if(audit_status == 4){
                cxhq = "有";
            }else if(audit_status == 5){
                zl = "有";
                cxhq = "有";
            };
            zhuge.track('测评驳回页面', {
                '重新获取额度按钮': cxhq,
                '完善资料按钮': zl,
            });
        }
        $('.rejeck_wszl').click(function(){
            zhuge.track('测评驳回-完善资料额度按钮');
        })
        $('.rejeck_cxhq').click(function(){
            zhuge.track('测评驳回-重新获取额度按钮');
        })
        var csrf = "<?php echo $csrf ?>";
        function jumpSelection() {
            zhuge.track('首页点击', {
                '按钮名称': '加速审核',
            });
            tongji('speed_up_audit', baseInfoss);
            setTimeout(function () {
                window.location.href = '/borrow/userinfo/selectioninfo';
            }, 100);

        }
        //存管解绑卡
        function unbankcard(){
            var bank_id = <?php echo $bank_id;?>;

            $.ajax({
                type: "POST",
                dataType: "json",
                url: "/new/bank/delcard?id=" + bank_id+'&type=1',
                async: false,
                data: {'_csrf': csrf},
                error: function (data) {
                },
                success: function (data) {
                    if (data.code == '0') {
                       if(data.data != ''){
                            location.href = data.data;
                       }else{
                            alert(data.message);
                       }
                    } else if (data.code == '2') {
                        alert(data.message);
                    } else {
                        alert(data.message);
                    }
                }
            });
        }

        //发起激活弹窗
        $('#go_loan').bind('click', function () {
//            zhuge.track('信用借款-立即借款');
            zhuge.track('首页点击', {
                '按钮名称': '立即借款',
            });
            tongji('go_loan_ajax', baseInfoss);
            $.ajax({
                url: "/borrow/loan/getcunguan",
                type: 'post',
                async: false,
                data: {_csrf: csrf},
                success: function (json) {
                    json = eval('(' + json + ')');
                    console.log(json);
                    if (json.rsp_code == '0000') { //跳转存管页面
                        //已开户 未绑卡 有密码 弹窗绑卡 直接去绑卡
                        //已开户 未绑卡 无密码 弹窗绑卡设密码 反向列表页
                        //已开户 已绑卡 无密码 正向列表
                        //已开户 未授权（还款和缴费授权） 正向列表页
                        if(json.isOpen != 1){
                            $('#toast_cg1').show();
                            $('#open_account').show();
                            $('#toast_cg_pwd').hide();
                            $('#toast_cg_card').hide();
                            $('#toast_cg_intime').hide();
                        }else if(json.isCard != 1 && json.isPass != 1){
                            //弹窗设密码绑卡
                            $('#toast_cg1').show();
                            $('#toast_cg_pwd').show();
                            $('#toast_cg_card').hide();
                            $('#open_account').hide();
                            $('#toast_cg_intime').hide();
                        }else if(json.isCard != 1){
                            //绑卡
                            $('#toast_cg1').show();
                            $('#toast_cg_card').show();
                            $('#toast_cg_pwd').hide();
                            $('#open_account').hide();
                            $('#toast_cg_intime').hide();
                        }else if(json.isOpen == 1 && json.isCard ==1 && json.isPass == 1 && json.isAuth == 1 && json.auth_error == 0){
                            //授权失效
                            $('#toast_cg1').show();
                            $('#toast_cg_intime').show();
                            $('#toast_cg_card').hide();
                            $('#open_account').hide();
                            $('#toast_cg_pwd').hide();
                        }else{
                            window.location.href = '/borrow/custody/list';
                        }
                    } else if (json.rsp_code == '1000') { //提示错误
                        alert(json.rsp_msg);
                        return false;
                    } else if (json.rsp_code == '2000') { //跳转到借款详情页
                        window.location.href = '/borrow/loan/startloan';
                    } else if (json.rsp_code == '3000') {  //存管开户身份证号与用户身份证号不一致 idcard_diff
                        $('#toast_cg1').show();
                        $('#idcard_diff').show();
                        $('#open_account').hide();
                        $('#toast_cg_pwd').hide();
                        $('#toast_cg_card').hide();
                    }
                },
                error: function (json) {
                    alert('请十分钟后发起借款');
                }
            });
        });

        $('.go_pwd_list').bind('click', function () {
            tongji('go_cunguan', baseInfoss);
            setTimeout(function () {
                window.location.href = '/borrow/custody/list';
            }, 100);
        });
        $('.go_idcard').bind('click', function () {
            $('#toast_cg1').hide();
            $('#toast_cg_pwd').hide();
            $('#toast_cg_card').hide();
            $('#open_account').hide();
            $('#idcard_diff').hide();

        });
        
        //发起假评测弹出toast提示
        function getcanloan_false(){
            $('#xtfmang_false').show();
            setTimeout(function () {
                $('#xtfmang_false').hide();
            }, 3000);
        }
        

        //发起评测
        function getcanloan(type) {
            if (type == 1) {
                zhuge.track('首页点击', {
                    '按钮名称': '重新获取额度-失效',
                });
                if ($('#credit_valid').hasClass('dis')) {
                    return false;
                }
                $('#credit_valid').addClass('dis');
            } else if (type == 2) {
                if ($('#credit_reject').hasClass('dis')) {
                    return false;
                }
                $('#credit_reject').addClass('dis');
            }
            zhuge.track('重新获取额度');
            tongji('re_get_quotas', baseInfoss);
            setTimeout(function () {
                var black_box = _fmOpt.getinfo();//获取同盾指纹
                $.ajax({
                    url: "/borrow/loan/getcanloan",
                    type: 'post',
                    async: false,
                    data: {_csrf: csrf,type:1,black_box:black_box},
                    success: function (json) {
                        json = eval('(' + json + ')');
                        if (json.rsp_code == '0000') {
                            if (json.is_change == 1) { //有待完善资料 跳转到认证页
                                window.location.href = '/borrow/userinfo/requireinfo';
                            } else if (json.is_change == 2) { //跳转到额度审核中页面
                                window.location.href = '/borrow/loan';
                            }
                        } else {
                            alert(json.rsp_msg);
                        }
                    },
                    error: function (json) {
                        alert('请十分钟后发起借款');
                    }
                });
            }, 2000);
        }
        //取消蒙层
        function close_toast() {
            $('#toast_cg1').hide();
            $('#toast_cg_intime').hide();
            $('#toast_cg_pwd').hide();
            $('#toast_cg_card').hide();
            $('#open_account').hide();
            $('#idcard_diff').hide();
            $('#toast').hide();
            $('#toast_tixian_fail').hide();
            $('#toast_over_tixian').hide();
        }

        function doHelp(url) {
            tongji('do_help', baseInfoss);
            setTimeout(function () {
                window.location.href = url;
            }, 100);
        }

    function buySiganl() { //测评激活
        tongji('evaluation_activation', baseInfoss);
        zhuge.track('额度待激活页面-测评激活额度按钮');
        is_click_evaluation(activation_btn_status);

    }

    function direct_activation() { //直接激活
        tongji('direct_activation', baseInfoss);
        zhuge.track('额度待激活页面-直接激活额度按钮');
        if (activation_btn_status == 0) {
            $('#cue_activating').show();
            setTimeout(function () {
                $('#cue_activating').hide();
            }, 3000);
        } else {
            if (redict_activation_num < 2) {
                setTimeout(function () {
                    window.location = direct_activation_url;
                }, 100);
            } else if (redict_activation_num == 2) {
                //弹框提示已激活2次
                $('#toast_mask').show();
                $('#toast').show();
            } else {
                console.log('已够三次，' + redict_activation_num);
            }
        }
    }

    function is_click_evaluation(click_status) {
        if (click_status == 0) {
            $('#cue_activating').show();
            setTimeout(function () {
                $('#cue_activating').hide();
            }, 3000);
            return false;
        }else{
            if (evaluation_activation_channel == 1) {  //下载智融app
                tongji('activation_down_app', baseInfoss);
                setTimeout(function () {
                    window.location = youxin_down_url;
                }, 100);

            } else if (evaluation_activation_channel == 2) {  //智融H5认证
                tongji('activation_zrys_h5', baseInfoss);
                setTimeout(function () {
                    window.location = yxl_authentication_url;
                }, 100);
            }
        }
    }
    $('#confirm_activation').click(function () {
        tongji('confirm_direct_activation', baseInfoss);

        setTimeout(function () {
            window.location = direct_activation_url;
        }, 100);
    });
    $('#reject_activation').click(function () {
        tongji('reject_direct_activation', baseInfoss);
        $('#toast_mask').hide();
        $('#toast').hide();
    });
    $('#reject_activation1').click(function () {
        $('#toast_mask').hide();
        $('#toast').hide();
    });

    function doHelp(url) {
        tongji('do_help', baseInfoss);
        setTimeout(function () {
            window.location.href = url;
        }, 100);
    }
    
    
        $.scrEvent({
        data: ['500元','1000元','1500元','2000元','2500元','3000元','3500元','4000元','4500元','5000元'],   // 数据
        //data: desc_lists,   // 数据
        evEle: '#w_title_txt',            // 选择器
        title: '选择申请额度',            // 标题
        defValue: '1000元',             // 默认值
        afterAction: function(data) { 
            console.log(data)//  点击确定按钮后,执行的动作
            if(data == '500元'){
                data = 500;
            }else if(data == '1000元'){
                data = '1,000';
            }else if(data == '1500元'){
                data = '1,500';
            }else if(data == '2000元'){
                data = '2,000';
            }else if(data == '2500元'){
                data = '2,500';
            }else if(data == '3000元'){
                data = '3,000';
            }else if(data == '3500元'){
                data = '3,500';
            }else if(data == '4000元'){
                data = '4,000';
            }else if(data == '4500元'){
                data = '4,500';
            }else if(data == '5000元'){
                data = '5,000';
            }
             $('#moneyTitle').html(data);
             $('#w_title_txt').html('更改额度');
        }
    });
    
      $.scrEvent({
        data: ['30天x3期','30天x6期','30天x9期','56天x1期'],   // 数据
        //data: desc_lists,   // 数据
        evEle: '#qixian',            // 选择器
        title: '选择期限',            // 标题
        defValue: '30天x3期',             // 默认值
        afterAction: function(data) { 
            console.log(data)//  点击确定按钮后,执行的动作
             $('#qixian').html(data);
          
        }
    });
</script>
