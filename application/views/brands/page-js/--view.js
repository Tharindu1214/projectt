$(document).ready(function(){
	bannerAdds();

	searchProducts(document.frmProductSearch);

	/* for toggling of grid/list view[ */
	$('.switch--link-js').on('click',function(e) {
		$('.switch--link-js').parent().removeClass("is--active");
		$(this).parent().addClass("is--active");
		if ($(this).hasClass('list')) {
			$('#productsList').removeClass('listing-products--grid').addClass('listing-products--list');
		}
		else if($(this).hasClass('grid')) {
			$('#productsList').removeClass('listing-products--list').addClass('listing-products--grid');
		}
	});
	/* ] */
});

(function() {
	bannerAdds = function(){
		fcom.ajax(fcom.makeUrl('Banner','brands'), '', function(res){
			$("#brandBanners").html(res);
		});
	};
})();
