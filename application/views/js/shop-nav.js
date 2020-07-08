$(document).ready(function() {
	
	/* for navigation drop down */    
		$('.shop-navchild').hover(function() {
            var el = $("body");
            if($(window).width()>1025){
            $(this).toggleClass("active");
            el.toggleClass("shop_nav_show");
            }    
            return false; 
        });
		
		
		/* for mobile shop-navigations */	
          $('.shop_link__mobilenav').click(function(){

              if($(this).hasClass('active')){
                  $(this).removeClass('active');
                  $(this).siblings('.shop-navigations > li .subnav').slideUp();
                  return false;
              }
              $('.shop_link__mobilenav').removeClass('active');
              $(this).addClass("active");
              if($(window).width()<1025){
                  $('.shop-navigations > li .subnav').slideUp();
                  $(this).siblings('.shop-navigations > li .subnav').slideDown();
              }
              return;
          });
		  
		  
		   /* for mobile toggle navigation */    
		$('.shop_navs_toggle').click(function() {
            $(this).toggleClass("active");
			var el = $("body");
			if(el.hasClass('toggled_shop-nav')) el.removeClass("toggled_shop-nav");
			else el.addClass('toggled_shop-nav');
            return false; 
        });
		
		$('body').click(function(){
            if($('body').hasClass('toggled_shop-nav')){
                $('.shop_navs_toggle').removeClass("active");
                $('body').removeClass('toggled_shop-nav');
            }
        });
    
        $('.mobile__overlay').click(function(){
            if($('body').hasClass('toggled_shop-nav')){
                $('.shop_navs_toggle').removeClass("active");
                $('body').removeClass('toggled_shop-nav');
            }
        });
		
		
		$('.shop-nav,.section_primary').click(function(e){
            e.stopPropagation();
            //return false;
        });
      
});





           


 