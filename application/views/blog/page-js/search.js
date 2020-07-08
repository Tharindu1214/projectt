$(document).ready(function(){
	searchBlogs(keyword);
	$(frmBlogSearch.keyword).val(keyword);
	/*$("#search-keyword-js").keyup(function(){
		var keyword = $(this).val();
		searchBlogs(keyword);
	});*/
});
goToSearchPage = function(page) {
	if(typeof page == undefined || page == null){
		page =1;
	}
	var frm = document.frmBlogSearchPaging;
	$(frm.page).val(page);
	searchBlogs('', frm);
};
var dv = '#blogs-listing-js';
searchBlogs = function(keyword, form){
	var data = '';
	if (form) {
        data = fcom.frmData(form);
    }
	if(keyword!=''){
		data +='&keyword='+keyword;
	}
	fcom.ajax(fcom.makeUrl('Blog', 'blogList'), data, function (ans) {
		$.mbsmessage.close();
		var res = $.parseJSON(ans);
		$(dv).html(res.html);

		if( $('#start_record').length > 0  ){
			$('#start_record').html(res.startRecord);
		}
		if( $('#end_record').length > 0  ){
			$('#end_record').html(res.endRecord);
		}
		if( $('#total_records').length > 0  ){
			$('#total_records').html(res.totalRecords);
		}

		if($("#loadMoreBtnDiv").length){
			$("#loadMoreBtnDiv").html( res.loadMoreBtnHtml );
		}
	});
}
