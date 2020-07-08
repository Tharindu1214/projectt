<?php
defined('SYSTEM_INIT') or die('Invalid Usage.'); 
$frmTags->setFormTagAttribute('class', 'web_form form_horizontal');

$frmTags->developerTags['colClassPrefix'] = 'col-md-';
$frmTags->developerTags['fld_default_col'] = 12;
/* [ */
?>
<section class="section">
<div class="sectionhead">

    <h4><?php echo Labels::getLabel('LBL_Product_Tags_Management_Setup',$adminLangId); ?></h4>
</div>
<div class="sectionbody space">
 <div class="row">


<div class="col-sm-12">
	<div class="tabs_nav_container responsive flat">
		<div class="tabs_panel_wrap" style="min-height: 500px;">
			<div class="tabs_panel">
				<?php echo $frmTags->getFormHtml(); ?>
				<div id="product_tags_list" class="col-xs-6"></div>
			</div>
		</div>
	</div>
</div>
</div></div></section>	
<script type="text/javascript">
$("document").ready(function(){
	$('input[name=\'tag_name\']').autocomplete({
		'source': function(request, response) {
			/* fcom.ajax(fcom.makeUrl('brands', 'autoComplete'), {keyword:encodeURIComponent(request)}, function(json) {
				response($.map(json, function(item) {
						return { label: item['name'],	value: item['id']	};
					}));
			}); */
			$.ajax({
				url: fcom.makeUrl('tags', 'autoComplete'),
				data: {keyword: request,fIsAjax:1},
				dataType: 'json',
				type: 'post',
				success: function(json) {
					response($.map(json, function(item) {
						return { label: item['name'] + ' (' + item['tag_identifier'] + ')',	value: item['id']	};
					}));
				},
			});
		},
		'select': function(item) {
			updateProductTag(<?php echo $product_id; ?>, item['value'] );
		}
	});
});
</script>