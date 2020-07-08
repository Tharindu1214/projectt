<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div id="body" class="body">
	<?php if($cPage['cpage_layout']==Contentpage::CONTENT_PAGE_LAYOUT1_TYPE) { ?>
	<div class="page-banner" style="background-image:url(<?php echo CommonHelper::generateUrl('image','cpageBackgroundImage', array($cPage['cpage_id'], $siteLangId, '', 0, false),CONF_WEBROOT_URL); ?>);">
		<div class="container">
			<div class="banner-txt">
				<h1><?php echo $cPage['cpage_image_title']; ?></h1>
				<h4><?php echo $cPage['cpage_image_content']; ?></h4>
			</div>
		</div>
    </div> 
	<?php 
	if($blockData){ ?>
		<div class="cms">
		<?php if(isset ($blockData[Contentpage::CONTENT_PAGE_LAYOUT1_BLOCK_1])  && $blockData[Contentpage::CONTENT_PAGE_LAYOUT1_BLOCK_1]['cpblocklang_text']){?>
		  <section class="section">
			<div class="container">
			  <?php echo FatUtility::decodeHtmlEntities($blockData[Contentpage::CONTENT_PAGE_LAYOUT1_BLOCK_1]['cpblocklang_text']);?>
			</div>
		  </section>
		<?php } if(isset ($blockData[Contentpage::CONTENT_PAGE_LAYOUT1_BLOCK_2])  && $blockData[Contentpage::CONTENT_PAGE_LAYOUT1_BLOCK_2]['cpblocklang_text']){?>
		  <section class="section section--gray">
			<div class="container">
			  <?php echo FatUtility::decodeHtmlEntities($blockData[Contentpage::CONTENT_PAGE_LAYOUT1_BLOCK_2]['cpblocklang_text']);?>
			</div>
		  </section>
		<?php } if(isset ($blockData[Contentpage::CONTENT_PAGE_LAYOUT1_BLOCK_3])  && $blockData[Contentpage::CONTENT_PAGE_LAYOUT1_BLOCK_3]['cpblocklang_text']){?>
		  <section class="section bg--second">
			<div class="container">
			  <?php echo FatUtility::decodeHtmlEntities($blockData[Contentpage::CONTENT_PAGE_LAYOUT1_BLOCK_3]['cpblocklang_text']);?>
			</div>
		  </section>
		<?php }if(isset ($blockData[Contentpage::CONTENT_PAGE_LAYOUT1_BLOCK_4])  && $blockData[Contentpage::CONTENT_PAGE_LAYOUT1_BLOCK_4]['cpblocklang_text']){?>
		  <section class="section">
			<div class="container">
			  <?php echo FatUtility::decodeHtmlEntities($blockData[Contentpage::CONTENT_PAGE_LAYOUT1_BLOCK_4]['cpblocklang_text']);?>
			</div>
		  </section>
		<?php }if(isset ($blockData[Contentpage::CONTENT_PAGE_LAYOUT1_BLOCK_5])  &&  $blockData[Contentpage::CONTENT_PAGE_LAYOUT1_BLOCK_5]['cpblocklang_text']){?>
		  <section class="">
			<div class="container">				
			  <?php echo FatUtility::decodeHtmlEntities($blockData[Contentpage::CONTENT_PAGE_LAYOUT1_BLOCK_5]['cpblocklang_text']);?>
			</div>
		  </section>
		<?php } ?>
    
     
      
    </div>
	<?php
	}
	//echo FatUtility::decodeHtmlEntities( $cPage['cpage_content'] ) ?>
	<?php } else { ?>
	<div class="bg--second pt-3 pb-3">
      <div class="container">
        <div class="row align-items-center justify-content-center">
          <div class="col-md-12">               
            <div class="section-head section--white--head section--head--center mb-0">
				<div class="section__heading">
					  <h2><?php echo $cPage['cpage_title']; ?></h2>
					<?php if(!$isAppUser){?>
					<div class="breadcrumbs breadcrumbs--white breadcrumbs--center">
					   <?php $this->includeTemplate('_partial/custom/header-breadcrumb.php'); ?>
					</div>
					<?php }?>
				</div>
			</div>			
        </div>
      </div>
    </div>
    </div>
	<section class="section bg--white">
	  <div class="container">
		<div class="cms">
			<?php echo FatUtility::decodeHtmlEntities( $cPage['cpage_content'] ) ?>
		</div>
	  </div>
	</section>
	<?php } ?>
	<div class="gap"></div>
</div>