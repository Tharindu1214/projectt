<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="row">
	<div class="col-sm-12"> 
		<h1><?php echo Labels::getLabel('LBL_View_Sales_Report',$adminLangId); ?> <?php echo FatDate::format($orderDate);?></h1>
		<?php echo  $frmSearch->getFormHtml();?>	
	</div>
	<div class="col-sm-12"> 		
		<section class="section">
		<div class="sectionhead">
			<h4><?php echo Labels::getLabel('LBL_Sales_Report',$adminLangId); ?> <?php echo FatDate::format($orderDate);?></h4>			
			<a href="javascript:void(0)" class="themebtn btn-default btn-sm" onClick="exportReport(<?php echo $orderDate;?>)";><?php echo Labels::getLabel('LBL_Export',$adminLangId); ?></a>			
		</div>
		<div class="sectionbody">
			<div class="tablewrap" >
				<div id="listing"> <?php echo Labels::getLabel('LBL_Processing...',$adminLangId); ?></div>
			</div> 
		</div>
		</section>
	</div>		
</div>