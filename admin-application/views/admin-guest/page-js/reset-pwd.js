(function() {
	reset_password = function(frm, v) {
		if (!$(frm).validate()) return;
		/* if (!v.isValid()){
			$('ul.errorlist').each(function(){
				$(this).parents('.field_control:first').addClass('error');
			});
			return; 
		} */
		fcom.updateWithAjax(fcom.makeUrl("adminGuest", "resetPasswordSubmit"), fcom.frmData(frm), function(t) {
			if(t.status == 1){
				fcom.waitAndRedirect(t.msg, fcom.makeUrl('adminGuest', 'loginForm'), 2000);
				$.systemMessage(t.msg, 'alert--success');
			}else{
				$.systemMessage(t.msg, 'alert--danger');
			}
		});    
		return false;
	}
	
	  /* for sliding effect */  
    if($(window).width()>1000)
    $('#moveleft').click(function() {
        $('.panels').animate({
        'marginLeft' : "0" //moves left
        });
        
        $('.innerpanel').animate({
        'marginLeft' : "100%" //moves right
        });
    });
    if($(window).width()>1000)
    $('#moveright').click(function() {
        $('.panels').animate({
        'marginLeft' : "50%" //moves right
        });
        
        $('.innerpanel').animate({
        'marginLeft' : "0" //moves right
        });
    });
     
     
    /* for mobile view slide */  
	if($(window).width()<1000)
		$('.linkslide').click(function() {
			$(this).toggleClass("active");
			var el = $("body");
			if(el.hasClass('active-left')) el.removeClass("active-left");
			else el.addClass('active-left');
		  
		}); 
    
	/* for forms elements */         
	function floatLabel(inputType){
		$(inputType).each(function(){
		var $this = $(this);
		var text_value = $(this).val();

		// on focus add class "active" to label
		$this.focus(function(){

		$this.closest('.field_control').addClass("active");
		});

		// on blur check field and remove class if needed
		$this.blur(function(){
		if($this.val() === '' || $this.val() === 'blank'){
		$this.closest('.field_control').removeClass('active');
		}
		});

		// Check input values on postback and add class "active" if value exists
		if(text_value!=''){
		$this.closest('.field_control').addClass("active");
		}

		// Automatically remove floatLabel class from select input on load
		  /* $('select').closest('.field_control').removeClass('active');*/
		});

		}
		// Add a class of "floatLabel" to the input field
		floatLabel(".web_form input[type='text'], .web_form input[type='password'], .web_form input[type='email'], .web_form select, .web_form textarea, .web_form input[type='file']"); 
     
     

     /* wave ripple effect */ 
	var parent, ink, d, x, y;
	$(".themebtn, .leftmenu > li > a, .actions > li > a, .leftlinks > li > a,.profilecover .profileinfo,.pagination li a, .circlebutton").click(function(e){
		parent = $(this);
		//create .ink element if it doesn't exist
		if(parent.find(".ink").length == 0)
			parent.prepend("<span class='ink'></span>");

		ink = parent.find(".ink");
		//incase of quick double clicks stop the previous animation
		ink.removeClass("animate");

		//set size of .ink
		if(!ink.height() && !ink.width())
		{
			//use parent's width or height whichever is larger for the diameter to make a circle which can cover the entire element.
			d = Math.max(parent.outerWidth(), parent.outerHeight());
			ink.css({height: d, width: d});
		}

		//get click coordinates
		//logic = click coordinates relative to page - parent's position relative to page - half of self height/width to make it controllable from the center;
		x = e.pageX - parent.offset().left - ink.width()/2;
		y = e.pageY - parent.offset().top - ink.height()/2;

		//set the position and add class .animate
		ink.css({top: y+'px', left: x+'px'}).addClass("animate");
	})
	
})(jQuery);