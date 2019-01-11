$(function() {
  /**
   * 点击拍摄身份证正面
   */
  $('#i_zheng').on('click', function() {
      $(".result").hide();
    $('#model').css({
      background: 'url("../310/images/shenfen_zheng.png") no-repeat',
      backgroundSize: 'contain'
    });
    $('#shade').fadeIn(100, function() {
      $('#photo_alert').slideDown(300);
      // 记录正面
      $('#file').attr('info', 0);
    });
  });

  /**
   * 点击拍摄身份证反面
   */
  $('#i_fan').on('click', function() {
      $(".result").hide();
    $('#model').css({
      background: 'url("../310/images/shenfen_fan.png") no-repeat',
      backgroundSize: 'contain'
    });
    $('#shade').fadeIn(100, function() {
      $('#photo_alert').slideDown(300);
      // 记录反面
      $('#file').attr('info', 1);
    });
  });

  /**
   * 关闭拍摄弹窗
   */
  $('#i_close').on('click', function() {
    $('#shade').fadeOut(100, function() {
      $('#photo_alert').slideUp(50);
    });
  });

  /**
   * 上传照片
   */
  $('#file').on('change', function() {
    var file = $('#file')[0].files[0];
    // 关闭弹窗
    $('#shade').fadeOut(100, function() {
      $('#photo_alert').slideUp(50);
    });
    identity(file);
    createForm(file);
  });
  /**
   * 上传照片
   */
  $('#file1').on('change', function() {
    var file = $('#file1')[0].files[0];
    // 关闭弹窗
    $('#shade').fadeOut(100, function() {
      $('#photo_alert').slideUp(50);
    });
    identity(file);
    createForm(file);
  });
  /**
   * 上传照片
   */
  $('#success_file').on('change', function() {
      var file = $('#success_file')[0].files[0];
      // 关闭弹窗
      $('#shade').fadeOut(100, function() {
          $('#photo_alert').slideUp(50);
      });
      identity(file);
      createForm(file);
  });
});

/**
 * 判断身份证正面反面
 * @param {File} file - 需要上传的图片文件
 */
function identity(file) {
  var eq = Number($('#file').attr('info'));
  if(eq==0){
      type = 1;
  }else if(eq==1){
      type = 2;
  }
  var reader = new FileReader();
  reader.readAsDataURL(file);
  reader.onload = function(e) {
    $('.paishe_icon')
      .eq(eq)
      .attr('src', '../310/images/chongpai_icon.png');
    $('.base')
      .eq(eq)
      .css({
        background: `url(${e.target.result}) no-repeat`,
        backgroundSize: 'cover'
      });
  };
}
