<?php include template("manage_header");?>
<div class="w2 clearfix">
	<?php include template("manage_left");?>
    <div class="mn">
    	<div class="crm"><a href="/manage/order.php?action=list">首页</a>&nbsp;&gt;&gt;&nbsp;<a href="#">订单管理</a>&nbsp;&gt;&gt;&nbsp;全部订单</div>
        <div class="wrap">
            <form action="/manage/order.php?action=list" method="post" id="excel_form_button">
            <table class="table" width="100%" border="1" bordercolor="#b61a3f" cellpadding="0" cellspacing="0" rules="rows">
                <tbody>
                    <tr class="odd">
                        <td>
                            <div class="itm">
                            	<div class="dt">订单号：</div>
                                <div class="dd"><input type="text" name="orderid" id="order_id" <?php if(!empty($productname) ){?> value="<?php echo $order_id; ?>"<?php }?>  class="ipt ipt1" /></div>
                            </div>
                            <div class="itm">
                                <div class="dt">订单状态：</div>
                                <div class="dd">
                                	<select name="state">
                                        <option value="all">--全部--</option>
                                        <option value="pay" <?php if($state == pay){?>selected <?php }?>>已支付</option>
                                        <option value="del" <?php if($state == del){?>selected <?php }?>>已删除</option>
                                        <option value="complete" <?php if($state == complete){?>selected <?php }?>>已完成</option>
                                        <option value="unpay" <?php if($state == unpay){?>selected <?php }?>>未支付</option>
                                    </select>
                                </div>
                            </div>
                            <div class="itm">
                            	<div class="dt">总金额：</div>
                                <div class="dd"><input type="text" name="smallorigin" <?php if(!empty($smallorigin) ){?> value="<?php echo $smallorigin; ?>"<?php }?>  class="ipt ipt2" /><span>—</span><input type="text" name="bigorigin" <?php if(!empty($bigorigin) ){?> value="<?php echo $bigorigin; ?>"<?php }?>  class="ipt ipt2" /></div>
                            </div>
                            
                        </td>
                        <td rowspan="2">
                        	<div class="btns"><input type="submit" id="export_simple_button" value="查找" class="btn btn1" /></div>
                        </td>
                    </tr>
                    <tr class="even">
                        <td>
                            <div class="itm">
                                <div class="dt">起始时间：</div>
                                <div class="dd"><input type="text" name="begintime" onclick="WdatePicker();" <?php if(!empty($newbegintime) ){?> value="<?php echo $newbegintime; ?>"<?php }?>  class="ipt ipt3" /></div>
                            </div>
                            <div class="itm">
                                <div class="dt">终止时间：</div>
                                <div class="dd"><input type="text" name="endtime" onclick="WdatePicker();" <?php if(!empty($newendtime) ){?> value="<?php echo $newendtime; ?>"<?php }?>  class="ipt ipt3" /></div>
                            </div>
                            <div class="itm">
                            	<div class="dt">卖家账户：</div>
                                <div class="dd"><input type="text" name="saleraccount"  <?php if(!empty($saleraccount) ){?> value="<?php echo $saleraccount; ?>"<?php }?>  class="ipt ipt4" /></div>
                            </div>
                            <div class="itm">
                            	<div class="dt">买家账户：</div>
                                <div class="dd"><input type="text" name="buyeraccount"  <?php if(!empty($buyeraccount) ){?> value="<?php echo $buyeraccount; ?>"<?php }?>  class="ipt ipt4" /></div>
                            </div>
                        </td>
                    </tr>
                    <tr class="odd">
                        <td>
                            <div class="itm">
                            	<dt class="dt">统计信息：</dt>
                                <dd class="dd">订单总笔数：<?php echo $countsum; ?>，结算订单<?php echo $countpaysum; ?>，待结算订单<?php echo $countunpaysum; ?>，已完成订单<?php echo $countcompletesum; ?>，已删除订单<?php echo $countdelsum; ?></dd>
                            </div>
                        </td>
                        <td>
                        	<div class="btns"><input type="submit" value="导出Excel" id="export_excel_button" class="btn btn2" /></div>
                        </td>
                    </tr>
                </tbody>
            </table>
            </form>
            <table class="table2" width="100%" border="1" bordercolor="#b61a3f" cellpadding="0" cellspacing="0" rules="rows">
            	<thead>
                	<tr>
                    	<th width="126">订单编号</th>
                        <th width="148">商品名称</th>
                        <th width="80">卖家</th>
                        <th width="80">买家</th>
                        <th width="26">数量</th>
                        <th width="80">总额</th>
                        <th width="52">订单状态</th>
                        <th width="26">操作</th>
                    </tr>
                </thead>
            	<tbody>
            	<?php if(is_array($order)){foreach($order AS $index=>$one) { ?>
                	<tr <?php if($index%2 != 0){?> class="odd" <?php } else { ?> class="even" <?php }?> >
                    	<td><?php echo $one['pay_id']; ?></td>
                        <td><a href="/manage/order.php?action=detail&id=<?php echo $one['id']; ?>" ><?php echo $one['productname']; ?></a></td>
                        <td><?php echo $one['saler']; ?></td>
                        <td><?php echo $one['buyer']; ?></td>
                        <td><?php echo $one['quantity']; ?></td>
                        <td>￥ <?php echo $one['origin']; ?></td>
                        <td><?php if($one['state'] == 'unpay'){?>未付款<?php } else if($one['state'] == 'pay' ) { ?>已付款<?php } else if($one['state'] == 'del') { ?>已删除<?php } else if($one['state'] == 'complete' ) { ?>已完成<?php }?></td>
                        <td><a href="/manage/order.php?action=detail&id=<?php echo $one['id']; ?>">详情</a></td>
                    </tr>
                <?php }}?>
                </tbody>
            </table>
            <div class="page clearfix">
            <?php if($count > 20){?>
		       <?php echo $pagestring; ?>
	       <?php }?>
            </div>
        </div>
    </div>
</div>
<?php include template("manage_footer");?>