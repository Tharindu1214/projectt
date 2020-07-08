<?php defined('SYSTEM_INIT') or die('Invalid usage'); ?>

<ul class="breadcrumb ">
  <li><a href="<?php echo CommonHelper::generateUrl('') ?>"><?php echo labels::getLabel('LBL_Home',$adminLangId);?></a></li>
  <?php
	if(!empty($this->variables['nodes'])){
		foreach($this->variables['nodes'] as $nodes){?>
			<?php if(!empty($nodes['href'])){?>
					<li><a href="<?php echo $nodes['href'];?>" <?php echo (!empty($nodes['other']))?$nodes['other']:'';?>>
						<?php

						$title= str_replace(' ', '_', $nodes['title']);

						echo Labels::getLabel('LBL_'.$title,$adminLangId);?>

						</a></li>
			<?php }else{?>
					<li><?php

					$title= str_replace(' ', '_', $nodes['title']);


					 echo (isset($nodes['title']))?Labels::getLabel('LBL_'.$title,$adminLangId):'';?></li>
		<?php 	}
		}
	}?>
</ul>
