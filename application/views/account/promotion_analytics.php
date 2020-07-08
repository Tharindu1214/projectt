<?php defined('SYSTEM_INIT') or die('Invalid Usage');
$frm->setFormTagAttribute('onSubmit','searchCredits(this); return false;');
$frm->setFormTagAttribute('class', 'form'); 
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 12;

$keyFld = $frm->getField('keyword');
$keyFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Keyword', $siteLangId));

$keyFld = $frm->getField('date_from');
$keyFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_From_Date', $siteLangId));
$keyFld->setWrapperAttribute('class','col-sm-6');
$keyFld->developerTags['col'] = 6;

$keyFld = $frm->getField('date_to');
$keyFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_To_Date', $siteLangId));
$keyFld->setWrapperAttribute('class','col-sm-6');
$keyFld->developerTags['col'] = 6;

$submitBtnFld = $frm->getField('btn_submit');
$submitBtnFld->setFieldTagAttribute('class','btn--block');
$submitBtnFld->setWrapperAttribute('class','col-xs-6');
$submitBtnFld->developerTags['col'] = 3;

$cancelBtnFld = $frm->getField('btn_clear');
$cancelBtnFld->setFieldTagAttribute('class','btn--block');
$cancelBtnFld->setWrapperAttribute('class','col-xs-6');
$cancelBtnFld->developerTags['col'] = 3;
?>
<?php $this->includeTemplate('_partial/dashboardNavigation.php'); ?>
<main id="main-area" class="main" role="main">
 <div class="content-wrapper content-space">
	<div class="content-header row justify-content-between mb-3">
		<div class="col-md-auto">
			<?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
			<h2 class="content-header-title"><?php echo Labels::getLabel('LBL_My_Promotions',$siteLangId) ?></h2>
		</div>
	</div>
	<div class="content-body">
		<div class="cards">
			<div class="cards-header p-4">
				<h5 class="cards-title"><?php echo Labels::getLabel('LBL_Promotion_Analytics',$siteLangId); ?></h5>
				<div class="padding20 fr">  <a href="<?php echo CommonHelper::generateUrl('account', 'promote')?>" class="btn small ">&laquo;&laquo; <?php echo Labels::getLabel('LBL_Back_To_Promotions',$siteLangId) ?></a></div>
			</div>
			<div class="cards-content pl-4 pr-4 ">
				<div class="darkgray-form">
					<div class="tabs-form">
					  <div class="tabz-content">
						<?php echo  str_replace("<br>", " ",$frm->getFormHtml()); ?>
					  </div>
					</div>
				</div>
				   <?php if ($total_records>0):?>
				<div class="tbl-listing">
					<h4><?php echo sprintf(Labels::getLabel('LBL_L_Items_x_to_y_of_z_total',$siteLangId),$start_record,$end_record,$total_records)?></h4>
					<table>
					  <tbody>
						<tr>
						  <th><?php echo Labels::getLabel('LBL_Date',$siteLangId) ?></th>
						  <th><?php echo Labels::getLabel('LBL_Impressions',$siteLangId) ?></th>
						  <th><?php echo Labels::getLabel('LBL_Clicks',$siteLangId) ?></th>
						  <th><?php echo Labels::getLabel('LBL_Orders',$siteLangId) ?></th>
						</tr>
						<?php $cnt=0;  foreach ($arr_listing as $sn=>$row): $sn++;  ?>
						<tr>
						  <td><span class="cellcaption"><?php echo Labels::getLabel('LBL_Date',$siteLangId) ?></span><?php echo FatDate::format($row["lprom_date"]) ?></td>
						  <td class="cellitem"><span class="cellcaption"><?php echo Labels::getLabel('LBL_Impressions',$siteLangId) ?></span><?php echo $row["lprom_impressions"] ?>
							</td>
							<td nowrap="nowrap"><span class="cellcaption"><?php echo Labels::getLabel('LBL_Clicks',$siteLangId) ?></span> 
							<?php echo $row["lprom_clicks"] ?></td>
							<td nowrap="nowrap"><span class="cellcaption"><?php echo Labels::getLabel('LBL_Orders',$siteLangId) ?></span>
							<?php echo $row["lprom_orders"] ?>
						   </td>
						</tr>
						<?php endforeach;?>
					  </tbody>
					</table>
					<?php if ($pages>1):?>
					<div class="pager">
					  <ul>
					  <?php echo getPageString('<li><a href="javascript:void(0)" onclick="listPages(xxpagexx);">xxpagexx</a></li>', $pages, $page,'<li class="active"><a  href="javascript:void(0)">xxpagexx</a></li>', '<li>...</li>');?>
					  </ul>
					</div>
					<?php endif;?>
				</div>
				<?php else: $this->includeTemplate('_partial/no-record-found.php' , array('siteLangId'=>$siteLangId),false); endif;?>
			</div>
		</div>
	</div>
  </div>
</main>
