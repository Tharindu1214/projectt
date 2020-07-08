$(document).on('click','.anchor--js',function(event){
	$(".cg--js ul li").removeClass('iss--active');
	$(this).parent().addClass('iss--active');
	$id = $(this).attr('data-role');
	var target_offset = $("."+$id).offset();
	var target_top = target_offset.top-60;
	$('html, body').animate({scrollTop:target_top}, 1000);
});

$(document).ready(function(){
	
	$(".cg--js ul li:first").addClass('iss--active');
});
