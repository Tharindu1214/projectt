(function() {
	var currentPage = 1;
	searchUsers = function(frm, page) {
		if (!page) {
			page = currentPage;
		}
		currentPage = page;
		/*[ this block should be before dv.html('... anything here.....') otherwise it will through exception in ie due to form being removed from div 'dv' while putting html*/
		var data = '';
		if (frm) {
			data = fcom.frmData(frm);
		}
		/*]*/
		
		var dv = $('#user-list');
		dv.html('Loading...');
		var pagesize = 1; 
		fcom.ajax(fcom.makeUrl('users', 'search', [page, pagesize]), data, function(t) {
			dv.html(t);
		});
	};
	
	showUserSearchPage = function(page) {
		searchUsers(document.frmUserSearchPaging, page);
	};
	
	reloadUserList = function() {
		searchUsers(document.frmUserSearchPaging, currentPage);
	}
	
	verifyUser = function(id, v) {
		fcom.updateWithAjax(fcom.makeUrl('users', 'verify'), {userId: id, v: v}, function(t) {
			reloadUserList();
		});
	};
	activateUser = function(id, v) {
		fcom.updateWithAjax(fcom.makeUrl('users', 'activate'), {userId: id, v: v}, function(t) {
			reloadUserList();
		});
	};
})();