<?php defined('SYSTEM_INIT') or die('Invalid Usage.');  ?>
<div class="tabs tabs--small tabs--scroll clearfix">
    <?php require_once(CONF_THEME_PATH.'seller/sellerCustomProductTop.php');?>
</div>
<div class="cards">
    <div class="cards-content pt-3 pl-4 pr-4 ">
        <div class="tabs__content">
            <div class="row row">
                <div class="col-md-12">
                    <?php $frmLinks->setFormTagAttribute('class', 'form form--horizontal');
                    $frmLinks->setFormTagAttribute('onsubmit', 'setupProductLinks(this); return(false);');
                    $frmLinks->developerTags['colClassPrefix'] = 'col-lg-6 col-md-';
                    $frmLinks->developerTags['fld_default_col'] = 6;
                    $frmLinks->removeField($frmLinks->getField('product_name'));
                    $fld1=$frmLinks->getField('tag_name');
                    // $fld1->fieldWrapper = array('<div class="col-md-8">', '</div>');
                    //$fld2 = $frmLinks->getField('addNewTagLink');
                    //$fld2->fieldWrapper  = array('<div class="col-md-4">', '</div>');
                    //$fld1->attachField($fld2);
                    //$customProductFrm->getField('option_name')->setFieldTagAttribute('class','mini');

                    $fld_div = $frmLinks->getField('choose_links');
                    /* $fld_div->fieldWrapper = array('<div class="box--scroller">','</div>'); */

                    echo $frmLinks->getFormHtml(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $("document").ready(function() {

        $('input[name=\'brand_name\']').autocomplete({
            'source': function(request, response) {
                /* fcom.ajax(fcom.makeUrl('brands', 'autoComplete'), {keyword:encodeURIComponent(request)}, function(json) {
                    response($.map(json, function(item) {
                            return { label: item['name'],    value: item['id']    };
                        }));
                }); */
                $.ajax({
                    url: fcom.makeUrl('brands', 'autoComplete'),
                    data: {
                        keyword: request,
                        fIsAjax: 1
                    },
                    dataType: 'json',
                    type: 'post',
                    success: function(json) {
                        response($.map(json, function(item) {
                            return {
                                label: item['name'],
                                value: item['id']
                            };
                        }));
                    },
                });
            },
            'select': function(item) {
                $('input[name=\'brand_name\']').val(item['label']);
                $('input[name=\'product_brand_id\']').val(item['value']);
            }
        });

        $('input[name=\'brand_name\']').keyup(function() {
            $('input[name=\'product_brand_id\']').val('');
        });

        $('input[name=\'tag_name\']').autocomplete({
            'source': function(request, response) {

                $.ajax({
                    url: fcom.makeUrl('seller', 'tagsAutoComplete'),
                    data: {
                        keyword: request,
                        fIsAjax: 1
                    },
                    dataType: 'json',
                    type: 'post',
                    success: function(json) {
                        response($.map(json, function(item) {

                            return {
                                label: item['name'] + ' (' + item['tag_identifier'] + ')',
                                value: item['id']
                            };
                        }));
                    },
                });
            },
            'select': function(item) {
                $('input[name=\'tag_name\']').val('');
                $('#product-tag' + item['value']).remove();
                $('#product-tag').append("<li id='product-tag" + item["value"] + "'><i class='remove_tag remove_param fa fa-trash'></i> " + item["label"] + "<input type='hidden' name='product_tag[]' value='" + item["value"] +
                    "' /></li>");
            }
        });

        $('#product-tag').on('click', '.remove_tag', function() {

            $(this).parent().remove();
        });
        <?php foreach($product_tags as $key => $val){?>
        $('#product-tag').append(
            "<li id='product-tag<?php echo $val["tag_id"];?>'><i class='remove_tag remove_param fa fa-trash'></i> <?php echo $val["tag_name"]." (".$val["tag_identifier"].")";?><input type='hidden' name='product_tag[]' value='<?php echo $val["tag_id"];?>' /></li>"
            );
        <?php } ?>

        $('input[name=\'choose_links\']').autocomplete({
            'source': function(request, response) {
                /* fcom.ajax(fcom.makeUrl('brands', 'autoComplete'), {keyword:encodeURIComponent(request)}, function(json) {
                    response($.map(json, function(item) {
                            return { label: item['name'],    value: item['id']    };
                        }));
                }); */
                /* $("#product_links_list").html(fcom.getLoader()); */
                $.ajax({
                    url: fcom.makeUrl('products', 'linksAutocomplete'),
                    data: {
                        keyword: request,
                        fIsAjax: 1
                    },
                    dataType: 'json',
                    type: 'post',
                    success: function(json) {
                        response($.map(json, function(item) {
                            return {
                                label: item['name'],
                                value: item['id']
                            };
                        }));
                        fcom.ajax(fcom.makeUrl('Seller', 'productLinks', [<?= $product_id;?>]), '', function(t) {
                            $("#product_links_list").html(t);
                        });
                        /* $("#product_links_list").html(''); */
                    },
                });
            },
            'select': function(item) {
                updateProductLink(<?= $product_id;?>, item['value']);
            }
        });

    });
</script>
