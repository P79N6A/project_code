<?php

use yii\widgets\LinkPager;

$this->title = "支付系统";
$status      = \app\models\BusinessChan::getStatus();
?>
<style>
    #sortable { list-style-type: none; margin: 0; padding: 0; width: 60%; }
</style>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
            <h5>业务通道排序</h5>
            <a id="sureSort" href="javascript:void(0)"><label style="float:right" class="btn btn-primary">确定排序</label></a>
        </header>
        <div class="body">
            <table class="table table-bordered table-condensed table-hover table-striped dataTable no-footer">
                <thead>
                    <tr role="row">
                        <th width='50px'>ID</th>
                        <th width='60px'>通道ID</th>
                        <th width='150px'>通道名称</th>
                        <th width='120px'>应用ID</th>
                        <th width='120px'>业务</th>
                    </tr>
                </thead>
                <tbody id="sortable">
                    <?php if (!empty($res)): ?>
                        <?php foreach ($res as $key => $val): ?>
                            <tr data_id="<?=$val['id']?>" role="row" class="even">
                                <td><?= $val['id'] ?></td>
                                <td><?= $val['channel_id'] ?></td>
                                <td><?= $val->channel->product_name ?></td>
                                <td><?= $val->app->name ?></td>
                                <td><?= $val->business->name ?></td>
                            </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>              
            </table>
        </div>
    </div>
</div>

<script src="/js/jquery-ui.js"></script>
<script>
    $(function () {
        $("#sortable").sortable();
        $("#sortable").disableSelection();
        
        $("#sureSort").click(function(){
            var arr = [];
            var trArr = $("#sortable > tr");
            $.each(trArr , function(i , obj){
                var id = $(obj).attr("data_id");
                arr.push(id);
            });
            
            var csrf = "<?=\Yii::$app->request->csrfToken?>";
            $.ajax({
                type: "POST",
                url: "dosort",
                dataType:"json",
                data: {data:arr,_csrf:csrf},
                success: function(data){
                    if (data.res_code == '0') {
                        $("#errorClass").addClass('alert-success').removeClass("alert-danger")
                    } else {
                        $("#errorClass").addClass('alert-danger').removeClass("alert-success")
                    }
                    $("#errotmsg").html(data.res_data);
                    $("#showError").fadeIn();
                    if (data.res_code == '0') {
                        window.location.href = 'index';
                    }
                }
            });
        })
        
    });
</script>