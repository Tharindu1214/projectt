<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
</div>
	<!--footer start here-->	
	<footer id="footer">
		<p>
		<?php if(CommonHelper::demoUrl()){
				$replacements = array(
					'{YEAR}'=> '&copy; '.date("Y"),
					'{PRODUCT}'=>'<a target="_blank" href="https://yo-kart.com">Yo!Kart</a>',
					'{OWNER}'=> '<a target="_blank" href="https://www.fatbit.com/">FATbit Technologies</a>',
				);
				echo CommonHelper::replaceStringData(Labels::getLabel('LBL_COPYRIGHT_TEXT', $adminLangId), $replacements);
			} else {		 
				echo FatApp::getConfig("CONF_WEBSITE_NAME_".$adminLangId, FatUtility::VAR_STRING, 'Copyright &copy; '.date('Y').' <a href="https://www.fatbit.com/">FATbit.com');?> 
		<?php }
		echo " ".CONF_WEB_APP_VERSION;
		?>
		</p>

	</footer>
	<!--footer start here-->
</div>

<?php
	$alertClass = '';
	if(Message::getInfoCount()>0) $alertClass = 'alert--info';
	elseif (Message::getErrorCount()>0) $alertClass = 'alert--danger';
	elseif (Message::getMessageCount()>0) $alertClass = 'alert--success';
?>

<div class="system_message alert alert--positioned-bottom-center alert--positioned-small <?php echo $alertClass; ?>">
	<div class="close"></div>
	<div class="sysmsgcontent content">
		<?php
		$haveMsg = false;
		if( Message::getMessageCount() || Message::getErrorCount() ){
			$haveMsg = true;
			echo html_entity_decode( Message::getHtml() );
		} ?>
	</div>
</div>

<?php if( $haveMsg ){ ?>
<script type="text/javascript">
	$("document").ready(function(){
		if( CONF_AUTO_CLOSE_SYSTEM_MESSAGES == 1 ){
			var time = CONF_TIME_AUTO_CLOSE_SYSTEM_MESSAGES * 1000;
			setTimeout(function(){
				$.systemMessage.close();
			}, time);
		}
	});
</script>
<?php } ?>

	<!--wrapper end here-->

	<?php if($isAdminLogged){?>
	<!--div class="color_pallete">
		<a href="#" class="pallete_control"><i class="ion-android-settings icon"></i></a>
		<div class="controlwrap">
			<h5>Color Palette</h5>
			<ul class="colorpallets">
				<li class="red"><a href="javascript:void(0)" class="color_red"></a></li>
				<li class="green"><a href="javascript:void(0)" class="color_green"></a></li>
				<li class="yellow"><a href="javascript:void(0)" class="color_yellow"></a></li>
				<li class="orange"><a href="javascript:void(0)" class="color_orange"></a></li>
				<li class="darkblue"><a href="javascript:void(0)" class="color_darkblue"></a></li>
				<li class="darkgrey"><a href="javascript:void(0)" class="color_darkgrey"></a></li>
				<li class="blue"><a href="javascript:void(0)" class="color_blue"></a></li>
				<li class="brown"><a href="javascript:void(0)" class="color_brown"></a></li>
			</ul>
		</div>
	</div-->

	<?php } ?>
	<?php if(CommonHelper::demoUrl()){
		if(FatApp::getConfig('CONF_SITE_TRACKER_CODE',FatUtility::VAR_STRING,'')){
			echo FatApp::getConfig('CONF_SITE_TRACKER_CODE',FatUtility::VAR_STRING,'');
		}
		if (FatApp::getConfig('CONF_AUTO_RESTORE_ON', FatUtility::VAR_INT, 1) && CommonHelper::demoUrl()) { 
			$this->includeTemplate( 'restore-system/page-content.php');
		}
	}
	?>
</body>
</html>
