<div class="y-wrap" style="width: 100%;">
    <div class="y-account">
        <div class="y-account-list">
            <p class="account-label">江西银行账户</p>
            <p><?php echo !empty($openInfo) ? $openInfo->accountId : ""; ?></p>
            <span></span>
        </div>
        <div class="y-account-list">
            <p class="account-label">绑定银行卡</p>
            <p style="font-size: 0.85rem;"><?php echo !empty($cardInfo) ? $cardInfo->card : ""; ?></p>
            <input type="hidden" id="bankId"  value="<?php echo !empty($cardInfo) ? $cardInfo->id : ''; ?>"/>
            <input type="hidden" id="csrfs" value="<?php echo $csrf; ?>" >
            <?php if($isCg['isCard'] != 0): ?>
<!--                <span id = "cgdelbanks" style="position:  absolute;right: 4%;">解绑</span>-->
            <span><a id="cgdelbanks" style="color: #F00D0D;" >解绑</a></span>
            <?php endif; ?>
        </div>
        <div class="y-account-list">
            <p class="account-label">预留手机号</p>
            <p><?php echo !empty($userInfo) ? $userInfo->mobile : ""; ?></p>
            <span></span>
        </div>
        <div class="y-account-list">
            <p class="account-label">交易密码</p>
            <?php if($isCg['isPass'] != 0): ?>
                <p>已设置</p>
            <?php else: ?>
                <p>未设置</p>
            <?php endif; ?>
            <?php if($isCg['isPass'] != 0): ?>
                <span><a href="/new/bank/editpwdnew?user_id=<?php echo $userInfo->user_id; ?>">修改</a></span>
            <?php endif; ?>
            <!-- <span>修改</span> -->
        </div>
        <div class="y-account-list">
            <p class="account-label">业务授权</p>
            <?php if($authStatus == 0): ?>
                <p>未授权</p>
            <?php elseif ($authStatus == 1): ?>
                <p>已授权</p>
            <?php elseif ($authStatus == 2): ?>
                <p>已过期</p>
            <?php else: ?>
                <p>未授权</p>
            <?php endif; ?>
            <!-- 已授权 -->
<!--            <span>立即授权</span>-->
            <!-- <span>重新授权</span> -->
        </div>
    </div>
    <div class="y-account-tips">
        <div class="tips-top">
            <i class="y-line"></i>
            <div class="y-tips-tit">温馨提示</div>
            <i class="y-line"></i>
            <div class="clear"></div>
        </div>
        <div class="clear"></div>
        <ul class="y-tips-list">
            <li>
                <i></i>
                <p>每个用户只能绑定一张存管银行卡</p>
            </li>
            <li>
                <i></i>
                <p>银行卡户主必须与您的实名认证姓名一致</p>
            </li>
            <li>
                <i></i>
                <p>不支持信用卡</p>
            </li>
            <li>
                <i></i>
                <p>解卡条件：江西银行账户月未0且没有未结清的借款时可进行解绑</p>
            </li>
            <li>
                <i></i>
                <p>若解绑卡失败，请联系客服！</p>
            </li>
        </ul>
    </div>
</div>
<script>
    $("#cgdelbanks").click(function () {
        setTimeout(function(){
            var bank_id = $("#bankId").val();
            var csrf = $("#csrfs").val();
            $.ajax({
                type: "POST",
                dataType: "json",
                url: "/new/bank/delcard?id=" + bank_id,
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
        },100);
    });
    //重写返回按钮
    var isApp = <?php
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'YYY_ANDROID') || strpos($_SERVER['HTTP_USER_AGENT'], 'yyyIOS')) {
            echo 1;
        } else {
            echo 2;
        }
        ?>;
    pushHistory();
    var bool = false;
    setTimeout(function () {
        bool = true;
    }, 500);
    window.addEventListener("popstate", function (e) {
        if (bool) {
            setTimeout(function () {
                if (isApp == 1) {
                    var u = navigator.userAgent, app = navigator.appVersion;
                    var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Linux') > -1;
                    var isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/);
                    var android = "com.business.bankcard.NewBankCardListAct";
                    var ios = "BankCardViewController";
                    var position = "-1";
                    console.log(isiOS);
                    console.log(isAndroid);
                    if (isiOS) {
//                        window.myObj.toPage(ios);
                    } else if (isAndroid) {
                        window.myObj.toPage(android, position);
                    }
                    window.myObj.closeHtml();
                    function closeHtml() {}
                }else{
                    window.location.href = "/new/bank/";
                    return false;
                }

            }, 1000);
        }
        pushHistory();
    }, false);
    function pushHistory() {
        var state = {
            url: "#"
        };
        window.history.pushState(state, "#");
    }
</script>