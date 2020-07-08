$(document).ready(function(){
	searchTestimonials();
});

(function() {
	var dv = '#listing';
	var currPage = 1;
	
	reloadListing = function(){
		searchTestimonials();
	};
	
	searchTestimonials = function(frm, append){
		if(typeof append == undefined || append == null){
			append = 0;
		}
		
		var data = fcom.frmData(frm);
		if( append == 1 ){
			$(dv).prepend(fcom.getLoader());
		} else {
			$(dv).html(fcom.getLoader());
		}
		
		
		fcom.updateWithAjax(fcom.makeUrl('Testimonials','search'), data, function(ans){
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
		var frm = document.frmSearchTestimonialsPaging;		
		$(frm.page).val(page);
		searchTestimonials(frm,1);
	};
	
})();