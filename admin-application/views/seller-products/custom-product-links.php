	<div class="box__head">
		<h4><?php echo Labels::getLabel('LBL_Add_Custom_Product',$adminLangId); ?></h4>
		<div class="group--btns"><a onclick="searchCustomProducts()"  class="btn btn--secondary btn--sm"><?php echo Labels::getLabel('LBL_back', $adminLangId);?></a></div>
	</div>
	<div class="box__body">		
		<div class="tabs tabs--small tabs--offset tabs--scroll clearfix">
			<?php require_once('sellerCustomProductTop.php');?>
		</div>
		<div class="tabs__content">
			<div class="form__content ">
				<div class="col-md-12">
					<?php 
					$frmLinks->setFormTagAttribute('class', 'web_form form--horizontal');
					$frmLinks->setFormTagAttribute('onsubmit', 'setupProductLinks(this); return(false);');
					$fld1=$frmLinks->getField('tag_name');
					$fld1->fieldWrapper = array('<div class="col-md-8">', '</div>');
					//$fld2 = $frmLinks->getField('addNewTagLink');
					//$fld2->fieldWrapper  = array('<div class="col-md-4">', '</div>');
					//$fld1->attachField($fld2);
					//$customProductFrm->getField('option_name')->setFieldTagAttribute('class','mini');
					echo $frmLinks->getFormHtml();
					?>  
				</div>
			</div>
		</div>
	</div>
	<script type="text/javascript">
	$("document").ready(function(){
		
		$('input[name=\'brand_name\']').autocomplete({
			'source': function(request, response) {
				/* fcom.ajax(fcom.makeUrl('brands', 'autoComplete'), {keyword:encodeURIComponent(request)}, function(json) {
					response($.map(json, function(item) {
							return { label: item['name'],	value: item['id']	};
						}));
				}); */
				$.ajax({
					url: fcom.makeUrl('brands', 'autoComplete'),
					data: {keyword: request,fIsAjax:1},
					dataType: 'json',
					type: 'post',
					success: function(json) {
						response($.map(json, function(item) {
							return { label: item['name'],	value: item['id']	};
						}));
					},
				});
			},
			'select': function(item) {
				$('input[name=\'brand_name\']').val(item['label']);
				$('input[name=\'product_brand_id\']').val(item['value']);
			}
		});
		
		$('input[name=\'brand_name\']').keyup(function(){
			$('input[name=\'product_brand_id\']').val('');
		});
		
		$('input[name=\'tag_name\']').autocomplete({
				'source': function(request, response) {
				
					$.ajax({
						url: fcom.makeUrl('sellerProducts', 'tagsAutoComplete'),
						data: {keyword: request,fIsAjax:1},
						dataType: 'json',
						type: 'post',
						success: function(json) {
							response($.map(json, function(item) {
							
								return { 
									label: item['name'] + ' (' + item['tag_identifier'] + ')',
									value: item['id']
									};
							}));
						},
					});
				},
				'select': function(item) {
					
					$('input[name=\'tag_name\']').val('');
				$('#product-tag' + item['value']).remove();
				$('#product-tag').append("<li id='product-tag" + item["value"] + "'><i class='remove_tag remove_param ion-android-delete icon'></i> " +item["label"] + "<input type='hidden' name='product_tag[]' value='" + item["value"] + "' /></li>");
					
				}
			});
			
			$('#product-tag').on('click', '.remove_tag', function() {
			/* $('#product-tag').delegate('.remove_tag', 'click', function() { */
                $(this).parent().remove();
            });
		<?php foreach($product_tags as $key => $val){
			
			?>
		
			$('#product-tag').append("<li id='product-tag<?php echo $val["tag_id"];?>'><i class='remove_tag remove_param ion-android-delete icon'></i> <?php echo $val["tag_identifier"];?><input type='hidden' name='product_tag[]' value='<?php echo $val["tag_id"];?>' /></li>");
		<?php } ?>
		
		
		
	});
</script>
