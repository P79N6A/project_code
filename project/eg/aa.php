<?php

$database = $_GET;
if (empty($database) || empty($database['table'])){
    echo "请输入数据表名";
    exit;
}
$database = $database['table'];

header("Content-type: text/html; charset=utf-8");
//配置数据库
// $dbserver   = "61.172.238.210";
// $dbusername = "hd";
// $dbpassword = "123456hd";
// $database   = "xdq";

$dbserver   = "182.92.80.211:3306";
$dbusername = "xhhadmin";
$dbpassword = "Xhuahua#Db!332";
$database   = $database;

$con = new mysqli($dbserver,$dbusername,$dbpassword,$database); if(!$con) die("connect error:".mysqli_connect_error()); else echo "success connect mysql\n";


$sql = "SET NAMES utf8";
$rs = $con->query($sql);


$table_result =  $con->query('show tables');

$no_show_table = array();    //不需要显示的表
$no_show_field = array();   //不需要显示的字段

//取得所有的表名
while($row = $table_result->fetch_array()){
    if(!in_array($row[0],$no_show_table)){
        $tables[]['TABLE_NAME'] = $row[0];
    }
}


//替换所以表的表前缀
if(@$_GET['prefix']){
    $prefix = 'czzj';
    foreach($tables as $key => $val){
        $tableName = $val['TABLE_NAME'];
        $string = explode('_',$tableName);
        if($string[0] != $prefix){
            $string[0] = $prefix;
            $newTableName = implode('_', $string);
            $con->query('rename table '.$tableName.' TO '.$newTableName);
        }
    }
    echo "替换成功！";exit();
}

//循环取得所有表的备注及表中列消息
foreach ($tables as $k=>$v) {
    $sql  = 'SELECT * FROM ';
    $sql .= 'INFORMATION_SCHEMA.TABLES ';
    $sql .= 'WHERE ';
    $sql .= "table_name = '{$v['TABLE_NAME']}'  AND table_schema = '{$database}'";
    $table_result = $con->query($sql);
    while ($t = $table_result->fetch_array() ) {
        $tables[$k]['TABLE_COMMENT'] = $t['TABLE_COMMENT'];
    }

    $sql  = 'SELECT * FROM ';
    $sql .= 'INFORMATION_SCHEMA.COLUMNS ';
    $sql .= 'WHERE ';
    $sql .= "table_name = '{$v['TABLE_NAME']}' AND table_schema = '{$database}'";

    $fields = array();
    $field_result = $con->query($sql);
    while ($t = $field_result->fetch_array() ) {
        $fields[] = $t;
    }
    $tables[$k]['COLUMN'] = $fields;
}
$con->close();


if(@$_GET[id]!='')
{
    $file = iconv("utf-8", "GBK", "test");
    header("Content-Type: application/doc");
    header("Content-Disposition: attachment; filename=" . $file . ".doc");
    echo $html;
}

$html = '';
//循环所有表
foreach ($tables as $k=>$v) {
    $html .= '    <h3>' . ($k + 1) . '、' . $v['TABLE_COMMENT'] .'  （'. $v['TABLE_NAME']. '）</h3>'."\n";
    $html .= '    <table border="1" cellspacing="0" cellpadding="0" width="100%">'."\n";
    $html .= '        <tbody>'."\n";
    $html .= '            <tr>'."\n";
    $html .= '                <th>字段名</th>'."\n";
    $html .= '                <th>数据类型</th>'."\n";
    $html .= '                <th>默认值</th>'."\n";
    $html .= '                <th>允许非空</th>'."\n";
    $html .= '                <th>主外键</th>'."\n";
    $html .= '                <th>自动递增</th>'."\n";
    $html .= '                <th>备注</th>'."\n";
    $html .= '            </tr>'."\n";

    foreach ($v['COLUMN'] as $f) {
        if(@!is_array($no_show_field[$v['TABLE_NAME']])){
            $no_show_field[$v['TABLE_NAME']] = array();
        }
        if(!in_array($f['COLUMN_NAME'],$no_show_field[$v['TABLE_NAME']])){
            $html .= '            <tr>'."\n";
            $html .= '                <td class="c1">' . $f['COLUMN_NAME'] . '</td>'."\n";
            $html .= '                <td class="c2">' . $f['COLUMN_TYPE'] . '</td>'."\n";
            $html .= '                <td class="c3">' . $f['COLUMN_DEFAULT'] . '</td>'."\n";
            $html .= '                <td class="c4">' . $f['IS_NULLABLE'] . '</td>'."\n";
            $html .= '                <td class="c5">' . $f['COLUMN_KEY'] . '</td>'."\n";
            $html .= '                <td class="c6">' . ($f['EXTRA']=='auto_increment'?'是':'&nbsp;') . '</td>'."\n";
            $html .= '                <td class="c7">' . $f['COLUMN_COMMENT'] . '</td>'."\n";
            $html .= '            </tr>'."\n";
        }
    }
    $html .= '        </tbody>'."\n";
    $html .= '    </table>'."\n";
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>数据库表结构</title>
    <meta name="generator" content="ThinkDb V1.0" />
    <meta name="author" content="" />
    <meta name="copyright" content="2008-2014 Tensent Inc." />
    <style>
        body, td, th { font-family: "微软雅黑"; font-size: 14px; }
        .warp{margin:auto; width:900px;}
        .warp h3{margin:0px; padding:0px; line-height:30px; margin-top:10px;}
        table { border-collapse: collapse; border: 1px solid #CCC; background: #efefef; }
        table th { text-align: left; font-weight: bold; height: 26px; line-height: 26px; font-size: 14px; text-align:center; border: 1px solid #CCC; padding:5px;}
        table td { height: 20px; font-size: 14px; border: 1px solid #CCC; background-color: #fff; padding:5px;}
        .c1 { width: 120px; }
        .c2 { width: 120px; }
        .c3 { width: 150px; }
        .c4 { width: 80px; text-align:center;}
        .c5 { width: 80px; text-align:center;}
        .c6 { width: 80px; }
        .c7 { width: 190px; }
    </style>
</head>
<body>
<div class="warp">
    <h1 style="text-align:center;"><?=$database?>数据库表结构</h1>
    <a href="#"><p onclick="window.location.href='test.php?id=2'">点击跳到下载页面</p></a>
    <?php
    echo $html
    //防止导出乱码


    ?>
</div>
</body>
</html>
