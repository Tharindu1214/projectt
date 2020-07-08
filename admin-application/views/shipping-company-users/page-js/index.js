$(document).ready(function(){
	searchUsers(document.frmUserSearch);
	$(document).on('click',function(){
		$('.autoSuggest').empty();
	});

	$(document).on('click','ul.linksvertical li a.redirect--js',function(){
		$( $(this) ).die();
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
		searchUsers(frm);
	};

	searchUsers = function(form,page){
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
		$("#userListing").html(fcom.getLoader());
 		fcom.ajax(fcom.makeUrl('ShippingCompanyUsers','search'),data, function(res){
			$("#userListing").html(res);
		});
	};


	userForm = function( user_id ){
		$.facebox(function() {
			addUserForm(user_id);
		});
	};

	addUserForm = function(user_id){
		fcom.displayProcessing();
		fcom.ajax(fcom.makeUrl('ShippingCompanyUsers', 'form', [ user_id ]), '', function(t) {
			fcom.updateFaceboxContent(t);
		});
	};

	setupUsers=function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('ShippingCompanyUsers', 'setup'), data, function(t) {
			reloadUserList();
			$(document).trigger('close.facebox');
		});
	};

	reloadUserList = function() {
		console.log(currentPage);
		searchUsers(document.frmUserSearchPaging, currentPage);
	};

	usersAutocomplete = function(v) {
		var dv = $('.autoSuggest');
		if(v.value == '') return;
		fcom.ajax(fcom.makeUrl('users', 'autoComplete'), { keyword:v.value, user_type: document.frmUserSearch.user_type.value }, function(t) {
			dv.show();
			dv.html(t);
		});
	};

	fillSuggetion = function(v) {
		$('#keyword').val(v);
		$('.autoSuggest').hide();
	};

	transactions = function(userId){
		transactionUserId = userId;
		$.facebox(function() {
			getTransactions(userId);
		});
	};

	getTransactions = function(userId){
		fcom.displayProcessing();
		fcom.ajax(fcom.makeUrl('Users', 'transaction', [userId]), '', function(t) {
			fcom.updateFaceboxContent(t);
		});
	};

	addUserTransaction = function(userId){
		fcom.displayProcessing();
		fcom.ajax(fcom.makeUrl('Users', 'addUserTransaction', [userId]), '', function(t) {
			fcom.updateFaceboxContent(t);
		});
	};

	setupUserTransaction = function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Users', 'setupUserTransaction'), data, function(t) {
			if(t.userId > 0) {
				getTransactions(t.userId);
			}
		});
	};

	goToTransactionPage = function(page) {
		if(typeof page == undefined || page == null){
			page = 1;
		}
		var frm = document.frmTransactionSearchPaging;
		$(frm.page).val(page);
		data = fcom.frmData(frm);
		fcom.displayProcessing();
		fcom.ajax(fcom.makeUrl('Users', 'transaction', [transactionUserId]), data, function(t) {
			fcom.updateFaceboxContent(t);
		});
		$.systemMessage.close();
	};

	toggleStatus = function(obj){
		if(!confirm(langLbl.confirmUpdateStatus)){return;}
		var userId = parseInt(obj.id);
		if(userId < 1){
			fcom.displayErrorMessage(langLbl.invalidRequest)
			return false;
		}
		data='userId='+userId;
		fcom.displayProcessing();
		fcom.ajax(fcom.makeUrl('users','changeStatus'),data,function(res){
		var ans =$.parseJSON(res);
			if(ans.status == 1){
				fcom.displaySuccessMessage(ans.msg);
				$(obj).toggleClass("active");
			}
		});
		$.systemMessage.close();
	};

	toggleBulkStatues = function(status){
        if(!confirm(langLbl.confirmUpdateStatus)){
            return false;
        }
        $("#frmShpCompUsrListing input[name='status']").val(status);
        $("#frmShpCompUsrListing").submit();
    };

	clearUserSearch = function(){
		document.frmUserSearch.reset();
		searchUsers(document.frmUserSearch);
	};

	getCountryStates = function(countryId,stateId,dv){
	fcom.displayProcessing();
		fcom.ajax(fcom.makeUrl('Users','getStates',[countryId,stateId]),'',function(res){
			$(dv).empty();
			$(dv).append(res);
		});
	$.systemMessage.close();
	};

})();
