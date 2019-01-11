
<footer>
    <ul  class="text-center">
        <li>
            <a href="https://open.weixin.qq.com/connect/oauth2/authorize?appid=<?php echo Yii::$app->params['AppID']; ?>&redirect_uri=<?php echo Yii::$app->params['app_url']; ?>/dev/loan&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect">
                <img src="/images/02.png" width="33%" style="height:22px;width:24px; "/>
                <?php if ($page == 'loan') { ?><div class="red n26"><?php } else { ?><div class="cor n26"><?php } ?>借款</div>
            </a>
        </li>
        <li>
            <a href="https://open.weixin.qq.com/connect/oauth2/authorize?appid=<?php echo Yii::$app->params['AppID']; ?>&redirect_uri=<?php echo Yii::$app->params['app_url']; ?>/dev/friends/first&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect">
                <img src="/images/icon_m_01.png" width="33%" style="height:22px;width:22px; "/>
                <?php if ($page == 'invest') { ?><div class="red n26"><?php } else { ?><div class="cor n26"><?php } ?>信用圈</div>
            </a>
        </li>
        <li>
            <a href="https://open.weixin.qq.com/connect/oauth2/authorize?appid=<?php echo Yii::$app->params['AppID']; ?>&redirect_uri=<?php echo Yii::$app->params['app_url']; ?>/dev/account&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect">
                <img src="/images/icon_m_03.png" width="33%" style="height:22px;width:22px; "/>
                <?php if ($page == 'account') { ?><div class="red n26"><?php } else { ?><div class="cor n26"><?php } ?>我</div>
            </a>
        </li>
    </ul>
</footer>
