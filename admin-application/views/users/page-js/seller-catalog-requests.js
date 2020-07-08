$(document).ready(function(){
	searchListing(document.frmCatalogReqSrch);	
});
(function() {
	var currentPage = 1;
	
	goToSearchPage = function(page) {	
		if(typeof page==undefined || page == null){
			page = 1;
		}		
		var frm = document.frmCatalogReqSrch;				
		searchListing(frm,page);
	};
	
	reloadList = function() {
		searchListing(document.frmSearchPaging, currentPage);
	};
	
	searchListing = function(form,page){
		if (!page) {
			page = currentPage;
		}
		currentPage = page;	
		var dv = $('#supplierCataloglist');		
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		dv.html(fcom.getLoader());
		data = data+'&page='+currentPage;
		fcom.ajax(fcom.makeUrl('Users','sellerCatalogRequestSearch'),data,function(t){
			dv.html(t);
		});
	};
	
	viewCatalogRequest = function(requestId){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('Users','viewCatalogRequest',[requestId]),'',function(t){
				fcom.updateFaceboxContent(t);
			});	
		});		
	};
	
	sellerCatalogRequestMsgForm = function(requestId){
		$.facebox(function() {
			fcom.displayProcessing();
			fcom.ajax(fcom.makeUrl('Users','sellerCatalogRequestMsgForm',[requestId]),'',function(t){
				fcom.updateFaceboxContent(t);
				searchCatalogRequestMessages(document.frmCatalogRequestMsgsSrch);
			});
		});
	};
	
 	searchCatalogRequestMessages = function(frm, append){
 		var dv = $("#messagesList");
		
		var data = fcom.frmData(frm);
		if( append == 1 ){
			$(dv).append(fcom.getLoader());
		} else {
			$(dv).html(fcom.getLoader());
		} 
		
		fcom.ajax(fcom.makeUrl('Users','catalogRequestMessageSearch'), data, function(res){
			
			var ans =$.parseJSON(res);
			
			if( append == 1 ){
				$(dv).find('.circularLoader').remove();
				$(dv).append(ans.html);
			} else {
				$(dv).html( ans.html );
			}
			
			$("#loadMoreBtnDiv").html( ans.loadMoreBtnHtml );

			fcom.resetFaceboxHeight();
		});
		
	}; 
		
	goToCatalogRequestMessageSearchPage = function(page) {
		if( typeof page==undefined || page == null ){
			page = 1;
		}		
		var frm = document.frmCatalogRequestMsgsSrchPaging;		
		$(frm.page).val(page);
		$("form[name='frmCatalogRequestMsgsSrchPaging']").remove();
		searchCatalogRequestMessages(frm, 1);
	};
	
	setUpCatalogRequestMessage = function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl( 'Users', 'setUpCatalogRequestMessage'), data, function(t) {
			searchCatalogRequestMessages( document.frmCatalogRequestMsgsSrch );
			fcom.scrollToTop('#frmArea');
			frm.reset();
		});
	};
	
	updateCatalogRequestForm = function (requestId){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('Users','updateCatalogRequestForm',[requestId]),'',function(t){
				fcom.updateFaceboxContent(t);
			});	
		});	
	};
	
	updateCatalogRequest = function (frm){
		if (!$(frm).validate()) { return; }
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Users', 'updateCatalogRequest'), data, function(t) {			
			reloadList();			
			$(document).trigger('close.facebox');
		});
	};
	
	showHideCommentBox = function(val){
		if(val == 2){
			$('#div_comments_box').removeClass('hide');			
		}else{
			$('#div_comments_box').addClass('hide');			
		}		
	};
	
	clearCatalogReqSrch = function(){
		document.frmCatalogReqSrch.reset();
		searchListing(document.frmCatalogReqSrch);
	};
	
})();	