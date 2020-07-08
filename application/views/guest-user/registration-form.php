<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>

<div id="body" class="body bg--gray">
  <section class="section">
    <div class="container">
		<div class="row justify-content-center">
			<div class="col-md-6">
				<div class="box box--white  p-4">
				  <div class="box__cell <?php echo (empty($pageData)) ? '' : '';?>">
					<?php $this->includeTemplate('guest-user/registerationFormTemplate.php', $data,false ); ?>
				  </div>
				</div>
			</div>
			<?php if(!empty($pageData)) { $this->includeTemplate('_partial/GuestUserRightPanel.php', $pageData ,false); } ?>
		</div>
	</div>
  </section>
</div>
