$(document).ready(function () {

	$('.wrapper-menu').click(function () {
		$('html').toggleClass("nav-opened");
		$(this).toggleClass("open");

		$('.search-toggle').removeClass('active');
		$('html').removeClass("form-opened");
	});

	$('.search-toggle').on('click', function () {
		$(this).toggleClass('active');
		$('html').toggleClass("form-opened");

		$('.wrapper-menu').removeClass("open");
		$('html').removeClass("nav-opened");
	})

	$('.js-tabs li').click(function() {
		$(this).siblings().removeClass('is--active');
		$(this).addClass('is--active');
		moveToTargetDiv(this);
		return false;
	});

	var tabs = $(".js-tabs li a");

	tabs.click(function() {
		var content = this.hash.replace('/', '');
		tabs.removeClass("active");
		$(this).addClass("active");
		$(this).parents('.container').find('.tabs-content').find('.content-data').hide();
		$(content).fadeIn(200);

	});
    
    $("body").mouseup(function(e){ 
        if (1 > $(event.target).parents('.social-toggle').length && $('.social-toggle').next().hasClass('open-menu')) {
            $('.social-toggle').next().toggleClass('open-menu');
        }
    });

});

$(document).on('click', '.social-toggle', function(){
	$(this).next().toggleClass('open-menu');
});

$("body").mouseup(function(e){ 
    if (1 > $(event.target).parents('.social-toggle').length && $('.social-toggle').next().hasClass('open-menu')) {
        $('.social-toggle').next().toggleClass('open-menu');
    }
});

function submitBlogSearch(frm){
	var qryParam=($(frm).serialize_without_blank());
	var url_arr = [];
	if( qryParam.indexOf("keyword") > -1 ){
		var keyword = $(frm).find('input[name="keyword"]').val();
		var protomatch = /^(https?|ftp):\/\//;
		url_arr.push('keyword-'+encodeURIComponent(keyword.replace(protomatch,'').replace(/\//g,'-')));
	}

	if( qryParam.indexOf("category") > -1 ){
		url_arr.push('category-'+$(frm).find('select[name="category"]').val());
	}

	if(themeActive == true ){
		url = fcom.makeUrl('Blog','search', url_arr)+'?theme-preview';
		document.location.href = url;
		return;
	}
	url = fcom.makeUrl('Blog','search', url_arr);
	document.location.href = url;
}
