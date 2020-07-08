<?php defined('SYSTEM_INIT') or die('Invalid usage');

$optionsString = '<option value="">'.Labels::getLabel("LBL_Select_City", $siteLangId).'</option>';
foreach( $citiesArr as $id => $cityName ){
	$selected = '';
	if( $cityId == $id ){
		$selected = 'selected';
	}
	$optionsString .= "<option value='".$id."' ".$selected.">".$cityName."</option>";
}

echo $optionsString;