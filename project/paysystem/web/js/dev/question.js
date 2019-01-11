// JavaScript Document
var hastouch = "ontouchstart" in window?true:false,
	tapstart = hastouch?"touchstart":"mousedown",
	tapmove = hastouch?"touchmove":"mousemove",
	tapend = hastouch?"touchend":"mouseup";

$(function(){
	$(".qa").bind(tapstart,function(){
		var e_index = $(this).index(".qa");
		$(".qa").each(function(index, element) {
            if(index == e_index){
				if($("span",$(this)).last().html() == "︿"){
					$("span",$(this)).last().html("﹀");
					$(this).next().slideUp("slow");
				}else{
					$("span",$(this)).last().html("︿");
					$(this).next().slideDown("slow");
				}
			}else{
				if($("span",$(this)).last().html() == "︿"){
					$(this).next().slideUp("slow");
					$("span",$(this)).last().html("﹀");
				}
			}
        });
	});
});