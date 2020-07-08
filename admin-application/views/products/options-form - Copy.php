<?php
defined('SYSTEM_INIT') or die('Invalid Usage.'); 
$frmOptions->setFormTagAttribute('class', 'web_form form_horizontal');
$frmOptions->setFormTagAttribute('onsubmit', 'setupProductOptions(this); return(false);');	

//$fld_product_name = $frmOptions->getField('product_name');

	
$fld_options_div = $frmOptions->getField('selected_options_div');


$box_html = new HtmlElement('div',array('class'=>'field-set'));
$option_div_caption = $box_html->appendElement('div',array('class'=>'caption-wraper'));
$option_div_caption->appendElement('label',array('class'=>'field_label'),'Option Groups:');

$option_div_field = $box_html->appendElement('div',array('class'=>'field-wraper'));


/* box[ */
$div_boxes_container = new HtmlElement('div',array('class'=>'boxes_container'));
$div_boxround = $div_boxes_container->appendElement('div',array('class' => 'boxround','style' => 'width:100%'));
$div_boxwraplist = $div_boxround->appendElement('div',array('class' => 'boxwraplist'));
$div_scrollerwrap = $div_boxwraplist->appendElement('div',array('class' => 'scrollerwrap'));
$ul_verticalcheck_list = $div_scrollerwrap->appendElement('ul',array('class' => 'verticalcheck_list','id' => 'product-option'));
if($productOptions){
	foreach($productOptions as $option){
		$a_under_li = new HtmlElement('a',array('href'=>'javascript:void(0)','title'=>'Remove'),' <i class="remove_option ion-ios-close"></i> ', true);
		$li = $ul_verticalcheck_list->appendElement('li',array('id' => 'product-option'.$option['option_id']), $a_under_li->getHtml().$option['option_name'].'<input type="hidden" value="'.$option['option_id'].'" name="product_option[]">', true);
	}
}
$sub_box_html = $div_boxes_container->getHtml();
/* ] */

$option_div_field->appendElement('div',array('class'=>'field_cover'),$sub_box_html,true);

$box_html = $box_html->getHtml();
$fld_options_div->value = $box_html;

$fld_product_name = $frmOptions->getField('product_name');
$fld_product_name->setFieldTagAttribute('readonly','readonly');
$fld_product_name->setFieldTagAttribute('disabled','disabled');
?>
<div class="col-sm-12">
	<h1>Product Options Management Setup</h1>
	<div class="tabs_nav_container responsive flat">
		<div class="tabs_panel_wrap">
			<div class="tabs_panel">
				<?php echo $frmOptions->getFormHtml(); ?>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
$("document").ready(function(){
	$('input[name=\'option_name\']').autocomplete({
		'source': function(request, response) {
			/* fcom.ajax(fcom.makeUrl('brands', 'autoComplete'), {keyword:encodeURIComponent(request)}, function(json) {
				response($.map(json, function(item) {
						return { label: item['name'],	value: item['id']	};
					}));
			}); */
			$.ajax({
				url: fcom.makeUrl('options', 'autoComplete'),
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
			$('#product-option' + item['value']).remove();
			$('#product-option').append('<li id="product-option' + item['value'] + '"><a href="javascript:void(0)" title="Remove"><i class="remove_option ion-ios-close"></i></a> ' + item['label'] + '<input type="hidden" name="product_option[]" value="' + item['value'] + '" /></li>');
		}
	});
	
	$('#product-option').on('click', '.remove_option', function() {
	/* $('#product-option').delegate('.remove_option', 'click', function() { */
		$(this).parent().parent().remove();
	});
	
	/* $('input[name=\'option_name\']').keyup(function(){
		$('input[name=\'product_brand_id\']').val('');
	}); */
});
</script>