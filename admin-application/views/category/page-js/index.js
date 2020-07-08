(function(){
	var currentPage = 1;
	searchCategory = function(frm, page) {
		if (!page) {
			page = currentPage;
		}
		currentPage = page;
		var dv = $('#user-list');
		var data = fcom.frmData(frm);
		dv.html('Loading...');
		var pagesize = 10; 
		fcom.ajax(fcom.makeUrl('category', 'search', [page, pagesize]), data, function(t) {
			dv.html(t);
		});
	};
	showCategorySearchPage = function(page) {
		searchCategory(document.frmUserSearchPaging, page);
	};
})();
