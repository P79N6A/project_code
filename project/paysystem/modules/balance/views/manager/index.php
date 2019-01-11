<?php

use yii\widgets\LinkPager;

$this->title = "支付系统";
$status      = \app\models\Manager::getStatus();
?>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
            <h5>账户管理</h5>
            <a href="/backend/manager/add"><label style="float:right" class="btn btn-primary">添加</label></a>
        </header>
        <div class="body">
            <form action="/backend/manager/index" method="GET" class="form-inline">
                <div class="row form-group">
                    <div class="col-lg-6">
                        <input class="form-control" value="<?= isset($get['username']) ? $get['username'] : '' ?>" name="username" placeholder="登陆账号" type="text">
                    </div>
                    <div class="col-lg-6">
                        <input class="form-control" value="<?= isset($get['realname']) ? $get['realname'] : '' ?>" name="realname" placeholder="真实姓名" type="text">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">搜索</button>
            </form>
            <br>
            <table class="table table-bordered table-condensed table-hover table-striped dataTable no-footer">
                <thead>
                    <tr role="row">
                        <th>ID</th>
                        <th>登录账号</th>
                        <th>真实姓名</th>
                        <th>IP地址</th>
                        <th>最后登录时间</th>
                        <th>创建时间</th>
                        <th width='150px'>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($res)): ?>
                        <?php foreach ($res as $key => $val): ?>
                            <tr role="row" class="even">
                                <td><?= $val['id'] ?></td>
                                <td><?= $val['username'] ?></td>
                                <td><?= $val['realname'] ?></td>
                                <td><?= $val['ip'] ?></td>
                                <td><?= $val['logintime'] ?></td>
                                <td><?= $val['create_time'] ?></td>
                                <td>
                                    <a href="/backend/manager/status?id=<?= $val['id'] ?>">
                                        <label class="btn <?php echo $val['status'] == 1 ? 'btn-success' : 'btn-danger' ?>">
                                            <?php echo isset($status[$val['status']]) ? $status[$val['status']] : '未知' ?>
                                        </label>
                                    </a>
                                    <a href="/backend/manager/update?id=<?= $val['id'] ?>">
                                        <label class="btn btn-primary">修改</label>
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