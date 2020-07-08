<?php
defined('SYSTEM_INIT') or die('Invalid Usage');

$pagination = '';

if($pageCount <= 1){ return $pagination;}

/*Number of links to display*/
$linksToDisp = isset($linksToDisp)?$linksToDisp:2;

/* Current page number */
$pageNumber = $page;

/*arguments mixed(array/string(comma separated)) // function arguments*/
$arguments =(isset($arguments))?$arguments:null;

/*padArgListTo boolean(T/F) // where to pad argument list (left/right) */
$padArgToLeft = (isset($padArgToLeft))?$padArgToLeft:true;

/*On clicking page link which js function need to call*/
$callBackJsFunc=isset($callBackJsFunc)?$callBackJsFunc:'goToSearchPage';


if ( null != $arguments ) {
	if (is_array($arguments)) {
		$args = implode(', ', $arguments);
	}elseif (is_string($arguments)) {
		$args = $arguments;
	}
	if($padArgToLeft){
		$callBackJsFunc = $callBackJsFunc . '(' . $args . ', xxpagexx);';
	}else{
		$callBackJsFunc = $callBackJsFunc . '(xxpagexx, ' . $args . ');';
	}
}else{
	$callBackJsFunc = $callBackJsFunc . '(xxpagexx);';
}

$pagination .= 	FatUtility::getPageString(
					'<li><a href="javascript:void(0);" onclick="' . $callBackJsFunc . '">xxpagexx</a></li>',
					$pageCount, $pageNumber,
					' <li class="selected"><a href="javascript:void(0);">xxpagexx</a></li>',
					' <li><a href="javascript:void(0);">...</a></li> ',
					$linksToDisp,
					' <li class="rewind"><a href="javascript:void(0);" onclick="' . $callBackJsFunc . '"><i class="fa fa-angle-left"></i><i class="fa fa-angle-left"></i></a></li>',
					' <li class="forward"><a href="javascript:void(0);" onclick="' . $callBackJsFunc . '"><i class="fa fa-angle-right"></i><i class="fa fa-angle-right"></i></a></li>',
					' <li class="prev"><a href="javascript:void(0);" onclick="' . $callBackJsFunc . '"><i class="fa fa-angle-left"></i></a></li>',
					' <li class="next"><a href="javascript:void(0);" onclick="' . $callBackJsFunc . '"><i class="fa fa-angle-right"></i></a></li>'
				);

$ul = new HtmlElement(
		'ul',
		array(
			'class' => 'pagination pagination--center',
		),
		$pagination,
		true
	);
echo '<div class="gap"></div>';
echo $ul->getHtml();
?>
