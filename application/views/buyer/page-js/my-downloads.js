$(document).ready(function(){
	searchBuyerDownloads(document.frmSrch);
});

(function() {
	var dv = "#listing";
	searchBuyerDownloads = function(frm,el){
		/*[ this block should be written before overriding html of 'form's parent div/element, otherwise it will through exception in ie due to form being removed from div */
		var data = fcom.frmData(frm);
		/*]*/

		$(dv).html( fcom.getLoader() );
		fcom.ajax(fcom.makeUrl('Buyer','downloadSearch'), data, function(res){
			$(dv).html(res);
			$(el).parent().siblings().removeClass('is-active');
			$(el).parent().addClass('is-active');
		});
	};

	searchBuyerDownloadLinks = function(frm,el){
		/*[ this block should be written before overriding html of 'form's parent div/element, otherwise it will through exception in ie due to form being removed from div */
		var data = fcom.frmData(frm);
		/*]*/

		$(dv).html( fcom.getLoader() );
		fcom.ajax(fcom.makeUrl('Buyer','downloadLinksSearch'), data, function(res){
			$(dv).html(res);
			$(el).parent().siblings().removeClass('is-active');
			$(el).parent().addClass('is-active');
		});
	};

	goToSearchPage = function(page) {
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmSrchPaging;
		$(frm.page).val(page);
		searchBuyerDownloads(frm);
	};

	goToLinksSearchPage = function(page) {
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmSrchPaging;
		$(frm.page).val(page);
		searchBuyerDownloadLinks(frm);
	};

	clearSearch = function(type){		
		/* document.frmSrch.reset(); */
		$('input[name=keyword').val('');
		if(type==1){
			searchBuyerDownloadLinks(document.frmSrch);
			return;
		}
		searchBuyerDownloads(document.frmSrch);
	};

	increaseDownloadedCount = function(linkId, opId ){
	/* function increaseDownloadedCount( linkId, opId ){ */
		fcom.ajax(fcom.makeUrl('buyer', 'downloadDigitalProductFromLink', [linkId,opId]), '', function(t) {
			var ans = $.parseJSON(t);
			if( ans.status == 0 ){
				$.systemMessage( ans.msg, 'alert--danger');
				return false;
			}
			location.reload();
			/* var dataLink = $(this).attr('data-link');
			window.location.href= dataLink; */
			return true;
		});
	}

})();
