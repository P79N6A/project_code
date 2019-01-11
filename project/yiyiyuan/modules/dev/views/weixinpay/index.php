<html>
<head>
    <title>G+统一对外接口</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

</head>
<body>
    <!-- 获取后台传来的表单数据,并自动提交 -->
     <label>*****下单接口******</label> <br/>
    <!--<form action="http://gplus.treespaper.com/gplus-api/rest/unifiedOrder" method="post">-->
    <form action="/dev/weixinpay/weixinpay" method="post">
      <br/>
      <label>* 商户ID</label> <input type="text" id = "mI" value="" name="merchantId"/> <br/>
      <label>* 时间戳 </label> <input type="text" id = "ts" value="1489473765666" name="timestamp"/> <br/>
      <label>*商户订单号 </label> <input type="text" id = "mOI" value="" name="merOrderId"/> <br/>

	  <label>* 渠道类型</label> <input type="text"  value="" name="channel"/> <br/>
      <label>* 订单总金额</label> <input type="text"  value="" name="totalFee"/> <br/>
	  <label>* 商品名称</label> <input type="text"  value="" name="productName"/> <br/>
	  <label>来源平台</label> <input type="text"  value="" name="platform"/> <br/>
	  <label>* 业务类型</label> <input type="text"  value="" name="businessType"/> <br/>
	  <label>附加数据</label> <input type="text"  value="" name="optional"/> <br/>
	  
	  <label>分析数据</label> <input type="text"  value="" name="analysis"/> <br/>
	  <label>* 同步返回页面</label> <input type="text"  value="" name="returnUrl"/> <br/>
	  <label>异步通知URL</label> <input type="text"  value="" name="serverUrl"/> <br/>
	  <label>订单失效时间</label> <input type="text"  value="" name="billTimeout"/> <br/>
	  <label>*商户的privateKey</label> <input type="text" id = "K" value="" name="privateKey"/> <br/>
	  <label>* 加密签名</label> <input type="text" id="SignatureTest" value="" name="signature"/> <br/>

     
      
      <label>signatureString=md5(merchantId + timestamp + key + merOrderId)，32位16进制格式,区分大小写</label><br/>
	  <label>业务类型为该商户开通的业务类型对应的ID，一般为电商：BT_114271207826925</label>
      <br/>
      
      <input type="submit" value="提交"/>
	 
    </form> 
</body>
</html>