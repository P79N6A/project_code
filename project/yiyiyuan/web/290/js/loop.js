;$(()=>{
	  var mySwiper = new Swiper('.swiper-container', {
	  	autoplay: 3000, //可选选项，自动滑动
	  	pagination: '.swiper-pagination',
	  	paginationType: 'bullets',
	  	paginationClickable: true,
	  	centeredSlides : true,
	  	slidesPerView: 1.5,
	  	spaceBetween: 20,
	  	paginationBulletRender: function(swiper, index, className) {
	  		var str_ = $(".swiper-wrapper .swiper-slide").eq(index).attr("dats-text")
	  		console.log($(".swiper-wrapper .swiper-slide").eq(index + 1).attr("dats-text"))
	  		return '<span class="' + className + '">' + str_ + '</span>';
	  	}
	  });
});
