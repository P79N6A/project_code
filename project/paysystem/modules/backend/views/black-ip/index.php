<?php

use yii\widgets\LinkPager;

$this->title = "支付系统";
$status      = \app\models\WhiteIp::getStatus();
?>
<div id="showConfirmMsg" style="display: none" class="alert alert-danger alert-dismissible" role="alert">
    <strong style="display: block;text-align: center" id="errotmsg">确定删除该条数据吗？</strong>
    <p style="text-align: center; margin-top: 10px;">
        <button data_id='' id='sureDelete' type="button" class="btn btn-danger">确定</button>
        <button id='closeConfirmMsg' type="button" class="btn btn-default">取消</button>
    </p>
</div>

<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
            <h5>IP白名单</h5>
            <a href="/backend/black-ip/add"><label style="float:right" class="btn btn-primary">添加</label></a>
        </header>
        <div class="body">
            <table class="table table-bordered table-condensed table-hover table-striped dataTable no-footer">
                <thead>
                    <tr role="row">
                        <th>ID</th>
                        <th>IP</th>
                        <th>创建时间</th>
                        <th width='150px'>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($res)): ?>
                        <?php foreach ($res as $key => $val): ?>
                            <tr role="row" class="even">
                                <td><?= $val['id'] ?></td>
                                <td><?= $val['ip'] ?></td>
                                <td><?= $val['create_time'] ?></td>
                                <td>
                                    <a href="/backend/black-ip/update?id=<?= $val['id'] ?>">
                                        <label class="btn btn-primary">修改</label>
                                    </a>
                                    <a class="btn_del" data_id=<?= $val['id'] ?> href="javascript:void(0);">
                                        <label class="btn btn-danger">删除</label>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>                
            </table>
            <div class="panel_pager">
                <?php echo LinkPager::widget(['pagination' => $pages]); ?>
            </div>
        </div>
    </div>
</div>

<script>
    $(function () {
        $(".btn_del").click(function () {
            var id = $(this).attr('data_id');
            $("#sureDelete").attr("data_id", id);
            $("#showConfirmMsg").fadeIn();
        });

        $("#sureDelete").click(function () {
            var id = parseInt($(this).attr('data_id'));
            $("#closeConfirmMsg").click();
            if (id <= 0) {
                return false;
            } else {
                var csrf = "<?= \Yii::$app->request->csrfToken ?>";
                $.ajax({
                    type: "POST",
                    url: "/backend/black-ip/delete",
                    dataType: "json",
                    data: {id: id, _csrf: csrf},
                    success: function (data) {
                        if (data.res_code == '0') {
                            $("#errorClass").addClass('alert-success').removeClass("alert-danger")
                        } else {
                            $("#errorClass").addClass('alert-danger').removeClass("alert-success")
                        }
                        $("#errotmsg").html(data.res_data);
                        $("#showError").fadeIn();
                        if (data.res_code == '0') {
                            window.location.href = '/backend/black-ip/index';
                        }
                    }
                });
            }
        })
    })
    $("#closeConfirmMsg").click(function () {
        $("#showConfirmMsg").fadeOut();
        $("#sureDelete").attr("data_id", '');
    })
</script>