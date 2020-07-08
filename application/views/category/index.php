<div id="body" class="body">
 <div class="bg--second pt-3 pb-3">
      <div class="container">
    <div class="section-head section--white--head justify-content-center mb-0">
            <div class="section__heading">
                  <h2 class="mb-0"><?php echo Labels::getLabel('LBL_Shop_By_Categories', $siteLangId);?></h2>
            </div>
        </div> 
    </div>
    </div>
  <section class="section">
    <div class="container">
        <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">    
            <div class="cg-nav-wrapper cg--js">
			<?php
				$featuredCategory = array_slice($categoriesArr, 0, 7);
				$chunkedCategories = array_chunk($featuredCategory,4,1);
				$embedMoreCat = false;
				if(count($categoriesArr)>7)
					$embedMoreCat = true;
				$catCount =1;
				foreach($chunkedCategories as $chunkedCat){

					echo"<ul>";
					foreach($chunkedCat as $category){
					 ?>
						 <li class=""><a class="anchor--js" data-role="anchor--js--link-<?php echo $catCount;?>"  href="javascript:void(0)"> <i class="cg-icon"><img src="<?php echo CommonHelper::generateUrl('category','icon',array($category['prodcat_id'],'1','collection_page'));?>"> </i> <span class="caption"><?php echo $category['prodcat_name']; ?></span> </a></li>
						 <?php
						  $catCount++;
						 if($embedMoreCat && $catCount==8){
							 ?>
							  <li class=""><a class="anchor--js" data-role="anchor--js--link-<?php echo $catCount;?>" href="javascript:void(0)"> <i class="cg-icon">
								<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 viewBox="0 0 28 28" style="enable-background:new 0 0 28 28;" xml:space="preserve">
  <g>
    <path style="fill:#cccccc;" d="M15,18v-4h-4v8l4,0.001V20h8v8h-8v-4H9V8H5V0h8v8h-2v4h4v-2h8v8H15z"/>
  </g>
</svg>
 </i> <span class="caption"><?php echo Labels::getLabel('LBL_More_Categories',$siteLangId); ?></span> </a></li>
							 <?php
						 }

					}
					echo"</ul>";

				} ?>

            </div>
            <span class="gap"></span>
			<?php $this->includeTemplate('category/categories-list.php',array('categoriesArr'=>$categoriesArr),false);?>
    
        </div>
      </div>
    </div>
  </section>	
</div>
