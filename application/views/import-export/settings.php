<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('onsubmit', 'updateSettings(this); return(false);');
$frm->setFormTagAttribute('class', 'form');

$frm->developerTags['colClassPrefix'] = 'col-lg-6 col-md-';
$frm->developerTags['fld_default_col'] = 6;

$variables = array('siteLangId'=>$siteLangId,'action'=>$action);
$this->includeTemplate('import-export/_partial/top-navigation.php', $variables, false); ?>
<div class="cards">
    <div class="cards-content pt-3 pl-4 pr-4 pb-4">
        <div class="tabs__content">
            <div class="row">
                <div class="col-md-12" id="settingFormBlock">
                    <?php echo $frm->getFormHtml(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
