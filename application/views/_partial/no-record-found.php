<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="block--empty align--center">
    <img class="block__img" src="<?php echo CONF_WEBROOT_URL; ?>images/empty_item.svg" alt="<?php echo Labels::getLabel('LBL_No_record_found', $siteLangId); ?>" width="80">
    <h4><?php
    if (isset($message)) {
        echo $message;
    } else {
        echo Labels::getLabel('LBL_No_record_found', $siteLangId);
    } ?></h4>
    <?php if (!empty($linkArr)) {
        foreach ($linkArr as $link) {
            $onClick = isset($link['onClick']) ? "onClick='".$link['onClick']."'" : "";
            echo "<a href='".$link['href']."' class='btn btn--primary btn--sm'" .$onClick.  ">".$link['label']."</a>";
        }
    }?>
</div>
