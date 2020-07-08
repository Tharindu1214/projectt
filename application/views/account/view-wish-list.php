<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$randomId = rand( 1, 1000 );
$frm->setFormTagAttribute('class', 'custom-form setupWishList-Js' );
$frm->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-';
$frm->developerTags['fld_default_col'] = 12;
$frm->setFormTagAttribute('id', 'setupWishList_Js_'.$randomId );
$frm->setFormTagAttribute('onsubmit', 'setupWishList(this,event); return(false);');
$uwlist_title_fld = $frm->getField('uwlist_title');
$uwlist_title_fld->addFieldTagAttribute('placeholder',Labels::getLabel('LBL_New_List', $siteLangId));
?>
<div class="pop-up-title"><?php echo Labels::getLabel('LBL_Your_List', $siteLangId); ?></div>
<?php if( $wishLists ){ ?>
<div class="collection__list">
  <ul class="listing--check">
    <?php foreach( $wishLists as $list ){ ?>
    <li onClick="addRemoveWishListProduct(<?php echo $selprod_id .', '.$list['uwlist_id']; ?>, event);" class="wishListCheckBox_<?php echo $list['uwlist_id']; ?> <?php echo array_key_exists( $selprod_id, $list['products'] ) ? ' is-active' : ''; ?>">
        <a href="javascript:void(0)">
            <?php echo ($list['uwlist_default']==1) ? Labels::getLabel('LBL_Default_list', $siteLangId) : $list['uwlist_title']; ?>
        </a>
    </li>
    <?php } ?>
  </ul>
</div>
<?php } ?>
<div class="collection__form form">
  <?php
		echo $frm->getFormTag();
		echo $frm->getFieldHtml('uwlist_title');
		echo $frm->getFieldHtml('selprod_id');
		echo $frm->getFieldHtml('btn_submit');
	?>
  </form>
  <?php echo $frm->getExternalJs(); ?> </div>
