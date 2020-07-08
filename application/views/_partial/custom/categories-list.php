
  <?php
	$catCount =1;
  foreach($categoriesArr as $category){?>
  
		<h3 class="heading3"> <a href="<?php echo CommonHelper::generateUrl('category','view',array($category['prodcat_id']));?>"><?php echo $category['prodcat_name']; ?></a> </h2>
	
	<?php if(!empty($category['children'])){?>
		
          <div class="site-map-list">

	
		<ul >
		  <?php foreach($category['children'] as $subcat){?>
		  <li><a href="<?php echo CommonHelper::generateUrl('category','view',array($subcat['prodcat_id']));?>"> <?php echo $subcat['prodcat_name']?></a></li>
		  <?php }?>
		</ul>
		</div>
	 
	<?php  
	}?>

  <?php $catCount++;} ?>
