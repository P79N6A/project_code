<style>
body{background: #030707;}
</style>
<div class="actrel">
        <img src="/images/activity/bgbg.jpg">
        <div class="timejpin">
                <img src="/images/activity/timelist.png">
                <div class="timelist">
                    <!--优惠券奖励部分-->
                    <?php if(!empty($activity_coupon)): ?>
                        <?php foreach ($activity_coupon as $value) {
                            $val_arr = explode("#", $value);
                            echo "<p><span>".$val_arr[0]."</span><em>".$val_arr[1]."</em></p>";
                        } ?>
                    <?php endif; ?>
                    <!--流量奖励部分-->
                    <?php if(!empty($activity_liuliang)): ?>
                        <?php foreach ($activity_liuliang as $value_l) {
                            $val_arr_l = explode("#", $value_l);
                            echo "<p><span>".$val_arr_l[0]."</span><em>".$val_arr_l[1]."</em></p>";
                        } ?>
                    <?php endif; ?>
                    <!--话费奖励部分-->
                    <?php if(!empty($activity_huafei)): ?>
                        <?php foreach ($activity_huafei as $value_h) {
                            echo "<p><span>".$value_h."</span><em>30元话费</em></p>";
                        } ?>
                    <?php endif; ?>
                </div>
                <p class="bzhu">备注：优惠券将在获得奖励时间当天发送至您的账户中流量及话费将在获得奖励时间后的3-5个工作日充值到您的注册手机号中</p>
        </div>
</div>