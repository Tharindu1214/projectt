<?php defined('SYSTEM_INIT') or die('Invalid Usage.');  ?>

<div id="body" class="body">
 <div class="bg--second pt-3 pb-3">
      <div class="container">
        <div class="row align-items-center justify-content-center">
          <div class="col-md-8">               
               <div class="section-head section--white--head justify-content-center mb-0">
            <div class="section__heading">
                  <h2><?php echo Labels::getLabel('LBL_All_Top_Brands',$siteLangId);?></h2>
           <div class="breadcrumbs breadcrumbs--white  breadcrumbs--center">
		<?php $this->includeTemplate('_partial/custom/header-breadcrumb.php'); ?>
      </div>
            </div>
        </div> 
        </div>
      </div>
    </div>
    </div>    
  <section class="section">
    <div class="container">      
      <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">    
            <div class="cg-main">
				<?php if(!empty($allBrands)){ $firstCharacter = '';
						foreach($allBrands as $brands){

						/* if($layoutDirection == 'rtl'){
							$str = substr(strtolower($brands['brand_name']), -1);
						}else{
							$str = substr(strtolower($brands['brand_name']), 0, 1);
						} */
						$str = substr(strtolower($brands['brand_name']), 0, 1);

						if(is_numeric($str)){
							$str = '0-9';
						}

						if($str != $firstCharacter){
							if($firstCharacter!=''){ echo "</ul></div>"; }
							$firstCharacter = $str;
				?>
              <div class="item">
                <h6><?php echo $firstCharacter;?></h6>
                <ul class="listing--onefifth">
                  <?php } ?>
                  <li><a href="<?php echo CommonHelper::generateUrl('Brands','view',array($brands['brand_id']));?>"><?php echo $brands['brand_name'];?></a></li>
                  <?php } ?>
                </ul>
              </div>
              <?php }?>
             
        </div>
      </div>
    </div>
  </section>
</div>
 
