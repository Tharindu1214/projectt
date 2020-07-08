$(document).ready(function() {
    shopForm();
});
$(document).on('change', '.logo-language-js', function() {
    var lang_id = $(this).val();
    shopImages('logo', 0, lang_id);
});
$(document).on('change', '.banner-language-js', function() {
    var lang_id = $(this).val();
    var slide_screen = $(".prefDimensions-js").val();
    shopImages('banner', slide_screen, lang_id);
});
$(document).on('change','.prefDimensions-js',function(){
	var slide_screen = $(this).val();
	var lang_id = $(".banner-language-js").val();
	shopImages('banner', slide_screen, lang_id);
});
$(document).on('change', '.bg-language-js', function() {
    var lang_id = $(this).val();
    shopImages('bg', 0, lang_id);
});
$(document).on('change', '.collection-language-js', function() {
    var lang_id = $(this).val();
    var scollection_id = document.frmCollectionMedia.scollection_id.value;
    shopCollectionImages(scollection_id, lang_id);
});
(function() {
    var runningAjaxReq = false;
    var dv = '#shopFormBlock';
    var dvt = '#shopFormChildBlock';

    checkRunningAjax = function() {
        if (runningAjaxReq == true) {
            //console.log(runningAjaxMsg);
            return;
        }
        runningAjaxReq = true;
    };

    goToCategoryBannerSrchPage = function(page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmCategoryBannerSrchPaging;
        $(frm.page).val(page);
        searchCategoryBanners(frm);
    };

    categoryBanners = function() {
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Seller', 'searchCategoryBanners'), '', function(t) {
            $(dv).html(t);
        });
    };

    addCategoryBanner = function(prodCatId) {
        $.facebox(function() {
            fcom.ajax(fcom.makeUrl('Seller', 'addCategoryBanner', [prodCatId]), '', function(t) {
                $.facebox(t, 'faceboxWidth');
            });
        });
    };

    /* categoryBannerLangForm = function( prodCatId, langId ){
    	$.facebox(function() {
    		fcom.ajax(fcom.makeUrl('Seller', 'categoryBannerLangForm',[prodCatId, langId]), '', function(t) {
    			$.facebox(t,'faceboxWidth');
    		});
    	});
    } */

    searchCategoryBanners = function(frm) {
        /*[ this block should be written before overriding html of 'form's parent div/element, otherwise it will through exception in ie due to form being removed from div */
        var data = fcom.frmData(frm);
        /*]*/
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Seller', 'searchCategoryBanners'), data, function(res) {
            $(dv).html(res);
        });
    };

    reloadCategoryBannerList = function() {
        searchCategoryBanners(document.frmCategoryBannerSrchPaging);
    };

    removeCategoryBanner = function(prodCatId, lang_id) {
        var agree = confirm(langLbl.confirmRemove);
        if (!agree) {
            return false;
        }
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'removeCategoryBanner', [prodCatId, lang_id]), '', function(t) {
            reloadCategoryBannerList();
            addCategoryBanner(prodCatId);
        });
    };

    shopForm = function() {
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Seller', 'shopForm'), '', function(t) {
            $(dv).html(t);
            jscolor.installByClassName("jscolor");
        });
    };

    setupShop = function(frm) {
        if (!$(frm).validate()) return;
        checkRunningAjax();
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'setupShop'), data, function(t) {
            runningAjaxReq = false;
            if (t.langId > 0) {
                shopLangForm(t.shopId, t.langId);
                return;
            }

            shopForm();
            return;
        });
    };

    shopLangForm = function(shopId, langId) {
        $(dv).html(fcom.getLoader());

        fcom.ajax(fcom.makeUrl('Seller', 'shopLangForm', [shopId, langId]), '', function(t) {
            $(dv).html(t);
            fcom.setEditorLayout(langId);
            var frm = $(dv + ' form')[0];
            var validator = $(frm).validation({
                errordisplay: 3
            });
            $(frm).submit(function(e) {
                e.preventDefault();
                if (validator.validate() == false) {
                    return;
                }
                var data = fcom.frmData(frm);
                fcom.updateWithAjax(fcom.makeUrl('Seller', 'setupShopLang'), data, function(t) {
                    runningAjaxReq = false;
                    $.mbsmessage.close();
                    if (t.langId > 0 && t.shopId > 0) {
                        shopLangForm(t.shopId, t.langId);
                        return;
                    }
                    returnAddressForm();
                });
            });
        });
    };

    setupShopLang = function(frm) {
        if (!$(frm).validate()) return;
        checkRunningAjax();
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'setupShopLang'), data, function(t) {
            runningAjaxReq = false;
            $.mbsmessage.close();
            if (t.langId > 0 && t.shopId > 0) {
                shopLangForm(t.shopId, t.langId);
                return;
            }
            shopForm();
        });
    };

    shopMediaForm = function(el) {
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Seller', 'shopMediaForm'), '', function(t) {
            $(dv).html(t);
            $(el).parent().siblings().removeClass('is-active');
            $(el).parent().addClass('is-active');
            shopImages('logo');
            shopImages('banner',1);
            shopImages('bg');
        });
    };

    shopImages = function(imageType, slide_screen, lang_id) {
        fcom.ajax(fcom.makeUrl('Seller', 'shopImages', [imageType, lang_id, slide_screen]), '', function(t) {
            if (imageType == 'logo') {
                $('#logo-image-listing').html(t);
            } else if (imageType == 'banner') {
                $('#banner-image-listing').html(t);
            } else {
                $('#bg-image-listing').html(t);
            }
        });
    };

    shopTemplates = function(el) {
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Seller', 'shopTemplate'), '', function(t) {
            $(dv).html(t);
            $(el).parent().siblings().removeClass('is-active');
            $(el).parent().addClass('is-active');
        });
    };
    themeColor = function(el) {
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Seller', 'shopThemeColor'), '', function(t) {
            $(dv).html(t);
            jscolor.installByClassName("jscolor");

        });
    };

    setTemplate = function(ltemplateId) {
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'setTemplate', [ltemplateId]), '', function(t) {
            shopTemplates();
        });
    };

    /* getCountryStates = function(countryId,stateId,dv){
    	fcom.ajax(fcom.makeUrl('Seller','getStates',[countryId,stateId]),'',function(res){
    		$(dv).empty();
    		$(dv).append(res);
    	});
    }; */

    setUpThemeColor = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('seller', 'setupThemeColor'), data, function(t) {
            $.mbsmessage.close();
        });
    };

    removeShopImage = function(BannerId, langId, imageType, slide_screen) {
        var agree = confirm(langLbl.confirmRemove);
        if (!agree) {
            return false;
        }
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'removeShopImage', [BannerId, langId, imageType, slide_screen]), '', function(t) {
            shopImages(imageType, slide_screen, langId);
        });
    };

    deleteShopCollection = function(scollection_id) {
        if (!confirm(langLbl.confirmDelete)) {
            return;
        }
        fcom.ajax(fcom.makeUrl('Seller', 'deleteShopCollection', [scollection_id]), '', function(res) {
            searchShopCollections();
        });
    };

    shopCollections = function(el) {
        $(dv).html(fcom.getLoader());
        // console.log($(el).parent());
        fcom.ajax(fcom.makeUrl('Seller', 'shopCollections'), '', function(t) {
            $(dv).html(t);
            $(el).parent().siblings().removeClass('is-active');
            $(el).parent().addClass('is-active');
            searchShopCollections();
        });
    };

    searchShopCollections = function(el) {
        $(dvt).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Seller', 'searchShopCollections'), '', function(t) {
            $(dvt).html(t);
            $(el).parent().siblings().removeClass('is-active');
            $(el).parent().addClass('is-active');
        });
    };

    shopCollectionProducts = function(el) {
        $(dv).html(fcom.getLoader());
        // console.log($(el).parent());
        fcom.ajax(fcom.makeUrl('Seller', 'shopCollection'), '', function(t) {
            $(dv).html(t);
            $(el).parent().siblings().removeClass('is-active');
            $(el).parent().addClass('is-active');
            getShopCollectionGeneralForm();
        });
    };

    getShopCollectionGeneralForm = function(scollection_id) {
        fcom.ajax(fcom.makeUrl('Seller', 'shopCollectionGeneralForm', [scollection_id]), '', function(t) {
            $(dvt).html(t);
        });
    };

    setupShopCollection = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('seller', 'setupShopCollection'), data, function(t) {
            $.mbsmessage.close();
            if (t.langId > 0) {
                editShopCollectionLangForm(t.collection_id, t.langId);
                return;
            }

        });
    };

    setupShopCollectionlangForm = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('seller', 'setupShopCollectionLang'), data, function(t) {
            $.mbsmessage.close();
            if (t.langId > 0) {
                editShopCollectionLangForm(t.scollection_id, t.langId);
            }
            if (t.openCollectionLinkForm) {
                sellerCollectionProducts(t.scollection_id);
                return;
            }
        });

    };

    editShopCollectionLangForm = function(scollection_id, langId) {
        if (typeof(scollection_id) == "undefined" || scollection_id < 0) {
            return false;
        }
        if (typeof(langId) == "undefined" || langId < 0) {
            return false;
        }
        fcom.ajax(fcom.makeUrl('seller', 'shopCollectionLangForm', [scollection_id, langId]), '', function(t) {
            $(dvt).html(t);
        });
    };

    sellerCollectionProducts = function(scollection_id) {
        $(dvt).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Seller', 'sellerCollectionProductLinkFrm', [scollection_id]), '', function(t) {
            $(dvt).html(t);
            bindAutoComplete();
        });
    };

    setUpSellerCollectionProductLinks = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'setUpSellerCollectionProductLinks'), data, function(t) {
            $.mbsmessage.close();
        });
    };

    resetDefaultCurrentTemplate = function() {
        var agree = confirm(langLbl.confirmReset);
        if (!agree) {
            return false;
        }
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'resetDefaultThemeColor'), '', function(t) {
            $.mbsmessage.close();
            themeColor();
        });
    };

    returnAddressForm = function() {
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Seller', 'returnAddressForm'), '', function(t) {
            $(dv).html(t);
        });
    };

    setReturnAddress = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'setReturnAddress'), data, function(t) {
            returnAddressLangForm(t.langId);
        });
    };

    returnAddressLangForm = function(langId) {
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Seller', 'returnAddressLangForm', [langId]), '', function(t) {
            $(dv).html(t);
        });
    };

    setReturnAddressLang = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'setReturnAddressLang'), data, function(t) {
            if (t.langId) {
                returnAddressLangForm(t.langId);
            } else {
                returnAddressForm();
            }
        });
    };

    collectionMediaForm = function(el, scollection_id) {
        $(dvt).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Seller', 'shopCollectionMediaForm', [scollection_id]), '', function(t) {
            $(dvt).html(t);
            $(el).parent().siblings().removeClass('is-active');
            $(el).parent().addClass('is-active');
            shopCollectionImages(scollection_id);
        });
    };

    shopCollectionImages = function(scollection_id, lang_id) {
        fcom.ajax(fcom.makeUrl('Seller', 'shopCollectionImages', [scollection_id, lang_id]), '', function(t) {
            $('#imageListing').html(t);
        });
    };

    removeCollectionImage = function(scollection_id, langId) {
        var agree = confirm(langLbl.confirmRemove);
        if (!agree) {
            return false;
        }
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'removeCollectionImage', [scollection_id, langId]), '', function(t) {
            shopCollectionImages(scollection_id, langId);
        });
    };

    toggleShopCollectionStatus = function(e, obj) {
        if (!confirm(langLbl.confirmUpdateStatus)) {
            e.preventDefault();
            return;
        }
        var scollection_id = parseInt(obj.value);
        if (scollection_id < 1) {
            return false;
        }
        data = 'scollection_id=' + scollection_id;
        fcom.ajax(fcom.makeUrl('Seller', 'changeShopCollectionStatus'), data, function(res) {
            var ans = $.parseJSON(res);
            if (ans.status == 1) {
                $.mbsmessage(ans.msg, true, 'alert--success');
            } else {
                $.mbsmessage(ans.msg, true, 'alert--danger');
            }
        });
    };

    toggleBulkCollectionStatues = function(status) {
        if (!confirm(langLbl.confirmUpdateStatus)) {
            return false;
        }
        $("#frmCollectionsListing input[name='collection_status']").val(status);
        $("#frmCollectionsListing").submit();
    };

    deleteSelectedCollection = function() {
        if (!confirm(langLbl.confirmDelete)) {
            return false;
        }
        $("#frmCollectionsListing").attr("action", fcom.makeUrl('Seller', 'deleteSelectedCollections')).submit();
    };

})();

function bindAutoComplete() {
    $("input[name='scp_selprod_id']").autocomplete({

        'source': function(request, response) {
            $.ajax({
                url: fcom.makeUrl('seller', 'autoCompleteProducts'),
                data: {
                    keyword: request,
                    fIsAjax: 1
                },
                dataType: 'json',
                type: 'post',
                success: function(json) {
                    response($.map(json, function(item) {

                        return {
                            label: item['name'] + '[' + item['product_identifier'] + ']',
                            value: item['id']
                        };
                    }));
                },
            });
        },
        'select': function(item) {
            $('input[name=\'scp_selprod_id\']').val('');
            $('#selprod-products' + item['value']).remove();
            $('#selprod-products ul ').append('<li id="selprod-products' + item['value'] + '"><i class="remove_link remove_param fa fa-remove"></i> ' + item['label'] + '<input type="hidden" name="product_ids[]" value="' + item['value'] + '" /></li>');
        }
    });
}
$(document).on('click', '.shopFile-Js', function() {
    var node = this;
    $('#form-upload').remove();
    var frmName = $(node).attr('data-frm');
    var fileType = $(node).attr('data-file_type');
    if ('frmShopLogo' == frmName) {
        var lang_id = document.frmShopLogo.lang_id.value;
        var imageType = 'logo';
    } else if ('frmShopBanner' == frmName) {
        var lang_id = document.frmShopBanner.lang_id.value;
        var slide_screen = document.frmShopBanner.slide_screen.value;
        var imageType = 'banner';
    } else {
        var lang_id = document.frmBackgroundImage.lang_id.value;
        var imageType = 'bg';
    }
    var frm = '<form enctype="multipart/form-data" id="form-upload" style="position:absolute; top:-100px;" >';
    frm = frm.concat('<input type="file" name="file" />');
    frm = frm.concat('<input type="hidden" name="lang_id" value="' + lang_id + '">');
    frm = frm.concat('<input type="hidden" name="slide_screen" value="' + slide_screen + '">');
    frm = frm.concat('<input type="hidden" name="file_type" value="' + fileType + '"></form>');
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
                url: fcom.makeUrl('Seller', 'uploadShopImages'),
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
                    $.mbsmessage.close();
                    $.systemMessage.close();
                    $('.text-danger').remove();
                    $('#input-field' + fileType).html(ans.msg);
                    if (ans.status == true) {
                        $.mbsmessage(ans.msg, true, 'alert--success');
                        $('#input-field' + fileType).removeClass('text-danger');
                        $('#input-field' + fileType).addClass('text-success');
                        $('#form-upload').remove();
                        shopImages(imageType, slide_screen, lang_id);
                    } else {
                        $.mbsmessage(ans.msg, true, 'alert--danger');
                        $('#input-field' + fileType).removeClass('text-success');
                        $('#input-field' + fileType).addClass('text-danger');
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                }
            });
        }
    }, 500);
});

$(document).on('click', '.catFile-Js', function() {
    var node = this;
    $('#form-upload').remove();
    var prodcat_id = document.frmCategoryMedia.prodcat_id.value;
    var lang_id = document.frmCategoryMedia.lang_id.value;
    var frm = '<form enctype="multipart/form-data" id="form-upload" style="position:absolute; top:-100px;" >';
    frm = frm.concat('<input type="file" name="file" />');
    frm = frm.concat('<input type="hidden" name="prodcat_id" value="' + prodcat_id + '">');
    frm = frm.concat('<input type="hidden" name="lang_id" value="' + lang_id + '">');
    frm = frm.concat('</form>');
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
                url: fcom.makeUrl('Seller', 'setupCategoryBanner'),
                type: 'post',
                dataType: 'json',
                data: new FormData($('#form-upload')[0]),
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $(node).val('loading..');
                },
                complete: function() {
                    $(node).val($val);
                },
                success: function(ans) {
                    $.mbsmessage.close();
                    $.systemMessage.close();
                    //$.mbsmessage(ans.msg, true, 'alert--success');
                    var dv = '#mediaResponse';
                    $('.text-danger').remove();
                    if (ans.status == true) {
                        $.systemMessage(ans.msg, 'alert--success');
                        $(dv).removeClass('text-danger');
                        $(dv).addClass('text-success');
                        reloadCategoryBannerList();
                        addCategoryBanner(prodcat_id);
                    } else {
                        $.systemMessage(ans.msg, 'alert--danger');
                        $(dv).removeClass('text-success');
                        $(dv).addClass('text-danger');
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                }
            });
        }
    }, 500);

});

$(document).on('click', '.shopCollection-Js', function() {
    var node = this;
    $('#form-upload').remove();
    var scollection_id = document.frmCollectionMedia.scollection_id.value;
    var lang_id = document.frmCollectionMedia.lang_id.value;
    var frm = '<form enctype="multipart/form-data" id="form-upload" style="position:absolute; top:-100px;" >';
    frm = frm.concat('<input type="file" name="file" />');
    frm = frm.concat('<input type="hidden" name="scollection_id" value="' + scollection_id + '">');
    frm = frm.concat('<input type="hidden" name="lang_id" value="' + lang_id + '">');
    frm = frm.concat('</form>');
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
                url: fcom.makeUrl('Seller', 'uploadCollectionImage'),
                type: 'post',
                dataType: 'json',
                data: new FormData($('#form-upload')[0]),
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $(node).val('loading..');
                },
                complete: function() {
                    $(node).val($val);
                },
                success: function(ans) {
                    $.mbsmessage.close();
                    $.systemMessage.close();
                    //$.mbsmessage(ans.msg, true, 'alert--success');
                    var dv = '#mediaResponse';
                    $('.text-danger').remove();
                    if (ans.status == true) {
                        $.systemMessage(ans.msg, 'alert--success');
                        $(dv).removeClass('text-danger');
                        $(dv).addClass('text-success');
                        shopCollectionImages(scollection_id, lang_id);
                    } else {
                        $.systemMessage(ans.msg, 'alert--danger');
                        $(dv).removeClass('text-success');
                        $(dv).addClass('text-danger');
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                }
            });
        }
    }, 500);

});
