<?php defined('SYSTEM_INIT') or die('Invalid usage');
if (!empty($list) && is_array($list)) {
    foreach ($list as $listItem) { ?>
        <li>
            <h3 class="filterDiv account" href="javascript:void(0);" data-cat-id="<?php echo $listItem['faqcat_id']; ?>" data-id="<?php echo $listItem['faq_id']; ?>"><?php echo $listItem['faq_title']; ?></h3>
            <div class="faqanswer" style="display: none;">
                <p><?php echo $listItem['faq_content']; ?></p>
            </div>
        </li>
        <?php
    }
}
