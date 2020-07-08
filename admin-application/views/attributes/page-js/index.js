$(document).ready(function(){
	searchListing(document.frmSearch);
});
(function() {
	var currentPage = 1;
	var runningAjaxReq = false;

	goToSearchPage = function(page) {	
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmSearchPaging;		
		$(frm.page).val(page);
		searchListing(frm);
	}

	reloadList = function() {
		var frm = document.frmSearchPaging;
		searchListing(frm);
	}

	addForm = function(id) {
		var frm = document.frmSearchPaging;			
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('Attributes', 'form', [id]), '', function(t) {
				$.facebox(t,'faceboxWidth');
			});
		});
	};

	setupAttrGroup = function(frm) {
		if (!$(frm).validate()) return;		
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Attributes', 'setup'), data, function(t) {
			reloadList();
			$(document).trigger('close.facebox');
		});
	};

	searchListing = function(form){		
		/*[ this block should be written before overriding html of 'form's parent div/element, otherwise it will through exception in ie due to form being removed from div */
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		/*]*/
		$("#listing").html('Loading....');
		
		fcom.ajax(fcom.makeUrl('Attributes','search'),data,function(res){
			$("#listing").html(res);
		});
	};
	
	deleteRecord=function(id){
		if(!confirm(langLbl.confirmDelete)){return;}
		data='id='+id;
		fcom.updateWithAjax(fcom.makeUrl('Attributes','delete_record'),data,function(res){		
			reloadList();
		});
	};
	
	clearSearch = function(){
		document.frmSearch.reset();
		searchListing(document.frmSearch);
	};
	
})();
