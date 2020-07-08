$(document).ready(function(){
	$('.gallery').modaal({
		type: 'image'
	});
	$('.social-toggle').on('click', function(event) {
	  $(this).next().toggleClass('open-menu');
	});
    $("body").mouseup(function(e){ 
        if (1 > $(event.target).parents('.social-toggle').length && $('.social-toggle').next().hasClass('open-menu')) {
            $('.social-toggle').next().toggleClass('open-menu');
        }
    });
    
    function DropDown(el) {
        this.dd = el;
        this.placeholder = this.dd.children('span');
        this.opts = this.dd.find('ul.drop li');
        this.val = '';
        this.index = -1;
        this.initEvents();
    }

    DropDown.prototype = {
        initEvents: function() {
            var obj = this;
            obj.dd.on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).toggleClass('active');
            });
            obj.opts.on('click', function() {
                var opt = $(this);
                obj.val = opt.text();
                obj.index = opt.index();
                obj.placeholder.text(obj.val);
                opt.siblings().removeClass('selected');
                opt.filter(':contains("' + obj.val + '")').addClass('selected');
                var link = opt.filter(':contains("' + obj.val + '")').find('a').attr('href');
                window.location.replace(link);
            }).change();
        },
        getValue: function() {
            return this.val;
        },
        getIndex: function() {
            return this.index;
        }
    };

    $(function() {

		$(".js-wrap-drop").each(function(index, element) {
            var div = '#js-wrap-drop' + index;
            new DropDown($(div));
        });
		// var dd1 = new DropDown($('.js-wrap-drop'));
        // create new variable for each menu
        $(document).click(function() {
            // close menu on document click
            $('.wrap-drop').removeClass('active');
        });
		$('.js-wrap-drop').click(function() {
			$(this).parent().siblings().children('.js-wrap-drop').removeClass('active');
			// $(this).siblings().children('.js-wrap-drop').addClass('active');
		});
    });

});


(function($) {

	var tabs =  $(".tabs-js li a");

	tabs.click(function() {
		var content = this.hash.replace('/','');
		tabs.removeClass("is-active");
		$(this).addClass("is-active");
	    $(".tabs-content").find('.tab-item').hide();
	    $(content).fadeIn(200);
	});



})(jQuery);

/* for sticky things*/
      /* if($(window).width()>1050){
        function sticky_relocate() {
            var window_top = $(window).scrollTop();
            var div_top = $('.fixed__panel').offset().top -110;
            var sticky_left = $('#fixed__panel');
            if((window_top + sticky_left.height()) >= ($('.unique-heading').offset().top - 40)){
                var to_reduce = ((window_top + sticky_left.height()) - ($('.unique-heading').offset().top - 40));
                var set_stick_top = -40 - to_reduce;
                sticky_left.css('top', set_stick_top+'px');
            }else{
                sticky_left.css('top', '110px');
                if (window_top > div_top) {
                    $('#fixed__panel').addClass('stick');
                } else {
                    $('#fixed__panel').removeClass('stick');
                }
            }
        }

        $(function () {
            $(window).scroll(sticky_relocate);
            sticky_relocate();
        });
  }           */
