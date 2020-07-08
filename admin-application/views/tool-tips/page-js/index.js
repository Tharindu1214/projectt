$(document).ready(function(){
	listTooltips();	
});
(function() {
	var currentPage = 1;
	
	goToSearchPage = function(page) {	
		if(typeof page == undefined || page == null){
			page = 1;
		}		
		var frm = document.frmSearchPaging;		
		$(frm.page).val(page);
		listTooltips(frm);
	};	
	
	listTooltips = function(form,page){
		if (!page) {
			page = currentPage;
		}
		currentPage = page;	
		
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
			
		$("#tooltipListing").html(fcom.getLoader());
		
		fcom.ajax(fcom.makeUrl('ToolTips','search'),data,function(res){
			$("#tooltipListing").html(res);
		});
	};
		
	reloadList = function(){
		listTooltips();
	};	
	
	addTooltip= function(id){
		$.facebox(function() {tooltipForm(id); });
	};

	tooltipForm = function(id) {
		fcom.displayProcessing();
		var frm = document.frmSearchPaging;			
		fcom.ajax(fcom.makeUrl('ToolTips', 'form', [id]), '', function(t) {
			fcom.updateFaceboxContent(t);
		});
	};	
	
	setupTooltip = function(frm) {
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('ToolTips', 'setup'), data, function(t) {
			reloadList();
			if (t.langId>0) {
				tooltipLangForm(t.tooltipId, t.langId,'add');
				return ;
			}
			$(document).trigger('close.facebox');
		});
	};
	
	addtooltipLangForm = function(tooltipId, langId,action = 'edit') {	
		$.facebox(function() {
			tooltipLangForm(tooltipId, langId,action); 
		});
	};

	tooltipLangForm = function(tooltipId, langId,action = 'add') {	
		fcom.displayProcessing();	
		fcom.ajax(fcom.makeUrl('ToolTips', 'langForm', [tooltipId, langId,action]), '', function(t) {
			fcom.updateFaceboxContent(t);
		});
	};
	
	setupTooltipLang = function(frm,action){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);		
		fcom.updateWithAjax(fcom.makeUrl('ToolTips', 'langSetup'), data, function(t) {
			reloadList();				
			if (t.langId>0) {
				tooltipLangForm(t.tooltipId, t.langId,action);
				return ;
			}
			$(document).trigger('close.facebox');
		});
	};
	
	clearSearch = function(frm){
		document.frmSearch.reset();
		reloadList();	
	};
	
	
})();	