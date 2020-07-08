$(document).on("change", "select[name='is_custom_or_catalog']", function(){
    if ( 0 == $(this).val() ) {
        $("input[name='product_seller_id']").val('');
        $("input[name='product_seller']").val('').attr('disabled','disabled');
    }else{
        $("input[name='product_seller']").removeAttr('disabled');
    }
});

$(document).ready(function(){
    searchProducts(document.frmSearch);

    $("input[name='product_seller']").autocomplete({
        'source': function(request, response) {
            if( '' != request ){
                $.ajax({
                    url: fcom.makeUrl('Products', 'autoCompleteSellerJson'),
                    data: {keyword: request},
                    dataType: 'json',
                    type: 'post',
                    success: function(json) {
                        response($.map(json, function(item) {
                            var email = '';
                            if( null !== item['credential_email'] ){
                                email = ' ('+item['credential_email']+')';
                            }
                            return { label: item['credential_username'] + email,    value: item['credential_user_id']    };
                        }));
                    },
                });
            }else{
                $("input[name='product_seller_id']").val('');
            }
        },
        'select': function(item) {
            $("input[name='product_seller_id']").val( item['value'] );
            $("input[name='product_seller']").val( item['label'] );
        }
    });

});

$(document).on('change','.option-js',function(){
/* $(document).delegate('.option-js','change',function(){ */
    var option_id = $(this).val();
    var product_id = $('#imageFrm input[name=product_id]').val();
    var lang_id = $('.language-js').val();
    productImages(product_id,option_id,lang_id);
});
$(document).on('change','.language-js',function(){
/* $(document).delegate('.language-js','change',function(){ */
    var lang_id = $(this).val();
    var product_id = $('#imageFrm input[name=product_id]').val();
    var option_id = $('.option-js').val();
    productImages(product_id,option_id,lang_id);
});

(function() {
    var currentProdId = 0;
    var currentPage = 1;
    var runningAjaxReq = false;

    searchProducts = function(frm){
        if( runningAjaxReq == true ){
            return;
        }
        runningAjaxReq = true;
        /*[ this block should be before dv.html('... anything here.....') otherwise it will through exception in ie due to form being removed from div 'dv' while putting html*/
        var data = '';
        if (frm) {
            data = fcom.frmData(frm);
        }
        /*]*/
        var dv = $('#listing');
        $(dv).html( fcom.getLoader() );

        fcom.ajax(fcom.makeUrl('Products','search'),data,function(res){
            runningAjaxReq = false;
            $("#listing").html(res);
        });
    };

    goToSearchPage = function(page) {
        if(typeof page==undefined || page == null){
            page =1;
        }
        var frm = document.frmProductSearchPaging;
        $(frm.page).val(page);
        searchProducts(frm);
    };

    reloadList = function() {
        var frm = document.frmProductSearchPaging;
        searchProducts(frm);
    };

    addProductForm= function(id, attrgrp_id){
        $.facebox(function() {productForm(id, attrgrp_id )});
    };

    productForm = function( id, attrgrp_id ) {
        fcom.displayProcessing();
        fcom.resetEditorInstance();

        fcom.ajax(fcom.makeUrl('Products', 'form', [ id, attrgrp_id]), '', function(t) {
            fcom.updateFaceboxContent(t,'faceboxWidth product-setup-width');

            if(CONF_PRODUCT_DIMENSIONS_ENABLE == 0)
            {
                addShippingTab(id,PRODUCT_TYPE_DIGITAL);
            }
        });
    };

    productAttributeGroupForm = function( ){
        $.facebox(function() {
            fcom.ajax(fcom.makeUrl('Products', 'productAttributeGroupForm'), '', function(t) {
                $.facebox(t,'faceboxWidth');
            });
        });
    };

    productLangForm = function(product_id, lang_id) {
        fcom.displayProcessing();
        fcom.resetEditorInstance();
    //    $.facebox(function() {
            fcom.ajax(fcom.makeUrl('Products', 'langForm', [product_id, lang_id]), '', function(t) {
                fcom.updateFaceboxContent(t);
            //    $.facebox(t);
                fcom.setEditorLayout(lang_id);
                var frm = $('#facebox form')[0];
                var validator = $(frm).validation({errordisplay: 3});
                $(frm).submit(function(e) {
                    e.preventDefault();

                    validator.validate();
                    if (!validator.isValid()) return;
                    /* if (validator.validate() == false) {
                        return ;
                    } */

                    var data = fcom.frmData(frm);
                    fcom.updateWithAjax(fcom.makeUrl('Products', 'langSetup'), data, function(t) {
                        fcom.resetEditorInstance();
                        reloadList();
                        if (t.lang_id>0) {
                            productLangForm(t.product_id, t.lang_id);
                            return ;
                        }
                        $(document).trigger('close.facebox');
                    });
                });

            });
        //});
    };

    setupProduct = function(frm) {
        if (!$(frm).validate()) return;
        var addingNew = ($(frm.product_id).val() == 0);
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Products', 'setup'), data, function(t) {
            reloadList();
            if (addingNew) {
                productLangForm(t.product_id, t.lang_id);
                return ;
            }
            $(document).trigger('close.facebox');
        });
    };

    setupProductLang = function(frm){
        if ( !$(frm).validate() ) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Products', 'langSetup'), data, function(t) {
            reloadList();
            if (t.lang_id>0) {
                productLangForm(t.product_id, t.lang_id);
                return ;
            }
            $(document).trigger('close.facebox');
            return ;
        });
        return;
    };

    clearSearch = function(){
        document.frmSearch.reset();
        document.frmSearch.product_seller_id.value = '';
        searchProducts(document.frmSearch);
    };

    productImagesForm = function( product_id ){
        fcom.ajax(fcom.makeUrl('Products', 'imagesForm', [product_id]), '', function(t) {
            productImages(product_id);
            $.facebox(t, 'faceboxWidth');
        });
    };

    productImages = function( product_id,option_id,lang_id ){
        fcom.ajax(fcom.makeUrl('Products', 'images', [product_id,option_id,lang_id]), '', function(t) {
            $('#imageupload_div').html(t);
            fcom.resetFaceboxHeight();
        });
    };

    submitImageUploadForm = function ( ){
        /* if ($.browser.msie && parseInt($.browser.version, 10) === 8 || $.browser.msie && parseInt($.browser.version, 10) === 9) {
            $('#imagefrm').removeAttr('onsubmit')
            $('#imagefrm').submit(); return true;
        } */
        var data = new FormData(  );
        $inputs = $('#imageFrm input[type=text],#imageFrm select,#imageFrm input[type=hidden]');
        $inputs.each(function() { data.append( this.name,$(this).val());});
        var product_id = $('#imageFrm input[name="product_id"]').val();
        $.each( $('#prod_image')[0].files, function(i, file) {
                $('#imageupload_div').html(fcom.getLoader());
                data.append('prod_image', file);
                $.ajax({
                    url : fcom.makeUrl('Products', 'uploadProductImages'),
                    type: "POST",
                    data : data,
                    processData: false,
                    contentType: false,
                    success: function(t){
                        try{
                            var ans = $.parseJSON(t);
                            productImages( $('#imageFrm input[name=product_id]').val(), $('.option-js').val(), $('.language-js').val() );
                            if( ans.status == 1 ){
                                $.systemMessage(ans.msg, 'alert--success');
                            }else {
                                $.systemMessage(ans.msg, 'alert--danger');
                            }
                        }
                        catch(exc){
                            productImages( $('#imageFrm input[name=product_id]').val(), $('.option-js').val(), $('.language-js').val() );
                            $.systemMessage(t, 'alert--danger');
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown){
                        alert("Error Occured.");
                    }
                });
            });
    };

    deleteImage = function( product_id, image_id ){
        var agree = confirm(langLbl.confirmDelete);
        if( !agree ){ return false; }
        fcom.ajax( fcom.makeUrl( 'Products', 'deleteImage', [product_id, image_id] ), '' , function(t) {
            var ans = $.parseJSON(t);
            if( ans.status == 0 ){
                fcom.displayErrorMessage( ans.msg);
                return;
            }else{
                fcom.displaySuccessMessage( ans.msg);
            }
            productImages( product_id, $('.option-js').val(), $('.language-js').val() );
        });
    };

    productLinksForm = function( id ){
        $.facebox(function() {
            fcom.ajax(fcom.makeUrl('Products', 'linksForm', [id]), '', function(t) {
                $.facebox(t,'faceboxWidth');
                reloadProductLinks(id);
            });
        });
    };

    setupProductLinks = function(frm){
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Products', 'setupProductLinks'), data, function(t) {
            $(document).trigger('close.facebox');
        });
    };
    addProductOptionsForm = function( id ){
        $.facebox(function() {
            productOptionsForm(id);
        });
    };


    productOptionsForm = function( id ){
        fcom.displayProcessing();
            fcom.ajax(fcom.makeUrl('Products', 'optionsForm', [id]), '', function(t) {
                fcom.updateFaceboxContent(t);
                reloadProductOptions(id);
            });
    };

    reloadProductOptions = function( product_id ){
        $("#product_options_list").html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Products', 'productOptions', [product_id]), '', function(t) {
            $("#product_options_list").html(t);
        });
    };

    updateProductOption = function (product_id, option_id){
        fcom.updateWithAjax(fcom.makeUrl('Products', 'updateProductOption'), 'product_id='+product_id+'&option_id='+option_id, function(t) {
            reloadProductOptions(product_id);
        });
    };

    removeProductOption = function(product_id, option_id){
        var agree = confirm(langLbl.confirmRemoveOption);
        if(!agree){ return false; }
        fcom.updateWithAjax(fcom.makeUrl('Products', 'removeProductOption'), 'product_id='+product_id+'&option_id='+option_id, function(t) {
            reloadProductOptions(product_id);
        });
    };

    optionForm = function(optionId){
        fcom.displayProcessing();
        if(currentProdId < 1){
            currentProdId = $('#product_id').val();
        }
        //$.facebox(function() {
            fcom.ajax(fcom.makeUrl('Options', 'form', [optionId]), '', function(t) {
                //$.facebox(t,'faceboxWidth');
                fcom.updateFaceboxContent(t);
                addOptionForm(optionId);
                optionValueListing(optionId);
                fcom.resetFaceboxHeight();
            });
        //});
    };

    addOptionForm = function(optionId){
        var dv = $('#loadForm');
        var data = 'product_id='+currentProdId;
        fcom.ajax(fcom.makeUrl('Options', 'addForm', [optionId]), data, function(t) {
            dv.html(t);
            fcom.resetFaceboxHeight();
        });
    };

    optionValueListing = function(optionId){
        if(optionId == 0 ) { $('#showHideContainer').addClass('hide'); return ;}
        var dv =$('#optionValueListing');
        dv.html(fcom.getLoader());
        var data = 'option_id='+optionId;
        fcom.ajax(fcom.makeUrl('OptionValues','search'),data,function(res){
            dv.html(res);
        });
    };

    optionValueForm = function (optionId,id){
        var dv = $('#loadForm');
        fcom.ajax(fcom.makeUrl('OptionValues', 'form', [optionId,id]), '', function(t) {
            dv.html(t);
            jscolor.installByClassName('jscolor');
            fcom.resetFaceboxHeight();
        });
    };

    setUpOptionValues = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('OptionValues', 'setup'), data, function(t) {
            if (t.optionId > 0 ) {
                optionValueListing(t.optionId);
                optionValueForm(t.optionId,0);
                return ;
            }
            $(document).trigger('close.facebox');
        });
    };

    deleteOptionValue = function(optionId,id){
        if(!confirm(langLbl.confirmDelete)){return;}
        data='id='+id+'&option_id='+optionId;
        fcom.updateWithAjax(fcom.makeUrl('OptionValues','deleteRecord'),data,function(res){
            optionValueListing(optionId);
            optionValueForm(optionId,0);
        });
    };

    optionValueSearchPage = function(page){
        if(typeof page==undefined || page == null){
            page =1;
        }
        var frm = document.frmSearchOptionValuePaging;
        $(frm.page).val(page);
        searchOptionValueListing(frm);
    };

    searchOptionValueListing = function(form){
        //$("#optionValueListing").html('Loading....');
        $("#optionValueListing").html(fcom.getLoader());

        var data = '';
        if (form) {
            data = fcom.frmData(form);
        }
        fcom.ajax(fcom.makeUrl('OptionValues','search'),data,function(res){
            $("#optionValueListing").html(res);
        });
    };

    showHideValues = function(obj){
        var type =obj.value;
        var data ='optionType='+type;
        fcom.ajax(fcom.makeUrl('Options','canSetValue'),data,function(t){
            var res = $.parseJSON(t);
            if(res.hideBox == true){
                $('#showHideContainer').addClass('hide'); return ;
            }
            $('#showHideContainer').removeClass('hide');
        });
    };

    submitOptionForm=function(frm,fn){
        $(frm).validate();
        var option_id = $(frm.option_id).val();
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Options', 'setup'), data, function(t) {
            if(t.optionId > 0){
                optionForm(t.optionId);
                if(currentProdId > 0){
                    updateProductOption(currentProdId,t.optionId);
                }
                return;
            }
            fcom.resetFaceboxHeight();
            $(document).trigger('close.facebox');
        });
    };

    productTagsForm = function( id ){
        $.facebox(function() {
            fcom.ajax(fcom.makeUrl('Products', 'tagsForm', [id]), '', function(t) {
                $.facebox(t,'faceboxWidth');
                reloadProductTags(id);
            });
        });
    };

    reloadProductTags = function( product_id ){
        $("#product_tags_list").html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Products', 'productTags', [product_id]), '', function(t) {
            $("#product_tags_list").html(t);
        });
    };

    updateProductTag = function (product_id, tag_id){
        fcom.updateWithAjax(fcom.makeUrl('Products', 'updateProductTag'), 'product_id='+product_id+'&tag_id='+tag_id, function(t) {
            reloadProductTags(product_id);
        });
    };

    removeProductTag = function(product_id, tag_id){
        var agree = confirm(langLbl.confirmRemove);
        if(!agree){ return false; }
        fcom.updateWithAjax(fcom.makeUrl('Products', 'removeProductTag'), 'product_id='+product_id+'&tag_id='+tag_id, function(t) {
            reloadProductTags(product_id);
        });
    };

    addTagForm = function(id) {
    fcom.displayProcessing();
            fcom.ajax(fcom.makeUrl('tags', 'form', [id]), '', function(t) {
                fcom.updateFaceboxContent(t);
                });
    };

    setupTag = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Tags', 'setup'), data, function(t) {
            reloadList();
            if (t.langId>0) {
                addTagLangForm(t.tagId, t.langId);
                return ;
            }
            $(document).trigger('close.facebox');
        });
    };

    addTagLangForm = function(tagId, langId) {
        $.facebox(function() {
            fcom.ajax(fcom.makeUrl('Tags', 'langForm', [tagId, langId]), '', function(t) {
                $.facebox(t);
            });
        });
    };

    setupTagLang = function(frm){
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Tags', 'langSetup'), data, function(t) {
            reloadList();
            if (t.langId>0) {
                addTagLangForm(t.tagId, t.langId);
                return ;
            }
            $(document).trigger('close.facebox');
        });
    };

    /* Custom product Specifications */
    productSpecifications = function(id){
        fcom.ajax(fcom.makeUrl('Products', 'customProductSpecifications', [id]), '', function(t) {
            $.facebox(t,'faceboxWidth');
            addProdSpec(id);
            reloadProductSpecifications(id);
        });
    };

    reloadProductSpecifications = function( productId){
        //fcom.displayProcessing();
        $("#product_specifications_list").html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Products', 'ProductSpecifications', [productId]),'', function(t) {
            try{
                res= jQuery.parseJSON(t);
                $("#product_specifications_list").html(res.msg);
            }catch (e){
                $("#product_specifications_list").html(t);
            }
            fcom.resetFaceboxHeight();
        });
    };

    addProdSpec = function(productId,prodSpecId){
        var dv = $('#loadForm');
        fcom.ajax(fcom.makeUrl('Products', 'prodSpecForm', [productId]),'prodSpecId='+prodSpecId, function(t) {
            /* $.facebox(t,'faceboxWidth product-setup-width'); */
            dv.html(t);
        });
    };

    deleteProdSpec = function(productId,prodSpecId){
        /* var agree = confirm("Do you want to delete record?");
        if( !agree ){ return false; } */
        fcom.updateWithAjax(fcom.makeUrl('Products', 'deleteProdSpec', [productId]),'prodSpecId='+prodSpecId, function(t) {
            reloadProductSpecifications(productId);
        });
    };

    submitSpecificationForm = function(frm){
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Products', 'setupProductSpecifications'), data, function(t) {
            reloadProductSpecifications(t.productId);
            if(t.productId > 0){
                productSpecifications(t.productId); return;
            }
            $(document).trigger('close.facebox');
        });
        return false;
    };

    toggleStatus = function(e,obj,canEdit){
        if(canEdit == 0){
            e.preventDefault();
            return;
        }
        if(!confirm(langLbl.confirmUpdateStatus)){
            e.preventDefault();
            return;
        }
        var productId = parseInt(obj.value);
        if(productId < 1){
            fcom.displayErrorMessage(langLbl.invalidRequest);
            return false;
        }
        data='productId='+productId;
        fcom.ajax(fcom.makeUrl('Products','changeStatus'),data,function(res){
        var ans = $.parseJSON(res);
            if( ans.status == 1 ){
                $(obj).toggleClass("active");
                fcom.displaySuccessMessage(ans.msg);
                /* setTimeout(function(){
                    reloadList();
                }, 1000); */
            } else {
                fcom.displayErrorMessage(ans.msg);
            }
        });
    };

    deleteProduct = function(productId){
        if(!confirm(langLbl.confirmDelete)){
            e.preventDefault();
            return;
        }
        if(productId < 1){
            fcom.displayErrorMessage(langLbl.invalidRequest);
            return false;
        }
        data='productId='+productId;
        fcom.ajax(fcom.makeUrl('Products','deleteProduct'),data,function(res){
        var ans = $.parseJSON(res);
            if( ans.status == 1 ){
                fcom.displaySuccessMessage(ans.msg);
                setTimeout(function(){
                    reloadList();
                }, 1000);
            } else {
                fcom.displayErrorMessage(ans.msg);
            }
        });
    };


    /* Product shipping  */
    addShippingTab = function(id,prodTypeDigital){

        var ShipDiv = "#tab_shipping";
        var e = document.getElementById("product_type");
        var type = e.options[e.selectedIndex].value;
        if(type == prodTypeDigital){
            $(ShipDiv).html('');
            $('.not-digital-js').hide();
            return;
        }else{
            $('.not-digital-js').show();
        }
        fcom.ajax(fcom.makeUrl('products','getShippingTab'),'product_id='+id,function(t){
            try{
                    res= jQuery.parseJSON(t);
                //    $.facebox(res.msg,'faceboxWidth');
                }catch (e){
                    $(ShipDiv).html(t);
                }
        });
    };

    shippingautocomplete = function(shipping_row) {
        $('input[name="product_shipping[' + shipping_row + '][country_name]"]').focusout(function() {
                setTimeout(function(){ $('.suggestions').hide(); }, 500);
        });

        $('input[name="product_shipping[' + shipping_row + '][company_name]"]').focusout(function() {
                setTimeout(function(){ $('.suggestions').hide(); }, 500);
        });

        $('input[name="product_shipping[' + shipping_row + '][processing_time]"]').focusout(function() {
                setTimeout(function(){ $('.suggestions').hide(); }, 500);
        });
        $('input[name="product_shipping[' + shipping_row + '][country_name]"]').autocomplete({
            'source': function(request, response) {
                $.ajax({
                    url: fcom.makeUrl('products', 'countries_autocomplete'),
                    data: {keyword: request,fIsAjax:1,includeEverywhere:true},
                    dataType: 'json',
                    type: 'post',
                    success: function(json) {
                        response($.map(json, function(item) {
                            return {
                                label: item['name'] ,
                                value: item['id']
                            };
                        }));
                    },
                });
            },
            'select': function(item) {
                $('input[name="product_shipping[' + shipping_row + '][country_name]"]').val(item.label);
                $('input[name="product_shipping[' + shipping_row + '][country_id]"]').val(item.value);
            }
        });

        $('input[name="product_shipping[' + shipping_row + '][company_name]"]').autocomplete({
                'source': function(request, response) {
                $.ajax({
                    url: fcom.makeUrl('products', 'shippingCompanyAutocomplete'),
                    data: {keyword: request,fIsAjax:1},
                    dataType: 'json',
                    type: 'post',
                    success: function(json) {
                        response($.map(json, function(item) {
                            return {
                                label: item['name'] ,
                                value: item['id']
                            };
                        }));
                    },
                });
            },
            'select': function(item) {
                $('input[name="product_shipping[' + shipping_row + '][company_name]"]').val(item.label);
                $('input[name="product_shipping[' + shipping_row + '][company_id]"]').val(item.value);
            }
        });

        $('input[name="product_shipping[' + shipping_row + '][processing_time]"]').autocomplete({
                'source': function(request, response) {
                $.ajax({
                    url: fcom.makeUrl('products', 'shippingMethodDurationAutocomplete'),
                    data: {keyword: request,fIsAjax:1},
                    dataType: 'json',
                    type: 'post',
                    success: function(json) {
                        response($.map(json, function(item) {
                            return {
                                label: item['name']+'['+ item['duraion']+']' ,
                                value: item['id']
                                };
                        }));
                    },
                });
            },
            'select': function(item) {
                $('input[name="product_shipping[' + shipping_row + '][processing_time]"]').val(item.label);
                $('input[name="product_shipping[' + shipping_row + '][processing_time_id]"]').val(item.value);
            }
        });
    };

 /*  End of  Product shipping  */

    removeProductCategory = function(product_id, option_id){
        var agree = confirm(langLbl.confirmRemoveOption);
        if(!agree){ return false; }
        fcom.updateWithAjax(fcom.makeUrl('Products', 'removeProductCategory'), 'product_id='+product_id+'&option_id='+option_id, function(t) {
            reloadProductLinks(product_id);
        });
    };

    updateProductLink = function (product_id, option_id){
        fcom.updateWithAjax(fcom.makeUrl('Products', 'updateProductLink'), 'product_id='+product_id+'&option_id='+option_id, function(t) {
            reloadProductLinks(product_id);
        });
    };

    reloadProductLinks = function( product_id ){
        $("#product_links_list").html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Products', 'productLinks', [product_id]), '', function(t) {
            $("#product_links_list").html(t);
        });
    };

    upcForm = function (id){
        $.facebox(function() {
            fcom.ajax(fcom.makeUrl('products', 'upcForm', [id]), '', function(t) {
                $.facebox(t,'faceboxWidth');
            });
        });
    };

    updateUpc = function(pid,optionValueId){
        var code = $("input[name='code"+optionValueId+"']").val();
        var msrp = $("input[name='msrp"+optionValueId+"']").val();
        var data = {'code':code,'msrp':msrp,'optionValueId':optionValueId};
        fcom.updateWithAjax(fcom.makeUrl('products', 'updateUpc',[pid]), data, function(t) {

        });
    };

    toggleBulkStatues = function(status){
        if(!confirm(langLbl.confirmUpdateStatus)){
            return false;
        }
        $("#frmProdListing input[name='status']").val(status);
        $("#frmProdListing").submit();
    };

    deleteSelected = function(){
        if(!confirm(langLbl.confirmDelete)){
            return false;
        }
        $("#frmProdListing").attr("action",fcom.makeUrl('Products','deleteSelected')).submit();
    };

})();
