(function($) {
  // 如果有元素移除
  var base36 = 0.96 * parseInt($('html').css('font-size'), 10);
  var base18 = 0.48 * parseInt($('html').css('font-size'), 10);
  $('.sel-boxs').remove();
  $('body').append(
    '<div class="sel-boxs">' +
      '   <div class="bg"></div>' +
      '   <div class="sel-box animated fadeInUp">' +
      '       <div class="btn">' +
      '           <div class="btn1 cancel">取消</div>' +
      '           <div class="name">加载中...</div>' +
      '           <div class="btn1 ok">完成</div>' +
      '       </div>' +
      '       <div class="sel-con">' +
      '           <div class="border"></div>' +
      '           <div class="table"></div>' +
      '       </div>' +
      '   </div>' +
      '</div>'
  );

  // 取消选择
  $('.sel-box .cancel,.sel-boxs .bg').click(function() {
    $('.sel-boxs .bg')[0].removeEventListener('touchmove', preDef, false);
    $('.sel-boxs .btn')[0].removeEventListener('touchmove', preDef, false);
    $('.sel-boxs')
      .find('.sel-box')
      .removeClass('fadeInUp')
      .addClass('fadeInDown');
    setTimeout(function() {
      $('.sel-boxs').hide();
    }, 300);
  });

  // 取消ios在zepto下的穿透事件
  $('.sel-con').on('touchend', function(event) {
    event.preventDefault();
  });

  // 取消默认行为
  var preDef = function(e) {
    e.preventDefault();
    return false;
  };

  function dataFrame(ele) {
    // ele数组转换成相应结构
    var eleText = '';
    for (let i = 0; i < ele.length; i++) {
      eleText += '<div class="ele">' + ele[i] + '</div>';
    }
    return '<div class="cell elem"><div class="scroll">' + eleText + '</div></div>';
  }
  // 封装说明：
  // 基于jQuery
  // 适合场景，只适用于单个值的选取模式
  $.scrEvent = function(params) {
      console.log(params)
    var dataArr = params.data || [];
    var evEle = params.evEle;
    var title = params.title || '';
    var defValue = params.defValue || dataArr[0]; // 首次默认值
    var type = params.type || 'click'; // 事件类型
    var beforeAction = params.beforeAction || function() {}; // 执行前的动作  无参数
    var afterAction = params.afterAction || function() {}; // 执行后的动作   参数：选择的文字

    $(evEle).attr('readonly', 'readonly');
    // 点击对应input执行事件
    $(evEle).on(type, function() {
      // 由于IOS点击(tap)其他区域 input也不失去焦点的特性
      $('input, textarea').each(function() {
        this.blur();
      });
      console.log($('.sel-boxs .bg'))
      $('.sel-boxs .bg')[0].addEventListener('touchmove', preDef, false);
      $('.sel-boxs .btn')[0].addEventListener('touchmove', preDef, false);
      beforeAction();
      $('.sel-con .table').html(dataFrame(dataArr));
      $('.sel-box .name').text(title);
      $('.sel-boxs')
        .show()
        .find('.sel-box')
        .removeClass('fadeInDown')
        .addClass('fadeInUp');
      // 默认值
      $(evEle).val() === '' ? (defValue = defValue) : (defValue = $(evEle).attr('data-sel01'));

      $('.sel-con')
        .find('.elem')
        .eq(0)
        .find('.ele')
        .each(function() {
          if ($(this).text() === defValue) {
            $(this).parents('.scroll')[0].scrollTop = $(this).index() * base36;
          }
        });
      // 选择器滚动获取值和确认赋值
      var scText = defValue; // 默认值为默认值
      $('.sel-con .scroll').scroll(function() {
        var that = $(this);
        // 数值显示

        var scTop = this.scrollTop + base18;
        var scNum = Math.floor(scTop / base36);
        scText = $(this)
          .find('.ele')
          .eq(scNum)
          .text();
        // 停止锁定
        clearTimeout($(this).attr('timer'));
        $(this).attr(
          'timer',
          setTimeout(function() {
            that[0].scrollTop = scNum * base36;
          }, 100)
        );
      });

      // 移除之前的绑定事件
      $('.sel-box .ok').off();
      // 确认选择
      $('.sel-box .ok').click(function() {
        $(evEle).attr('data-sel01', scText);
        $(evEle)
          .text(scText)
          .css({ color: '#444444' });
        afterAction(scText);
        $('.sel-boxs')
          .find('.sel-box')
          .removeClass('fadeInUp')
          .addClass('fadeInDown');
        setTimeout(function() {
          $('.sel-boxs').hide();
        }, 300);

        $('.sel-boxs .bg')[0].removeEventListener('touchmove', preDef, false);
        $('.sel-boxs .btn')[0].removeEventListener('touchmove', preDef, false);
      });
    });
  };
})($);
