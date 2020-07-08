$(document).ready(function(){
	searchUserRequests();
	
	$('input[name=\'keyword\']').autocomplete({
		'source': function(request, response) {		
			$.ajax({
				url: fcom.makeUrl('Users', 'autoCompleteJson'),
				data: {keyword: request, fIsAjax:1},
				dataType: 'json',
				type: 'post',
				success: function(json) {
					response($.map(json, function(item) {
						return { label: item['name'] +'(' + item['username'] + ')', value: item['id'], name: item['username']	};
					}));
				},
			});
		},
		'select': function(item) {
			$("input[name='user_id']").val( item['value'] );
			$("input[name='keyword']").val( item['name'] );
		}
	});
	
	$('input[name=\'keyword\']').keyup(function(){
		$('input[name=\'user_id\']').val('');
	});
	
});

(function() {
	var currentPage = 1;
	var transactionUserId = 0;
	var rewardUserId = 0;
	
	goToSearchPage = function(page) {	
		if(typeof page == undefined || page == null){
			page = 1;
		}		
		var frm = document.frmUserSearchPaging;		
		$(frm.page).val(page);
		searchUserRequests(frm);
	};
		
	searchUserRequests = function(form,page){
		if (!page) {
			page = currentPage;
		}
		currentPage = page;	
		/*[ this block should be before dv.html('... anything here.....') otherwise it will through exception in ie due to form being removed from div 'dv' while putting html*/
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		/*]*/	
		
		$("#userRequestsListing").html(fcom.getLoader());
		
		fcom.ajax(fcom.makeUrl('UserGdprRequests','userRequestsSearch'),data,function(res){
			$("#userRequestsListing").html(res);
		});
	};
	
	reloadUserList = function() {
		searchUserRequests(document.frmUserSearchPaging, currentPage);
	};
	
	
	updateRequestStatus = function (reqId,reqStatus){
		if(!confirm(langLbl.confirmChangeRequestStatus)){return;}
		var data = 'reqId='+reqId+'&status='+reqStatus;
		fcom.updateWithAjax(fcom.makeUrl('UserGdprRequests', 'updateRequestStatus'), data, function(t) {					
			if(t.status == 1){
				searchUserRequests();
			}	
		});
	};
	
	clearSearch = function(){
		document.frmUserSearch.reset();
		document.frmUserSearch.user_id.value = '';
		searchUsers( document.frmUserSearch );
	};
	
	/* deleteUserRequest = function (reqId){
		if(!confirm(langLbl.confirmDelete)){return;}
		var data = 'reqId='+reqId;
		fcom.updateWithAjax(fcom.makeUrl('UserGdprRequests', 'deleteUserRequest'), data, function(t) {			
			if(t.userReqId > 0) {
				searchUserRequests();
			}	
		});
	}; */
	
	viewRequestPurpose = function (reqId){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('UserGdprRequests','viewUserRequest',[reqId]),'',function(t){
				fcom.updateFaceboxContent(t);
			});
		});		
	};
	
	truncateUserData = function (userId,userReqId){
		if(!confirm(langLbl.confirmTruncateUserData)){return;}
		var data = 'userId='+userId+'&reqId='+userReqId;
		fcom.updateWithAjax(fcom.makeUrl('UserGdprRequests', 'truncateUserData'), data, function(t) {					
			if(t.userReqId > 0) {
				searchUserRequests();
			}	
		});
	};

	
})();