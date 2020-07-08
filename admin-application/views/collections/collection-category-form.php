<?php
defined('SYSTEM_INIT') or die('Invalid Usage.'); 
$frm->setFormTagAttribute('class', 'web_form form_horizontal');
$frm->developerTags['colClassPrefix'] = 'col-md-';							
$frm->developerTags['fld_default_col'] = 12;
?>

<section class="section">
	<div class="sectionhead">
		<h1><?php echo Labels::getLabel('LBL_Collection_Categories_Setup',$adminLangId); ?></h1>
	</div>
	<div class="sectionbody space">
		<div class="row">	
			<div class="col-sm-12">
				<div class="tabs_nav_container responsive flat">
					<div class="tabs_panel_wrap" style="min-height: 500px;">
						<div class="tabs_panel">
							<?php echo $frm->getFormHtml(); ?>
							<div id="categories_list" class="col-xs-10"></div>
						</div>
						
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
<script type="text/javascript">
$("document").ready(function(){
	$('input[name=\'categories\']').autocomplete({
		'source': function(request, response) {
			$.ajax({
				url: fcom.makeUrl('ProductCategories', 'autocomplete'),
				data: {keyword: request,fIsAjax:1},
				dataType: 'json',
				type: 'post',
				success: function(json) {
					response($.map(json, function(item) {
						return { label: item['prodcat_identifier'] ,	value: item['prodcat_id']	};
					}));
				},
			});
		},
		'select': function( item ) {
			updateCollectionCategories(<?php echo $collection_id; ?>, item['value'] );
		}
	});
});
</script>