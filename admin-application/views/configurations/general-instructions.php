<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
	$tbid = isset($tabId)?$tabId:'tabs_'.$frmType;
?>
<ul class="tabs_nav innerul">
	<?php if( $frmType == Configurations::FORM_IMPORT_EXPORT){ ?>
		<li><a class='active' href="javascript:void(0);" onclick="generalInstructions(<?php echo $frmType;?>);"><?php echo Labels::getLabel('LBL_Instructions',$adminLangId); ?></a></li>
	<?php } ?>
	<?php if( $frmType != Configurations::FORM_MEDIA && $frmType != Configurations::FORM_SHARING ){ ?>
	<?php
	$active = '';
	if(  $frmType != Configurations::FORM_IMPORT_EXPORT ){
		$active = 'active';
	}
	?>
	<li><a class='<?php echo $active; ?>' href="javascript:void(0)" onClick="getForm(<?php echo $frmType;?>,'<?php echo $tbid;?>')"><?php echo Labels::getLabel('LBL_Basic',$adminLangId); ?></a></li>
	<?php } ?>
	<?php
	if( $dispLangTab ){
		foreach( $languages as $langId => $langName ){ ?>
			<li><a href="javascript:void(0);" class="<?php echo ($lang_id == $langId) ? 'active' : '' ; ?>" onClick="getLangForm(<?php echo $frmType;?>,<?php echo $langId;?>,'<?php echo $tbid; ?>')"><?php echo $langName; ?></a></li>
		<?php }
	} ?>
</ul>
<div class="tabs_panel_wrap">
	<?php
		if( !empty($pageData['epage_content']) ){
			?>
			<h2><?php echo $pageData['epage_label'];?></h2>
			<?php
			echo FatUtility::decodeHtmlEntities( nl2br($pageData['epage_content']) );
		}else{
			echo 'Sorry!! No Instructions.';
		}
	?>
</div>
