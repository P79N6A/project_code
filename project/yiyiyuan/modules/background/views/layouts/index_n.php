<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width,initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
        <title>赚钱妖怪</title>
        <link rel="stylesheet" type="text/css" href="/css/webunion/reset.css"/>
        <link rel="stylesheet" type="text/css" href="/css/webunion/webunion.css?v=20160415001"/>
        <script type="text/javascript" src="/js/webunion/jquery-1.10.1.min.js"></script>
        <script type="text/javascript" src="/js/webunion/script.js"></script>
		<script>
		$(function(){
			$('.navbanner').click(function(){
				$('.nav_ulli').show();
				$('#overDiv_n').show();
			})
			$('#overDiv_n').click(function(){
				$('.nav_ulli').hide();
				$('#overDiv_n').hide();
			})
		})
		</script>
    </head>
<body>
	<div class="all_nav">
		<span class="nav_right"><img src="/images/webunion/nav_right.png"></span>
	    <p><?= $this->title; ?></p>
	    <span class="navbanner"><img src="/images/webunion/nav_list.png"></span>
	    <div class="nav_ulli" style="display:none">       
	        <ul>
	        	<a href="/background/default/index"><li>返回首页</li></a>
	            <a href="/background/default/spread"><li>我要推广</li></a>
	            <a href="/background/default/commission"><li>佣金介绍</li></a>
	            <a href="/background/default/question"><li>常见问题</li></a>
	            <a href="/background/default/contact"><li>联系我们</li></a>
	            <a href="/background/default/opinion"><li>意见反馈</li></a>
	            <a href="/dev/loan/index"><li class="return">先花一亿元》</li></a>
	        </ul>
	    </div>
	</div>
	<!-- 透明遮挡层 -->
	<div id="overDiv_n" style="display:none"></div>

	<?= $content ?>

</body>
</html>