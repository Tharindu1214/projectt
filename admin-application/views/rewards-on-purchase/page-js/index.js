$(document).ready(function(){
	searchRewardsOnPurchase(document.frmRewardsOnPurchase);
});
(function(){
	var currentPage = 1;
	var runningAjaxReq = false;
	var dv = '#listing';

	goToSearchPage = function(page) {
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmRewardsOnPurchaseSrchPaging;
		$(frm.page).val(page);
		searchRewardsOnPurchase(frm);
	};

	reloadList = function() {
		var frm = document.frmRewardsOnPurchaseSrchPaging;
		searchRewardsOnPurchase(frm);
	};

	searchRewardsOnPurchase = function(form){

		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}

		$(dv).html(fcom.getLoader());

		fcom.ajax(fcom.makeUrl('RewardsOnPurchase','search'),data,function(res){
			$(dv).html(res);
		});
	};

	rewardsOnPurchaseForm =  function (ropId){
		$.facebox(function() {
			addRewardPurchaseForm(ropId);
		});
	};

	addRewardPurchaseForm = function (ropId){
		fcom.displayProcessing();
		fcom.ajax(fcom.makeUrl('RewardsOnPurchase', 'form', [ropId]), '', function(t) {
			fcom.updateFaceboxContent(t);
		});
	};

	setupRewardsOnPurchase = function(frm){
		$(frm.btn_submit).attr("disabled", true);
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('RewardsOnPurchase', 'setup'), data, function(t) {
			reloadList();
			$(document).trigger('close.facebox');
		});
	};

	clearSearch = function(){
		document.frmRewardsOnPurchase.reset();
		searchRewardsOnPurchase(document.frmRewardsOnPurchase);
	};

	deleteRecord = function(id){
		if(!confirm("Do you really want to delete this record?")){return;}
		data='id='+id;
		fcom.updateWithAjax(fcom.makeUrl('RewardsOnPurchase','deleteRecord'),data,function(res){
			reloadList();
		});
	};
	deleteSelected = function(){
        if(!confirm(langLbl.confirmDelete)){
            return false;
        }
        $("#frmRewardsOnPurchaseListing").attr("action",fcom.makeUrl('RewardsOnPurchase','deleteSelected')).submit();
    };

})()
