$(document).ready(function() {
    searchPackages();
});
(function() {

    var runningAjaxReq = false;
    var dv = '#listing';

    reloadList = function() {

        searchPackages();
    };

    PackageForm = function(packageId) {
        $.facebox(function() {
            editPackageForm(packageId);
        });
    };

    editPackageForm = function(packageId) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('SellerPackages', 'form', [packageId]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    };

    submitPackageForm = function(frm, fn) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('SellerPackages', 'setup'), data, function(t) {
            reloadList();
            if (t.langId > 0) {
                editPackageLangForm(t.spackageId, t.langId);
                return;
            }
            $(document).trigger('close.facebox');
        });
    };


    editPackageLangForm = function(spackageId, langId) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('SellerPackages', 'langForm', [spackageId, langId]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    };

    setupLangPackage = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('SellerPackages', 'langSetup'), data, function(t) {
            reloadList();
            if (t.langId > 0) {
                editPackageLangForm(t.spackageId, t.langId);
                return;
            }
            $(document).trigger('close.facebox');
        });
    };

    searchPackages = function() {
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('SellerPackages', 'search'), '', function(res) {
            $(dv).html(res);
            $(".new-plan").addClass('hide');
        });
    };

    searchPlans = function(spackageId) {
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('SellerPackages', 'searchPlans'), 'spackageId=' + spackageId, function(t) {
            $("#packageDetail").html(t);
            $(".new-plan").removeClass('hide');

        });
    };

    planForm = function(spackageId, planId) {

        if (spackageId == '') return;
        $.facebox(function() {
            addPlanForm(spackageId, planId);
        });
    }

    addPlanForm = function(spackageId, planId) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('SellerPackages', 'planForm', [spackageId, planId]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    };

    submitPlanForm = function(frm, fn) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);

        fcom.updateWithAjax(fcom.makeUrl('SellerPackages', 'setupPlan'), data, function(t) {
            if (t.spackageId > 0) {
                searchPlans(t.spackageId);
                $(document).trigger('close.facebox');
                return;
            }
            reloadList();

            $(document).trigger('close.facebox');
        });
    };

    setPlanFields = function(spackageType) {

        if (spackageType == 1) {
            $(".trial_frequency").hide();
            $(".trial_interval").hide();
            $(".package_price").hide();
        } else {
            $(".trial_frequency").show();
            $(".trial_interval").show();
            $(".package_price").show();
        }
    };

    toggleStatus = function(obj) {
        if (!confirm(langLbl.confirmUpdateStatus)) {
            return;
        }
        var spackageId = parseInt(obj.id);
        if (spackageId < 1) {
            fcom.displayErrorMessage(langLbl.invalidRequest);
            return false;
        }
        data = 'spackageId=' + spackageId;
        fcom.ajax(fcom.makeUrl('SellerPackages', 'changeStatus'), data, function(res) {
            var ans = $.parseJSON(res);
            if (ans.status == 1) {
                fcom.displaySuccessMessage(ans.msg);
                $(obj).toggleClass("active");
            } else {
                fcom.displayErrorMessage(ans.msg);
            }
        });
    };

	toggleBulkStatues = function(status){
        if(!confirm(langLbl.confirmUpdateStatus)){
            return false;
        }
        $("#frmSellerPkgListing input[name='status']").val(status);
        $("#frmSellerPkgListing").submit();
    };

})();
