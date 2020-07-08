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
