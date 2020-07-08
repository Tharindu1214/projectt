$(document).ready(function(){
	searchQuestionBanks(document.frmQuestionBankSearch);
});
(function() {
	var currentPage = 1;
	var runningAjaxReq = false;
	var dv = '#qbankListing';
	
	goToSearchPage = function(page) {	
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmQuestionBankSearchPaging;		
		$(frm.page).val(page);
		searchQuestionBanks(frm);
	};

	reloadList = function() {
		var frm = document.frmQuestionBankSearchPaging;
		searchQuestionBanks(frm);
	};
	
	questionBankForm = function(qbankId){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('QuestionBanks', 'form', [qbankId]), '', function(t) {				
				$.facebox(t,'faceboxWidth');				
			});
		});
	};

	setupQuestionBank = function(frm) {
		if (!$(frm).validate()) return;		
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('QuestionBanks', 'setup'), data, function(t) {
			reloadList();
			if (t.langId>0) {
				questionBankLangForm(t.qbankId, t.langId);
				return ;
			}
			$(document).trigger('close.facebox');
		});
	};
	
	questionBankLangForm =  function(qbankId,langId){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('QuestionBanks', 'langForm', [qbankId, langId]), '', function(t) {
				$.facebox(t);
			});
		});
	};	
	
	setupQuestionBankLang = function(frm){ 
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);		
		fcom.updateWithAjax(fcom.makeUrl('QuestionBanks', 'setupLang'), data, function(t) {
			reloadList();				
			if (t.langId>0) {
				questionBankLangForm(t.qbankId, t.langId);
				return ;
			}
			$(document).trigger('close.facebox');
		});
	};
	
	searchQuestionBanks = function(form){				
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}		
		$(dv).html();
		
		fcom.ajax(fcom.makeUrl('QuestionBanks','search'),data,function(res){
			$(dv).html(res);
		});
	};
	
	deleteRecord = function(id){
		if(!confirm(langLbl.confirmDelete)){return;}
		data='id='+id;
		fcom.ajax(fcom.makeUrl('QuestionBanks','deleteRecord'),data,function(res){		
			reloadList();
		});
	};
	
	clearSearch = function(){
		document.frmQuestionBankSearch.reset();
		searchQuestionBanks(document.frmQuestionBankSearch);
	};

})();
