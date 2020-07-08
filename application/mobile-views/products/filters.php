<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$conditions = array();
$conditionTitles = Product::getConditionArr($siteLangId);
foreach ($conditionsArr as $condition) {
    if ($condition['selprod_condition'] == 0) {
        continue;
    }
    $conditions[] = array(
        'title' => $conditionTitles[$condition['selprod_condition']],
        'value' => $condition['selprod_condition'],
    );
}

$optionRows = $optionsValues = $optionsResult = array();
if (isset($options) && 0 < count($options)) {
    function sortByOrder($a, $b)
    {
        return $a['option_id'] - $b['option_id'];
    }
    usort($options, 'sortByOrder');

    foreach ($options as $opt) {
        $optionRows[$opt['option_id']] = [
            'option_id' => $opt['option_id'],
            'option_is_color' => $opt['option_is_color'],
            'option_name' => $opt['option_name']
        ];
        $optionsValues[$opt['option_id']]['values'][] = [
            'optionvalue_name' => $opt['optionvalue_name'],
            'optionvalue_id' => $opt['optionvalue_id'],
            'optionvalue_color_code' => $opt['optionvalue_color_code'],
        ];
    }
    $optionsResult = array_replace_recursive($optionRows, $optionsValues);
}

$data = array(
    'productFiltersArr' => empty($productFiltersArr) ? (object)array() : $productFiltersArr,
    'headerFormParamsAssocArr' => $headerFormParamsAssocArr,
    'categoriesArr' => $categoriesArr,
    'shopCatFilters' => $shopCatFilters,
    'prodcatArr' => $prodcatArr,
    'brandsArr' => $brandsArr,
    'brandsCheckedArr' => $brandsCheckedArr,
    'optionValueCheckedArr' => $optionValueCheckedArr,
    'conditionsArr' => $conditions,
    'conditionsCheckedArr' => $conditionsCheckedArr,
    'options' => array_values($optionsResult),
    'priceArr' => $priceArr,
    'priceInFilter' => $priceInFilter,
    'filterDefaultMinValue' => $filterDefaultMinValue,
    'filterDefaultMaxValue' => $filterDefaultMaxValue,
    'availability' => $availability,
    'availabilityArr' => array_values($availabilityArr),
);
