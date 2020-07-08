$(document).ready(function(){
	searchRequestedCatalog(document.frmSearchCatalogReq);
});
(function() {
	var runningAjaxReq = false;
	var dv = '#listing';
	
	searchRequestedCatalog = function(frm){ 		
		/*[ this block should be written before overriding html of 'form's parent div/element, otherwise it will through exception in ie due to form being removed from div */
		var data = fcom.frmData(frm);
		/*]*/
		
		$(dv).html( fcom.getLoader() );
		fcom.ajax(fcom.makeUrl('Seller','searchRequestedCatalog'),data,function(res){			
			$(dv).html(res);
		});
	};
	
	reloadList = function(){
		searchRequestedCatalog(document.frmSearchCatalogReq);
	};
	
	goToCatalogReqSearchPage = function(page){
		if(typeof page == undefined || page == null){
			page = 1;
		}
		var frm = document.frmCatalogReqSearchPaging;		
		$(frm.page).val(page);
		searchRequestedCatalog(frm);
	};
	
	viewRequestedCatalog = function(scatrequest_id){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('Seller', 'viewRequestedCatalog', [ scatrequest_id ]), '', function(t) {
				$.facebox(t,'faceboxWidth');
			});
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
	
	messageForm = function(scatrequest_id){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('Seller', 'catalogRequestMsgForm', [ scatrequest_id ]), '', function(t) {
				$.facebox(t,'faceboxWidth');
				searchCatalogRequestMessages(document.frmCatalogRequestMsgsSrch);
			});
		});
	};
	
	searchCatalogRequestMessages = function(frm, append = 0){
		
		var dv = $("#messagesList");
		var data = fcom.frmData(frm);
		if( append == 1 ){
			$(dv).prepend(fcom.getLoader());
		} else {
			$(dv).html(fcom.getLoader());
		}
		fcom.updateWithAjax(fcom.makeUrl('Seller','catalogRequestMessageSearch'), data, function(ans){
			$.mbsmessage.close();
			if( append == 1 ){
				$(dv).find('.loader-yk').remove();
				$(dv).prepend(ans.html);
			} else {
				$(dv).html( ans.html );
			}
			
			/* for LoadMore[ */
			$("#loadMoreBtnDiv").html( ans.loadMoreBtnHtml );
			/* ] */
		});
	};
	
	setUpCatalogRequestMessage = function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl( 'Seller', 'setUpCatalogRequestMessage'), data, function(t) {
			searchCatalogRequestMessages( document.frmCatalogRequestMsgsSrch );
			frm.reset();
		});
	};
	
	
	addNewCatalogRequest = function(){
		$(dv).html( fcom.getLoader() );
			
		/* $.facebox(function() { */
		fcom.ajax(fcom.makeUrl('Seller', 'addCatalogRequest'), '', function(t) {
			//$.facebox(t,'faceboxWidth');
			$(dv).html(t);
			var frm = $(dv+' form')[0];
			var validator = $(frm).validation({errordisplay: 3});
			$(frm).submit(function(e) {
				e.preventDefault();
				validator.validate();
				if (!validator.isValid()) return;
				
				$.mbsmessage(langLbl.processing,true,'alert--process alert');
				$.ajax({
				url: fcom.makeUrl('Seller', 'setupCatalogRequest'),
				type: 'post',
				dataType: 'json',
				data: new FormData($(frm)[0]),
				cache: false,
				contentType: false,
				processData: false,
				
				success: function(ans) {
					$.mbsmessage(ans.msg, true, 'alert--success');
					if(ans.status == true){
						$.mbsmessage(t.msg);
						searchRequestedCatalog(document.frmCatalogReqSearchPaging);
					}
				},
				error: function(xhr, ajaxOptions, thrownError) {
					alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				}
				});
				
				/* var data = fcom.frmData(frm);
				fcom.updateWithAjax(fcom.makeUrl('Seller', 'setUpCatalogRequest'), data, function(t) {
					$.mbsmessage(t.msg);
					searchRequestedCatalog(document.frmCatalogReqSearchPaging);
					fcom.resetEditorInstance();
				}); */
			});
		});
		/* });	 */
	};
	
	/* setupCatalogRequest = function (frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Seller', 'setUpCatalogRequest'), data, function(t) {
			searchRequestedCatalog(document.frmCatalogReqSearchPaging);
			$(document).trigger('close.facebox');
		});		
	}; */
	
	deleteRequestedCatalog = function( scatrequest_id ){
		var agree = confirm(langLbl.confirmDelete);
		if( !agree ){
			return false;
		}
		fcom.updateWithAjax(fcom.makeUrl('Seller', 'deleteRequestedCatalog'), 'scatrequest_id=' + scatrequest_id, function(t) {
			searchRequestedCatalog(document.frmCatalogReqSearchPaging);
			$.mbsmessage.close();			
		});
	};
	
})();