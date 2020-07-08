$(document).ready(function(){
	searchPolling(document.frmPollingSearch);
});

(function() {
	var currentPage = 1;
	var runningAjaxReq = false;
	var dv = '#listing';
	
	goToSearchPage = function(page) {	
		if(typeof page == undefined || page == null){
			page =1;
		}
		var frm = document.frmPollingSearchPaging;		
		$(frm.page).val(page);
		searchPolling(frm);
	};
	
	reloadList = function() {
		var frm = document.frmPollingSearchPaging;
		searchPolling(frm);
	};
	
	searchPolling = function(form){
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		$(dv).html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('Polling','search'),data,function(res){			
			$(dv).html(res);
		});
	};
	
	pollingForm = function( polling_id ){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('Polling', 'form', [polling_id]), '', function(t) {
				$.facebox(t,'faceboxWidth');
			});
		});
	};
	
	setupPolling = function(frm){ 
		if (!$(frm).validate()) return;
		var addingNew = ( $(frm.polling_id).val() == 0 );
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Polling', 'setup'), data, function(t) {								
			reloadList();
			if ( addingNew ) {
				pollingLangForm( t.pollingId, t.langId );
				return ;
			}			
			$(document).trigger( 'close.facebox' );
		});
	};
	
	pollingLangForm = function( polling_id, lang_id ){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('Polling', 'langForm', [polling_id, lang_id]), '', function(t) {
				$.facebox(t);
			});
		});
	};
	
	setupPollingLang = function( frm ){
		if ( !$(frm).validate() ) return;		
		var data = fcom.frmData( frm );
		fcom.updateWithAjax(fcom.makeUrl('Polling', 'langSetup'), data, function(t) {
			reloadList();				
			if ( t.langId > 0 ) {
				pollingLangForm(t.pollingId, t.langId);
				return ;
			}
			else if(t.openLinksForm){
				linksForm(t.pollingId);
				return;
			}
			$(document).trigger('close.facebox');
			return ;
		});
	};
	
	linksForm = function( polling_id ){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('Polling', 'linksForm', [polling_id]), '', function(t) {
				$.facebox(t,'faceboxWidth');
			});
		});
	};
	
	reloadLinkedProducts = function( polling_id ){
		$("#linked_entities_list").html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('Polling', 'linkedProducts', [polling_id]), '', function(t) {
			$("#linked_entities_list").html(t);
		});
	}
	
	updateLinkedProducts = function (polling_id, product_id){
		fcom.updateWithAjax(fcom.makeUrl('Polling', 'updateLinkedProducts'), 'polling_id='+polling_id+'&product_id='+product_id, function(t) {
			reloadLinkedProducts(polling_id);
		});
	}
	
	removeLinkedProduct = function(polling_id, product_id){
		var agree = confirm(langLbl.confirmRemoveProduct);
		if(!agree){ return false; }
		fcom.updateWithAjax(fcom.makeUrl('Polling', 'removeLinkedProduct'), 'polling_id='+polling_id+'&product_id='+product_id, function(t) {
			reloadLinkedProducts(polling_id);
		});
	};
	
	reloadLinkedCategories = function( polling_id ){
		$("#linked_entities_list").html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('Polling', 'linkedCategories', [polling_id]), '', function(t) {
			$("#linked_entities_list").html(t);
		});
	}
	
	updateLinkedCategories = function (polling_id, prodcat_id){
		fcom.updateWithAjax(fcom.makeUrl('Polling', 'updateLinkedCategories'), 'polling_id='+polling_id+'&prodcat_id='+prodcat_id, function(t) {
			reloadLinkedCategories(polling_id);
		});
	}
	
	removeLinkedCategory = function(polling_id, prodcat_id){
		var agree = confirm(langLbl.confirmRemoveCategory);
		if(!agree){ return false; }
		fcom.updateWithAjax(fcom.makeUrl('Polling', 'removeLinkedCategory'), 'polling_id='+polling_id+'&prodcat_id='+prodcat_id, function(t) {
			reloadLinkedCategories(polling_id);
		});
	};
	
	clearSearch = function(){
		document.frmTaxSearch.reset();
		searchTax(document.frmTaxSearch);
	};	
	
})();	