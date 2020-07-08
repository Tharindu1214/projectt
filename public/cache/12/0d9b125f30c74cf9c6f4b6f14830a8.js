(function() {
    addExportForm = function(actionType) {
        $.facebox(function() {
            //getExportForm(actionType);
            exportForm(actionType);

        });
    };
    exportForm = function(actionType) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('ImportExport', 'exportForm', [actionType]), '', function(t) {
            fcom.updateFaceboxContent(t, 'faceboxWidth');
        });
    }
    exportData = function(frm, actionType) {
        if (!$(frm).validate()) return;
        document.frmImportExport.action = fcom.makeUrl('ImportExport', 'exportData', [actionType]);
        document.frmImportExport.submit();
    };

    exportMediaForm = function(actionType) {
        //	$.facebox(function() {
        fcom.ajax(fcom.makeUrl('ImportExport', 'exportMediaForm', [actionType]), '', function(t) {
            fcom.updateFaceboxContent(t, 'faceboxWidth');
        });
        //});
    };

    exportMedia = function(frm, actionType) {
        if (!$(frm).validate()) return;
        document.frmImportExport.action = fcom.makeUrl('ImportExport', 'exportMedia', [actionType]);
        document.frmImportExport.submit();
    };

    addImportForm = function(actionType) {
        $.facebox(function() {
            // importForm(actionType);
            getInstructions(actionType);
        });
    };
    importForm = function(actionType) {
        fcom.ajax(fcom.makeUrl('ImportExport', 'importForm', [actionType]), '', function(t) {
            fcom.updateFaceboxContent(t, 'faceboxWidth');
        });

    }
    getInstructions = function(actionType) {
        fcom.ajax(fcom.makeUrl('ImportExport', 'importInstructions', [actionType]), '', function(t) {
            fcom.updateFaceboxContent(t, 'faceboxWidth');
        });

    }
    importMediaForm = function(actionType) {
        //$.facebox(function() {
        fcom.ajax(fcom.makeUrl('ImportExport', 'importMediaForm', [actionType]), '', function(t) {
            fcom.updateFaceboxContent(t, 'faceboxWidth');
        });
        //	});
    };

    importFile = function(method, actionType) {
        var data = new FormData();
        $inputs = $('#frmImportExport input[type=text],#frmImportExport select,#frmImportExport input[type=hidden]');
        $inputs.each(function() {
            data.append(this.name, $(this).val());
        });
        $.each($('#import_file')[0].files, function(i, file) {
            fcom.displayProcessing(langLbl.processing, ' ', true);
            $('#fileupload_div').html(fcom.getLoader());
            data.append('import_file', file);
            $.ajax({
                url: fcom.makeUrl('ImportExport', method, [actionType]),
                type: "POST",
                data: data,
                processData: false,
                contentType: false,
                success: function(t) {
                    try {
                        var ans = $.parseJSON(t);                        
                        if (ans.status == 1) {
                            reloadList();
                            $(document).trigger('close.facebox');
                            $(document).trigger('close.mbsmessage');
                            fcom.displaySuccessMessage(ans.msg);
                        } else {
                            $('#fileupload_div').html('');
                            $(document).trigger('close.mbsmessage');
                            fcom.displayErrorMessage(ans.msg);
                        }

                        if (typeof ans.CSVfileUrl !== 'undefined') {
                            location.href = ans.CSVfileUrl;
                        } else {
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        }
                    } catch (exc) {
                        $(document).trigger('close.mbsmessage');
                        fcom.displayErrorMessage(t);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert("Error Occured.");
                }
            });
        });
    };

    showHideExtraFld = function(type, BY_ID_RANGE, BY_BATCHES) {
        if (type == BY_ID_RANGE) {
            $(".range_fld").show();
            $(".batch_fld").hide();
        } else if (type == BY_BATCHES) {
            $(".range_fld").hide();
            $(".batch_fld").show();
        } else {
            $(".range_fld").hide();
            $(".batch_fld").hide();
        }
    };

})();
$(document).ready(function() {

    searchUsers(document.frmUserSearch);

    $(document).on('click', function() {
        $('.autoSuggest').empty();
    });

    $('input[name=\'keyword\']').autocomplete({
        'source': function(request, response) {
            $.ajax({
                url: fcom.makeUrl('Users', 'autoCompleteJson'),
                data: {
                    keyword: request,
                    fIsAjax: 1
                },
                dataType: 'json',
                type: 'post',
                success: function(json) {
                    response($.map(json, function(item) {
                        return {
                            label: item['name'] + '(' + item['username'] + ')',
                            value: item['id'],
                            name: item['username']
                        };
                    }));
                },
            });
        },
        'select': function(item) {
            $("input[name='user_id']").val(item['value']);
            $("input[name='keyword']").val(item['name']);
        }
    });

    $('input[name=\'keyword\']').keyup(function() {
        $('input[name=\'user_id\']').val('');
    });

    //redirect user to login page
    $(document).on('click', 'ul.linksvertical li a.redirect--js', function(event) {
        event.stopPropagation();
    });

});

(function() {
    var currentPage = 1;
    var transactionUserId = 0;
    var rewardUserId = 0;

    goToSearchPage = function(page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmUserSearchPaging;
        $(frm.page).val(page);
        searchUsers(frm);
    };

    searchUsers = function(form, page) {
        if (!page) {
            page = currentPage;
        }
        currentPage = page;
        /*[ this block should be before dv.html('... anything here.....') otherwise it will through exception in ie due to form being removed from div 'dv' while putting html*/
        var data = '';
        if (form) {
            data = fcom.frmData(form);
        }
        /*]*/

        $("#userListing").html(fcom.getLoader());

        fcom.ajax(fcom.makeUrl('Users', 'search'), data, function(res) {
            $("#userListing").html(res);
        });
    };

    reloadUserList = function() {
        searchUsers(document.frmUserSearchPaging, currentPage);
    };

    fillSuggetion = function(v) {
        $('#keyword').val(v);
        $('.autoSuggest').hide();
    };

    addUserForm = function(id) {
        var frm = document.frmUserSearchPaging;
        $.facebox(function() {
            userForm(id);
        });
    };

    userForm = function(id) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('Users', 'form', [id]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    };

    addBankInfoForm = function(id) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('Users', 'bankInfoForm', [id]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });

    };


    setupBankInfo = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Users', 'setupBankInfo'), data, function(t) {
            if (t.userId > 0) {
                addUserAddress(t.userId);
            }
        });
    };

    userAddresses = function(id) {
        $.facebox(function() {
            addUserAddress(id);
        });
    };

    addUserAddress = function(id) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('Users', 'addresses', [id]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    };

    addAddress = function(userId, id) {
        $.facebox(function() {
            addOneAddress(userId, id)
        });
    };

    addOneAddress = function(userId, id) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('Users', 'addressForm', [userId, id]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    };


    setupAddress = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Users', 'setupAddress'), data, function(t) {
            if (t.userId > 0) {
                addUserAddress(t.userId);
            }
        });
    };

    deleteAddress = function(userId, id) {
        if (!confirm(langLbl.confirmDelete)) {
            return;
        }
        var data = 'user_id=' + userId + '&id=' + id;
        fcom.updateWithAjax(fcom.makeUrl('Users', 'deleteAddress'), data, function(t) {
            if (t.userId > 0) {
                addUserAddress(t.userId);
            }
        });
    };

    deleteUser = function(userId) {
        if (!confirm(langLbl.confirmDelete)) {
            return;
        }
        var data = 'user_id=' + userId;
        fcom.updateWithAjax(fcom.makeUrl('Users', 'deleteAccount'), data, function(t) {
            reloadUserList();
        });
    };

    transactions = function(userId) {
        transactionUserId = userId;
        $.facebox(function() {
            addTransaction(userId);
        });
    };


    addTransaction = function(userId) {
        //fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('Users', 'transaction', [userId]), '', function(t) {
            $('#facebox').height($(window).height() - 46).css('overflow-y', 'auto');
            fcom.updateFaceboxContent(t);
        });
    };

    goToTransactionPage = function(page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmTransactionSearchPaging;
        $(frm.page).val(page);
        data = fcom.frmData(frm);
    };

    updateTransaction = function(data) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('Users', 'transaction', [transactionUserId]), data, function(t) {
            fcom.updateFaceboxContent(t);
        });
    };


    addUserTransaction = function(userId) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('Users', 'addUserTransaction', [userId]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    };

    setupUserTransaction = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Users', 'setupUserTransaction'), data, function(t) {
            if (t.userId > 0) {
                addTransaction(t.userId);
            }
        });
    };

    addUserLangForm = function(userId, langId) {
        $.facebox(function() {
            addLangForm(userId, langId);
        });
    };

    addLangForm = function(userId, langId) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('Users', 'langForm', [userId, langId]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    };

    rewards = function(userId) {
        rewardUserId = userId;
        $.facebox(function() {
            addReward(userId);
        });
    };

    addReward = function(userId) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('Users', 'rewards', [userId]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    };

    goToRewardPage = function(page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmRewardSearchPaging;
        $(frm.page).val(page);
        data = fcom.frmData(frm);
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('Users', 'rewards', [rewardUserId]), data, function(t) {
            fcom.updateFaceboxContent(t);
        });
    };

    addUserRewardPoints = function(userId) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('Users', 'addUserRewardPoints', [userId]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    };

    setupUserRewardPoints = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Users', 'setupUserRewardPoints'), data, function(t) {
            if (t.userId > 0) {
                addReward(t.userId);
            }
        });
    };

    changePasswordForm = function(id) {
        var frm = document.frmUserSearchPaging;
        $.facebox(function() {
            changeUserPassword(id);
        });
    };


    changeUserPassword = function(id) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('Users', 'changePasswordForm', [id]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    };

    updatePassword = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.displayProcessing();
        fcom.updateWithAjax(fcom.makeUrl('Users', 'updatePassword'), data, function(t) {
            $(document).trigger('close.facebox');
        });
        $.systemMessage.close();
    };

    setupUsers = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Users', 'setup'), data, function(t) {
            if (t.userId > 0) {
                addBankInfoForm(t.userId);
                return false;
            }
            $(document).trigger('close.facebox');
        });
    };

    addNewUsers = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.displayProcessing();
        fcom.updateWithAjax(fcom.makeUrl('Users', 'addNewUser'), data, function(t) {
            reloadUserList();
            $(document).trigger('close.facebox');
        });
        $.systemMessage.close();
    };

    verifyUser = function(id, v) {
        if (!confirm(langLbl.confirmUpdateStatus)) {
            return;
        }
        fcom.displayProcessing();
        fcom.updateWithAjax(fcom.makeUrl('users', 'verify'), {
            userId: id,
            v: v
        }, function(t) {
            reloadUserList();
        });
        $.systemMessage.close();
    };

    toggleStatus = function(obj) {
        if (!confirm(langLbl.confirmUpdateStatus)) {
            return;
        }
        var userId = parseInt(obj.id);
        if (userId < 1) {
            fcom.displayErrorMessage(langLbl.invalidRequest);
            return false;
        }
        data = 'userId=' + userId;
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('users', 'changeStatus'), data, function(res) {
            var ans = $.parseJSON(res);
            if (ans.status == 1) {
                $(obj).toggleClass("active");
                fcom.displaySuccessMessage(ans.msg);
            } else {
                fcom.displayErrorMessage(ans.msg);
            }
        });
        $.systemMessage.close();
    };

    clearUserSearch = function() {
        document.frmUserSearch.reset();
        document.frmUserSearch.user_id.value = '';
        searchUsers(document.frmUserSearch);
    };

    getCountryStates = function(countryId, stateId, dv) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('Users', 'getStates', [countryId, stateId]), '', function(res) {
            $(dv).empty();
            $(dv).append(res);
        });
        $.systemMessage.close();
    };

    sendMailForm = function(id) {
        $.facebox(function() {
            sendMailToUser(id);
        });
    };

    sendMailToUser = function(id) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('Users', 'sendMailForm', [id]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    };

    sendMail = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.displayProcessing();
        fcom.updateWithAjax(fcom.makeUrl('Users', 'sendMail'), data, function(t) {
            $(document).trigger('close.facebox');
        });
        $.systemMessage.close();
    };

    deletedUser = function() {
        document.location.href = fcom.makeUrl('deletedUsers');
    };

	toggleBulkStatues = function(status){
        if(!confirm(langLbl.confirmUpdateStatus)){
            return false;
        }
        $("#frmUsersListing input[name='status']").val(status);
        $("#frmUsersListing").submit();
    };

    deleteSelected = function(){
        if(!confirm(langLbl.confirmDelete)){
            return false;
        }
        $("#frmUsersListing").attr("action",fcom.makeUrl('Users','deleteSelected')).submit();
    };

})();
