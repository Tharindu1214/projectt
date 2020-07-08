$(document).ready(function() {
    searchPromotions(document.frmPromotionSearch);
});
$(document).on('change', '.language-js', function() {
    /* $(document).delegate('.language-js','change',function(){ */
    var langId = $(this).val();
    var promotion_id = $("form#frm_fat_id_frmPromotionMedia input[name='promotion_id']").val();
    var screen = $(".display-js").val();
    images(promotion_id, langId, screen);
});
$(document).on('change', '.display-js', function() {
    /* $(document).delegate('.display-js','change',function(){ */
    var screen = $(this).val();
    var promotion_id = $("form#frm_fat_id_frmPromotionMedia input[name='promotion_id']").val();
    var langId = $(".language-js").val();
    images(promotion_id, langId, screen);
});
$(document).on('blur', "input[name='promotion_budget']", function() {
    /* $(document).delegate("input[name='promotion_budget']",'blur',function(){ */
    var frm = document.frmPromotion;
    var data = fcom.frmData(frm);
    fcom.ajax(fcom.makeUrl('Promotions', 'checkValidPromotionBudget'), data, function(t) {
        var ans = $.parseJSON(t);
        if (ans.status == 0) {
            $.mbsmessage(ans.msg, false, 'alert alert--danger');
            return;
        }
        $.mbsmessage.close();
    });
});
$(document).on('change', "select[name='banner_blocation_id']", function() {
    /* $(document).delegate("select[name='banner_blocation_id']",'change',function(){ */
    $("input[name='promotion_budget']").trigger('blur');
});
(function() {
    var currentPage = 1;
    var runningAjaxReq = false;
    var dv = '#ppcListing';

    goToSearchPage = function(page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmPromotionSearchPaging;
        $(frm.page).val(page);
        searchPromotions(frm);
    };

    reloadList = function() {
        var frm = document.frmPromotionSearchPaging;
        searchPromotions(frm);
    };

    searchPromotions = function(form) {
        var data = '';
        if (form) {
            data = fcom.frmData(form);
        }

        $(dv).html(fcom.getLoader());

        fcom.ajax(fcom.makeUrl('promotions', 'search'), data, function(res) {
            $(dv).html(res);
        });
    };

    clearPromotionSearch = function() {
        document.frmPromotionSearch.reset();
        searchPromotions(document.frmPromotionSearch);
    };

    promotionForm = function(promotionId) {
        $.facebox(function() {
            addPromotionForm(promotionId);
        });
    };

    addPromotionForm = function(promotionId) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('Promotions', 'form', [promotionId]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    };

    setupPromotion = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Promotions', 'setupPromotion'), data, function(t) {
            reloadList();
            if (t.langId) {
                promotionLangForm(t.promotionId, t.langId);
                return false;
            }
            $(document).trigger('close.facebox');
        });
    };
    updateSellerRequestForm = function(requestId) {
        $.facebox(function() {
            updateSellerForm(requestId);
        });
    };

    updateSellerForm = function(requestId) {
        fcom.ajax(fcom.makeUrl('Users', 'updateSellerRequestForm', [requestId]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    };

    promotionLangForm = function(promotionId, langId) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('Promotions', 'promotionLangForm', [promotionId, langId]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    };

    promotionMediaForm = function(promotionId) {
        $.facebox(function() {
            fcom.ajax(fcom.makeUrl('Promotions', 'promotionMediaForm', [promotionId]), '', function(t) {
                fcom.updateFaceboxContent(t);
                images(promotionId, 0, $('.display-js').val());
            });
        });
    };

    images = function(promotion_id, lang_id, screen_id) {
        fcom.ajax(fcom.makeUrl('Promotions', 'images', [promotion_id, lang_id, screen_id]), '', function(t) {
            $('#image-listing-js').html(t);
            fcom.resetFaceboxHeight();
        });
    };

    addPromotionMedia = function(promotionId) {
        fcom.ajax(fcom.makeUrl('Promotions', 'promotionMediaForm', [promotionId]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    };

    setupPromotionLang = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Promotions', 'setupPromotionLang'), data, function(t) {
            reloadList();
            if (t.langId > 0) {
                promotionLangForm(t.promotionId, t.langId);
                return false;
            }
            $(document).trigger('close.facebox');
        });
    };

    removePromotionBanner = function(promotionId, bannerId, langId, screen) {
        if (!confirm(langLbl.confirmDelete)) {
            return;
        }
        data = 'promotionId=' + promotionId + '&bannerId=' + bannerId + '&langId=' + langId + '&screen=' + screen;
        fcom.updateWithAjax(fcom.makeUrl('Promotions', 'removePromotionBanner'), data, function(res) {
            images(promotionId, langId, screen);
        });
    };

    deletepromotionRecord = function(id) {
        if (!confirm(langLbl.confirmDelete)) {
            return;
        }
        data = 'id=' + id;
        fcom.updateWithAjax(fcom.makeUrl('Promotions', 'deletePromotionRecord'), data, function(res) {
            reloadList();
        });
    };

    updatePromotion = function(requestId) {
        $.facebox(function() {
            updatePromotionForm(requestId);
        });
    };

    updatePromotionForm = function(requestId) {
        fcom.ajax(fcom.makeUrl('Promotions', 'updatePromotion', [requestId]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    };

	deleteSelected = function(){
        if(!confirm(langLbl.confirmDelete)){
            return false;
        }
        $("#frmPromotionsListing").attr("action",fcom.makeUrl('Promotions','deleteSelected')).submit();
    };

})();

$(document).on('click', '.bannerFile-Js', function() {
    var node = this;
    $('#form-upload').remove();
    var promotionId = document.frmPromotionMedia.promotion_id.value;
    var langId = document.frmPromotionMedia.lang_id.value;
    var promotionType = document.frmPromotionMedia.promotion_type.value;
    var banner_screen = document.frmPromotionMedia.banner_screen.value;

    var frm = '<form enctype="multipart/form-data" id="form-upload" style="position:absolute; top:-100px;" >';
    frm = frm.concat('<input type="file" name="file" />');
    frm = frm.concat('<input type="hidden" name="promotion_id" value="' + promotionId + '"/>');
    frm = frm.concat('<input type="hidden" name="lang_id" value="' + langId + '"/>');
    frm = frm.concat('<input type="hidden" name="promotion_type" value="' + promotionType + '"/>');
    frm = frm.concat('<input type="hidden" name="banner_screen" value="' + banner_screen + '"/>');
    $('body').prepend(frm);
    $('#form-upload input[name=\'file\']').trigger('click');
    if (typeof timer != 'undefined') {
        clearInterval(timer);
    }
    timer = setInterval(function() {
        if ($('#form-upload input[name=\'file\']').val() != '') {
            clearInterval(timer);
            $val = $(node).val();
            $.ajax({
                url: fcom.makeUrl('Promotions', 'promotionUpload', [promotionId]),
                type: 'post',
                dataType: 'json',
                data: new FormData($('#form-upload')[0]),
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $(node).val('Loading');
                },
                complete: function() {
                    $(node).val($val);
                },
                success: function(ans) {
                    $('#form-upload').remove();
                    images(promotionId, langId, banner_screen);
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                }
            });
        }
    }, 500);
});
