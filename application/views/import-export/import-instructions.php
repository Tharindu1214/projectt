<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="tabs tabs-sm tabs--scroll clearfix">
    <ul>
        <li class="is-active"><a class="is-active" href="javascript:void(0);" onclick="getInstructions('<?php echo $actionType;?>');"><?php echo Labels::getLabel('LBL_Instructions',$siteLangId); ?></a></li>

        <li><a href="javascript:void(0);" onclick="importForm('<?php echo $actionType;?>');"><?php echo Labels::getLabel('LBL_Content',$siteLangId); ?></a></li>
        <?php if($displayMediaTab){?>
        <li><a href="javascript:void(0);" onclick="importMediaForm('<?php echo $actionType;?>');"><?php echo Labels::getLabel('LBL_Media',$siteLangId); ?></a></li>
        <?php }?>
    </ul>
</div>
<div class="form__subcontent">
    <?php
        if( !empty($pageData['epage_content']) ){
			?>
				<h2><?php echo $pageData['epage_label'];?></h2>
				<hr>
			<?php
            	echo FatUtility::decodeHtmlEntities( $pageData['epage_content'] );
        }else{
            echo 'Sorry!! No Instructions.';
        }
    ?>
</div>
