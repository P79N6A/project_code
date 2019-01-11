$(function(){
     $('.dblist .dbstcont .dbsyxsa').each(function(index,element){
        $(element).click(function(){
            $(this).next("div").toggleClass("showHide");
            $(this).children(".jtcxcs").attr("class");
            $(this).children(".jtcxcs").toggleClass("twotwo");
            var imgSrc=$(this).children(".jtcxcs").children("img");
            if($(imgSrc).attr("src")=="/images/triangle2.png"){
                $(imgSrc).attr("src","/images/triangle.png")
            }else{
                $(imgSrc).attr("src","/images/triangle2.png")            
            }
        });
     });
     //投资担保
     $('#putAll').click(function(){
    	var maxAmount = $("#maxinvest").val();
    	var stages = $("#stages").val();
    	$("#investAmount").val(maxAmount);
    	var income = ( maxAmount * 0.15 / 12 ) * stages ;
    	$("#expect").text(income);
     });
     
     $('#investAmount').blur(function(){
    	var realAmount = $(this).val();
    	var maxAmount = $("#maxinvest").val();
     	var stages = $("#stages").val();
     	if( realAmount == '' ){
     		alert('请输入投资金额');
     		return false;
     	}
     	if( parseInt(realAmount) > parseInt(maxAmount) ){
     		alert('投资金额超过限制');
     		return false;
     	}else{
         	var income = ( realAmount * 0.15 / 12 ) * stages ;
         	$("#expect").text(income);
     	} 
     	
     });
     
     $("#investButton").click(function(){
    	 var realAmount = $(this).val();
     	var maxAmount = $("#maxinvest").val();
      	var stages = $("#stages").val();
      	if( realAmount > maxAmount ){
      		alert('投资金额超过限制');
      		return false;
      	} 
      	
      	$("#investForm").submit();
     });
})