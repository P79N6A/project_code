$(document).ready(function(){	
	$('.gzgz_rights').click(function(){
		var click = $(this).attr('click');
		if (click==1) {
			$(this).removeClass('two');
	        $(this).parent('.user_gzgz').siblings('.user_txtxdts').hide();
	        $(this).attr('click',0);
		}else{
	        $(this).addClass('two');
	        $(this).parent('.user_gzgz').siblings('.user_txtxdts').show();
	        $(this).attr('click',1);
	    }
    })
 });