<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$category_banner_fld = $mediaFrm->getField('category_banner');
$category_banner_fld->addFieldTagAttribute('class','btn btn--primary btn--sm');

$haveImage = AttachedFile::getAttachment( AttachedFile::FILETYPE_CATEGORY_BANNER_SELLER, $shop_id, $prodCatId, $formLangId );
?>
<div class="popup__body">
	<h2><?php echo Labels::getLabel('LBL_Manage_Category_Banner',$siteLangId).' ('.$catData['prodcat_name'].')'; ?></h2>
	<ul class="tabs tabs--small    -js clearfix setactive-js">
		<?php
		foreach( $languages as $langId => $langName ){?>
		<li class="<?php echo ($formLangId == $langId) ? 'is-active' : '' ?>"><a href="javascript:void(0)" onClick="categoryBannerLangForm(<?php echo $prodCatId ?>, <?php echo $langId;?>)">
		<?php echo $langName;?></a></li>
		<?php } ?>
	</ul>
	<div class="tabs__content">
		<div class="row ">
			<div class="col-md-12">
				<div class="preview">
					<?php if( $haveImage ){ ?>
					<div class="profile__pic"><img src="<?php echo CommonHelper::generateUrl( 'Category', 'sellerBanner', array( $shop_id, $prodCatId, $formLangId, 'WIDE'));?>" alt="<?php echo Labels::getLabel('LBL_Banner', $siteLangId);?>"></div>
					<?php
					$category_banner_fld->htmlAfterField = '<a class = "btn btn--primary btn--sm" href="javascript:void(0);" onClick="removeCategoryBanner('. $prodCatId .', ' . $formLangId . ')">'.Labels::getLabel('LBL_Remove',$siteLangId).'</a>';
					} ?>

					<div class="btngroup--fix">
					<?php echo $mediaFrm->getFormHtml(); ?>
					</form>
					<small class="text--small"><?php echo sprintf(Labels::getLabel('MSG_Upload_shop_banner_text',$siteLangId),'2000*500')?></small>
					<?php echo $mediaFrm->getExternalJS();?>
					</div>
					<div id="mediaResponse"></div>
				</div>
			</div>
		</div>
	</div>
</div>
