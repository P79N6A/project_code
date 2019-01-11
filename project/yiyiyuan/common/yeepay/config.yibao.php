<?php 
return [
#投资通生产环境下的商户编号
'merchantaccount'=>'10012471228',
#
#商户公钥
'merchantPublicKey'=>'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCKa/v0rF5UGgfIvHZR0NrxJ3wAN4eU+9BtNV/w86uBiLWHxRBkhA6asEA64zB1/0Hgh71ZaPre74686rrduuRVW/1MRGuQd73z82jSIMgOQh9bWCPUm4kgiDmTrnZWxvAedaivJelmdi5TK3CoPBjzK0xsvYysxuNf68XiZoPNXQIDAQAB',
#
#商户私钥
'merchantPrivateKey'=>'MIICdgIBADANBgkqhkiG9w0BAQEFAASCAmAwggJcAgEAAoGBAIpr+/SsXlQaB8i8dlHQ2vEnfAA3h5T70G01X/Dzq4GItYfFEGSEDpqwQDrjMHX/QeCHvVlo+t7vjrzqut265FVb/UxEa5B3vfPzaNIgyA5CH1tYI9SbiSCIOZOudlbG8B51qK8l6WZ2LlMrcKg8GPMrTGy9jKzG41/rxeJmg81dAgMBAAECgYAyjshjEIYPj8ZiEjvhHDirtjE7XwzdZLA3AzS8rDNrR4SOR3L6U6WF6HQ9TffIUWg9WzbUrlxbCwKGi/GexQFagaXu7C+Noy0sS9kqFXFmykbi5PGfBUH/P34Mhm5pboO22xBBzJGJgmojEDE0cUFPxBwusAsdq/JGYgeC0AEjXQJBAPXDf4PujuRhYyDxN5iW3FgdQbRkNQ86LTAu5916Hs58G6JxpcHX2X37ZnYETqivVZf9ySEfBb/v13kH66S4ExsCQQCQL+7jZA6lwjFZlx8sIGxSToicLoyplyU1UAw5a4fs2QpQUiN86KnxLR972cAUerSbfTz+GwGs08mY6adSWLDnAkEA319XEOTMv0q8vH5B19CWaQf+ZiUGDNcFp1uaprSON4KZ42WEENFM/rJ3CCEWFT93fnPOUOpPYYpuv7SxOr+LrQJAGQza2hK2IMI+RKxmtAnmB96xCUFlGsmxozOHDCrMcK+8hPvgQoFBlS8buy63mlc/LYxynkse3WHmMnTVpw7VnQJAdwr/16D3azIZj991fmhZvWOOQxz8N2BzCJJO4fyc4wrC+a6ccqwpIHTdpJl0/LbwS3BXPrh/aKzJ3xor1t+RNQ==',

#
#易宝公玥
'yeepayPublicKey'=>'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC1S53dP/KHcRBqfRB8ZQxhp+8UeIiUhtBpOKsEnmvvalk7bNbKBoj3AD75qtt1s17W5krk1fFIotvjzDLBxPhCdd2N2+vKdjlw8He0qhYYt6nXQ131UcEFmRnl+dThGs5Vz8lejV4Uvu2xYZiaEHEAZZpBRWlnESgkmLe2v9jzIwIDAQAB',


#绑卡请求地址
'bindBankcardURL'=>'https://ok.yeepay.com/payapi/api/tzt/invokebindbankcard',
#
#绑卡确认请求地址
'confirmBindBankcardURL'=>'https://ok.yeepay.com/payapi/api/tzt/confirmbindbankcard',
#
#4.2.1 发送短验-支付请求地址
'payNeedSmsURL'=>'https://ok.yeepay.com/payapi/api/tzt/pay/bind/reuqest',
#
#4.2.2 发送短信验证码接口请求地址
'smsSendURL'=>'https://ok.yeepay.com/payapi/api/tzt/pay/validatecode/send',
#
#4.2.3 确认支付请求地址
'smsConfirmURL'=>'https://ok.yeepay.com/payapi/api/tzt/pay/confirm/validatecode',
#
#4.3 支付接口--不发送短验请求地址
'directBindPayURL'=>'https://ok.yeepay.com/payapi/api/tzt/directbindpay',
#
#订单查询请求地址
'paymentQueryURL'=>'https://ok.yeepay.com/merchant/query_server/pay_single',
#
#取现接口请求地址
'withdrawURL'=>'https://ok.yeepay.com/payapi/api/tzt/withdraw',
#
#取现查询接口请求地址
'queryWithdrawURL'=>'https://ok.yeepay.com/payapi/api/tzt/drawrecord',
#
#绑卡查询接口请求地址
'queryAuthbindListURL'=>'https://ok.yeepay.com/payapi/api/bankcard/authbind/list',
#
#银行卡信息查询接口请求地址
'bankCardCheckURL'=>'https://ok.yeepay.com/payapi/api/bankcard/check',
#
#清算数据下载请求地址
'payClearDataURL'=>'https://ok.yeepay.com/merchant/query_server/pay_clear_data',
#
#单笔退款请求地址
'refundURL'=>'https://ok.yeepay.com/merchant/query_server/direct_refund',
#
#退款查询请求地址
'refundQueryURL'=>'https://ok.yeepay.com/merchant/query_server/refund_single',
#
#退款清算文件请求地址
'refundClearDataURL'=>'https://ok.yeepay.com/merchant/query_server/refund_clear_data',
#
#4.4 支付结果查询请求地址
'payapiQueryURL'=>'https://ok.yeepay.com/payapi/api/query/order',

#
#支付结果回调地址
'payResultCallbackURL'=>'http://open.xianhuahua.cn/xianhuahua_open/Common/CallBack/YeePay/Tzt/PayResult',
];