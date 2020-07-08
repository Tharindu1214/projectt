<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); 
$frm->developerTags['colClassPrefix'] = 'col-lg-';
$frm->developerTags['fld_default_col'] = 12;
?>
<section class="section">
<div class="sectionhead">
   
    <h4><?php echo Labels::getLabel('LBL_Product_Rating_Information',$adminLangId); ?></h4>
</div>
<div class="sectionbody space">
    <div class="border-box border-box--space">

<div class="repeatedrow">
	<div class="rowbody">
		<div class="listview">
			<dl class="list">
				<dt><?php echo Labels::getLabel('LBL_Product_Name',$adminLangId); ?></dt>
				<dd><?php echo $data['product_name'];?></dd>
			</dl>
			<dl class="list">
				<dt><?php echo Labels::getLabel('LBL_Reviewed_By',$adminLangId); ?></dt>
				<dd><?php echo $data['reviewed_by'];?></dd>
			</dl>
			<dl class="list">
				<dt><?php echo Labels::getLabel('LBL_Date',$adminLangId); ?></dt>
				<dd><?php echo FatDate::format($data['spreview_posted_on']);?></dd>
			</dl>
			<?php foreach($ratingData as $rating){?>
			<dl class="list">
				<dt><?php echo $ratingTypeArr[$rating['sprating_rating_type']];?></dt>
				<dd><ul class="rating list-inline">
				  <?php for($j=1;$j<=5;$j++){ ?>	
				  <li class="<?php echo $j<=round($rating["sprating_rating"])?"active":"in-active" ?>">
					<svg xml:space="preserve" enable-background="new 0 0 70 70" viewBox="0 0 70 70" height="18px" width="18px" y="0px" x="0px" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns="http://www.w3.org/2000/svg" id="Layer_1" version="1.1">
					<g><path d="M51,42l5.6,24.6L35,53.6l-21.6,13L19,42L0,25.4l25.1-2.2L35,0l9.9,23.2L70,25.4L51,42z M51,42" fill="<?php echo $j<=round($rating["sprating_rating"])?"#ff3a59":"#474747" ?>" /></g></svg>
					</li>
				   <?php } ?>
				</ul></dd>
			</dl>
			<?php }?>
			<dl class="list">
				<dt><?php echo Labels::getLabel('LBL_Overall_Rating',$adminLangId); ?></dt>
				<dd>
				<ul class="rating list-inline">
				<?php for($j=1;$j<=5;$j++){ ?>	
				  <li class="<?php echo $j<=round($avgRatingData['average_rating'])?"active":"in-active" ?>">
					<svg xml:space="preserve" enable-background="new 0 0 70 70" viewBox="0 0 70 70" height="18px" width="18px" y="0px" x="0px" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns="http://www.w3.org/2000/svg" id="Layer_1" version="1.1">
					<g><path d="M51,42l5.6,24.6L35,53.6l-21.6,13L19,42L0,25.4l25.1-2.2L35,0l9.9,23.2L70,25.4L51,42z M51,42" fill="<?php echo $j<=round($avgRatingData['average_rating'])?"#ff3a59":"#474747" ?>" /></g></svg>
					</li>
				<?php } ?>
				</ul></dd>
			</dl>
			<dl class="list">
				<dt><?php echo Labels::getLabel('LBL_Review_Title',$adminLangId); ?></dt>
				<dd><?php 
				$findKeywordStr = implode('|', $abusiveWords);
				if($findKeywordStr==''){
					echo $data['spreview_title'];
				}else{
					echo preg_replace('/'.$findKeywordStr.'/i', '<span class="highlight">$0</span>', $data['spreview_title']);
				}
				?></dd>
			</dl>
			<dl class="list">
				<dt><?php echo Labels::getLabel('LBL_Review_Comments',$adminLangId); ?></dt>
				<?php if($findKeywordStr==''){ ?>
				<dd><?php echo nl2br($data['spreview_description']);?></dd>
				<?php }else{ ?>
					<dd><?php echo preg_replace('/'.$findKeywordStr.'/i', '<span class="highlight">$0</span>', nl2br($data['spreview_description']));?></dd>
				<?php } ?>
			</dl>				
		</div>		
	</div>
	<?php /* if($data['spreview_status'] == SelProdReview::STATUS_PENDING){ */?>
	<div class="form_horizontal">


	<h3><?php echo Labels::getLabel('LBL_Change_Status',$adminLangId); ?></h3>
</div>
	<div class="rowbody">
		<div class="listview">
			<?php 
			$frm->setFormTagAttribute('class', 'web_form form_horizontal');
			$frm->setFormTagAttribute('onsubmit', 'updateStatus(this); return(false);');
			echo $frm->getFormHtml();?>
		</div>
	</div>	
	<?php /* } */?>
</div>
</div></div></section>