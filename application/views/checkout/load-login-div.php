<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<div class="selected-panel " id="alreadyLoginDiv">
  <div class="selected-panel-type"><?php echo Labels::getLabel('LBL_Login', $siteLangId); ?></div>
  <div class="selected-panel-data"><?php echo UserAuthentication::getLoggedUserAttribute('user_email'); ?></div>
</div>
