$(document).ready(function(){

//$('#cssmenu > ul > li ul').each(function(index, e){
//  var count = $(e).find('li').length;
//  var content = '<span class="cnt">' + count + '</span>';
//  $(e).closest('li').children('a').append(content);
//});
$('#cssmenu ul ul li:odd').addClass('odd');
$('#cssmenu ul ul li:even').addClass('even');
if($('#cssmenu li').hasClass('active'))
{
    $('.active').find('ul').slideDown('normal');
}
$('#cssmenu > ul > li > a').click(function() {
  $('#cssmenu li').removeClass('active');
  $(this).closest('li').addClass('active');	
  var checkElement = $(this).next();
  if((checkElement.is('ul')) && (checkElement.is(':visible'))) {
    $(this).closest('li').removeClass('active');
    checkElement.slideUp('normal');
  }
  if((checkElement.is('ul')) && (!checkElement.is(':visible'))) {
    $('#cssmenu ul ul:visible').slideUp('normal');
    checkElement.slideDown('normal');
  }
  if($(this).closest('li').find('ul').children().length == 0) {
    return true;
  } else {
    return false;	
  }		
});
$('#cssmenu a').click(function() {
	$('#cssmenu a').css('color','');
	/*$('#cssmenu a').css('background-image','');*/
	$(this).closest('a').css('color','#000');
	/*$(this).closest('a').css('background-image','linear-gradient(to bottom, #56BBED 0%, #3AA5F2 100%)');*/
	
});
})
