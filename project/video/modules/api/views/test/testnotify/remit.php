
<div class="row" style="padding-top:50px;">
    <div class="col-md-6">
<form action="" method="POST" target="_blank">
      <div class="form-group">
        <label class="radio-inline">
          <input type="radio" name="aid" checked="checked" id="aid" value="1"> 一亿元
        </label>
        <label class="radio-inline">
          <input type="radio" name="aid" id="aid" value="4"> 花生米富
        </label>
      </div>
      <div class="form-group">
        <label for="channel_id">出款通道</label>
                    <select class="form-control"  name="channel_id" id="channel_id">
                        <option value="1">新浪</option>
                        <option value="6">融宝</option>
                        <option value="8">宝付</option>
                        <option value="9">连连</option>
                        <option value="2">中信</option>
                        <option value="3">玖富</option>
                    </select>
      </div>
      <div class="form-group">
        <label for="req_id">订单号</label>
        <input class="form-control" type="input" name="req_id" id="req_id" value="" />
      </div>
      <div class="form-group">
        <label for="client_id">流水号</label>
        <input class="form-control" type="input" name="client_id" id="client_id" value="" />
      </div>
      <div class="form-group">
        <label for="callbackurl">callbackurl</label>
        <input class="form-control" type="input" name="callbackurl" id="callbackurl" value="http://yyy.xianhuahua.com/dev/notify/remitbackurl" />
      </div>  
      <div class="form-group">
        <label for="settle_amount">订单金额</label>
                    <input class="form-control" type="input" name="settle_amount" id="settle_amount" style="width:200px" value="" />
      </div>
      <div class="form-group">
        <label for="notify_result">通知结果</label>
                    <select class="form-control" name="notify_result" id="notify_result">
                        <option value="6">成功</option>
                        <option value="7">玖富接收成功</option>
                        <option value="11">失败</option>
                    </select>
      </div>
        <input type="submit" name="提交" value="提交" onclick="return submitNotify()"/>
</form>

    </div>
</div>
<script>
    function submitNotify() {
        var aid = $("#aid").val();
        var req_id = $("#req_id").val();
        var callbackurl = $("#callbackurl").val();
        var settle_amount = $("#settle_amount").val();
        var notify_result = $("#notify_result").val();
        var channel_id = $("#channel_id").val();
        if(req_id==""){
            alert("请输入订单号");
            return false;
        }
        if(callbackurl==""){
            alert("请输入回调地址");
            return false;
        }
        if(settle_amount==""){
            alert("请输入订单金额");
            return false;
        }
        return true;
    }
   </script>