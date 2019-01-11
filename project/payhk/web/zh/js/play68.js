function play68_init() {
	updateShare(0);
}


function play68_submitScore(score) {
	updateShareScore(score);
	Play68.shareFriend();
	 // setTimeout( function() { Play68.shareFriend(); }, 1000 )
}

function updateShare(perfection) {
	var descContent = "唤亲朋，带好友，手抖画个明月秋";
    var score = (perfection * 100).toFixed(2);
	if (perfection == 0) {
		shareTitle = '先花一亿元-画个月圆送亲人！';
	}else if (perfection < 0.6) {
        shareTitle = '我画了一个'+score+'分的月，嫦娥已经疯了！';
    } else if (perfection < 0.75) {
        shareTitle = '我画了一个'+score+'分的月，新月or残月，已傻傻分不清！';
    } else if (perfection < 0.85) {
        shareTitle = '我画了一个'+score+'分的月，吃个五仁馅儿的月饼再战！';
    } else if (perfection < 0.9) {
        shareTitle = '我画了一个'+score+'分的月，此乃良辰美景差几天啊！';
    } else if (perfection < 0.94) {
        shareTitle = '我画了一个'+score+'分的月，快一起花前月下把酒言欢！';
    } else if (perfection < 0.96) {
        shareTitle = '我画了一个'+score+'分的月，圆到玉兔都开心的起舞了！';
    } else if (perfection < 0.98) {
        shareTitle = '我画了一个'+score+'分的月，就要和十五的月儿一样圆啦！';
    } else if (perfection < 1) {
        shareTitle = '我画了一个'+score +'分的月，今晚满城的月光都是我赏的！';
    }else {
        shareTitle = '我画了一个'+score+'分的月，对！我就是满月！';
    }
	appid = '';
	Play68.setShareInfo(shareTitle,descContent);
    document.title = shareTitle;
}

function updateShareScore(bestScore) {
  updateShare(bestScore); 
}