$(document).ready(function() {
    searchShippingMethods(document.frmShippingMethodsSearch);
});

(function() {
    var runningAjaxReq = false;
    var dv = '#sMethodListing';

    reloadList = function() {
        var frm = document.frmShippingMethodsSearch;
        searchShippingMethods(frm);
    };

    searchShippingMethods = function(form) {
        var data = '';
        if (form) {
            data = fcom.frmData(form);
        }
        $(dv).html(fcom.getLoader());

        fcom.ajax(fcom.makeUrl('ShippingMethods', 'search'), data, function(res) {
            $(dv).html(res);
        });
    };

    editForm = function(sMethodId) {
        $.facebox(function() {
            editGeneralForm(sMethodId);
        });
    };

    editGeneralForm = function(sMethodId) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('ShippingMethods', 'form', [sMethodId]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    }

    setup = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('ShippingMethods', 'setup'), data, function(t) {
            reloadList();
            if (t.langId > 0) {
                editLangForm(t.sMethodId, t.langId);
                return;
            }
            $(document).trigger('close.facebox');
        });
    }

    editLangForm = function(sMethodId, langId) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('ShippingMethods', 'langForm', [sMethodId, langId]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    };


    setupLang = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('ShippingMethods', 'langSetup'), data, function(t) {
            reloadList();
            if (t.langId > 0) {
                editLangForm(t.sMethodId, t.langId);
                return;
            }
            $(document).trigger('close.facebox');
        });
    };

    settingsForm = function() {
        $.facebox(function() {
            fcom.ajax(fcom.makeUrl('ShipStationSettings', ''), '', function(t) {
                fcom.updateFaceboxContent(t);
            });
        });
    };

    setupShippingSettings = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('ShipStationSettings', 'setup'), data, function(t) {
            $(document).trigger('close.facebox');
        });
    };

    toggleStatus = function(obj) {
        if (!confirm(langLbl.confirmUpdateStatus)) {
            return;
        }
        var shippingapiId = parseInt(obj.id);
        if (shippingapiId < 1) {
            fcom.displayErrorMessage(langLbl.invalidRequest)
            return false;
        }
        data = 'shippingapiId=' + shippingapiId;
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('ShippingMethods', 'changeStatus'), data, function(res) {
            var ans = $.parseJSON(res);
            if (ans.status == 1) {
                fcom.displaySuccessMessage(ans.msg);
                $(obj).toggleClass("active");
                setTimeout(function() {
                    reloadList();
                }, 1000);
            } else {
                fcom.displayErrorMessage(ans.msg);
            }
        });
        $.systemMessage.close();
    };

	toggleBulkStatues = function(status){
        if(!confirm(langLbl.confirmUpdateStatus)){
            return false;
        }
        $("#frmShpApiListing input[name='status']").val(status);
        $("#frmShpApiListing").submit();
    };

})();
