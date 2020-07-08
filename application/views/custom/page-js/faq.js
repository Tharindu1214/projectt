window.recordCount = 0;
$(document).ready(function(){
   /* $(document).on('click','.acc_ctrl',function(e){


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
  }); */


/* for sticky left panel
  if($(window).width()>1050){
    function sticky_relocate() {
        var window_top = $(window).scrollTop();
        var div_top = $('.fixed__panel').offset().top -30;
        var sticky_left = $('#fixed__panel');
        if((window_top + sticky_left.height()) >= ($('footer').offset().top - 60)){
            var to_reduce = ((window_top + sticky_left.height()) - ($('footer').offset().top - 60));
            var set_stick_top = -60 - to_reduce;
            sticky_left.css('top', set_stick_top+'px');
        }else{
            sticky_left.css('top', '30px');
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
}   */

	searchFaqs(0);
	faqRightPanel();
});

(function() {
	var dv = '#listing';
	var dvCategoryPanel = '#categoryPanel';
	var currPage = 1;
	var faqCatId=1;
	reloadListing = function(){
		// searchFaqs(document.frmSearchFaqs);
		searchFaqs(0);
	};

	$(document).on('click','a.selectedCat',function(){
		var catId=$(this).attr('id');
		var faqId=0;
		window.location.href = fcom.makeUrl('Custom','faqDetail', [catId,faqId]);
	});
	$(document).on('click','a.selectedFaq',function(){
		var faqId=$(this).attr('data-id');
		var faqcatId=$(this).attr('data-cat-id');

		window.location.href = fcom.makeUrl('Custom','faqDetail', [faqcatId,faqId]);
	});

	searchFaqs = function(catId){

		if (catId < 0) {
			catId = 0;
		}
		$(dv).html(fcom.getLoader());
		if (0 < catId) {
			$('.is--active').removeClass('is--active');
			$('#'+catId).addClass('is--active');
		}
		fcom.updateWithAjax(fcom.makeUrl('Custom','SearchFaqs', [catId]), '', function(ans){
			$.mbsmessage.close();

				$(dv).find('.loader-yk').remove();
				$(dv).html(ans.html);

			window.recordCount = ans.recordCount;

		});


	};

	faqRightPanel = function(){

		fcom.updateWithAjax(fcom.makeUrl('Custom','faqCategoriesPanel'), '', function(ans){
			$.mbsmessage.close();

				$(dv).find('.loader-yk').remove();
				$(dvCategoryPanel).html(ans.categoriesPanelHtml);

			window.recordCount = ans.recordCount;

		},'',false);
	}
	goToLoadMore = function(page){
		if(typeof page == undefined || page == null){
			page = 1;
		}
		currPage = page;

		var frm = document.frmSearchFaqsPaging;
		$(frm.page).val(page);
		searchFaqs(frm,1);
	};

})();

 /******** for faq accordians  ****************/

$(document).on('click','.accordians__trigger-js',function(){
  if($(this).hasClass('is-active')){
      $(this).removeClass('is-active');
      $(this).siblings('.accordians__target-js').slideUp();
      return false;
  }
 $('.accordians__trigger-js').removeClass('is-active');
 $(this).addClass("is-active");
 $('.accordians__target-js').slideUp();
 $(this).siblings('.accordians__target-js').slideDown();
});

$(document).on('click','.nav--vertical-js li',function(){

	if(!window.recordCount)
	{
		document.frmSearchFaqs.reset();
		$this = $(this).find('a');
		searchFaqs(document.frmSearchFaqs , 0 ,function(){$this.trigger('click');});
		event.stopPropagation();
		return false;
	}
	else{
		$('.nav--vertical-js li').removeClass('is-active');
		$(this).addClass('is-active');
	}
});

/* for click scroll function */
$(document).on('click',".scroll",function(event){

	if(!window.recordCount)
	{
		document.frmSearchFaqs.reset();
		$this = $(this);//.find('a');
		searchFaqs(document.frmSearchFaqs , 0 ,function(){$this.trigger('click');});
		event.stopPropagation();
		return false;
	}
	event.preventDefault();
	var full_url = this.href;
	var parts = full_url.split("#");
	var trgt = parts[1];
	if($("#"+trgt).length){
		var target_offset = $("#"+trgt).offset();
		var target_top = target_offset.top;
		$('html, body').animate({scrollTop:target_top}, 1000);
	}
});
