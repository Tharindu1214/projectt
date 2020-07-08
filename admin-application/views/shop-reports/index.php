<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<script>
var shopId = '<?php echo $shopId; ?>';
</script>
<div class='page'>
	<div class='container container-fluid'>
		<div class="row">
			<div class="col-lg-12 col-md-12 space">
				<div class="page__title">
					<div class="row">
						<div class="col--first col-lg-6">
							<span class="page__icon">
							<i class="ion-android-star"></i></span>
							<h5><?php echo Labels::getLabel('LBL_Manage_Shop_Reports',$adminLangId); ?> </h5>
							<?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>
						</div>
					</div>
				</div>
				<div class="col-sm-12"> 		
					<section class="section">
						<div class="sectionhead">
                            <h4><?php echo Labels::getLabel('LBL_Shop_Reports_Listing',$adminLangId); ?></h4>
							<?php
							$ul = new HtmlElement( "ul",array("class"=>"actions actions--centered") );
							$li = $ul->appendElement("li",array('class'=>'droplink'));

							$li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green','title'=>Labels::getLabel('LBL_Edit',$adminLangId)),'<i class="ion-android-more-horizontal icon"></i>', true);
							$innerDiv=$li->appendElement('div',array('class'=>'dropwrap'));
							$innerUl=$innerDiv->appendElement('ul',array('class'=>'linksvertical'));

							$innerLiBack=$innerUl->appendElement('li');

							$innerLiBack->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Back_to_Shops',$adminLangId),"onclick"=>"window.location.replace('".CommonHelper::generateUrl('Shops')."')"),Labels::getLabel('LBL_Back_to_Shops',$adminLangId), true);
							
							echo $ul->getHtml();
							?>
						</div>
						<div class="sectionbody">
							<div class="tablewrap" >
								<div id="listing"> <?php echo Labels::getLabel('LBL_Processing...',$adminLangId); ?></div>
							</div> 
						</div>
					</section>
				</div>		
			</div>
		</div>
	</div>
</div>
<script>

</script>