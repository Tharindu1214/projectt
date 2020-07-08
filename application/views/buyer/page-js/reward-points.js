$(document).ready(function(){
	searchRewardPoints(document.frmRewardPointSearch);
});

(function() {
	var dv = '#rewardPointsListing';
	
	searchRewardPoints = function(frm){		
		var data = '';
		if (frm) {
			data = fcom.frmData(frm);
		}
		$(dv).html( fcom.getLoader() );
		fcom.ajax(fcom.makeUrl('Buyer','rewardPointsSearch'), data, function(res){
			$(dv).html(res);
		}); 
	};
	
	goToSearchPage = function(page) {	
		if(typeof page == undefined || page == null){
			page = 1;
		}		
		var frm = document.frmRewardPointSearchPaging;		
		$(frm.page).val(page);
		searchRewardPoints(frm);
	};
	
	generateCoupon = function(){
		var checkboxValue ='';
		$(":checkbox").each(function () {
			if($(this).hasClass('rewardOptions-Js')){
				var ischecked = $(this).is(":checked");
				if (ischecked) {
					checkboxValue += $(this).val() + "|";
				}
			}
		});		
		var data = 'rewardOptions='+checkboxValue;
		fcom.updateWithAjax(fcom.makeUrl('Buyer','generateCoupon'), data, function(res){
			
		});
	};
	
	clearSearch = function(){
		document.frmRewardPointSearch.reset();
		searchRewardPoints(document.frmRewardPointSearch);
	};
	
})();	