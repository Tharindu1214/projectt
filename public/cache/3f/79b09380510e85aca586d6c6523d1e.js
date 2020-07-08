$(document).ready(function() {
    searchAdminUsers();

    $(document).on('click', 'ul.linksvertical li a.redirect--js', function(event) {
        event.stopPropagation();
    });
});

(function() {
    var runningAjaxReq = false;
    var dv = '#listing';

    reloadList = function() {
        searchAdminUsers();
    };

    searchAdminUsers = function() {
        $(dv).html(fcom.getLoader());

        fcom.ajax(fcom.makeUrl('AdminUsers', 'search'), '', function(res) {
            $(dv).html(res);
        });
    };

    adminUserForm = function(id) {
        $.facebox(function() {
            addForm(id);
        });
    };

    addForm = function(id) {
        fcom.ajax(fcom.makeUrl('AdminUsers', 'form', [id]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    };

    editAdminUserForm = function(adminId) {
        $.facebox(function() {
            editForm(adminId);
        });
    };

    editForm = function(adminId) {
        fcom.ajax(fcom.makeUrl('AdminUsers', 'form', [adminId]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    };

    setupAdminUser = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('AdminUsers', 'setup'), data, function(t) {
            reloadList();
            $(document).trigger('close.facebox');
        });
    }

    changePasswordForm = function(id) {
        $.facebox(function() {
            fcom.ajax(fcom.makeUrl('AdminUsers', 'changePassword', [id]), '', function(t) {
                fcom.updateFaceboxContent(t);
            });
        });
    };

    setupChangePassword = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('AdminUsers', 'setupChangePassword'), data, function(t) {
            reloadList();
            $(document).trigger('close.facebox');
        });
    }

    toggleStatus = function(obj) {

        if (!confirm(langLbl.confirmUpdateStatus)) {
            return;
        }
        var adminId = parseInt(obj.id);
        if (adminId < 1) {
            fcom.displayErrorMessage(langLbl.invalidRequest);
            return false;
        }
        data = 'adminId=' + adminId;
        fcom.ajax(fcom.makeUrl('AdminUsers', 'changeStatus'), data, function(res) {
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
    };

	toggleBulkStatues = function(status){
        if(!confirm(langLbl.confirmUpdateStatus)){
            return false;
        }
        $("#frmAdmUsersListing input[name='status']").val(status);
        $("#frmAdmUsersListing").submit();
    };


    /* deleteRecord = function(id){
    	if(!confirm(langLbl.confirmDelete)){return;}
    	data='adminId='+id;
    	fcom.ajax(fcom.makeUrl('AdminUsers','deleteRecord'),data,function(res){
    		reloadList();
    	});
    }; */

    clearSearch = function() {
        document.frmSearch.reset();
        searchAdminUsers(document.frmSearch);
    };
})();
