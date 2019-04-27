
$(function(){

  $('.mypage_content h3').on('click', function() {
    if ($(this).next().hasClass('hide')) {
      $(this).next().removeClass('hide');
    } else {
      $(this).next().addClass('hide');
    }
  });

  $('#flash_message').on('click', function() {
    $(this).hide();
  });

});
