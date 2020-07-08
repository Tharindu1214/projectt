$(document).ready(function() {
    searchReason(document.frmReasonSearch);
});

(function() {
    var runningAjaxReq = false;
    var dv = '#listing';

    goToSearchPage = function(page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmReasonSearchPaging;
        $(frm.page).val(page);
        searchReason(frm);
    }

    reloadList = function() {

        searchReason();
    };

    searchReason = function(form) {
        var data = '';
        if (form) {
            data = fcom.frmData(form);
        }
        $(dv).html(fcom.getLoader());

        fcom.ajax(fcom.makeUrl('OrderReturnReasons', 'search'), data, function(res) {
            $(dv).html(res);
        });
    };
    addReasonForm = function(id) {

        $.facebox(function() {
            reasonForm(id);
        });
    };

    reasonForm = function(id) {
        fcom.displayProcessing();
        //$.facebox(function() {
        fcom.ajax(fcom.makeUrl('OrderReturnReasons', 'form', [id]), '', function(t) {
            //$.facebox(t,'faceboxWidth');
            fcom.updateFaceboxContent(t);
        });
        //});
    };
    editReasonFormNew = function(reasonId) {
        $.facebox(function() {
            editReasonForm(reasonId);
        });
    };

    editReasonForm = function(reasonId) {
        //	$.facebox(function() {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('OrderReturnReasons', 'form', [reasonId]), '', function(t) {
            //$.facebox(t,'faceboxWidth');
            fcom.updateFaceboxContent(t);

        });
        //	});
    };

    setupReason = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('OrderReturnReasons', 'setup'), data, function(t) {
            reloadList();
            if (t.langId > 0) {
                editReasonLangForm(t.reasonId, t.langId);
                return;
            }
            $(document).trigger('close.facebox');
        });
    }

    editReasonLangForm = function(reasonId, langId) {
        fcom.displayProcessing();
        //	$.facebox(function() {
        fcom.ajax(fcom.makeUrl('OrderReturnReasons', 'langForm', [reasonId, langId]), '', function(t) {
            //$.facebox(t,'faceboxWidth');
            fcom.updateFaceboxContent(t);
        });
        //	});
    };

    setupLangReason = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('OrderReturnReasons', 'langSetup'), data, function(t) {
            reloadList();
            if (t.langId > 0) {
                editReasonLangForm(t.reasonId, t.langId);
                return;
            }
            $(document).trigger('close.facebox');
        });
    };

    deleteRecord = function(id) {
        if (!confirm(langLbl.confirmDelete)) {
            return;
        }
        data = 'reasonId=' + id;
        fcom.updateWithAjax(fcom.makeUrl('OrderReturnReasons', 'deleteRecord'), data, function(res) {
            reloadList();
        });
    };

	deleteSelected = function(){
        if(!confirm(langLbl.confirmDelete)){
            return false;
        }
        $("#frmReturnReasonListing").submit();
    };

    clearSearch = function() {
        document.frmSearch.reset();
        searchReason(document.frmSearch);
    };
})();
