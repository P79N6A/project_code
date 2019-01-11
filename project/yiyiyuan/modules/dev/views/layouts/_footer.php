<footer>
                <ul class="text-center">
                	<li>
                    	<a href="https://open.weixin.qq.com/connect/oauth2/authorize?appid=<?php echo Yii::$app->params['AppID'];?>&redirect_uri=<?php echo Yii::$app->params['app_url'];?>/dev/invest&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect">
                    	<?php if( $page == 'invest'){?>
                    	<img src="/images/dev/01.png" width="33%"/>
                            <div class="red">投资</div>
                        <?php }else{?>
                        <img src="/images/dev/011.png" width="33%"/>
                            <div class="cor">投资</div>
                        <?php }?>
                    	</a>
                    </li>
                	<li class="text-center">
                    	<a href="https://open.weixin.qq.com/connect/oauth2/authorize?appid=<?php echo Yii::$app->params['AppID'];?>&redirect_uri=<?php echo Yii::$app->params['app_url'];?>/dev/loan&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect">
                    	<?php if( $page == 'loan'){?>
                    	<img src="/images/dev/02.png" width="33%"/>
                            <div class="red">借款</div>
                        <?php }else{?>
                        <img src="/images/dev/022.png" width="33%"/>
                            <div class="cor">借款</div>
                        <?php }?>
                    	</a>
                    </li>
                    <li>
                    	<a href="https://open.weixin.qq.com/connect/oauth2/authorize?appid=<?php echo Yii::$app->params['AppID'];?>&redirect_uri=<?php echo Yii::$app->params['app_url'];?>/dev/account&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect">
                    	<?php if( $page == 'account'){?>
						<img src="/images/dev/03.png" width="33%"/>
                            <div class="red">账户</div>
                        <?php }else{?>
                        <img src="/images/dev/033.png" width="33%"/>
                            <div class="cor">账户</div>
                        <?php }?>
                    	</a>
                    </li>
                </ul>
            </footer>
            
 
