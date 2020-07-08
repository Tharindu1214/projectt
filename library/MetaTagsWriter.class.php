<?php
class MetaTagsWriter {

	static function getMetaTags( $controller, $action, $arrParameters ){
		$langId = CommonHelper::getLangId();
		if( !$langId ){
			$langId = FatApp::getConfig('CONF_DEFAULT_SITE_LANG', FatUtility::VAR_INT, 1);
		}
		
		$websiteName = FatApp::getConfig('CONF_WEBSITE_NAME_'.$langId, FatUtility::VAR_STRING, '');
		
		$fatUtlityObj = new FatUtility;
		$controller = explode('-', FatUtility::camel2dashed($controller));
		array_pop($controller);
		$controllerName = implode('-', $controller);
		$controllerName = ucfirst(FatUtility::dashed2Camel($controllerName));
		
		$srch = new MetaTagSearch( $langId );
		$cond = $srch->addCondition('meta_controller', '=', $controllerName);
		$cond->attachCondition('meta_controller','=','','OR');
		
		$cond1 = $srch->addCondition('meta_action', '=', $action);
		$cond1->attachCondition('meta_action','=','','OR');
		
		$srch->addOrder('meta_default','asc');
		
		if( !empty($arrParameters) ){
			switch ($controllerName) {
				/* case 'products':
					if( isset($arrParameters[0]) && FatUtility::int($arrParameters[0]) > 0 && $action == 'view' ){
						$srch->addCondition( 'meta_record_id', '=', FatUtility::int( $arrParameters[0] ) );
					}
				break; */
				default: 
					if( isset($arrParameters[0]) && FatUtility::int($arrParameters[0]) > 0){
						$cond = $srch->addCondition( 'meta_record_id', '=', FatUtility::int( $arrParameters[0] ) );
						$cond->attachCondition('meta_record_id','=',0,'OR');
					}
					if( isset($arrParameters[1]) && FatUtility::int($arrParameters[1]) > 0){	
						$cond = $srch->addCondition('meta_subrecord_id', '=', FatUtility::int( $arrParameters[1] ));
						$cond->attachCondition('meta_subrecord_id','=',0,'OR');
					}
				break;
			}
		}
		
		$srch->doNotCalculateRecords();
		$srch->setPageSize(1);
		$srch->addMultipleFields( array( 
			'IFNULL(meta_title, meta_identifier) as meta_title', 
			'meta_keywords', 'meta_description', 'meta_other_meta_tags' ) );
		
		$rs = $srch->getResultSet();
		
		if( $metas = FatApp::getDb()->fetch( $rs ) ){ 
			$title = $metas['meta_title']. ' | '.$websiteName;
			echo '<title>' . $title . '</title>' . "\n";
			if(isset($metas['meta_description']))
				echo '<meta name="description" content="'.$metas['meta_description'].'" />';
			if(isset($metas['meta_keywords']))
				echo '<meta name="keywords" content="'.$metas['meta_keywords'].'" />';
			if(isset($metas['meta_other_meta_tags']))
				echo CommonHelper::renderHtml($metas['meta_other_meta_tags'], ENT_QUOTES, 'UTF-8');
		} else {
			return '<title>' . $websiteName . '</title>';
		}
		/* $srch = Meta::metaSearch();
		
		$srch->addCondition('meta_controller', 'LIKE', $controllerName);
		$srch->addCondition('meta_action', 'LIKE', $action);
		
		if(!empty($arrParameters) && $controllerName == 'content' ){
			if(isset($arrParameters[0]) && FatUtility::int($arrParameters[0]) > 0){
				$srch->addCondition('meta_record_id', '=', $arrParameters[0]);
			}
			
			if(isset($arrParameters[1]) && FatUtility::int($arrParameters[1]) > 0){
				$srch->addCondition('meta_sub_record_id', '=', $arrParameters[1]);
			}
		}
		
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		
		$rs = $srch->getResultSet(); */
		
		/* if($metas = FatApp::getDb()->fetch($rs, 'cpage_id')){
			echo '<title>' . $metas['meta_title'] . '</title>' . "\n";
			if(isset($metas['meta_description']))
				echo '<meta name="description" content="'.$metas['meta_description'].'" />';
			if(isset($metas['meta_keywords']))
				echo '<meta name="keywords" content="'.$metas['meta_keywords'].'" />';
			if(isset($metas['meta_other_meta_tags']))
				echo FatUtility::decodeHtmlEntities($metas['meta_other_meta_tags'], ENT_QUOTES, 'UTF-8');
		}else{ */
			//return '<title>' . $websiteName  . '</title>';
		/* } */
	}
	
	/* static function getHeaderTags( $controller, $action, $arrParameters, $anotherTitle = '', $metaData = array() ) {
		
		$title = '';
		
		if( $anotherTitle == '' ) { 
		
			$controller = explode( '-', FatUtility::camel2dashed( $controller ) );
			
			if( current( $controller ) == 'home' ) $title = '';
			else $title = implode( ' ', $controller );
			
			if( $title != '' ) 
				$title = trim( str_replace( 'controller', '', $title ) );
			
			if( $action != '' ) { 
				
				$action = explode( '-', FatUtility::camel2dashed( $action ) );
				if( current( $action ) == 'index' ) $actionTitle = '';
				else $actionTitle = implode( ' ', $action );
			
				if( $actionTitle != '' ) 
					$title .= ( ( $title )? ' - ' : '' ) . $actionTitle;
				
			}
			
		} else { 
			$title = $anotherTitle; 
		}
		
		$data = self::getFrontendTitle( ucwords( $title ) );
		$data .= self::getMetaTags( $metaData );
		
		return $data;
		
	}
	
	static function getAdminTitle( $title = '' ){
		if( strlen( trim ( $title ) ) > 0 ) { 
			echo '<title> Administrator | ' . $title . ' | ' . FatApp::getConfig( "conf_website_name" ) . '</title>' . "\n";
		}else{
			echo '<title> Administrator | ' . FatApp::getConfig( "conf_website_name" ) . '</title>' . "\n";
		}
	}
	
	static function getFrontendTitle( $title = '' ){
		
		if( strlen( trim( $title ) ) > 0 ) { 
			return '<title>' . $title . ' | ' . FatApp::getConfig( "conf_website_name" ) . '</title>' . "\n";
		} else { 
			return '<title>' . FatApp::getConfig( "conf_website_name" ) . '</title>' . "\n";
		}
		
	}
	
	static function getMetaTags( $metaData = array() ){
		
		$data = '';
		if( is_array( $metaData ) && !empty( $metaData ) ) { 
			foreach( $metaData as $name => $content ) { 
				$data .= '<meta name="' .$name. '" content="' . $content . '" />' . "\n";
			}
		}
		
		return $data;
		
	} */
	
}