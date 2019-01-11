<?php include template("header");?>

<div class="blank108"></div>
<div class="blank60"></div>
<div class="w2 clearfix">
	<div class="commodityContent clearfix">
    	<div class="contentMain">
        	<div class="cmh">亲，您想卖什么呢？</div>
            <div class="cmc">
            	<div class="box">
                	<div class="mt" id="mt_up"><strong>请描述您的商品或服务</strong><span class="s1"></span></div>
                    <div class="mc" id="box_up">
                    	<div class="sem"></div>
                    	<div class="sname">
                        	<input type="text" id="product_name" maxlength="30" class="text" /><label for="product_name">请输入您的商品或服务名称</label>
                            <div class="ts" id="product_name_ts"></div>
                        </div>
                        <div class="sintro">
                        	<textarea class="text" id="product_content" maxlength="300"></textarea><label for="product_content">商品描述</label>
                            <div class="ts" id="product_content_ts"></div>
                        </div>
                        <div class="sprice">
                        	<span class="s1">价格：</span>
                            <span class="s2">￥ <input maxlength="10" type="text" id="product_price" class="text" /> 元<label for="product_price">格式提示19.99</label></span>
                            <div class="vvv1">提示：您的商品在成功出售后，三好网会按照每笔订单金额的3%收取服务费！</div>
                        </div>
                        <div class="sselect">
                        	<select name="type" class="select"><option value="1">这是一实物</option></select>
                        </div>
                        <div class="sfile">
                        	<span class="spanbg"></span>
                        	<input type="file" id="product_image" name="filename" doing="0" multiple="true" />
                        </div>
                        <div class="slist">
                       		
                        </div>
                    </div>
                </div>
                <div class="box">
                	<div class="mt" id="mt_down"><strong>更多选项</strong><span class="s2"></span></div>
                    <div class="mc" id="box_down">
                    	<div class="sem"></div>
                        <div class="sitem">
                        	<div class="dt"><input type="checkbox" id="product_num_checkbox" />设置商品可售数量</div>
                            <div id="product_num" style="display:none;" class="dd"><input maxlength="5" id="product_property_num" type="text" class="text" /></div>
                        </div>
                        <div class="sitem">
                        	<div class="dt"><input type="checkbox" id="product_oldprice_checkbox" />设置商品原价</div>
                            <div id="product_oldprice" style="display:none;" class="dd"><input maxlength="10" id="product_property_oldprice" type="text" class="text" /></div>
                        </div>
                        <div class="sitem">
                        	<div class="dt"><input type="checkbox" id="product_express_price_checkbox" />设置物流快递费用</div>
                            <div id="product_express_price" style="display:none;" class="dd"><input maxlength="10" id="product_property_express_price" type="text" class="text" /></div>
                        </div>
                        <script type="text/javascript" src="/static/js/My97DatePicker/WdatePicker.js"></script>
                        <div class="sitem">
                        	<div class="dt"><input type="checkbox" id="product_end_time_check" />设置售卖截止日期</div>
                        	<div id="product_end_time" style="display:none;" class="dd o"><a href="javascript:void(0)" onclick="WdatePicker({el:$dp.$('product_property_end_time'),minDate:'%y-%M-{%d}'});" class="btn"></a><input readonly id="product_property_end_time" onclick="WdatePicker({minDate:'%y-%M-{%d}'});" type="text" class="text" /></div>
                        </div>
                        <div class="sitem2">
                        	<div class="dt"><input id="product_property_check" type="checkbox" />设置商品属性</div>
                            <div style="display:none;" class="dd"><a style="cursor:pointer;" tid="one" class="btn"></a><input maxlength="4" id="product_property_beforeone" tid="one" type="text" class="text o1" /><label for="product_property_beforeone" class="label1">名称：如码数</label><input id="product_property_afterone" tid="one" type="text" class="text o2" /><label for="product_property_afterone" class="label2">请用空格分开具体属性内容</label></div>
                            <div style="display:none;" class="dd2"><a style="cursor:pointer;" id="product_add_property">添加属性</a></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="cmb">
            	<input type="hidden" name="product_id" id="product_id" value="" />
            	<div class="fore1"><a href="javascript:void(0);" onclick="checkproduct();" class="btn">发布新商品</a></div>
                <div class="fore2"><a href="javascript:void(0);" id="product_add_draft">先存为草稿</a></div>
            </div>
        </div>
        <div class="contentSide">
        	<div class="cmh"><span id="automaticsave"></span>售卖页面效果预览</div>
            <div class="cmc">
            	<div class="cmm">
                    <div class="name" id="product_name_beta"></div>
                    <div class="silder-new fl">
                        <div id="slider" class="slider-box">
                        <div class="contentimg"><img src="/static/images/dfimg.jpg" width="280" height="210" alt="" /></div>   
                        </div>
                        <div class="pagination-box">
                            <div id="paginate-slider" class="paginate-slider">
                               
                            </div>
                        </div>
                    </div>
                    <div class="clear"></div>
                    <div class="intro">
                        <div class="fore1"><strong>商品描述</strong></div>
                        <div class="fore2" id="product_content_beta"></div>
                        <div class="fore3"><a href="javascript:void(0);">发布新商品</a></div>
                    </div>
                </div>
                <div class="cms">
                    <div class="buy">
                        <h3><span id="product_price_beta"></span></h3>
                        <dl class="item">
                            <dt>数量</dt>
                            <dd>
                                <div class="wrap-input">
                                    <a href="javascript:void(0);" class="btn-reduce">-</a>
                                    <input type="text" class="text" />
                                    <a href="javascript:void(0);" class="btn-add">+</a>
                                </div>
                                <!--<div class="store-prompt">库存仅有18件</div>-->
                            </dd>
                        </dl>
                        <!--
                        <dl class="item">
                            <dt>颜色</dt>
                            <dd><select class="select"><option>红色</option></select></dd>
                        </dl>
                        <dl class="item">
                            <dt>大小</dt>
                            <dd><select class="select"><option>S</option></select></dd>
                        </dl>-->
                        <div class="buybtn"><a href="javascript:void(0);">购买</a></div>
                    </div>
                    <div class="buyt">
                        <dl class="item">
                            <dt>运费</dt>
                            <dd id="express_price_beta"></dd>
                        </dl>
                        <dl class="item">
                            <dt>库存</dt>
                            <dd id="product_max_number_beta"></dd>
                        </dl>
                        <dl class="item">
                            <dt>剩余时间</dt>
                            <dd id="product_end_time_beta"></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="blank40"></div>
<script>
setTimeout('checkdraft()',2*60*1000);
</script>

<?php include template("footer");?>