$(document).ready(function() {
    searchBlogComments(document.frmSearch);
});
(function() {
    var currentPage = 1;
    var runningAjaxReq = false;

    goToSearchPage = function(page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmSearchPaging;
        $(frm.page).val(page);
        searchBlogComments(frm);
    }

    reloadList = function() {
        var frm = document.frmSearchPaging;
        searchBlogComments(frm);
    }

    view = function(id) {
        $.facebox(function() {
            fcom.ajax(fcom.makeUrl('BlogComments', 'view', [id]), '', function(t) {
                $.facebox(t, 'faceboxWidth');
            });
        });
    };

    updateStatus = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('BlogComments', 'updateStatus'), data, function(t) {
            reloadList();
            $(document).trigger('close.facebox');
        });
    };

    searchBlogComments = function(form) {
        var data = '';
        if (form) {
            data = fcom.frmData(form);
        }
        $("#listing").html('Loading....');
        fcom.ajax(fcom.makeUrl('BlogComments', 'search'), data, function(res) {
            $("#listing").html(res);
        });
    };

    deleteRecord = function(id) {
        if (!confirm(langLbl.confirmDelete)) {
            return;
        }
        data = 'id=' + id;
        fcom.updateWithAjax(fcom.makeUrl('BlogComments', 'deleteRecord'), data, function(res) {
            reloadList();
        });
    };

    clearSearch = function() {
        document.frmSearch.reset();
        searchBlogComments(document.frmSearch);
    };

	deleteSelected = function(){
        if(!confirm(langLbl.confirmDelete)){
            return false;
        }
        $("#frmBlogCmtsListing").attr("action",fcom.makeUrl('BlogComments','deleteSelected')).submit();
    };

})();
