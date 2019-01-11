<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>开放平台测试</title>
        <!-- Bootstrap -->
        <link href="/bootstrap/css/bootstrap.min.css" rel="stylesheet">

        <!-- 如果要使用Bootstrap的js插件，必须先调入jQuery -->
        <script src="/bootstrap/js/jquery.min.js"></script>
        <!-- 包括所有bootstrap的js插件或者可以根据需要使用的js插件调用　-->
        <script src="/bootstrap/js/bootstrap.min.js"></script>
    </head>



    <body>
<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
<div class="container">
    <div class="navbar-header">
      <a href="#" class="navbar-brand">测试工具</a>
    </div>

	<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">

<ul class="nav navbar-nav">
<?php
$pathInfo = Yii::$app->request->pathInfo;
$navs = [
	['name' => '历史',
		'type' => 'nest',
		'navs' => [
			['name' => '学籍测试(未完)', 'url' => '/api/test/testeduroll'],
			['name' => '身份验证', 'url' => '/api/test/testidcard'],
		],
	],
	['name' => '易宝',
		'type' => 'nest',
		'navs' => [
			['name' => '支付路由', 'url' => '/api/test/testpayroute'],
			['name' => '易宝一键支付', 'url' => '/api/test/testquick'],
			['name' => '易宝API', 'url' => '/api/test/testtzt'],
			['name' => '支付日志查询', 'url' => '/api/test/testpaylog', 'target' => "_blank"],
			['name' => '解析易宝回调', 'url' => '/api/test/parseyeepay', 'target' => "_blank"],
		],
	],

             ['name' => '连连支付', 'url' => '/api/test/testlian'],
	['name' => '银联四联', 'url' => '/api/test/test-bank4'],
	['name' => '中信出款', 'url' => '/api/test/testzx', 'target' => "_blank"],
	['name' => 'sina接口', 'url' => '/api/test/testsina', 'target' => "_blank"],
	['name' => '玖富', 'url' => '/api/test/testjiufu', 'target' => "_blank"],

	['name' => '聚信立', 'url' => '/api/test/testjxl'],
	['name' => '融360', 'url' => '/api/test/testrong'],
	['name' => '同盾', 'url' => '/api/test/testfraudm'],
	['name' => '短信接口', 'url' => '/api/test/testsms', 'target' => "_blank"],
	['name' => '解析数据', 'url' => '/api/test/test/parse', 'target' => "_blank"],
	['name' => '测试通知', 'url' => '/api/test/testnotify', 'target' => "_blank"],
];
$pathInfo = '/' . $pathInfo;
foreach ($navs as $nav) {
	$nest = isset($nav['type']) && $nav['type'] == 'nest';
	if ($nest) {
		?>
    <li class="dropdown">
      <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><?=$nav['name']?> <span class="caret"></span></a>
      <ul class="dropdown-menu">
      	<?php
foreach ($nav['navs'] as $sub_nav) {
			$active = strpos($pathInfo, $sub_nav['url']) !== false;
			?>
	  	<li <?php echo $active ? 'class="active"' : ""; ?>>
	  		<a target="<?=$sub_nav['target'] ? $sub_nav['target'] : '_self'?>" href="<?=$sub_nav['url']?>"><?=$sub_nav['name']?></a>
	  	</li>
        <?php }?>
      </ul>
    </li>

<?php } else {
		$active = strpos($pathInfo, $nav['url']) !== false;
		?>

  	<li <?php echo $active ? 'class="active"' : ""; ?>>
  		<a target="<?=$nav['target'] ? $nav['target'] : '_self'?>" href="<?=$nav['url']?>"><?=$nav['name']?></a>
  	</li>

 <?php }}?>
  </ul>

<ul class="nav navbar-nav navbar-right">
	<li>
		<div class="u-logout">
			您好! &nbsp;&nbsp;

		</div>
	</li>
</ul>


	</div>
</div>
</nav>

<div class="container" style="margin-top:50px;">
	<?=$content?>
</div>

    </body>
</html>
