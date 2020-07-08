<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>

<div class="col-md-12">
	<div class="container container-fluid container--fluid">
		<div class="tabs--inline clearfix">
			<ul class="tabs_nav tabs_nav--internal">
                <li ><a onclick="getShopCollectionGeneralForm();" href="javascript:void(0)"><?php echo Labels::getLabel('TXT_GENERAL', $adminLangId);?></a></li>
                <?php 					
                foreach($language as $lang_id => $langName){?>	
                <li class=""><a href="javascript:void(0)" onClick="editShopCollectionLangForm(<?php echo $scollection_id ?>, <?php echo $lang_id;?>)">
                <?php echo $langName;?></a></li>
                <?php } ?>
                <li class="is-active"> 
                    <a onclick="sellerCollectionProducts(<?php echo $scollection_id ?>)" href="javascript:void(0);"> <?php echo Labels::getLabel('TXT_LINK', $adminLangId);?> </a>
                </li>
            </ul>
		</div>
	</div>

	<div class="form__subcontent">
		<?php 
		$sellerCollectionproductLinkFrm->setFormTagAttribute('onsubmit','setUpSellerCollectionProductLinks(this); return(false);');
		$sellerCollectionproductLinkFrm->setFormTagAttribute('class','web_form form--horizontal');
		$sellerCollectionproductLinkFrm->developerTags['colClassPrefix'] = 'col-md-';
		$sellerCollectionproductLinkFrm->developerTags['fld_default_col'] = 12; 

	echo $sellerCollectionproductLinkFrm->getFormHtml(); ?>
	</div>
</div>




<script type="text/javascript">
$("document").ready(function(){
    $('#selprod-products').on('click', '.remove_buyTogether', function() {
    /* $('#selprod-products').delegate('.remove_buyTogether', 'click', function() { */
        $(this).parent().remove();
    });	
});


		
	
	<?php  
	if(isset($products)&& !empty($products)){
		foreach($products as $key => $val){
		?>
		$('#selprod-products ul').append("<li id=\"selprod-products<?php echo $val['selprod_id'];?>\"><i class=\"icon ion-close-round\"></i><?php echo $val['product_name'];?>[<?php echo $val['product_identifier'];?>]<input type=\"hidden\" name=\"product_ids[]\" value=\"<?php echo $val['selprod_id'];?>\" /></li>");
  	<?php }   
	}?>
	

</script>