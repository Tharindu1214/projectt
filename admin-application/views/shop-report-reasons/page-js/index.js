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

        fcom.ajax(fcom.makeUrl('ShopReportReasons', 'search'), data, function(res) {
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
        fcom.ajax(fcom.makeUrl('ShopReportReasons', 'form', [id]), '', function(t) {
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
        fcom.displayProcessing();
        //$.facebox(function() {
        fcom.ajax(fcom.makeUrl('ShopReportReasons', 'form', [reasonId]), '', function(t) {
            //$.facebox(t,'faceboxWidth');
            fcom.updateFaceboxContent(t);
        });
        //});
    };

    setupReason = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('ShopReportReasons', 'setup'), data, function(t) {
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
        //$.facebox(function() {
        fcom.ajax(fcom.makeUrl('ShopReportReasons', 'langForm', [reasonId, langId]), '', function(t) {
            ///$.facebox(t,'faceboxWidth');
            fcom.updateFaceboxContent(t);
        });
        //});
    };

    setupLangReason = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('ShopReportReasons', 'langSetup'), data, function(t) {
            reloadList();
            if (t.langId > 0) {
                editReasonLangForm(t.reasonId, t.langId);
                $.mbsmessage(t.ans, true, 'alert--success');
                return;
            }
            $(document).trigger('close.facebox');
        });
    };

    toggleStatus = function(obj) {

        if (!confirm(langLbl.confirmUpdateStatus)) {
            return;
        }
        var reasonId = parseInt(obj.id);
        if (reasonId < 1) {
            fcom.displayErrorMessage(langLbl.invalidRequest);
            //$.mbsmessage(langLbl.invalidRequest,true,'alert--danger');
            return false;
        }
        data = 'reasonId=' + reasonId;
        fcom.ajax(fcom.makeUrl('ShopReportReasons', 'changeStatus'), data, function(res) {
            var ans = $.parseJSON(res);
            if (ans.status == 1) {
                $(obj).toggleClass("active");
                setTimeout(function() {
                    reloadList();
                }, 1000);
                fcom.displaySuccessMessage(ans.msg);
                //$.mbsmessage(ans.msg,true,'alert--success');
            } else {
                fcom.displayErrorMessage(ans.msg);
                //$.mbsmessage(ans.msg,true,'alert--danger');
            }
        });
    };

    deleteRecord = function(id) {
        if (!confirm(langLbl.confirmDelete)) {
            return;
        }
        data = 'reasonId=' + id;
        fcom.updateWithAjax(fcom.makeUrl('ShopReportReasons', 'deleteRecord'), data, function(res) {
            reloadList();
        });
    };

	deleteSelected = function(){
        if(!confirm(langLbl.confirmDelete)){
            return false;
        }
        $("#frmShpRptRsnListing").submit();
    };


    clearSearch = function() {
        document.frmSearch.reset();
        searchReason(document.frmSearch);
    };
})();
