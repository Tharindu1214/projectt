$(document).ready(function() {
    searchShippingCompanies(document.frmShippingMethodsSearch);
});

(function() {
    var runningAjaxReq = false;
    var dv = '#sCompanyListing';

    reloadList = function() {
        var frm = document.frmShippingMethodsSearch;
        searchShippingCompanies(frm);
    };

    searchShippingCompanies = function(form) {
        var data = '';
        if (form) {
            data = fcom.frmData(form);
        }
        $(dv).html(fcom.getLoader());

        fcom.ajax(fcom.makeUrl('ShippingCompanies', 'search'), data, function(res) {
            $(dv).html(res);
        });
    };

    editForm = function(sCompanyId) {
        $.facebox(function() {
            editCompanyForm(sCompanyId);
        });
    };

    editCompanyForm = function(sCompanyId) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('ShippingCompanies', 'form', [sCompanyId]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    };


    /*
    settingsForm = function (sCompanyId){
    	$.facebox(function() {
    		fcom.ajax(fcom.makeUrl('ShippingCompanies', 'settingsForm', [sCompanyId]), '', function(t) {
    			$.facebox(t,'faceboxWidth');
    		});
    	});
    }; */

    setup = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('ShippingCompanies', 'setup'), data, function(t) {
            reloadList();
            if (t.langId > 0) {
                editLangForm(t.sCompanyId, t.langId);
                return;
            }
            $(document).trigger('close.facebox');
        });
    }

    editLangForm = function(sCompanyId, langId) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('ShippingCompanies', 'langForm', [sCompanyId, langId]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    };

    setupLang = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('ShippingCompanies', 'langSetup'), data, function(t) {
            reloadList();
            if (t.langId > 0) {
                editLangForm(t.sCompanyId, t.langId);
                return;
            }
            $(document).trigger('close.facebox');
        });
    };

    settingsForm = function() {
        $.facebox(function() {
            fcom.ajax(fcom.makeUrl('ship-station-settings'), '', function(t) {
                $.facebox(t, 'faceboxWidth');
            });
        });
    };

    setupShippingSettings = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('ship-station-settings', 'setup'), data, function(t) {
            settingsForm(code);
        });
    };

    toggleStatus = function(obj) {
        if (!confirm(langLbl.confirmUpdateStatus)) {
            return;
        }
        var scompanyId = parseInt(obj.id);
        if (scompanyId < 1) {
            fcom.displayErrorMessage(langLbl.invalidRequest)
            return false;
        }
        data = 'scompanyId=' + scompanyId;
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('ShippingCompanies', 'changeStatus'), data, function(res) {
            var ans = $.parseJSON(res);
            if (ans.status == 1) {
                fcom.displaySuccessMessage(ans.msg);
                $(obj).toggleClass("active");
            } else {
                fcom.displayErrorMessage(ans.msg)
            }
        });
        $.systemMessage.close();
    };

	toggleBulkStatues = function(status){
        if(!confirm(langLbl.confirmUpdateStatus)){
            return false;
        }
        $("#frmShpCompListing input[name='status']").val(status);
        $("#frmShpCompListing").submit();
    };

})();
