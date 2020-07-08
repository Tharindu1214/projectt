if (getCookie("screenWidth") != screen.width) {
	$.ajax({url: fcom.makeUrl('Custom', 'updateScreenResolution', [screen.width, screen.height])});
}

var Dashboard = function() {
	var menuChangeActive = function menuChangeActive(el) {
		var hasSubmenu = $(el).hasClass("has-submenu");
		$(global.menuClass + " .is-active").removeClass("is-active");
		$(el).addClass("is-active");
	};
	var sidebarChangeWidth = function sidebarChangeWidth() {
		var $menuItemsTitle = $("li .menu-item__title");
		if ($("body").hasClass('sidebar-is-reduced')) {
			$("body").removeClass('sidebar-is-reduced').addClass('sidebar-is-expanded');
            $("<div class='sidebar-overlay--js'></div>").appendTo("body");
			var visibility = 1;
		} else {
			$("body").removeClass('sidebar-is-expanded').addClass('sidebar-is-reduced');
            $("div.sidebar-overlay--js").remove();
			var visibility = 0;
		}
		$.ajax({url: fcom.makeUrl('Custom', 'setupSidebarVisibility', [visibility])});
		// $("body").toggleClass("sidebar-is-reduced sidebar-is-expanded");
		$(".hamburger-toggle").toggleClass("is-opened");
	};
	return {
		init: function init() {
			$(document).on("click", ".js-hamburger, .sidebar-overlay--js", sidebarChangeWidth);
			$(document).on("click", ".js-menu li", function(e) {
			 	menuChangeActive(e.currentTarget);
			});
		}
	};
}();
Dashboard.init();

$(document).on('click','.menu-toggle ',function() {
	if(!$(this).parent().hasClass("is--active") && $(".collections-ui").hasClass("is--active")){
		$(".collections-ui").removeClass("is--active")
		$(".menu-toggle").removeClass("cross");
		$('html').removeClass("nav-active");
	}
	$(this).parent().toggleClass("is--active");
	$('html').toggleClass("nav-active");
	$(this).toggleClass("cross");
});

/* Expand/collapse/accordion */
$(function () {
	$('.js-acc-triger').on('click', function (e) {
		e.preventDefault();
		if ($(this).hasClass('active')) {
			$(this).removeClass('active');
			$(this).next().stop().slideUp(300);
		} else {
			$(this).addClass('active');
			$(this).next().stop().slideDown(300);
		}
	});
});
/*Ripple*/
$('[ripple]').on('click', function (e) {
	var rippleDiv = $('<div class="ripple" />'),
		rippleOffset = $(this).offset(),
		rippleY = e.pageY - rippleOffset.top,
		rippleX = e.pageX - rippleOffset.left,
		ripple = $('.ripple');

	rippleDiv.css({
		top: rippleY - (ripple.height() / 2),
		left: rippleX - (ripple.width() / 2),
		background: $(this).attr("ripple-color")
	}).appendTo($(this));

	window.setTimeout(function () {
		rippleDiv.remove();
	}, 1500);
});

/*Tabs*/
$(document).ready(function () {
	$(".tabs-content-js").hide();
	$(".tabs--flat-js li:first").addClass("is-active").show();
	$(".tabs-content-js:first").show();
	$(".tabs--flat-js li").click(function () {
		$(this).parent().find("li").removeClass("is-active");
		$(this).addClass("is-active");
		$(".tabs-content-js").hide();
		var activeTab = $(this).find("a").attr("href");
		$(activeTab).fadeIn();
		return false;
		setSlider();
	});
});

/* for search form */
 $(document).on('click','.toggle--search-js',function() {
	$(this).toggleClass("is--active");
	$('html').toggleClass("is--form-visible");
	/* $('.search--keyword--js').focus(); */
});
$("document").ready(function(){

 $('.parents--link').click(function() {


	$(this).parent().toggleClass("is--active");
	$(this).parent().find('.childs').toggleClass("opened");
});
/* for Dashbaord Links form */
});

// Wait for window load
$(window).on('load',function() {
	// Animate loader off screen
	$(".pageloader").remove();
	setSelectedCatValue();
});

$("document").ready(function(){
	/*common drop down function  */
	$('.dropdown__trigger-js').each(function(){
		$(this).click(function() {
            if($('html').hasClass('cart-is-active')){
             $('.cart').removeClass('cart-is-active');
             $('html').removeClass("cart-is-active");
            }
            if($('body').hasClass('toggled_left')){
                $('.navs_toggle').removeClass("active");
                $('body').removeClass('toggled_left');
            }
            if($('html').hasClass('toggled-user')){
                $('.dropdown__trigger-js').parent('.dropdown').removeClass("is-active");
                $("html").removeClass("toggled-user");
            }else{
                $(this).parent('.dropdown').toggleClass("is-active");
                $("html").toggleClass("toggled-user");
            }


            return false;
        });
	});
	$('html, .common_overlay').click(function(){
		if($('.dropdown').hasClass('is-active')){
			$('.dropdown').removeClass('is-active');
			$('html').removeClass('toggled-user');
		}
	});
	$('.dropdown__target-js').click(function(e){
		e.stopPropagation();
	});

	$('.collections-ui').on('click','.collection__container',function(e){
		e.stopPropagation();
	});

	$('#cartSummary').on('click','.cart-detail',function(e){
		e.stopPropagation();
	});

	$('.main-search').on('click','.form--search-popup',function(e){

		if(!$(e.target).hasClass('close-layer')){
			e.stopPropagation();
		}else{
			/* $('.toggle--search-js').toggleClass("is--active");
			$('html').toggleClass("is--form-visible");	 */
			if($('html').hasClass('is--form-visible')){
				$('html').removeClass('is--form-visible');
				$('.toggle--search-js').toggleClass("is--active");
			}
		}
	});

	/* for fixed header */
	$(window).scroll(function(){
		body_height = $("#body").position();
		scroll_position = $(window).scrollTop();
		if( typeof body_height !== typeof undefined && body_height.top < scroll_position)
			$("body").addClass("fixed");
		else
			$("body").removeClass("fixed");
	});

	/* for footer */
	if( $(window).width() < 767 ){
	 /* FOR FOOTER TOGGLES */
		$('.toggle__trigger-js').click(function(){
		  if($(this).hasClass('is-active')){
			  $(this).removeClass('is-active');
			  $(this).siblings('.toggle__target-js').slideUp();return false;
		  }
		  $('.toggle__trigger-js').removeClass('is-active');
		  $(this).addClass("is-active");
			 $('.toggle__target-js').slideUp();
			 $(this).siblings('.toggle__target-js').slideDown();
		});
	}

	/* for footer accordion */
	$(function() {
		$('.accordion_triger').on('click', function(e) {
			e.preventDefault();
			if ($(this).hasClass('active')) {
				$(this).removeClass('active');
				$(this).next()
				.stop()
				.slideUp(300);
			} else {
				$(this).addClass('active');
				$(this).next()
				.stop()
				.slideDown(300);
			}
		});
		/* $(document).delegate('.cart > a','click',function(){
		$('html').toggleClass("cart-is-active");
		$(this).toggleClass("cart-is-active");
		}); */
	});


	/* for cart area */
	$('.cart').on('click',function(){
		if($('html').hasClass('toggled-user')){
			$('.dropdown__trigger-js').parent('.dropdown').removeClass("is-active");
			$("html").removeClass("toggled-user");
		}
		$('html').toggleClass("cart-is-active");
		$(this).toggleClass("cart-is-active");
		/* return false;  */
	});
	$('html').click(function(){
		if($('html').hasClass('cart-is-active')){
			$('html').removeClass('cart-is-active');
			$('.cart').toggleClass("cart-is-active");
		}
		if( $('.collection__container').hasClass('open-menu')){
			$('.open-menu').parent().toggleClass('is-active');
			$('.open-menu').toggleClass('open-menu');
		}
	});

	$('.cart').click(function(e){
		e.stopPropagation();
	});


});

/*ripple effect*/
$(function(){
	var ink, d, x, y;
	$(".ripplelink, .slick-arrow").click(function(e){
		if($(this).find(".ink").length === 0){
			$(this).prepend("<span class='ink'></span>");
		}
		ink = $(this).find(".ink");
		ink.removeClass("animate");

		if( !ink.height() && !ink.width() ){
			d = Math.max($(this).outerWidth(), $(this).outerHeight());
			ink.css({height: d, width: d});
		}
		x = e.pageX - $(this).offset().left - ink.width()/2;
		y = e.pageY - $(this).offset().top - ink.height()/2;
		ink.css({top: y+'px', left: x+'px'}).addClass("animate");
	});
});



/*back-top*/
$(document).ready(function(){
	// hide #back-top first
	$(".back-to-top").hide();

	// fade in #back-top
	$(function () {
		$(window).scroll(function () {
			if ($(this).scrollTop() > 100) {
				$('.back-to-top').fadeIn();
			} else {
				$('.back-to-top').fadeOut();
			}
		});
		// scroll body to 0px on click
		$('.back-to-top a').click(function () {
			$('body,html').animate({
				scrollTop: 0
			}, 800);
			return false;
		});
	});

	$('.switch-button').click(function() {
		$(this).toggleClass("is--active");
		if($(this).hasClass("buyer") && !$(this).hasClass("is--active")){
			window.location.href=fcom.makeUrl('seller');
		}if($(this).hasClass("seller") && $(this).hasClass("is--active")){
			window.location.href=fcom.makeUrl('buyer');
		}
	});

	var t;
	$('a.loadmore').on('click', function(e) {
		e.preventDefault();
		clearTimeout(t);
		$(this).toggleClass('loading');
		t = setTimeout(function() {
		$('a.loadmore').removeClass("loading")
		}, 2500);
	});

});



/*  like animation  */
$(document).ready(function(){
	var debug = /*true ||*/ false;
	var h = document.querySelector('.heart-wrapper-Js');
    $(document).on('click', '.heart-wrapper-Js',function(){
	/* $(document).delegate('.heart-wrapper-Js','click',function(){ */
		product_id= $(this).attr('data-id');
		toggleProductFavorite(product_id,$(this));
		h = document.querySelector('heart-wrapper-Js');
	});

/*   function toggleActivate(){
    h.classList.toggle('is-active');
  }   */

  if(debug){
    var elts = Array.prototype.slice.call(h.querySelectorAll(':scope > *'),0);
    var activated = false;
    var animating = false;
    var count = 0;
    var step = 1000;

    function setAnim(state){
		elts.forEach(function(elt){
			elt.style.animationPlayState = state;
		});
    }

    h.addEventListener('click',function(){
      if (animating) return;
      if ( count > 27 ) {
        h.classList.remove('is-active');
        count = 0;
        return;
      }
      if (!activated) h.classList.add('is-active') && (activated = true);

      console.log('Step : '+(++count));
      animating = true;

      setAnim('running');
      setTimeout(function(){
        setAnim('paused');
        animating = false;
      },step);
    },false);

    setAnim('paused');
    elts.forEach(function(elt){
      elt.style.animationDuration = step/1000*27+'s';
    });
  }
});

	$(function () {
		var elem = "";
		var settings = {
			mode: "toggle",
			limit: 2,
		};
		var text = "";
		$.fn.viewMore = function (options) {
			$.extend(settings, options)
			text = $(this).html();
			elem = this;
			initialize();
		};

		function initialize() {
			total_li= $(elem).children('ul').children('li').length;
			console.log(total_li);
			limit= settings.limit;
			console.log(limit);
			extra_li= total_li-limit;
			if (total_li > limit) {
			   $(elem).children('ul').children('li:gt('+(limit-1)+')').hide();
				$(elem).append('<a class="read_more_toggle closed"  onClick="bindChangeToggle(this);"><span class="ink animate"></span> <span class="read_more">View More</span></a>');
			}
		}
	});

	function bindChangeToggle(obj) {
        if ($(obj).hasClass('closed')) {
            $(obj).find('.read_more').text('.. View Less');
           $(obj).removeClass('closed');
           $('#accordian').children('ul').children('li').show();
        } else {
          $(obj).addClass('closed');
          $(obj).find('.read_more').text('.. View More');
          $('#accordian').children('ul').children('li:gt(0)').hide();
        }
   }

    function setSelectedCatValue(id){
		var currentId = 'category--js-'+id;
		var e = document.getElementById(currentId);
		if(e != undefined){
			var catName = e.text;
            $(e).parent().siblings().removeClass('is-active');
            $(e).parent().addClass('is-active');
			$('#selected__value-js').html(catName);
			$('#selected__value-js').closest('form').find('input[name="category"]').val(id);
            $('.dropdown__trigger-js').parent('.dropdown').removeClass("is-active");
		}
	}

   function animation(obj){
		if( $(obj).val().length > 0 ){
			if(!$('.submit--js').hasClass('is--active'))
			$('.submit--js').addClass('is--active');
		} else {
			$('.submit--js').removeClass('is--active');
		}
	}

	(function() {
		Slugify = function( str,str_val_id,is_slugify,caption ){
			var str = str.toString().toLowerCase()
			.replace(/\s+/g, '-')           // Replace spaces with -
			.replace(/[^\w\-]+/g, '')       // Remove all non-word chars
			.replace(/\-\-+/g, '-')         // Replace multiple - with single -
			.replace(/^-+/, '')             // Trim - from start of text
			.replace(/-+$/, '');
			if ( $("#"+is_slugify).val()==0 ){
				$("#"+str_val_id).val(str);
				$("#"+caption).html(siteConstants.webroot+str);
			}
		};

		getSlugUrl = function( obj, str, extra, pos ){
			if( typeof pos == undefined || pos == null ){
				pos = 'pre';
			}
			var str = str.toString().toLowerCase()
			.replace(/\s+/g, '-')           // Replace spaces with -
			.replace(/[^\w\-]+/g, '')       // Remove all non-word chars
			.replace(/\-\-+/g, '-')         // Replace multiple - with single -
			.replace(/^-+/, '')             // Trim - from start of text
			.replace(/-+$/, '');
			if( extra && pos == 'pre' ){
				str = extra+'-'+str;
			} if( extra && pos == 'post' ){
				str = str +'-'+extra;
			}
			$(obj).next().html( siteConstants.webroot + str );
		};
	})();

/* scroll tab active function */
moveToTargetDiv('.tabs--scroll ul li.is-active','.tabs--scroll ul',langLbl.layoutDirection);

$(document).on('click','.tabs--scroll ul li',function(){
	if($(this).hasClass('fat-inactive')){ return; }
    $(this).closest('.tabs--scroll ul li').removeClass('is-active');
    $(this).addClass('is-active');
    moveToTargetDiv('.tabs--scroll ul li.is-active','.tabs--scroll ul',langLbl.layoutDirection);
});

function moveToTargetDiv(target, outer ,layout){
	var out = $(outer);
	var tar = $(target);
	//var x = out.width();
	//var y = tar.outerWidth(true);
	var z = tar.index();
	var q = 0;
	var m = out.find('li');

    for(var i = 0; i < z; i++){
          q+= $(m[i]).outerWidth(true)+4;
    }

	$('.tabs--scroll ul').animate({
		scrollLeft: Math.max(0, q )
	}, 800);
	return false;
}

function moveToTargetDivssss(target, outer ,layout){
	var out = $(outer);
	var tar = $(target);
	var z = tar.index();
	var m = out.find('li');

	if(layout == 'ltr'){
		var q = 0;
		for(var i = 0; i < z; i++){
			q+= $(m[i]).outerWidth(true)+4;
		}
	}else{
		var ulWidth = 0;
		$(outer+" li").each(function() {
			ulWidth = ulWidth + $(this).outerWidth(true);
		});

		var q = 0;
		for(var i = 0; i <= z; i++){
			q+= $(m[i]).outerWidth(true);
		}
		q = ulWidth - q;

		/* var q = out.last().outerWidth(true);
		var q = ulWidth;
		for(var i = z; i > 0; i--){
			q-= $(m[i]).outerWidth(true);
		}   */
	}
	out.animate({
		scrollLeft: Math.max(0,q )
	}, 800);
	return false;
}

function getCookie(cname) {
  var name = cname + "=";
  var ca = document.cookie.split(';');
  for(var i = 0; i < ca.length; i++) {
    var c = ca[i];
    while (c.charAt(0) == ' ') {
      c = c.substring(1);
    }
    if (c.indexOf(name) == 0) {
      return c.substring(name.length, c.length);
    }
  }
  return "";
}
