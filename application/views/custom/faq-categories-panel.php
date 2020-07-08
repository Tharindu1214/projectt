<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
if(count($listCategories)){
    $faqMainCat = current($listCategories)['faqcat_id'];
}
if (isset($listCategories) && is_array($listCategories)) {
    foreach ($listCategories as $faqCat) { ?>
        <a href="javascript:void(0);" onClick="searchFaqs(<?php echo $faqCat['faqcat_id']; ?>);" id="<?php echo $faqCat['faqcat_id']; ?>" class="<?php echo($faqCat['faqcat_id'] == $faqMainCat ? 'is--active' : '')?>"><?php echo $faqCat['faqcat_name']; ?></a>
        <?php
    }
}
