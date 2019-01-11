<?php $this->title="支付系统"?>


<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
        <h5>支付网关</h5>
        </header>
        <div class="body">
        <table class="table table-bordered table-condensed table-hover table-striped dataTable no-footer">
        <thead>
            <tr role="row">
                <th width='50px'>ID</th>
                <th width='120px'>名称</th>
                <th width='120px'>内部业务号</th>
                <th>商编</th>
                <th width='100px'>状态</th>
                <th width='100px'>银行支持</th>
                <th>简介</th>
            </tr>
        </thead>
        <tbody>
            <tr role="row" class="odd">
                <td>1</td>
                <td>易宝投资通</td>
                <td>ybtzt</td>
                <td>10012471228</td>
                <td>
                    <label class="btn btn-success">正常</label>
                </td>
                <td><a href="/backend/bank?channel_id=1">查看</a></td>
                <td><a href="#">查看</a></td>
            </tr>
            <tr role="row" class="even">
                <td>2</td>
                <td>易宝投资通</td>
                <td>ybtzt2</td>
                <td>10022471229</td>
                <td><label class="btn btn-danger">禁用</label></td>
                <td><a href="/backend/bank?channel_id=2">查看</a></td>
                <td><a href="#">查看</a></td>
            </tr>
            <tr role="row" class="odd">
                <td>3</td>
                <td>易宝快捷支付</td>
                <td>ybquick</td>
                <td>10012537679</td>
                <td><label class="btn btn-success">正常</label></td>
                <td><a href="/backend/bank?channel_id=3">查看</a></td>
                <td><a href="#">查看</a></td>
            </tr>
            <tr role="row" class="even">
                <td>4</td>
                <td>融宝快捷</td>
                <td>rbquick</td>
                <td>aabbcc</td>
                <td><label class="btn btn-success">正常</label></td>
                <td><a href="/backend/bank?channel_id=4">查看</a></td>
                <td><a href="#">查看</a></td>
            </tr>
            <tr role="row" class="even">
                <td>5</td>
                <td>融宝代收</td>
                <td>rbpay</td>
                <td>ddeeff</td>
                <td><label class="btn btn-success">正常</label></td>
                <td><a href="/backend/bank?channel_id=5">查看</a></td>
                <td><a href="#">查看</a></td>
            </tr>
            <tr role="row" class="even">
                <td>6</td>
                <td>连连支付</td>
                <td>rbpay</td>
                <td>ddeeff</td>
                <td><label class="btn btn-success">正常</label></td>
                <td><a href="/backend/bank?channel_id=6">查看</a></td>
                <td><a href="#">查看</a></td>
            </tr>

        </tbody>                
        </table>
        </div>
    </div>
</div>