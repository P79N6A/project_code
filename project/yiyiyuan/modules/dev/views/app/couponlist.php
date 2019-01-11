<div class="youhuiju">
    <div class="user_rule">
        <a class="rule_two" href="/dev/app/couponrule"><img src="/images/banner/rule_gz.png">使用规则</a>
    </div>
    <?php if (!empty($couponlist)): ?>
        <div class="layer" style="position:absolute;">
            <div class="content padlr">
                <?php foreach ($couponlist as $key => $val): ?>
                    <div class="item">
                        <img src="/images/banner/yhjj2.png" class="available2">
                        <div class="price_left">
                            <p class="black">借款<span>券</span></p>
                        </div>
                        <div class="price_right">
                            <p class="one_two"><?php echo $val->title; ?></p>
                            <p class="one_three">有效期：<?php echo date('Y年m月d日', strtotime($val->end_date)-24*3600); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
                <p class="centera"><?php if ($status == 1): ?>没有更多可用券了<?php else: ?>没有更多过期券了<?php endif; ?>  <a href="/dev/app/couponlist?user_id=<?php echo $user_id; ?>&status=<?php echo $status == 1 ? 2 : 1; ?>"> <?php if ($status == 1): ?>查看过期券>><?php else: ?>查看可用券>><?php endif; ?></a></p>
            </div>                    
        </div>
    <?php else: ?>
        <div class="noquan">
            <img src="/images/banner/noquan.png">
            <p>吓？！小主还没有优惠券？！</p>
            <p class="centera"><?php if ($status == 1): ?>没有更多可用券了<?php else: ?>没有更多过期券了<?php endif; ?>  <a href="/dev/app/couponlist?user_id=<?php echo $user_id; ?>&status=<?php echo $status == 1 ? 2 : 1; ?>"> <?php if ($status == 1): ?>查看过期券>><?php else: ?>查看可用券>><?php endif; ?></a></p>
        </div> 
    <?php endif; ?>
</div>