<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<ul class="columlist list-vertical" id="product-option">
<?php if ($productOptions) {
    $lis= '';
    foreach ($productOptions as $option) {
        $lis .= '<li id="product-option' . $option['option_id'] . '"><a href="javascript:void(0)" title="'. Labels::getLabel('LBL_Remove', $siteLangId) .'" onClick="removeProductOption('.$productId.','.$option['option_id'].');"><i class="fa fa-remove" data-option-id="' . $option['option_id'] . '"></i> '.$option['option_name'].' ('.$option['option_identifier'].') </a><input type="hidden" value="'.$option['option_id'].'"  name="product_option[]">';
    }
    echo $lis;
} ?>
</ul>
