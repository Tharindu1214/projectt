$(document).ready(function(){
	searchProduct(document.frmProductSearch);
});

(function() {
	var runningAjaxReq = false;
	var dv = '#listing';

	goToSearchPage = function(page) {
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmProductSearchPaging;
		$(frm.page).val(page);
		searchProduct(frm);
	}

	reloadList = function() {
		searchProduct();
	};

	searchProduct = function(form){
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		$(dv).html(fcom.getLoader());

		fcom.ajax(fcom.makeUrl('SellerProducts','searchThresholdLevelProducts'),data,function(res){
			$(dv).html(res);
		});
	};

	sendMailForm = function (userId,selprodId){
		$.systemMessage(langLbl.processing,'alert--process');
		fcom.ajax(fcom.makeUrl('SellerProducts', 'sendMailThresholdStock', [userId,selprodId]), '', function(res) {
			var ans =$.parseJSON(res);
			if(ans.status == 1){
                $.systemMessage(ans.msg,'alert--success',true);
				reloadList();
			}else{
                $.systemMessage(ans.msg,'alert--danger',true);
			}
		});
	};

	sendMail = function (frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('SellerProducts', 'sendMailThresholdStock'), data, function(t) {
			$(document).trigger('close.facebox');
		});
	};
	clearSearch = function(){
		document.frmProductSearch.reset();
		searchProduct(document.frmProductSearch);
	};
})();
