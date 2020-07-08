<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php echo Labels::getLabel('LBL_All_categories_with', $siteLangId);?>: <?php echo $keyword;?>
<div class="row">
<?php if (!empty($rootCategories)) {
    $result = array();
    $str = "<div class='slider-item col-lg-6 col-md-6 col-sm-6 col-xs-12 categories-devider'><div class='box-border box-categories' data-simplebar>";
    $str.= "<ul>";
    if (!empty($rootCategories)) {
        foreach ($rootCategories as $category) {
            //$totalRecord =  $category['totalRecord'];
            $totalRecord =  $childCountArr[$category['prodcat_id']]['total_child_count'];
            $class = '';
            if ($prodRootCatCode == $category['prodrootcat_code']) {
                $class = 'active';
            }
            if ($category['prodcat_parent'] == 0 && $totalRecord == 1) {
                $function = 'customCatalogProductForm(0,'.$category['prodcat_id'].')';
            } else {
                $function = 'categorySearchByCode(\''.$category['prodrootcat_code'].'\')';
            }
            $str.= '<li class="'.$class.'" onClick="'.$function.'"><a class="selectCategory" href="javascript:void(0)">'.strip_tags($category['prodcat_name']).'('.$totalRecord.')</a> </li>';
        }
    }
    $str.= "</ul>";
    $str.= "</div></div>";

    $str.= "<div class='slider-item col-lg-6 col-md-6 col-sm-6 col-xs-12 categories-devider'><div class='box-border box-categories scrollbar'>";
    $str.= "<ul>";
    if (!empty($childCategories[$prodRootCatCode])) {
        foreach ($childCategories[$prodRootCatCode] as $catId=>$category) {
            $str.= "<li onClick='customCatalogProductForm(0,".$catId.")'><a class='selectCategory' href='javascript:void(0)'>".strip_tags($category['prodcat_name'])."</a></li>
            <li>".html_entity_decode($category['structure'], ENT_QUOTES, 'utf-8')."</li>
            ";
        }
    }
    $str.= "</ul>";
    $str.= "</div></div>";
    echo $str;
} else {
    $this->includeTemplate('_partial/no-record-found.php', array('siteLangId'=>$siteLangId), false);
}
?>
</div>
