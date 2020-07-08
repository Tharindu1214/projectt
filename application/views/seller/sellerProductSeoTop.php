<div class="container container--fluid">
    <div class="tabs tabs-sm tabs--scroll clearfix">
        <ul>
            <li class="<?php echo ($seoActiveTab == 'GENERAL')?'is-active':''?>">
                <a href="javascript:void(0)" onclick="getProductSeoGeneralForm(<?php echo "$selprod_id" ?>);">
                    <?php echo Labels::getLabel('LBL_Basic', $siteLangId);?>
                </a>
            </li>
            <?php $inactive=($metaId==0)?'fat-inactive':'';
            foreach ($languages as $langId => $langName) { ?>
                <li class="<?php echo ($langId == $selprod_lang_id) ? 'is-active' : ''; ?>">
                    <a href="javascript:void(0);"
                        <?php if ($metaId > 0) { ?>
                            onclick="editProductMetaTagLangForm(<?php echo "$metaId,$langId,'$metaType'" ?>);"
                        <?php } ?>>
                        <?php echo $langName; ?>
                    </a>
                </li>
            <?php } ?>
        </ul>
    </div>
</div>
