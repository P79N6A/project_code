var hastouch = "ontouchstart" in window?true:false,
	tapstart = hastouch?"touchstart":"mousedown",
	tapmove = hastouch?"touchmove":"mousemove",
	tapend = hastouch?"touchend":"mouseup";
	
function showWay(){
	$("#win").height($(window).height());
	$("#modal").height($(window).height());
	$("#win").show();
}
function closeWin(){
	$("#win").hide();
	return false;
}