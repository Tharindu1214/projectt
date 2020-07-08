$(document).ready(function(){
	searchTaxCategories(document.frmSearchTaxCat);
});
(function() {	
	var runningAjaxReq = false;
	var dv = '#listing';
	
	searchTaxCategories = function (frm){
		/*[ this block should be written before overriding html of 'form's parent div/element, otherwise it will through exception in ie due to form being removed from div */
		var data = fcom.frmData(frm);
		/*]*/
		$(dv).html( fcom.getLoader() );
		
		fcom.ajax(fcom.makeUrl('sellerProducts','searchTaxCategories'),data,function(res){		
			$(dv).html(res);
		});
	};
	
	goToSearchPage = function(page){
		if(typeof page==undefined || page == null){
			page = 1;
		}
		var frm = document.frmSearchTaxCatPaging;		
		$(frm.page).val(page);
		searchTaxCategories(frm);
	};
	
	reloadList = function(){
		searchTaxCategories(document.frmSearchTaxCat);
	};
	
	changeTaxRates = function(taxcatId){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('sellerProducts', 'changeTaxRates', [taxcatId]), '', function(t) {
				$.facebox(t,'faceboxWidth');
			});
		});
	};
	
	setUpTaxRates = function(frm){
		if (!$(frm).validate()) return;		
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('sellerProducts', 'setUpTaxRates'), data, function(t) {						
			reloadList();
			$(document).trigger('close.facebox');
		});
		return false;
	};
	
	resetCatTaxRates = function(taxcatId){
		fcom.updateWithAjax(fcom.makeUrl('sellerProducts', 'resetCatTaxRates',[taxcatId]), '', function(t) {
			if(t.taxcatId > 0){
				reloadList();
			}
		});
	};
	
})();	