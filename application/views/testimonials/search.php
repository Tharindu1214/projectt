<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
if( !empty($list) ){  ?>
<div class="row">
	<?php foreach( $list as $listItem ){ 
	//CommonHelper::printArray($listItem);
	?>
	 <!-- ***** Testimonials Item Start ***** -->
        <div class="col-lg-4 col-md-6 col-sm-12 h-100">
          <div class="testimonials-item">
            <div class="user">
              <img alt="<?php echo $listItem['testimonial_user_name'];?>" src="<?php echo CommonHelper::generateFullUrl('Image','testimonial',array($listItem['testimonial_id'],0,'THUMB')); ?>" >
            </div>
            <div class="testimonials-content">
              <h3 class="user-name"><?php echo $listItem['testimonial_user_name']; ?></h3>
              
              <div class="txt">
                <p class=""  data-simplebar> <?php echo $listItem['testimonial_text']; ?></p>
              </div>
            </div>
          </div>
        </div>
<?php } ?>
</div>
<?php } else {
	$this->includeTemplate('_partial/no-record-found.php' , array('siteLangId'=>$siteLangId),false);
}