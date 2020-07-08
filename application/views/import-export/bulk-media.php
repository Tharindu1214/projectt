<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'form ');

$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 4;
$frm->setFormTagAttribute('onSubmit', 'uploadZip(); return false;');

$variables = array('siteLangId'=>$siteLangId,'action'=>$action);
$this->includeTemplate('import-export/_partial/top-navigation.php', $variables, false); ?>
<div class="cards">
    <div class="cards-content pt-3 pl-4 pr-4 pb-4">
        <div class="content-body">
            <div class="replaced">
                <?php echo $frm->getFormHtml();  ?>
            </div>
            <h6 class=""><?php echo Labels::getLabel('LBL_Uploaded_Media_Directory_List', $siteLangId); ?></h6>
            <div class="row">
                <div class="col-lg-12">
                    <div id="listing"> <?php echo Labels::getLabel('LBL_Processing...', $siteLangId); ?></div>
                    <span class="gap"></span>
                </div>
            </div>
        </div>
    </div>
</div>
