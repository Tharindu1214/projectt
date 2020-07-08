<?php  defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<div id="" >
  <div class="">
    <div class="product-options">
      <div class="pop-up-title"><?php echo Labels::getLabel('LBL_Product',$siteLangId);?></div>
      <ul>
        <?php if( User::canAddCustomProduct() ){ ?>
        <li  data-heading="OR" ><a href="<?php echo CommonHelper::generateUrl('seller','customProductForm' );?>"><i class="icn fa  fa-camera"></i>
          <p><?php echo Labels::getLabel('LBL_Create_new_product',$siteLangId);?> </p>
          <span><?php echo Labels::getLabel('LBL_Create_your_Product',$siteLangId);?></span> </a> </li>
        <?php } else if((isset($canAddCustomProduct) && $canAddCustomProduct==false) && (isset($canRequestProduct) && $canRequestProduct === true )){ ?>
        <li  data-heading="OR"><a href="<?php echo CommonHelper::generateUrl('Seller','requestedCatalog');?>" class="btn btn--primary btn--sm"><i class="icn fa fa-file-text "></i>
          <p><?php echo Labels::getLabel('LBL_Request_A_Product',$siteLangId);?></p>
          <span><?php echo Labels::getLabel('LBL_Request_to_add_a_new_product_in_catalog',$siteLangId);?></span></a></li>
        <?php } ?>
        <li  data-heading="OR"><a href="<?php echo CommonHelper::generateUrl('seller','catalog',array(1));?>"><i class="icn fa fa-camera-retro"></i>
          <p><?php echo Labels::getLabel('LBL_Search_and_add_Products_from_marketplace',$siteLangId);?></p>
          <span><?php echo Labels::getLabel('LBL_Search_and_pick_to_sell_products_from_existing_marketplace_products',$siteLangId);?></span> </a> </li>
        <li  data-heading="OR"><a href="<?php echo CommonHelper::generateUrl('ImportExport','index'); ?>"><i class="icn fa fa-file-text-o"></i>
          <p><?php echo Labels::getLabel('LBL_Import_Export',$siteLangId);?></p>
          <span><?php echo Labels::getLabel('LBL_Import_Export_Existing_Data',$siteLangId);?></span> </a></li>
      </ul>
    </div>
  </div>
</div>
