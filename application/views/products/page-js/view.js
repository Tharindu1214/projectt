$("document").ready(function(){

	$('.xzoom, .xzoom-gallery').bind('click', function(event) {
        var xzoom = $(this).data('xzoom');
        xzoom.closezoom();
        var gallery = xzoom.gallery().cgallery;
        var i, images = new Array();
        for (i in gallery) {
            images[i] = {src: gallery[i]};
        }
        $.magnificPopup.open({items: images, type:'image', gallery: {enabled: true}});
        event.preventDefault();
    });

	/* Product Main image to be static on scroll par a particular window scroll range[ */

	$(window).scroll(function(){

	/*		var prodDetailHeight = $('.product-detail').height();
			var dv = $("#img-static");
			var mainNavposition = $('.main-bar').position();
			var mainNavHeight = $('.main-bar').height();

			if($('.gallery-js').is(':visible')){
				if(prodDetailHeight > dv.height() && $(window).width() > 991){
					dv.addClass( "img-static-scroll" );
				}
				dv.css('top',parseInt(mainNavposition.top)+parseInt(mainNavHeight)+40);
				//dv.css('width',dv.parent('div').width());
				dv.css('width',$('.details__body').width());
				dv.removeClass( "img-absolute-scroll" );
				$('.details__body').css('height','');
			}

			if(prodDetailHeight > dv.height() && $(window).width() > 991){
				var scrollTop = $(this).scrollTop();
				var divTop = $(".stop-img-static--js").offset().top;

				if(scrollTop == 0){
					dv.removeClass( "img-static-scroll" );
					dv.removeClass( "img-absolute-scroll" );
					$('.details__body').css('height','');
				}else if( (scrollTop + dv.height()) >= divTop - 105){
					$('.details__body').css('height',$('.product-detail').height());
					dv.css('top','');
					dv.removeClass( "img-static-scroll" );
					dv.addClass( "img-absolute-scroll" );
				}else{
					dv.addClass( "img-static-scroll" );
					dv.css('top',parseInt(mainNavposition.top)+parseInt(mainNavHeight)+40);
					dv.css('width',dv.parent('div').width());
					dv.removeClass( "img-absolute-scroll" );
					$('.details__body').css('height','');
				}
			}else{

				dv.removeClass( "img-static-scroll" );
				dv.css('top','');
				dv.css('width','');
			}*/

		$(".xzoom, .xzoom-gallery").xzoom();
	});

	/* ] */
	/* Product Main image to be static on scroll par a particular window scroll range[ */
	/* $(window).scroll(function(){
		var scrollTop = $(this).scrollTop();
		var divTop = $(".stop-img-static--js").offset().top;
		var dv = $("#img-static");
		var mainNavposition = $('.main-bar').position();
		var mainNavHeight = $('.main-bar').height();
		if( scrollTop > 165 && scrollTop < ( divTop - 700) ){
			dv.addClass( "img-static-scroll" );
			//dv.css('top',80);
			dv.css('top',parseInt(mainNavposition.top)+parseInt(mainNavHeight)+10);
			dv.css('width',dv.parent('div').width());
		} else {
			dv.removeClass( "img-static-scroll" );
			dv.css('top','auto');
			dv.css('width','auto');
		}
	}); */
	/* ] */

	$(".cancel").on('click', function(){
		$(this).parent().parent().siblings().toggleClass('cancelled--js ');
		$(this).toggleClass('remove-add-on');
	});

	$(".be-first").click(function(){
		$('html, body').animate({scrollTop: $("#itemRatings").offset().top - 130 }, 'slow');
		fcom.scrollToTop( $("#itemRatings") );
	});


		/* var frmObj = $(this).parents("form");
		var selprod_id = $(frmObj).find('input[name="selprod_id"]').val();
		var quantity = $(frmObj).find('input[name="quantity"]').val();
		cart.add(selprod_id, quantity); */

	$(".itemthumb").click(function(){
		var mainSrc = $(this).find('img').attr('main-src');
		$(".item__main").find('img').attr('src',mainSrc);
	});

    $('.js-collection-corner').slick( getSlickSliderSettings(5, 1, langLbl.layoutDirection) );

	/* for on scoll jump navigation fix */
	/* var elementPosition = $('.nav--jumps').offset();
	$(window).scroll(function(){
		if( $(window).scrollTop() > elementPosition.top ){
			$('.nav--jumps').addClass('nav--jumps-fixed');
		} else {
			$('.nav--jumps').removeClass('nav--jumps-fixed');
		}
	});
	 */
	$(".link_li").click(function(event){
		event.preventDefault();

		var target_offset = $(".product--specifications").offset();
		var target_top = target_offset.top-100;
		$('html, body').animate({scrollTop:target_top}, 1000);
	});
	/* for click scroll function */
	$(".scroll").click(function(event){
		event.preventDefault();
		var full_url = this.href;
		var parts = full_url.split("#");
		var trgt = parts[1];
		fcom.scrollToTop('#'+trgt);
		var target_offset = $("#"+trgt).offset();
		var target_top = target_offset.top-60;
		$('html, body').animate({scrollTop:target_top}, 1000);
	});

	$(".link--write").click(function(){
		$('html, body').animate({scrollTop: $("#itemRatings").offset().top - 130 }, 'slow');
		fcom.scrollToTop( $("#itemRatings") );
	});

	bannerAdds();
	reviews(document.frmReviewSearch);
});

function getSortedReviews(elm){
	if($(elm).length){
		var sortBy = $(elm).data('sort');
		if(sortBy){
			document.frmReviewSearch.orderBy.value = $(elm).data('sort');
			$(elm).parent().siblings().removeClass('is-active');
			$(elm).parent().addClass('is-active');
		}
	}
	reviews(document.frmReviewSearch);
}

function reviewAbuse(reviewId){
	if(reviewId){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('Reviews', 'reviewAbuse', [reviewId]), '', function(t) {
				$.facebox(t,'faceboxWidth');
			});
		});
	}
}

function setupReviewAbuse(frm){
	if (!$(frm).validate()) return;
	var data = fcom.frmData(frm);
	fcom.updateWithAjax(fcom.makeUrl('Reviews', 'setupReviewAbuse'), data, function(t) {
		$(document).trigger('close.facebox');
	});
	return false;
}

(function() {
	var setProdWeightage = false;
	var timeSpendOnProd = false;
	bannerAdds = function(){
		fcom.ajax(fcom.makeUrl('Banner','products'), '', function(res){
			$("#productBanners").html(res);
		});
	};

	setProductWeightage = function(code){
		var data = 'selprod_code='+code;
		if(setProdWeightage == true && timeSpendOnProd == true) { return;}
		if(setProdWeightage == true) {
			timeSpendOnProd = true;
			data+='&timeSpend=true';
		}
		setProdWeightage = true;
		fcom.ajax(fcom.makeUrl('Products','logWeightage'), data, function(res){
		});
	};

	/* reviews section[ */
	var dv = '#itemRatings .listing__all';
	var currPage = 1;

	reviews = function(frm, append){
		if( typeof append == undefined || append == null ){
			append = 0;
		}

		var data = fcom.frmData(frm);
		if( append == 1 ){
			$(dv).prepend(fcom.getLoader());
		} else {
			$(dv).html(fcom.getLoader());
		}

		fcom.updateWithAjax(fcom.makeUrl('Reviews','searchForProduct'), data, function(ans){
			$.mbsmessage.close();

			if( ans.totalRecords ){
				$('#reviews-pagination-strip--js').show();
			}
			if( append == 1 ){
				$(dv).find('.loader-yk').remove();
				$(dv).find('form[name="frmSearchReviewsPaging"]').remove();
				$(dv).append(ans.html);
				$('#reviewEndIndex').html(( Number($('#reviewEndIndex').html()) + ans.recordsToDisplay));
			} else {
				$(dv).html(ans.html);
				$('#reviewStartIndex').html(ans.startRecord);
				$('#reviewEndIndex').html(ans.recordsToDisplay);
			}
			$('#reviewsTotal').html(ans.totalRecords);
			$("#loadMoreReviewsBtnDiv").html( ans.loadMoreBtnHtml );
		}, '', false);
	};

	goToLoadMoreReviews = function(page){
		if(typeof page == undefined || page == null){
			page = 1;
		}
		currPage = page;
		var frm = document.frmSearchReviewsPaging;
		$(frm.page).val(page);
		reviews(frm,1);
	};

	/*] */

	markReviewHelpful = function(reviewId, isHelpful){
		if( isUserLogged() == 0 ){
			loginPopUpBox();
			return false;
		}
		isHelpful = (isHelpful) ? isHelpful : 0;
		var data = 'reviewId='+reviewId+'&isHelpful=' + isHelpful;
		fcom.updateWithAjax(fcom.makeUrl('Reviews','markHelpful'), data, function(ans){
			$.mbsmessage.close();
			reviews(document.frmReviewSearch);
			/* if(isHelpful == 1){

			} else {

			} */
		});
	}

	shareSocialReferEarn = function( selprod_id, socialMediaName ){
		if( isUserLogged() == 0 ){
			loginPopUpBox();
			return false;
		}
		var data = 'selprod_id=' + selprod_id + '&socialMediaName='+socialMediaName;

		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('Account', 'shareSocialReferEarn'), data, function(t) {
				$.facebox( t,'faceboxWidth');
			});
		});
		return false;
	}

	rateAndReviewProduct = function( product_id ){
		if( isUserLogged() == 0 ){
			loginPopUpBox();
			return false;
		}
		/* var data = 'product_id=' + product_id; */
		window.location = fcom.makeUrl('Reviews', 'write', [product_id]);
	}

	checkUserLoggedIn = function(){
		if( isUserLogged() == 0 ){
			loginPopUpBox();
			return false;
		}else return true;
	}

})();
 jQuery(document).ready(function($) {
      $('a[rel*=facebox]').facebox()
    });
/* for sticky things*/
      if($(window).width()>1050){
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


  }

$('.gallery').modaal({
    type: 'image'
});
