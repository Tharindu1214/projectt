$(document).ready(function() {
    searchWords(document.frmWordSearch);
});

(function() {
    var currentPage = 1;
    var runningAjaxReq = false;
    var dv = '#listing';

    goToSearchPage = function(page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmWordSrchPaging;
        $(frm.page).val(page);
        searchWords(frm);
    };

    reloadList = function() {
        var frm = document.frmWordSrchPaging;
        searchWords(frm);
    };

    searchWords = function(form) {
        $(dv).html(fcom.getLoader());
        var data = '';
        if (form) {
            data = fcom.frmData(form);
        }
        fcom.ajax(fcom.makeUrl('AbusiveWords', 'search'), data, function(res) {
            $(dv).html(res);
        });
    };

    abusiveKeywordForm = function(abusiveId) {
        $.facebox(function() {
            addAbusiveKeywordForm(abusiveId);
        });
    };

    addAbusiveKeywordForm = function(abusiveId) {
        fcom.ajax(fcom.makeUrl('AbusiveWords', 'form', [abusiveId]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    };

    setupAbusiveWords = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('AbusiveWords', 'setup'), data, function(t) {
            reloadList();
            $(document).trigger('close.facebox');
        });
    };

    removeKeyword = function(id) {
        if (!confirm("Do you really want to delete this record?")) {
            return;
        }
        data = 'id=' + id;
        fcom.updateWithAjax(fcom.makeUrl('AbusiveWords', 'deleteRecord'), data, function(res) {
            reloadList();
        });
    };

    clearSearch = function() {
        document.frmWordSearch.reset();
        searchWords(document.frmWordSearch);
    };

	deleteSelected = function(){
        if(!confirm(langLbl.confirmDelete)){
            return false;
        }
        $("#frmAbusiveWordsListing").attr("action",fcom.makeUrl('AbusiveWords','deleteSelected')).submit();
    };
})()
