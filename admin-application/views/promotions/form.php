<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frmPromotion->setFormTagAttribute('class', 'web_form form_horizontal');
$frmPromotion->setFormTagAttribute('onsubmit', 'setupPromotion(this); return(false);');
$frmPromotion->developerTags['colClassPrefix'] = 'col-md-';
$frmPromotion->developerTags['fld_default_col'] = 12;

$shopFld = $frmPromotion->getField('promotion_shop');	
$shopFld->setWrapperAttribute( 'class' , 'promotion_shop_fld');	
$shopFld->htmlAfterField = '<small>'.Labels::getLabel('LBL_Note:_Used_to_promote_shop.',$adminLangId).'</small>';

$shopCpcFld = $frmPromotion->getField('promotion_shop_cpc');
$shopCpcFld->setWrapperAttribute( 'class' , 'promotion_shop_fld');
$shopCpcFld->htmlAfterField = '<small>'.Labels::getLabel('MSG_PPC_cost_per_click_for_shop',$adminLangId).'</small>';

$productFld = $frmPromotion->getField('promotion_product');	
$productFld->setWrapperAttribute( 'class' , 'promotion_product_fld');	
$productFld->htmlAfterField = '<small>'.Labels::getLabel('LBL_Note:_Used_to_promote_product.',$adminLangId).'</small>';

$productCpcFld = $frmPromotion->getField('promotion_product_cpc');
$productCpcFld->setWrapperAttribute( 'class' , 'promotion_product_fld');
$productCpcFld->htmlAfterField = '<small>'.Labels::getLabel('MSG_PPC_cost_per_click_for_Product',$adminLangId).'</small>';	

$locationFld = $frmPromotion->getField('banner_blocation_id');
$locationFld->setWrapperAttribute( 'class' , 'location_fld');

$urlFld = $frmPromotion->getField('banner_url');	
$urlFld->setWrapperAttribute( 'class' , 'banner_url_fld');	
$urlFld->htmlAfterField = '<small>'.Labels::getLabel('LBL_Note:_Used_to_promote_through_banner.',$adminLangId).'</small>';

/* $bannerTargetUrlFld = $frmPromotion->getField('banner_target');	
$bannerTargetUrlFld->setWrapperAttribute( 'class' , 'banner_url_fld'); */

$slideUrlFld = $frmPromotion->getField('slide_url');	
$slideUrlFld->setWrapperAttribute( 'class' , 'slide_url_fld');	
$slideUrlFld->htmlAfterField = '<small>'.Labels::getLabel('LBL_Note:_Used_to_promote_through_slider.',$adminLangId).'</small>';

/* $slideTargetUrlFld = $frmPromotion->getField('slide_target');	
$slideTargetUrlFld->setWrapperAttribute( 'class' , 'slide_url_fld'); */	

$slideCpcFld = $frmPromotion->getField('promotion_slides_cpc');
$slideCpcFld->setWrapperAttribute( 'class' , 'slide_url_fld');
$slideCpcFld->htmlAfterField = '<small>'.Labels::getLabel('MSG_PPC_cost_per_click_for_Slides',$adminLangId).'</small>';
?>
<section class="section">
	<div class="sectionhead">
		<h4><?php echo Labels::getLabel('LBL_Promotion_Setup',$adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">      
		<div class="tabs_nav_container responsive flat">
			<ul class="tabs_nav">
				<li ><a  class="active"  href="javascript:void(0);" onClick="addPromotionForm(<?php echo $promotionId;?>)"><?php echo Labels::getLabel('LBL_General',$adminLangId);?></a></li>	
			<?php $inactive = ($promotionId==0)?'fat-inactive':'';		
			foreach($language  as $langId => $langName){?>	
				<li ><a  href="javascript:void(0)" <?php if($promotionId>0){ ?> onClick="promotionLangForm(<?php echo $promotionId;?>,<?php echo $langId;?>)" <?php }?>>
			<?php echo $langName;?></a></li>
			<?php } ?>
			
			<?php if($promotionType == Promotion::TYPE_BANNER || $promotionType == Promotion::TYPE_SLIDES){?>
			<li ><a  class="<?php echo $inactive; ?>" href="javascript:void(0)" <?php if($promotionId>0){ ?> onClick="promotionMediaForm(<?php echo $promotionId;?>)" <?php }?>><?php echo Labels::getLabel('LBL_Media',$adminLangId); ?></a></li>		
			<?php }?>			
			</ul>
			<div class="tabs_panel_wrap">
				<div class="tabs_panel">
					<?php echo $frmPromotion->getFormHtml(); ?>
				</div>
			</div>						
		</div>
	</div>						
</section>


<script type="text/javascript">
jQuery('.time').datetimepicker({
  datepicker:false,
  format:'H:i'
});

$("document").ready(function(){
	var PROMOTION_TYPE_BANNER = <?php echo Promotion::TYPE_BANNER; ?>;
	var PROMOTION_TYPE_SHOP = <?php echo Promotion::TYPE_SHOP; ?>;
	var PROMOTION_TYPE_PRODUCT = <?php echo Promotion::TYPE_PRODUCT; ?>;
	var PROMOTION_TYPE_SLIDES = <?php echo Promotion::TYPE_SLIDES; ?>;		
	
	$("select[name='promotion_type']").change(function(){
		var promotionType = $(this).val();
		$(".promotion_shop_fld").hide();
		$(".promotion_product_fld").hide();			
		$(".banner_url_fld").hide();			
		$(".location_fld").hide();
		$(".slide_url_fld").hide();
		
		if( promotionType == PROMOTION_TYPE_BANNER ){					
			$(".banner_url_fld").show();			
			$(".location_fld").show();			
		}
		
		if( promotionType == PROMOTION_TYPE_SHOP ){
			$(".promotion_shop_fld").show();			
		}
		
		if( promotionType == PROMOTION_TYPE_PRODUCT ){			
			$(".promotion_product_fld").show();
		}
		
		if( promotionType == PROMOTION_TYPE_SLIDES ){
			$(".slide_url_fld").show();
		}
		
		fcom.updateWithAjax(fcom.makeUrl('Promotions', 'getTypeData', [<?php echo $promotionId;?>, promotionType ]), '', function(t) {
			$.systemMessage.close();			
			if(t.promotionType == PROMOTION_TYPE_SHOP){				
				$("input[name='promotion_shop']").val(t.label);
			}else if(t.promotionType == PROMOTION_TYPE_PRODUCT){		
				$("input[name='promotion_product']").val(t.label) ;
			}
			$("input[name='promotion_record_id']").val(t.value)	;
		});	
	});
	
	$("select[name='promotion_type']").trigger('change');
		
	$('input[name=\'promotion_product\']').autocomplete({
		'source': function(request, response) {
			$.ajax({
				url: fcom.makeUrl('Promotions', 'autoCompleteSelprods',[$('input[name=\'promotion_user_id\']').val()]),
				data: {keyword: request,fIsAjax:1},
				dataType: 'json',
				type: 'post',
				success: function(json) {
					
					response($.map(json, function(item) {
						return { label: item['name'] ,	value: item['id']	};
					}));
				},
			});
		},
		'select': function(item) {			
			$("input[name='promotion_product']").val(item['label'])	;			
			$("input[name='promotion_record_id']").val(item['value'])	;			
		}
	}); 

});
</script>