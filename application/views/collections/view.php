<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div id="body" class="body">
    <section class="bg--second pt-3 pb-3">
		<div class="container">
			<div class="section-head section--white--head justify-content-center mb-0">
				<div class="section__heading">
					<h2 class="mb-0"><?php echo $collection['collection_name']; ?></h2>
				</div>
			</div>
		</div>
	</section>
	<section class="section">
		<div class="container">
			<div id="listing"></div>
            <span class="gap"></span>
            <div id="loadMoreBtnDiv"></div>
		</div>
	</section>
	
</div>
<?php echo $searchForm->getFormHtml();?>
