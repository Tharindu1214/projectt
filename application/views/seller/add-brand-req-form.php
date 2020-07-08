<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frmBrandReq->setFormTagAttribute('class', 'form form--horizontal');
$frmBrandReq->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-';
$frmBrandReq->developerTags['fld_default_col'] = 12;
$frmBrandReq->setFormTagAttribute('onsubmit', 'setupBrandReq(this); return(false);');
$identifierFld = $frmBrandReq->getField(Brand::DB_TBL_PREFIX.'id');
$identifierFld->setFieldTagAttribute('id', Brand::DB_TBL_PREFIX.'id');
?>

<div class="box__head">
<h4><?php echo Labels::getLabel('LBL_Request_New_Brand', $langId); ?></h4>
</div>
<div class="box__body">
    <div class="tabs tabs--small tabs--scroll">
        <ul>
            <li class="is-active"><a href="javascript:void(0)" onclick="addBrandReqForm(<?php echo $brandReqId; ?>);"><?php echo Labels::getLabel('LBL_Basic', $siteLangId);?></a></li>
            <?php $inactive=($brandReqId==0)?'fat-inactive':'';
            foreach ($languages as $langId => $langName) {?>
            <li class="<?php echo $inactive;?>"><a href="javascript:void(0);"
                <?php if ($brandReqId > 0) { ?>
                    onclick="addBrandReqLangForm(<?php echo $brandReqId ?>, <?php echo $langId;?>);"
                <?php }?>><?php echo $langName;?></a>
            </li>
            <?php } ?>
            <li class="<?php echo $inactive;?>"><a href="javascript:void(0)"
                <?php if ($brandReqId > 0) { ?>
                    onclick="brandMediaForm(<?php echo $brandReqId ?>);"
                <?php } ?>><?php echo Labels::getLabel('LBL_Media', $siteLangId); ?></a>
            </li>
        </ul>
    </div>
    <?php
        echo $frmBrandReq->getFormHtml();
    ?>
</div>
