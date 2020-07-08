<?php  
	$defaultUploadPath = '/user-uploads/editor';
	/*assetmanager\server\delfile.php use defaultUploadPath variable for application folder*/
	
	$path_for_images = ''; /* Relative to URI Root - This is for Innova */	
	
	/*assetmanager\server\delfile.php use path_for_images variable for admin-application folder*/
	
	$sellerSession = 'yokartUserSession';
	$adminSession = 'yokartAdmin';
	
	$is_seller_for_file_manager = 0;
	$is_admin_for_file_manager = 0;
	
	$admin = (isset($_SESSION[$adminSession]['admin_id']) && is_numeric($_SESSION[$adminSession]['admin_id']) && intval($_SESSION[$adminSession]['admin_id']) > 0 && strlen(trim($_SESSION[$adminSession]['admin_username'])) >= 4);
		
	$seller = (isset($_SESSION[$sellerSession]['user_id']) && is_numeric($_SESSION[$sellerSession]['user_id']) && intval($_SESSION[$sellerSession]['user_id']) > 0 && (strlen(trim($_SESSION[$sellerSession]['user_name'])) >= 4)); 
	
	if( !($admin || $seller) ){
		echo '<br/>You do not have access to file manager, Please contact admin!';
		exit(0);
	}	
	
	if($admin){
		$is_admin_for_file_manager = 1;
	}else if($seller){
		$is_admin_for_file_manager = 1;
		$is_seller_for_file_manager = 1;
	}else{
		/*exit(0)*/
	}
	
	if($is_seller_for_file_manager){
		$path_for_images = $defaultUploadPath."/".$_SESSION[$sellerSession]['user_id']; /* Relative to URI Root - This is for Innova */
	}else if($is_admin_for_file_manager){	
		$path_for_images = $defaultUploadPath; /* Relative to URI Root - This is for Innova */	
	}else{
		exit(0);	
	}
	
		
	if(!file_exists($path_for_images)){	
		//create the folder
		//$dir_to_create = ealpath(dirname(__FILE__). '/../').$path_for_images;
		//@mkdir($dir_to_create, 0777, true);
		//create the folder
		$dir_to_create = realpath(dirname(__FILE__). '/../').$path_for_images;
		if(!file_exists($dir_to_create)){
			mkdir($dir_to_create, 0777, true);
		}

	} 
?>