$(document).ready(function() {
    searchBlogContributions(document.frmSearch);
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
        searchBlogContributions(frm);
    }

    reloadList = function() {
        var frm = document.frmSearchPaging;
        searchBlogContributions(frm);
    }

    view = function(id) {
        $.facebox(function() {
            fcom.ajax(fcom.makeUrl('BlogContributions', 'view', [id]), '', function(t) {
                $.facebox(t, 'faceboxWidth');
            });
        });
    };
    updateStatus = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('BlogContributions', 'updateStatus'), data, function(t) {
            reloadList();
            $(document).trigger('close.facebox');
        });
    };

    searchBlogContributions = function(form) {
        var data = '';
        if (form) {
            data = fcom.frmData(form);
        }
        $("#listing").html('Loading....');
        fcom.ajax(fcom.makeUrl('BlogContributions', 'search'), data, function(res) {
            $("#listing").html(res);
        });
    };

    deleteRecord = function(id) {
        if (!confirm(langLbl.confirmDelete)) {
            return;
        }
        data = 'id=' + id;
        fcom.updateWithAjax(fcom.makeUrl('BlogContributions', 'deleteRecord'), data, function(res) {
            reloadList();
        });
    };

	deleteSelected = function(){
        if(!confirm(langLbl.confirmDelete)){
            return false;
        }
        $("#frmBlogContributionListing").attr("action",fcom.makeUrl('BlogContributions','deleteSelected')).submit();
    };

    clearSearch = function() {
        document.frmSearch.reset();
        searchBlogContributions(document.frmSearch);
    };

})();
