<h2><?php echo Labels::getLabel('Lbl_Web_Poll',$siteLangId); ?></h2>
<div class="box box--white box--polling">
   <div class="box__head">
	   <h6><?php echo $pollQuest['polling_question']; ?></h6>
   </div>
   <div class="box__body">
	   <?php
	   $pollForm->addFormTagAttribute('onsubmit','submitPoll(this);return false;');
	   $pollForm->addFormTagAttribute('class','form');
	   $pollForm->developerTags['colClassPrefix'] = 'col-md-';
	   $pollForm->developerTags['fld_default_col'] = '12';
	   echo $pollForm->getFormHtml(); ?>
	   <div role="alert" class="alert alert--success alert--positoned poll--msg-js" style="display:none;">
			<a href="javascript:void(0)" onclick='$(this).parent().hide();' class="close poll--link-js"></a>
			<div class="alert__content">
				<h4><?php echo Labels::getLabel('MSG_Congratulations',$siteLangId); ?></h4>
				<p><?php echo Labels::getLabel('MSG_Poll_successfully_submitted',$siteLangId); ?></p>
			</div>
	   </div>

	   <!--<div role="alert" class="alert alert--info alert--positoned poll--results-js" style="">
		   <a href="javascript:void(0)" onclick='$(this).parent().hide();' class="close view--link-js"></a>
		   <div class="alert__content">
				<h4>Polling Result</h4>
				<ul>
					<li>Yes <strong>60%</strong></li>
					<li>No <strong>30%</strong></li>
					<li>May be <strong>10%</strong></li>
				</ul>
			</div>
	   </div>-->
	</div>
</div>