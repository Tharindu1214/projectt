<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('onSubmit', 'searchCategory(this); return(false);');

$frm->setFormTagAttribute('class', 'form');
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 12;

$keyFld = $frm->getField('keyword');
$keyFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Keyword', $siteLangId));
$keyFld->developerTags['col'] = 8;
$keyFld->developerTags['noCaptionTag'] = true;

$submitBtnFld = $frm->getField('btn_submit');
$submitBtnFld->setFieldTagAttribute('class', 'btn--block');
$submitBtnFld->setWrapperAttribute('class', 'col-sm-3');
$submitBtnFld->developerTags['col'] = 2;
$submitBtnFld->developerTags['noCaptionTag'] = true;

$cancelBtnFld = $frm->getField('btn_clear');
$cancelBtnFld->setFieldTagAttribute('class', 'btn--block btn--primary-border');
$cancelBtnFld->setWrapperAttribute('class', 'col-sm-3');
$cancelBtnFld->developerTags['col'] = 2;
$cancelBtnFld->developerTags['noCaptionTag'] = true; ?>
<div class="cards">
    <div class="cards-content p-4 ">
        <?php echo $frm->getFormHtml(); ?>
        <div class="search-form"></div>
        <h5><?php echo Labels::getLabel('Lbl_Select_Your_Product_category', $siteLangId);?></h5>
        <div id="categories-js" class="categories-add-step">
            <div class="row select-categories-slider select-categories-slider-js slick-slider" id="categoryListing" dir="<?php echo CommonHelper::getLayoutDirection();?>">
            </div>
        </div>
        <div id="categorySearchListing"></div>
        <div class="gap"></div>
        <p class="note"><?php /* echo Labels::getLabel('Lbl_Note:_if_not_found_it_may_either_require_approval',$siteLangId); */ ?></p>
    </div>
</div>
<script>
    $('.select-categories-slider-js').slick(getSlickSliderSettings(3, 1, langLbl.layoutDirection, false,{1199: 3,1023: 2,767: 1,480: 1}));
</script>
