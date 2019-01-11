// JavaScript Document
//function shareTip(){
//	var div = $('<div id="shareTip" style="display: none;"><p><img src="/images/dev/guide.png" width="100%" alt="点击右上角分享"/></p></div>');
//	$(".Hcontainer").append(div);
//	if($(document).height() < $(window).height()){
//		$("#shareTip").height($(window).height());
//	}else{
//		$("#shareTip").height($(document).height());
//	}
//	$("img",$("#shareTip")).css("margin-top",$(document).scrollTop());
//	$("#shareTip").show();
//        //Todo:暂时使用click代替tapstart
//	$("#shareTip").bind("click",function(){
//		$("#shareTip").hide();
//	});
//}
    function shareTip(){        
	var div = $('<div class="Hmask" id="shareTip" style="display: none;"><img src="/images/guide.png" class="guide_share"></div>');
	$(".Hcontainer").append(div);
	if($(document).height() < $(window).height()){
		$("#shareTip").height($(window).height());
	}else{
		$("#shareTip").height($(document).height());
	}
//	$("img",$("#shareTip")).css("margin-top",$(document).scrollTop());
	$("#shareTip").show();
        //Todo:暂时使用click代替tapstart
	$("#shareTip").bind("click",function(){
		$("#shareTip").hide();
	});
}