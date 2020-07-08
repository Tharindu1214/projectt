<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'web_form form_horizontal');
$frm->setFormTagAttribute( 'onSubmit', 'importFile("importMedia",'.$actionType.'); return false;' );
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 12;

?>


<section class="section">
    <div class="sectionhead">

        <h4><?php echo $title; ?><?php // echo Labels::getLabel('LBL_Import_Categories'.$title,$adminLangId); ?></h4>
    </div>
    <div class="sectionbody space">
        <div class="row">
        	<div class="col-sm-12">
            	<h1><?php //echo $title; ?></h1>
            	<div class="tabs_nav_container responsive flat">
            		<ul class="tabs_nav">
                        <li><a href="javascript:void(0);" onclick="getInstructions('<?php echo $actionType;?>');"><?php echo Labels::getLabel('LBL_Instructions',$adminLangId); ?></a></li>
            			<li><a href="javascript:void(0);" onclick="importForm('<?php echo $actionType;?>');"><?php echo Labels::getLabel('LBL_Content',$adminLangId); ?></a></li>
            			<li><a class="active" href="javascript:void(0);" onclick="importMediaForm('<?php echo $actionType;?>');"><?php echo Labels::getLabel('LBL_Media',$adminLangId); ?></a></li>
            		</ul>
            		<div class="tabs_panel_wrap">
            			<div class="tabs_panel">
            				<?php echo $frm->getFormHtml(); ?>
            			</div>
            		</div>
            	</div>
            </div>
        </div>
    </div>
</section>
