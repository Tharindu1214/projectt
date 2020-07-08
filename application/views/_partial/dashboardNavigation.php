<?php
if( User::canViewSupplierTab() && isset($_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab']) &&  $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'] =='S'){
	$this->includeTemplate('_partial/seller/sellerDashboardNavigation.php');
} else if( User::canViewAdvertiserTab() && isset($_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'])  && $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'] =='Ad'){
	$this->includeTemplate('_partial/advertiser/advertiserDashboardNavigation.php');
} else if( User::canViewAffiliateTab() && isset( $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab']) && $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'] =='AFFILIATE' ){
	$this->includeTemplate('_partial/affiliate/affiliateDashboardNavigation.php');
} else {
	$this->includeTemplate('_partial/buyerDashboardNavigation.php');
}
?>
