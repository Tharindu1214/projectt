<?php foreach($categories as $category){?>
	<div class="cell__item">
		<div class="cell__list">
			<div class="cell__head"><a href="#"><?php echo $category['prodcat_name'];?> <span>(25)</span></a></div>
			<?php if(!empty($category['children'])){?>
			<div class="cell__body">
				<ul class="list__vertical">
					<?php foreach($category['children'] as $subcat){?>
					<li><a href="#"> <?php echo $subcat['prodcat_name']?></a></li>
					<?php }?>
				</ul>
			</div>
			<?php }?>
		</div>
	</div>
<?php }?>
