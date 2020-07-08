<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
    $selProdId = (!empty($data['splprice_selprod_id']) ? $data['splprice_selprod_id'] : 0);
    $frm = SellerProduct::specialPriceForm($siteLangId);
    $prodName = $frm->addTextBox(Labels::getLabel('LBL_Product', $siteLangId), 'product_name', '', array('class'=>'selProd--js', 'placeholder' => Labels::getLabel('LBL_Select_Product', $siteLangId)));
    $prodName->requirements()->setRequired();

    $startDate = $frm->getField('splprice_start_date');
    $startDate->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Price_Start_Date', $siteLangId));
    $startDate->setFieldTagAttribute('class', 'date_js');

    $endDate = $frm->getField('splprice_end_date');
    $endDate->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Price_End_Date', $siteLangId));
    $endDate->setFieldTagAttribute('class', 'date_js');

    $splPrice = $frm->getField('splprice_price');
    $splPrice->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Special_Price', $siteLangId));

    $frm->setFormTagAttribute('class', 'form');
    $frm->setFormTagAttribute('onsubmit', 'updateSpecialPriceRow(this, '.$selProdId.'); return(false);');
    $frm->addHiddenField('', 'addMultiple');

    $frm->setFormTagAttribute('id', 'frmAddSpecialPrice-'.$selProdId);
    $frm->setFormTagAttribute('name', 'frmAddSpecialPrice-'.$selProdId);

    $startDate = $frm->getField('splprice_start_date');
    $startDate->setFieldTagAttribute('id', 'splprice_start_date'.$selProdId);

    $endDate = $frm->getField('splprice_end_date');
    $endDate->setFieldTagAttribute('id', 'splprice_end_date'.$selProdId);

    $frm->addSubmitButton('', 'btn_update', Labels::getLabel('LBL_Save_Changes', $siteLangId), array('class'=>'btn--block btn btn--primary'));

if (!empty($data) && 0 < count($data)) {
    $prodName->setFieldTagAttribute('readonly', 'readonly');
    $frm->fill($data);
}
?>
<div class="cards-content pt-4 pl-4 pr-4 pb-0">
    <div class="replaced">
        <?php
        echo $frm->getFormTag();
        echo $frm->getFieldHtml('splprice_selprod_id');
        echo $frm->getFieldHtml('addMultiple');
        ?>
            <div class="row">
                <div class="col-lg-3 col-md-3">
                    <div class="field-set">
                        <div class="field-wraper">
                            <?php echo $frm->getFieldHtml('product_name'); ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-2">
                    <div class="field-set">
                        <div class="field-wraper">
                            <?php echo $frm->getFieldHtml('splprice_start_date'); ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-2">
                    <div class="field-set">
                        <div class="field-wraper">
                            <?php echo $frm->getFieldHtml('splprice_end_date'); ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3">
                    <div class="field-set">
                        <div class="field-wraper">
                            <?php echo $frm->getFieldHtml('splprice_price'); ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-2">
                    <div class="field-set">
                        <div class="field-wraper">
                            <div class="field_cover">
                                <?php echo $frm->getFieldHtml('btn_update'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="divider"></div>
