<div class="zuoyminew jk_item">
    <div class="shezhiminay">
        <?php if ($status == 5 && $loanview == 1): ?>
            <img style="width:32%;" src="/290/images/daihk5.png">
            <div class="imgimgnew"><img src="/290/images/daihaik5.png"></div>
        <?php elseif (in_array($status, [6]) && in_array($loanview, [1, 5])): ?>
            <img style="width:32%;" src="/290/images/daihk6.png">
            <div class="imgimgnew"><img src="/290/images/daihaik6.png"></div>
        <?php elseif (in_array($status, [20]) && $loanview == 5): ?>
            <img style="width:30%;" src="/290/images/daifqtx.png">
        <?php elseif (in_array($status, [21]) && $loanview == 5): ?>
<!--              <img style="width:30%;" src="/298/images/safe.png">-->
              <img style="width:30%;" src="/newdev/images/loan/activation.png">
              <div class="dj-status-content" style="position: absolute;top:10px; right:5%; font-size: 1.15rem; color: #c2c2c2;">
                <span class="dj-status-txt">剩余激活时间&nbsp;<span id="dateShow" style="color:#e74747;"><span class="h">00</span>:<span class="m">00</span>:<span class="s">00</span></span></span>
              </div>

              <style>
                  .shezhiminay{padding: 10px 0;}
                  .zuoyminew .daihukan_cont{margin-top: 0; border-top:1px solid #f3f3f3;}
                  .zuoyminew .bgrey.pcjhye{width:40%;float: left; }
                  .zuoyminew .bgrey.zjjhye{width:40%;float: right;}
                  .btom-text{position: fixed;bottom:10%;font-size: 1rem; color: #c2c2c2; text-align: center; width: 100%;}
              </style>
        <?php elseif (in_array($status, [22]) && $loanview == 5): ?>
            <img style="width:30%;" src="/298/images/matching.png">
            <!--<div class="imgimgnew"><img src="/290/images/daihaik2.png"></div>-->
        <?php elseif (in_array($status, [23]) && $loanview == 5): ?>
            <img style="width:30%;" src="/290/images/bohui.png">
            <div class="imgimgnew yuqiym" >
                <div class="corname" style="background: #fff;padding: 15px 0;text-align: left; font-weight: bold; color: #444; font-size: 1.1rem; font-weight: normal;">由于您长时间未提现，借款已被驳回，<span id="dateShowRej" style="color:#e74747;">
                <span class="h">00</span>:<span class="m">00</span>:<span class="s">00</span>
            </span>
                    后才可发起借款！
                </div>
            </div>
            <div style="background: #f3f3f3;"></div>
        <?php elseif ($status == 9 && $loanview == 2): ?>
            <img style="width:30%;" src="/290/images/daihk.png">
            <div class="imgimgnew"><img src="/290/images/daihaik2.png"></div>
        <?php elseif ($status == 11 && $loanview == 2): ?>
            <img style="width:32%;" src="/290/images/daihk3.png">
            <div class="imgimgnew"><img src="/290/images/daihaik3.png"></div>
        <?php elseif ($loanview == 3): ?>
            <img style="width:30%;" src="/290/images/daihk4.png">
            <div class="imgimgnew yuqiym">
                <p>你的逾期行为已经严重影响了你的信用评级，同时让你朋友的利益遭受损失，请马上还清借款！</p>
            </div>
        <?php elseif ($status == 18 && $loanview == 4): ?>
            <img style="width:25%;" src="/290/images/daihk7.png">
        <?php elseif ($status == 19 && $loanview == 4): ?>
            <img style="width:25%;" src="/290/images/daihk8.png">
        <?php endif; ?>
    </div>
    <div class="daihukan_cont" <?php if ($loanview == 4): ?> style="margin-top: 0" <?php endif; ?>>
        <div class="daoqihk">
            <?php if ($loanview == 1 || $loanview == 4 || $loanview == 5): ?>
                <div class="xzuoym">
                    <p>借款金额(元)</p>
                    <p><span><?= $loan_amount; ?></span></p>
                </div>
                <div class="xzuoym">
                    <p>借款周期(天) </p>
                    <p><span><?= $days; ?> </span> (分<?= $term; ?>期还)</p>
                </div>
            <?php elseif ($loanview == 2): ?>
                <p>应还金额(元)</p>
                <p><span><?= $huankuan_amount; ?></span></p>
            <?php elseif ($loanview == 3): ?>
                <p>逾期应还金额(元)</p>
                <p><span><?= $huankuan_amount; ?></span></p>
            <?php endif; ?>
        </div>
        <?php if ($loanview == 1 || $loanview == 5 || $loanview == 2 || $loanview == 4): ?>
            <div class="rowym">
                <div class="corname">到账金额(元)</div>
                <div class="corliyou"><?= $out_amount; ?></div>
            </div>
        <?php elseif ($loanview == 3): ?>
            <div class="rowym">
                <div class="corname">应还本金(元)</div>
                <div class="corliyou"><?= $loan_amount; ?></div>
            </div>
        <?php endif; ?>
        <?php if ($service_amount > 0): ?>
            <div class="rowym">
                <div class="corname">保险费(元)</div>
                <div class="corliyou"><?= $service_amount; ?></div>
            </div>
        <?php endif; ?>
        <div class="rowym">
            <div class="corname"><?= $term; ?>期总利息(元)</div>
            <div class="corliyou"><?= $interest_amount; ?></div>
        </div>
        <?php if ($overdue_days > 0 && ($loanview == 2 || $loanview == 3)): ?>
            <div class="rowym">
                <div class="corname">贷后管理费(元)</div>
                <div class="corliyou"><?= $overdue_amount; ?></div>
            </div>
        <?php endif; ?>
        <?php if ($loanview == 2): ?>
            <?php if ($coupon_amount != 0): ?>
                <div class="rowym">
                    <div class="corname">优惠券减免(元)</div>
                    <div class="corliyou"><?= $coupon_amount; ?></div>
                </div>
            <?php endif; ?>
            <?php if ($like_amount != 0): ?>
                <div class="rowym">
                    <div class="corname">点赞减息(元)</div>
                    <div class="corliyou"><?= $like_amount; ?></div>
                </div>
            <?php endif; ?>
            <div class="rowym">
                <div class="corname">第<?= $phase; ?>期（共<?= $term; ?>期）还款时间</div>
                <div class="corliyou"><?= $huankuantime; ?></div>
            </div>
        <?php endif; ?>
        <?php if ($overdue_days > 0 && ($loanview == 2 || $loanview == 3)): ?>
            <div class="rowym">
                <div class="corname">逾期天数(天)</div>
                <div class="corliyou"><?= $overdue_days; ?></div>
            </div>
        <?php endif; ?>
    </div>
    <?php if ($loanview == 5 && $status == 20): ?>
        <div style="margin: 20px 10%;">
            <h3 style="font-size: 14px; padding-bottom: 10px;">为你的人身安全添加保障</h3>
            <div >
                <p style="overflow: hidden;">
                    <input style="float: left;margin-top: 3px; margin-right: 3px;" name="buyinsures" type="checkbox" <?php if($insurance_default_check == 1){ ?>checked="checked"<?php } ?>>
                    <span>
                        本人承诺投保信息的真实性，理解并同意
                        <a style="color: #0000CC" href="<?= $insurance_url; ?>">《借款人意外伤害保险条款》</a>
                    </span>
                </p >
                <span style="float:right"><?= $insurance_amount; ?></span><p style="margin-left: 5%; color: #000000;" id="marks"><?= $insurance_checked_explain; ?></p >
            </div>        	
        </div>
    <?php endif; ?>
    <?php if ($loanview == 5 && $status == 21): ?>
<!--        <div class="rowym">
            <div class="corname" style="background: #fff;padding: 18px 0;text-align: center; border-top: 1px dotted #e1e1e1;font-weight: bold;">前往智融钥匙，并在<span id="dateShow" style="color:#e74747;"><span class="h">00</span>:<span class="m">00</span>:<span class="s">00</span></span>内完成安全认证！</div
        </div>-->
    <?php endif; ?>
    <div id="tixing" style="display: none;color: red; text-align: center;"></div>
    <?php if ($status != 11): ?>
        <?php if ($loanview == 2): ?>
            <button type="submit" class="bgrey" onclick="doRepay(<?php echo $loan_id; ?>)">我要展期</button>
        <?php elseif ($loanview == 3): ?>
            <button type="submit" class="bgrey" onclick="doRepay(<?php echo $loan_id; ?>)">马上展期</button>
        <?php elseif ($loanview == 4 && $status == 18): ?>
            <button type="submit" class="bgrey" onclick="getMoney(<?php echo $loan_id; ?>)">马上提现</button>
        <?php elseif ($loanview == 5 && $status == 20): ?>
            <button type="submit" class="bgrey" onclick="buyInsure(<?php echo $loan_id; ?>)">发起提现</button>
        <?php elseif ($loanview == 5 && $status == 21): ?>
            <button type="submit" class="bgrey pcjhye" id="evaluation_activation" onclick="buySiganl()">测评激活</button>
            <button type="submit" class="bgrey zjjhye" id="redirct_activation" onclick="direct_activation()">直接激活</button>
            
            <div class="btom-text">
                测评激活通过与第三方大数据金融信用平台合作，<br/> 可提高您的激活成功率
            </div>
            
            <div class="Hmask" style="display: none;" id="toast_mask"></div>
            <div id="toast" style=" width: 90%;position: fixed; top: 20%;left: 5%;border-radius: 5px; z-index: 100;background: #fff; padding-bottom: 20px; display: none;" id="tanceng" >
                <img src="/newdev/images/loan/cha.png" id="reject_activation1" style="position: absolute; top: -45px;right: 0; display: inline-block;width: 10%;">
                <h3 style="text-align: center;font-size: 18px;padding-top: 25px;color: #444;font-weight: bold;">是否确认激活？</h3>
                <p style="padding: 10px 5% 25px; text-align: left; font-size: 16px; color: #444;">你已激活失败两次，三次激活失败将导致借款驳回！</p >
                <button style="width: 40%; color: #c2c2c2; background: #fff; border:1px solid #c2c2c2; font-size: 16px;  margin-left:6%;padding: 5px 0;border-radius: 20px;" id="confirm_activation">确认激活</button>
                <button style="width: 40%; background: #c90000; border:1px solid #c90000; font-size: 16px; color: #fff; margin: 0 6% 0 5%;padding: 5px 0;border-radius: 20px;" id="reject_activation">取消</button>
           </div>
            
            <div class="alert-box" id="cue_activating" style="width: 90%;display: none; position: fixed; top: 50%;left: 5%;border-radius: 5px; z-index: 100; padding:10px 0;background:rgba(0,0,0,0.5); color: #fff;text-align: center;font-size: 1.2rem; ">
                    您的激活申请正在处理中，请耐心等待
           </div>
            
            
<!--        <div class="dj-btn-group">
            <div class="test-btn" id="evaluation_activation" onclick="buySiganl()" >测评激活</div>
            <div class="direct-btn" id="redirct_activation" onclick="direct_activation()" >直接激活</div>
                 激活提示信息 
                <div class="alert-box" id="cue_activating" style="display: none">
                    <span class=""> 您的激活申请正在处理中，请耐心等待</span>
                </div>
                <div class="btom-text .clearfix">
                    <span>测评激活通过与第三方大数据金融信用平台合作， 可提高您的激活成功率
                    </span>
                </div>
        </div>-->
        <?php endif; ?>
        <?php if ($loanview == 2 && $term == 1): ?>
            <!--    <button type="submit" class="bgrey hanhaoyou" onclick="doShare('--><?php //echo $shareUrl;                          ?><!--    ')">喊好友减息</button>-->
        <?php endif; ?>
    <?php endif; ?>
    <div class="marbot100"></div>
</div>
<!--底部定位-->
<!-- <div class="mMenu">
    <ul class="fCf">
        <li class="item-1 active"><a href="/new/loan"><i class="icon"></i> <em>借款</em></a></li>
        <li class="item-2"><a href="/mall/index?type=weixin"><i class="icon"></i> <em>商城</em></a></li>
        <li class="item-3"><a href="/new/account"><i class="icon"></i> <em>我</em></a></li>
    </ul>
</div> -->
<div class="Hmask" style="display: none;"></div>
<div style=" width: 90%;position: fixed; top: 20%;left: 5%;border-radius: 5px; z-index: 100;background: #fff; padding-bottom: 20px; display: none;" id="tanceng" >
    <p style="padding: 25px 5%; text-align: left; font-size: 18px; color: #c90000;"><?=$insurance_dialog_msg;?></p >
    <button style="width: 40%; color: #c2c2c2; background: #fff; border:1px solid #c2c2c2; font-size: 16px;  margin-left:6%;padding: 5px 0;border-radius: 20px;" id="staywith"><?=$insurance_dialog_cancel_text;?></button>
    <button style="width: 40%; background: #c90000; border:1px solid #c90000; font-size: 16px; color: #fff; margin: 0 6% 0 5%;padding: 5px 0;border-radius: 20px;" id="giveup" onclick="buy(<?php echo $loan_id; ?>, 1)"><?=$insurance_dialog_ok_text;?></button>
   </div>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
        var csrf = '<?php echo $csrf; ?>';
        var direct_activation_url = '<?php echo $direct_activation_url; ?>';
        var activation_btn_status = '<?php echo $activation_btn_status; ?>';
        var mobile = '<?php echo $user_info->mobile; ?>';
        var loan_id = '<?php echo $loan_id; ?>';
        var evaluation_activation_channel = '<?php echo $evaluation_activation_channel; ?>';
        var youxin_down_url = '<?php echo $youxin_down_url; ?>';
        var yxl_authentication_url = '<?php echo $yxl_authentication_url; ?>';
        console.log(activation_btn_status);
        $('input[name="buyinsures"]').change(function () {
            var is_chk = $('input[name="buyinsures"]').is(':checked');
            var insurance_checked = '<?= $insurance_checked_explain; ?>';
            var insurance_unchecked = '<?= $insurance_unchecked_explain; ?>';
            if (!is_chk) {
                $('#marks').html(insurance_unchecked);
                $('#marks').css('color','#e74747');
            } else {
                $('#marks').html(insurance_checked);
                $('#marks').css('color','#000000');
            }
        });
        function doRepay(loan_id) {
            window.location = '/renew/renewal?loan_id=' + loan_id;
        }

        function getMoney(loan_id) {
            $(".bgrey").attr('disabled', true);
            window.location = '/new/depository/getmoneyopen?loan_id=' + loan_id;
        }

        function buyInsure(loan_id) {
            var is_chk = $('input[name="buyinsures"]').is(':checked');
            $(".bgrey").attr('disabled', true);
            if (!is_chk) {
                $.post("/new/buyinsurance/getalertnum", {loan_id: loan_id,_csrf:csrf}, function (result) {
                    var data = eval("(" + result + ")");
                    if (data.code === '0000') {
                        if(data.isAlert == 1){
                            $('.Hmask').show();
                            $('#tanceng').show();
                        }else{
                            buy(loan_id, 2)
                        }
                    } else {
                        alert(data.msg);
                        $(".bgrey").attr('disabled', false);
                    }
                })
            } else {
                buy(loan_id, 1);
            }
        }

        function buySiganl() { //测评激活
                $.ajax({
        	url: '/new/evaluationactivation/clickstatus',
        	type: 'get',
        	data:{loan_id:loan_id},
                dataType: 'json',
                success: function(msg){
                    if( msg.back_code === '0000' ){
                        var click_status = msg.click_status;
                        //console.log(click_status);
                       is_click_evaluation(click_status);
                    }else{
                        console.log(msg.back_msg);
                    }
                    },
                    error:function(msg){
                     console.log('请求是否可点击测评激活按钮接口失败'+msg)
                    }
                });

        }
        
        function is_click_evaluation(click_status){
            
            if(  click_status ==0){
                $('#cue_activating').show();
                   setTimeout(function(){
                      $('#cue_activating').hide();
                   },3000);
                return false;
            }
           
            if( click_status != 0 ){   
                if(evaluation_activation_channel == 1){  //下载智融app
                     tongji('activation_down_app');
                    setTimeout(function(){
                        window.location = youxin_down_url;
                    },100);
                    
                }else if(evaluation_activation_channel == 2){  //智融H5认证
                       tongji('activation_zrys_h5');
                        setTimeout(function(){
                          window.location = yxl_authentication_url;
                        },100);
                     
                }
            }
        }

        function direct_activation(){
             var redict_activation_num = '<?php echo $redict_activation_num; ?>';
             tongji('direct_activation');
             if(activation_btn_status == 0){
                 $('#cue_activating').show();
                   setTimeout(function(){
                            $('#cue_activating').hide();
                   },3000);
            }else{
                if(redict_activation_num < 2){
                   
                        setTimeout(function(){
                            window.location = direct_activation_url;
                          },100);
                    
                }else if(redict_activation_num == 2){
                    //弹框提示已激活2次
                    $('#toast_mask').show();
                    $('#toast').show();          
                }else{
                    console.log('已够三次，'+ redict_activation_num);
                }
            }    
        }
        
        $('#confirm_activation').click(function(){
            tongji('confirm_direct_activation');
             
              setTimeout(function(){
                 window.location = direct_activation_url;
              },100);
        });
        $('#reject_activation').click(function(){
            tongji('reject_direct_activation');
             $('#toast_mask').hide();
             $('#toast').hide();
        });
        $('#reject_activation1').click(function(){
             $('#toast_mask').hide();
             $('#toast').hide();
        });
        $("#staywith").click(function () {
            $('.Hmask').hide();
            $('#tanceng').hide();
        });

        function buy(loan_id, is_chk) {
            $.post("/new/buyinsurance", {is_chk: is_chk, loan_id: loan_id, source: 1,_csrf:csrf}, function (result) {
                var data = eval("(" + result + ")");
                if (data.code === '0000') {
                    location.href = data.url;
                } else {
                    $('.Hmask').hide();
                    $('#tanceng').hide();
                    $('#tixing').html(data.msg);
                    $('#tixing').show();
                    setTimeout(function () {
                        $('#tixing').html('');
                        $('#tixing').hide();
                    }, 3000);
                }
            });
        }

        function doShare(url) {
            window.location = url;
        }

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

        $(function(){
            var dateShow = "<?php echo $dateShow = date('Y/m/d H:i:s',$yxl_count_down+time());?>";
            $.leftTime(dateShow,function(d){
                if(d.status){
                    var $dateShow1=$("#dateShow");
                    $dateShow1.find(".h").html(d.h);
                    $dateShow1.find(".m").html(d.m);
                    $dateShow1.find(".s").html(d.s);
                }
            });
        });

        $(function(){
            var dateShowRej = "<?php echo $dateShow = date('Y/m/d H:i:s',$reject_over_time+time());?>";
            $.leftTime(dateShowRej,function(d){
                if(d.status){
                    var $dateShow1=$("#dateShowRej");
                    $dateShow1.find(".h").html(d.h);
                    $dateShow1.find(".m").html(d.m);
                    $dateShow1.find(".s").html(d.s);
                }
            });
        });
        
        function tongji(event) {
        <?php \app\common\PLogger::getInstance('weixin','',$encodeUserId); ?>
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