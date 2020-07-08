$(document).ready(function() {
    searchCollection(document.frmSearch);
    $(document).on("click", ".language-js", function(){
        $(".CollectionImages-js li").addClass('d-none');
        $('#Image-'+$(this).val()).removeClass('d-none');
    });
    $(document).on("click", ".bgLanguage-js", function(){
        $(".bgCollectionImages-js li").addClass('d-none');
        $('#bgImage-'+$(this).val()).removeClass('d-none');
    });
});

(function() {
    var runningAjaxReq = false;
    var dv = '#listing';

    reloadList = function() {
        var frm = document.frmSearch;
        searchCollection(frm);
    };

    goToSearchPage = function(page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmCollectionSearchPaging;
        $(frm.page).val(page);
        searchCollection(frm);
    };
    getCollectionTypeLayout = function(frm, collectionType, searchForm) {


        callCollectionTypePopulate(collectionType);


        fcom.ajax(fcom.makeUrl('Collections', 'getCollectionTypeLayout', [collectionType, searchForm]), '', function(t) {
            $("#" + frm + " [name=collection_layout_type]").html(t);
        });
    }
    searchCollection = function(form) {
        /*[ this block should be before dv.html('... anything here.....') otherwise it will through exception in ie due to form being removed from div 'dv' while putting html*/
        var data = '';
        if (form) {
            data = fcom.frmData(form);
        }
        /*]*/
        $(dv).html(fcom.getLoader());

        fcom.ajax(fcom.makeUrl('Collections', 'search'), data, function(res) {
            $(dv).html(res);
        });
    };

    addCollectionForm = function(id) {

        $.facebox(function() {
            collectionForm(0);
        });
    };

    collectionForm = function(id) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('Collections', 'form', [id]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    };

    collectionLayouts = function() {
        fcom.ajax(fcom.makeUrl('Collections', 'layouts'), '', function(t) {
            fcom.updateFaceboxContent(t, 'content fbminwidth faceboxWidth');
        });
    };


    editCollectionFormNew = function(collectionId) {
        $.facebox(function() {
            editCollectionForm(collectionId);

        });
    };
    editCollectionForm = function(collectionId) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('Collections', 'form', [collectionId]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    };

    setupCollection = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Collections', 'setup'), data, function(t) {
            reloadList();

            if (t.langId > 0 && t.collectionId > 0) {
                editCollectionLangForm(t.collectionId, t.langId);
                return;
            }
            /* if(t.openMediaForm)
            {
            	collectionMediaForm(t.collectionId);
            	return;
            } */
            $(document).trigger('close.facebox');
        });
    }

    editCollectionLangForm = function(collectionId, langId) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('Collections', 'langForm', [collectionId, langId]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    };

    setupLangCollection = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Collections', 'langSetup'), data, function(t) {
            reloadList();
            if (t.langId > 0) {
                editCollectionLangForm(t.collectionId, t.langId);
                return;
            }
            /* if(t.openMediaForm)
            {
            	collectionMediaForm(t.collectionId);
            	return;
            } */
            $(document).trigger('close.facebox');
        });
    };

    deleteRecord = function(id) {
        if (!confirm(langLbl.confirmDelete)) {
            return;
        }
        data = 'collectionId=' + id;
        fcom.updateWithAjax(fcom.makeUrl('Collections', 'deleteRecord'), data, function(res) {
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
        var collectionId = parseInt(obj.value);
        if (collectionId < 1) {
            fcom.displayErrorMessage(langLbl.invalidRequest);
            return false;
        }
        data = 'collectionId=' + collectionId;
        fcom.ajax(fcom.makeUrl('Collections', 'changeStatus'), data, function(res) {
            var ans = $.parseJSON(res);
            if (ans.status == 1) {
                fcom.displaySuccessMessage(ans.msg);
            } else {
                fcom.displayErrorMessage(ans.msg);
            }
        });
    };

    selprodForm = function(id) {
        $.facebox(function() {
            fcom.ajax(fcom.makeUrl('Collections', 'selprodForm', [id]), '', function(t) {
                $.facebox(t, 'faceboxWidth');
                reloadProducts(id);
            });
        });
    };

    collectionCategoryForm = function(collection_id) {
        $.facebox(function() {
            fcom.ajax(fcom.makeUrl('Collections', 'collectionCategoryForm', [collection_id]), '', function(t) {
                $.facebox(t, 'faceboxWidth');
                reloadCollectionCategories(collection_id);
            });
        });
    };

    collectionShopForm = function(collection_id) {
        $.facebox(function() {
            fcom.ajax(fcom.makeUrl('Collections', 'collectionShopForm', [collection_id]), '', function(t) {
                $.facebox(t, 'faceboxWidth');
                reloadCollectionShops(collection_id);
            });
        });
    };

    collectionBrandsForm = function(collection_id) {
        $.facebox(function() {
            fcom.ajax(fcom.makeUrl('Collections', 'collectionBrandsForm', [collection_id]), '', function(t) {
                $.facebox(t, 'faceboxWidth');
                reloadCollectionBrands(collection_id);
            });
        });
    };

    reloadProducts = function(collection_id) {
        $("#products_list").html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Collections', 'collectionSelprods', [collection_id]), '', function(t) {
            $("#products_list").html(t);
        });
    };

    reloadCollectionCategories = function(collection_id) {
        $("#categories_list").html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Collections', 'collectionCategories', [collection_id]), '', function(t) {
            $("#categories_list").html(t);
        });
    }

    reloadCollectionShops = function(collection_id) {
        $("#shops_list").html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Collections', 'collectionShops', [collection_id]), '', function(t) {
            $("#shops_list").html(t);
        });
    }

    reloadCollectionBrands = function(collection_id) {
        $("#brands_list").html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Collections', 'collectionBrands', [collection_id]), '', function(t) {
            $("#brands_list").html(t);
        });
    }

    updateProduct = function(collection_id, selprod_id) {
        fcom.updateWithAjax(fcom.makeUrl('Collections', 'updateSelProd'), 'collection_id=' + collection_id + '&selprod_id=' + selprod_id, function(t) {
            reloadProducts(collection_id);
        });
    };

    updateCollectionCategories = function(collection_id, prodcat_id) {
        fcom.updateWithAjax(fcom.makeUrl('Collections', 'updateCollectionCategories'), 'collection_id=' + collection_id + '&prodcat_id=' + prodcat_id, function(t) {
            reloadCollectionCategories(collection_id);
        });
    };

    updateCollectionShops = function(collection_id, shop_id) {
        fcom.updateWithAjax(fcom.makeUrl('Collections', 'updateCollectionShops'), 'collection_id=' + collection_id + '&shop_id=' + shop_id, function(t) {
            reloadCollectionShops(collection_id);
        });
    };

    updateCollectionBrands = function(collection_id, brand_id) {
        fcom.updateWithAjax(fcom.makeUrl('Collections', 'updateCollectionBrands'), 'collection_id=' + collection_id + '&brand_id=' + brand_id, function(t) {
            reloadCollectionBrands(collection_id);
        });
    };

    removeCollectionSelprod = function(collection_id, selprod_id) {
        var agree = confirm(langLbl.confirmRemoveProduct);
        if (!agree) {
            return false;
        }
        fcom.updateWithAjax(fcom.makeUrl('Collections', 'removeCollectionSelprod'), 'collection_id=' + collection_id + '&selprod_id=' + selprod_id, function(t) {
            reloadProducts(collection_id);
        });
    };

    removeCollectionCategory = function(collection_id, prodcat_id) {
        var agree = confirm(langLbl.confirmRemoveCategory);
        if (!agree) {
            return false;
        }
        fcom.updateWithAjax(fcom.makeUrl('Collections', 'removeCollectionCategory'), 'collection_id=' + collection_id + '&prodcat_id=' + prodcat_id, function(t) {
            reloadCollectionCategories(collection_id);
        });
    }

    removeCollectionShop = function(collection_id, shop_id) {
        var agree = confirm(langLbl.confirmRemoveShop);
        if (!agree) {
            return false;
        }
        fcom.updateWithAjax(fcom.makeUrl('Collections', 'removeCollectionShop'), 'collection_id=' + collection_id + '&shop_id=' + shop_id, function(t) {
            reloadCollectionShops(collection_id);
        });
    }

    removeCollectionBrand = function(collection_id, brand_id) {
        var agree = confirm(langLbl.confirmRemoveBrand);
        if (!agree) {
            return false;
        }
        fcom.updateWithAjax(fcom.makeUrl('Collections', 'removeCollectionBrand'), 'collection_id=' + collection_id + '&brand_id=' + brand_id, function(t) {
            reloadCollectionBrands(collection_id);
        });
    }

    collectionMediaForm = function(collectionId) {
        fcom.ajax(fcom.makeUrl('Collections', 'mediaForm', [collectionId]), '', function(t) {
            $.facebox(t);
            var parentSiblings = $(".displayMediaOnly--js").closest("div.row").siblings('div.row:not(:first)');
            if (0 < $(".displayMediaOnly--js:checked").val()) {
                parentSiblings.show();
            } else {
                parentSiblings.hide();
            }
        });
    };

    removeCollectionImage = function(collectionId, langId) {
        if (!confirm(langLbl.confirmDeleteImage)) {
            return;
        }
        fcom.updateWithAjax(fcom.makeUrl('Collections', 'removeImage', [collectionId, langId]), '', function(t) {
            collectionMediaForm(collectionId);
        });
    };

    removeCollectionBGImage = function(collectionId, langId) {
        if (!confirm(langLbl.confirmDeleteImage)) {
            return;
        }
        fcom.updateWithAjax(fcom.makeUrl('Collections', 'removeBgImage', [collectionId, langId]), '', function(t) {
            collectionMediaForm(collectionId);
        });
    };

    clearSearch = function() {
        document.frmSearch.reset();
        searchCollection(document.frmSearch);
        var collectionType = 0;
        fcom.ajax(fcom.makeUrl('Collections', 'getCollectionTypeLayout', [collectionType, 1]), '', function(t) {
            $("[name=collection_layout_type]").html(t);
        });
    };
    callCollectionTypePopulate = function(val) {
        if (val == 1) {
            $("#collection_criteria_div").show();
        } else {
            $("#collection_criteria_div").hide();
        }
    };

	toggleBulkStatues = function(status){
        if(!confirm(langLbl.confirmUpdateStatus)){
            return false;
        }
        $("#frmCollectionListing input[name='status']").val(status);
        $("#frmCollectionListing").submit();
    };

    deleteSelected = function(){
        if(!confirm(langLbl.confirmDelete)){
            return false;
        }
        $("#frmCollectionListing").attr("action",fcom.makeUrl('Collections','deleteSelected')).submit();
    };

    displayMediaOnly = function(collectionId, obj) {
        var parentSiblings = $(obj).closest("div.row").siblings('div.row:not(:first)');
        var value = (obj.checked) ? 1 : 0;
        fcom.ajax(fcom.makeUrl('Collections', 'displayMediaOnly', [collectionId, value]), '', function(t) {
			var ans = $.parseJSON(t);
            if(0 == ans.status){
                $.systemMessage(ans.msg,'alert--danger');
                $(obj).prop('checked', false);
                return false
            } else{
                (0 < value) ? parentSiblings.show() : parentSiblings.hide();
            }
		});
    };
})();

$(document).on('click', '.File-Js', function() {
    var node = this;
    $('#form-upload').remove();
    var fileType = $(node).attr('data-file_type');
    var collection_id = $(node).attr('data-collection_id');

    if (fileType == FILETYPE_COLLECTION_IMAGE) {
        var langId = document.frmCollectionMedia.image_lang_id.value;
    } else if (fileType == FILETYPE_COLLECTION_BG_IMAGE) {
        var langId = document.frmCollectionMedia.bg_image_lang_id.value;
    }

    var frm = '<form enctype="multipart/form-data" id="form-upload" style="position:absolute; top:-100px;" >';
    frm = frm.concat('<input type="file" name="file" />');
    frm = frm.concat('<input type="hidden" name="file_type" value="' + fileType + '">');
    frm = frm.concat('<input type="hidden" name="collection_id" value="' + collection_id + '">');
    frm = frm.concat('<input type="hidden" name="lang_id" value="' + langId + '">');
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
                url: fcom.makeUrl('Collections', 'uploadImage'),
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
                    if(0 == ans.status){
            			$.mbsmessage.close();
            			$.systemMessage(ans.msg,'alert--danger');
            		} else {
                        collectionMediaForm(ans.collection_id);
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                }
            });
        }
    }, 500);
});

(function() {
    displayImageInFacebox = function(str) {
        $.facebox('<img class="mx-auto d-block" width="800px;" src="' + str + '">');
    }
})();
