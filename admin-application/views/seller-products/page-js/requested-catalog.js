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
		fcom.ajax(fcom.makeUrl('sellerProducts','searchRequestedCatalog'),data,function(res){		
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
			fcom.ajax(fcom.makeUrl('sellerProducts', 'viewRequestedCatalog', [ scatrequest_id ]), '', function(t) {
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
			fcom.ajax(fcom.makeUrl('sellerProducts', 'catalogRequestMsgForm', [ scatrequest_id ]), '', function(t) {
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
		fcom.updateWithAjax(fcom.makeUrl('sellerProducts','catalogRequestMessageSearch'), data, function(ans){
			if( append == 1 ){
				$(dv).find('.loader-Js').remove();
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
		fcom.updateWithAjax(fcom.makeUrl( 'sellerProducts', 'setUpCatalogRequestMessage'), data, function(t) {
			
			searchCatalogRequestMessages( document.frmCatalogRequestMsgsSrch );
			frm.reset();
		});
	};
	
	
	addNewCatalogRequest = function(){
		$(dv).html( fcom.getLoader() );
		fcom.resetEditorInstance();		
		/* $.facebox(function() { */
		fcom.ajax(fcom.makeUrl('sellerProducts', 'addCatalogRequest'), '', function(t) {
			//$.facebox(t,'faceboxWidth');
			$(dv).html(t);
			var frm = $(dv+' form')[0];
			var validator = $(frm).validation({errordisplay: 3});
			$(frm).submit(function(e) {
				e.preventDefault();
				validator.validate();
				if (!validator.isValid()) return;
				
				$.systemMessage(langLbl.processing,'alert--process');
				$.ajax({
				url: fcom.makeUrl('sellerProducts', 'setupCatalogRequest'),
				type: 'post',
				dataType: 'json',
				data: new FormData($(frm)[0]),
				cache: false,
				contentType: false,
				processData: false,
				
				success: function(ans) {
					fcom.displaySuccessMessage(ans.msg);
					//$.systemMessage(ans.msg, 'alert-success');
					if(ans.status == true){
						$.systemMessage(t.msg,'alert--success');
						fcom.displaySuccessMessage(t.msg);
						searchRequestedCatalog(document.frmCatalogReqSearchPaging);
					}
				},
				error: function(xhr, ajaxOptions, thrownError) {
					alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				}
				});
				
				/* var data = fcom.frmData(frm);
				fcom.updateWithAjax(fcom.makeUrl('sellerProducts', 'setUpCatalogRequest'), data, function(t) {
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
		fcom.updateWithAjax(fcom.makeUrl('sellerProducts', 'setUpCatalogRequest'), data, function(t) {
			searchRequestedCatalog(document.frmCatalogReqSearchPaging);
			$(document).trigger('close.facebox');
		});		
	}; */
	
	deleteRequestedCatalog = function( scatrequest_id ){
		var agree = confirm(langLbl.confirmDelete);
		if( !agree ){
			return false;
		}
		fcom.updateWithAjax(fcom.makeUrl('sellerProducts', 'deleteRequestedCatalog'), 'scatrequest_id=' + scatrequest_id, function(t) {
			searchRequestedCatalog(document.frmCatalogReqSearchPaging);
			/* $.mbsmessage.close(); */			
		});
	};
	
})();