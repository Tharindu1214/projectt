$(document).ready(function(){
	searchCoupons(document.frmCouponSrch);
});
(function() {
	var dv = '#couponListing';
	searchCoupons = function(frm, append){		
		if(typeof append == undefined || append == null){
			append = 0;
		}
		
		var data = '';
		if (frm) {
			data = fcom.frmData(frm);
		}
		
		if( append == 1 ){
			$(dv).prepend(fcom.getLoader());
		} else {
			$(dv).html(fcom.getLoader());
		}
		fcom.updateWithAjax(fcom.makeUrl('Offers','search'), data, function(ans){
			$.mbsmessage.close();
			if( append == 1 ){
				$(dv).find('.loader-yk').remove();
				$(dv).append(ans.html);
			} else {
				$(dv).html(ans.html);
			}
			$("#loadMoreBtnDiv").html( ans.loadMoreBtnHtml );
		}); 		 
	};
	
	goToLoadMore = function(page){
		if(typeof page == undefined || page == null){
			page = 1;
		}
		currPage = page;
		var frm = document.frmCouponSrchPaging;		
		$(frm.page).val(page);
		searchCoupons(frm,1);
	};
})();	