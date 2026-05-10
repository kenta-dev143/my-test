$(function(){
  $('.global-nav li a').each(function(){
    var $href = $(this).attr('href');
    if(location.href.match($href)) {
      $(this).parent().addClass('current');
    } else {
      $(this).parent().removeClass('current');
    }
  });
});