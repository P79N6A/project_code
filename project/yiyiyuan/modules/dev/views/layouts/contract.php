<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width,initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
        <title><?= $this->title; ?></title>
        <style type="text/css">
             html,body{background: #e7edf0; font-family: "Microsoft YaHei"; padding: 0; margin: 0;}
            .xieyidm{background: #fff; margin:5px; padding: 5px;}
            .xieyidm h3{margin:0;font-size:15px;font-style:normal;text-align:center;padding-top:6px; }
            .xieyidm h3 span{ font-size: 12px;} 
            .xieyidm p{ line-height:24px; font-size:13px; padding-left: 10px;}
			table.tablediv {font-size:11px;color:#333333;border-width: 1px;border-color: #666666;border-collapse: collapse;}
			table.tablediv th {border-width: 1px;padding: 3px;border-style: solid;border-color: #666666;}
			table.tablediv td {border-width: 1px;padding: 8px;border-style: solid;border-color: #666666;background-color: #ffffff;}
            .xieyidm button.arggen{position:fixed;font-size:1.3em;height:40px;line-height:40px;color:#fff;text-align:center;width:100%; background: #e74747;bottom: 0;left: 0; border: 0;}
            .xieyidm button.change_gray{background: #aaa; color:#fff;border: 0;}
        </style>
    </head>
<body>
    <?= $content ?>
</body>
</html>