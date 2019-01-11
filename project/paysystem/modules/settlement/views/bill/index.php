<?php
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use yii\helpers\Html;
$this->title = '出款账单管理';
$status      = \app\models\Business::getStatus();
?>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
            <h5><?php echo $this->title. '>'?>获取上游对账单</h5>
        </header>
        <div class="body">
            <table class="table table-bordered table-condensed table-hover table-striped dataTable no-footer">
                <thead>
                    <tr>
                        <td colspan="9">
                            <form action="/settlement/bill/index?" method="get" id="post_form">
                                <input type="hidden" value="search" />
                                日期：<input type="date" name="search_date" value="<?=$search_date?>" />
                                <input type="submit" value="查询对账单" />
                            </form>
                        </td>
                    </tr>

                </thead>
                <tbody>
                <tr role="row">
                    <th>全选</th>
                    <th>出款通道名称</th>
                    <th>总笔数</th>
                    <th>总金额/元</th>
                    <th>手续费/元</th>
                    <th>来源</th>
                    <th>对账状态</th>
                    <th>对账时间</th>
                    <th>操作</th>
                </tr>
                <?php
                    if (!empty($channel_data)){
                        foreach($channel_data as $key=>$value){
                            $bill_data = empty($value['data'])?"":$value['data'];
                ?>
                <tr>
                    <td><input type="checkbox"></td>
                    <td align="center">
                        <?=ArrayHelper::getValue($value, 'channel_name', $value); ?>
                    </td>
                    <td align="center">
                        <?=ArrayHelper::getValue($bill_data, 'total_pen_count', ''); ?>
                    </td>
                    <td align="center">
                        <?=ArrayHelper::getValue($bill_data, 'total_money', ''); ?>
                    </td>
                    <td align="center">
                        <?=ArrayHelper::getValue($bill_data, 'withdraw_fee', ''); ?>
                    </td>
                    <td align="center">
                        <?php
                            if (!empty($bill_data['source'])){
                                if ($bill_data['source'] == 1){
                                    echo "未下载";
                                }elseif($bill_data['source'] == 2){
                                    echo "已上传";
                                }else{
                                    echo "已下载";
                                }
                            }
                        ?>
                    </td>
                    <td align="center">
                        <?php
                        if (!empty($bill_data['audit_status']) && $bill_data['audit_status'] == 1){
                            echo "未对账";
                        }elseif(!empty($bill_data['audit_status']) && $bill_data['audit_status'] == 3){
                            echo "已对账";
                        }
                        ?>
                    </td>
                    <td align="center">
                        <?php
                        if (!empty($bill_data['audit_status'])){
                            echo $bill_data['audit_status'] == 1 ? "" : ArrayHelper::getValue($bill_data, 'modify_time', '');
                        }
                        ?>
                    </td>
                    <td align="center">
                        <?php
                            if (empty($bill_data['audit_status'])){
                                echo '<a href="/settlement/bill/add?channel_type='.$key.'&channel_date='.$search_date.'" class="btn btn-primary">上传</a>';
                            }else{
                                echo '<a href="/settlement/bill/channellist?channel_type='.$key.'&channel_date='.$search_date.'" class="btn btn-primary">查看</a>';
                            }
                        ?>
                    </td>
                </tr>
                <?php
                        }
                    }
                ?>

                </tbody>                
            </table>
        </div>
    </div>
</div>
<script src="/bootstrap/js/bootstrap.min.js" type="text/javascript" charset="utf-8"></script>
<script>
</script>