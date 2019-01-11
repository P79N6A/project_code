<div class="friend_tzsy">
    <div class="frien_title"><?php echo!empty($list) ? '认证好友收益' : '暂无认证红包收益'; ?></div>
    <?php foreach ($list as $key => $val): ?>
        <?php $auth_user = $val->authuser->userwx; ?>
        <div class="frerecord_content">
            <div class="frecontent_left">
                <dl>
                    <dt><img src="<?php echo!empty($auth_user) && !empty($auth_user->head) ? $val->authuser->userwx->head : '/images/tx_img1.png'; ?>"></dt>
                    <dd>
                        <p class="left_ddp" style="height: 2rem;"><?php echo!empty($auth_user) ? $auth_user->nickname : ''; ?></p>
                        <p class="left_ddate"><?php echo date('H:i m月d日', strtotime($val->create_time)); ?></p>
                    </dd>
                </dl>
            </div>
            <div class="frecontent_right">
                <div class="frecontent_conte">
                    <p class="fren_green">红包金额</p>
                    <p class="fren_point"><?php echo round($val->amount, 2); ?>元</p>
                </div>
            </div>
        </div>
    <?php endforeach; ?>    
</div>