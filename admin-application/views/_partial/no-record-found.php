<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="block--empty align--center">
    <h2><?php if (isset($message)) {
        echo $message;
        } else {
            echo Labels::getLabel('LBL_No_record_found', $adminLangId);
        } ?></h2>
    <div class="action">
        <?php if (!empty($linkArr)) {
            foreach ($linkArr as $link) {
                $onClick = isset($link['onClick']) ? "onClick='".$link['onClick']."'" : "";
                echo "<a href='".$link['href']."' class='themebtn btn-default btn-sm'" .$onClick.  ">".$link['label']."</a>";
            }
        }?>
    </div>
</div>
