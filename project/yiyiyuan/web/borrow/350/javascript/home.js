var swiper = new Swiper('.swiper-containert', {
    slidesPerView: 5,
    spaceBetween: 0,
    grabCursor: true,
    observer:true,//修改swiper自己或子元素时，自动初始化swiper
    observeParents:true//修改swiper的父元素时，自动初始化swiper
});
// 点击banner
$('.w_swiperTop').click(function(){
    $('.w_swiperTop').find('a').removeClass('swiper-active');
    $(this).find('a').addClass('swiper-active');
})
// 第二个轮播
var swiper1 = new Swiper('.swiper-conLoop', {
    loop: true,
    autoplay: {
    delay: 2000 //1秒切换一次
    },
    observer:true,//修改swiper自己或子元素时，自动初始化swiper
    observeParents:true//修改swiper的父元素时，自动初始化swiper
});
// 种类轮播
var swiper = new Swiper('.swiper-centerLoop', {
    slidesPerView: 5,
    spaceBetween: 10,
    observer:true,//修改swiper自己或子元素时，自动初始化swiper
    observeParents:true//修改swiper的父元素时，自动初始化swiper
  });

//   各商品轮播
var swiper = new Swiper('.swiper-goodsLoop', {
    slidesPerView: 3,
    spaceBetween: 10,
    freeMode: true,
    observer:true,//修改swiper自己或子元素时，自动初始化swiper
    observeParents:true//修改swiper的父元素时，自动初始化swiper
  });

// 弹窗轮播
var swiper = new Swiper('.swiper-main', {
    slidesPerView: 1,
    spaceBetween: 5,
    freeMode: false,
    loop: true,
    observer:true,//修改swiper自己或子元素时，自动初始化swiper
    observeParents:true//修改swiper的父元素时，自动初始化swiper
})