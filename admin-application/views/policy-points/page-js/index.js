$(document).ready(function() {
    searchPolicyPoint(document.frmPolicyPointSearch);
});

(function() {
    var runningAjaxReq = false;
    var dv = '#listing';

    goToSearchPage = function(page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmPolicyPointSearchPaging;
        $(frm.page).val(page);
        searchPolicyPoint(frm);
    }

    reloadList = function() {

        searchPolicyPoint();
    };

    searchPolicyPoint = function(form) {
        var data = '';
        if (form) {
            data = fcom.frmData(form);
        }
        $(dv).html(fcom.getLoader());

        fcom.ajax(fcom.makeUrl('PolicyPoints', 'search'), data, function(res) {
            $(dv).html(res);
        });
    };

    addPolicyPointForm = function(id) {

        $.facebox(function() {
            policyPointForm(id)
        });
    };

    policyPointForm = function(id) {
        fcom.displayProcessing();

        fcom.ajax(fcom.makeUrl('PolicyPoints', 'form', [id]), '', function(t) {
            //$.facebox(t,'faceboxWidth');
            fcom.updateFaceboxContent(t);
        });
    };
    editPolicyPointFormNew = function(ppointId) {
        $.facebox(function() {
            editPolicyPointForm(ppointId);
        });
    };

    editPolicyPointForm = function(ppointId) {
        fcom.displayProcessing();
        //$.facebox(function() {
        fcom.ajax(fcom.makeUrl('PolicyPoints', 'form', [ppointId]), '', function(t) {
            //$.facebox(t,'faceboxWidth');
            fcom.updateFaceboxContent(t);
        });
        //});
    };

    setupPolicyPoint = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('PolicyPoints', 'setup'), data, function(t) {
            reloadList();
            if (t.langId > 0) {
                editPolicyPointLangForm(t.ppointId, t.langId);
                return;
            }
            $(document).trigger('close.facebox');
        });
    }

    editPolicyPointLangForm = function(ppointId, langId) {
        fcom.displayProcessing();
        //$.facebox(function() {
        fcom.ajax(fcom.makeUrl('PolicyPoints', 'langForm', [ppointId, langId]), '', function(t) {
            //$.facebox(t,'faceboxWidth');
            fcom.updateFaceboxContent(t);
        });
        //});
    };

    setupLangPolicyPoint = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('PolicyPoints', 'langSetup'), data, function(t) {
            reloadList();
            if (t.langId > 0) {
                editPolicyPointLangForm(t.ppointId, t.langId);
                return;
            }
            $(document).trigger('close.facebox');
        });
    };

    deleteRecord = function(id) {
        if (!confirm(langLbl.confirmDelete)) {
            return;
        }
        data = 'ppointId=' + id;
        fcom.updateWithAjax(fcom.makeUrl('PolicyPoints', 'deleteRecord'), data, function(res) {
            reloadList();
        });
    };

    toggleStatus = function(e, obj, canEdit) {
        if (canEdit == 0) {
            e.preventDefault();
            return;
        }
        if (!confirm(langLbl.confirmUpdateStatus)) {
            e.preventDefault();
            return;
        }
        var ppointId = parseInt(obj.value);
        if (ppointId < 1) {
            fcom.displayErrorMessage(langLbl.invalidRequest);
            //$.mbsmessage(langLbl.invalidRequest,true,'alert--danger');
            return false;
        }
        data = 'ppointId=' + ppointId;
        fcom.ajax(fcom.makeUrl('PolicyPoints', 'changeStatus'), data, function(res) {
            var ans = $.parseJSON(res);
            if (ans.status == 1) {
                fcom.displaySuccessMessage(ans.msg);
                //$.mbsmessage(ans.msg,true,'alert--success');
                $(obj).toggleClass("active");
            } else {
                fcom.displayErrorMessage(ans.msg);
                //$.mbsmessage(ans.msg,true,'alert--danger');
            }
        });
    };

    clearSearch = function() {
        document.frmSearch.reset();
        searchPolicyPoint(document.frmSearch);
    };
	toggleBulkStatues = function(status){
        if(!confirm(langLbl.confirmUpdateStatus)){
            return false;
        }
        $("#frmPolicyPointListing input[name='status']").val(status);
        $("#frmPolicyPointListing").submit();
    };

    deleteSelected = function(){
        if(!confirm(langLbl.confirmDelete)){
            return false;
        }
        $("#frmPolicyPointListing").attr("action",fcom.makeUrl('PolicyPoints','deleteSelected')).submit();
    };
})();
