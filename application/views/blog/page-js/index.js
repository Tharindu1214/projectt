$(document).ready(function(){
	//searchBlogs(document.frmBlogSearch);
	bannerAdds();
});
(function() {
	bannerAdds = function(){
		fcom.ajax(fcom.makeUrl('Banner','blogPage'), '', function(res){
			$("#div--banners").html(res);
		});
	};

	var dv = '#listing';
	var currPage = 1;

	reloadListing = function(){
		searchBlogs(document.frmBlogSearch);
	};

	searchBlogs = function(frm, append){
		if(typeof append == undefined || append == null){
			append = 0;
		}

		var data = fcom.frmData(frm);
		if( append == 1 ){
			$(dv).prepend(fcom.getLoader());
		} else {
			$(dv).html(fcom.getLoader());
		}
		if(bpCategoryId){
			data +='&categoryId='+bpCategoryId;
		}
		if(keyword){
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
	};

	goToSearchPage = function(page){
		if(typeof page == undefined || page == null){
			page = 1;
		}
		currPage = page;
		var frm = document.frmBlogSearchPaging;
		$(frm.page).val(page);
		searchBlogs(frm);
	};

})();
