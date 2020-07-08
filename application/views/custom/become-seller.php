<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>

	<section class="section section--slide" style="background-image:url(<?php echo CONF_WEBROOT_URL; ?>images/page-bg.jpg)">
		<div class="slide__text">
			<h2><?php echo Labels::getLabel('LBL_Sell_on_yokart', $siteLangId); ?></h2>
			<a href="<?php echo CommonHelper::generateUrl('Supplier', 'Account'); ?>" class="btn btn--primary btn--h-large"><?php echo Labels::getLabel('LBL_Open_a_shop', $siteLangId); ?></a>
		</div>
		<div class="slide__caption">
			<ul>
				<li><?php echo Labels::getLabel('LBL_More_Customers.', $siteLangId); ?></li>
				<li><?php echo Labels::getLabel('LBL_Higher_Sales.', $siteLangId); ?></li>
				<li><?php echo Labels::getLabel('LBL_One_Location.', $siteLangId); ?></li>
				<li><?php echo Labels::getLabel('LBL_Low_Commission_Fees.', $siteLangId); ?></li>
			</ul>
		</div>
	</section>
   
   
   <?php if( (isset($contentBlocks[Extrapage::BECOME_SELLER_PAGE_BLOCK1]['epage_content']) && $contentBlocks[Extrapage::BECOME_SELLER_PAGE_BLOCK1]['epage_content'] != '') || (isset($contentBlocks[Extrapage::BECOME_SELLER_PAGE_BLOCK2]['epage_content']) && $contentBlocks[Extrapage::BECOME_SELLER_PAGE_BLOCK2]['epage_content'] != '') ){ ?>
	<section class="section section--intro">
		<div class="container container--fixed">
			<?php if( isset($contentBlocks[Extrapage::BECOME_SELLER_PAGE_BLOCK1]['epage_content']) && $contentBlocks[Extrapage::BECOME_SELLER_PAGE_BLOCK1]['epage_content'] != '' ){ ?>
			<div class="threecols">
				<?php echo CommonHelper::renderHtml($contentBlocks[Extrapage::BECOME_SELLER_PAGE_BLOCK1]['epage_content']); ?>
				<?php /* <div class="box box--white box--small">
					<div class="box__content">
						<h2>Fees & Documents</h2>
						<p>All you need is to have a business</p>
					</div>
				</div>
				<div class="box box--white box--large">
					<div class="box__content">
						<img src="<?php echo CONF_WEBROOT_URL; ?>images/icon_user.svg" alt="">
						<h2>Become a Seller</h2>
						<p>Open a shop and have more opportunities</p>
					</div>
				</div>
				<div class="box box--white box--small">
					<div class="box__content">
						<h2>Explore the way</h2>
						<p>How to easily sell your product</p>
					</div>
				</div> */ ?>
			</div>
			<?php } ?>
			
			<?php if( isset($contentBlocks[Extrapage::BECOME_SELLER_PAGE_BLOCK2]['epage_content']) && $contentBlocks[Extrapage::BECOME_SELLER_PAGE_BLOCK2]['epage_content'] != '' ){ ?>
			<div class="row--counter">
				<?php echo CommonHelper::renderHtml($contentBlocks[Extrapage::BECOME_SELLER_PAGE_BLOCK2]['epage_content']); ?>
			</div>
			<?php } ?>
		</div>   
	</section>
	<?php } ?>
	
   
   
   <?php if( (isset($contentBlocks[Extrapage::BECOME_SELLER_PAGE_BLOCK3]['epage_content']) && $contentBlocks[Extrapage::BECOME_SELLER_PAGE_BLOCK3]['epage_content'] != '') || (isset($contentBlocks[Extrapage::BECOME_SELLER_PAGE_BLOCK4]['epage_content']) && $contentBlocks[Extrapage::BECOME_SELLER_PAGE_BLOCK4]['epage_content'] != '')  ){ ?>
	<section class="section section--pattern" style="background-image:url(<?php echo CONF_WEBROOT_URL; ?>images/pattern2.png); background-color:#1689e5;">
		<div class="container container--fixed">
			<div class="row">
				
				<?php if( isset($contentBlocks[Extrapage::BECOME_SELLER_PAGE_BLOCK3]['epage_content']) && $contentBlocks[Extrapage::BECOME_SELLER_PAGE_BLOCK3]['epage_content'] != '' ){ ?>
				<div class="col-md-6">
					<?php echo CommonHelper::renderHtml($contentBlocks[Extrapage::BECOME_SELLER_PAGE_BLOCK3]['epage_content']); ?>
					<?php /* <h2>The Lowest Commission Fees</h2>
					<p>eFalak Marketplace offers the lowest commission fees 
					of major online marketplace and already includes 
					credit card processing fees.<br><br> </p>
					<p class="color--warning">Why wait? Sell in a wide range of categories just 
					waiting to be filled with your products.</p> */ ?>
				</div>
				<?php } ?>
				
				<?php if( isset($contentBlocks[Extrapage::BECOME_SELLER_PAGE_BLOCK4]['epage_content']) && $contentBlocks[Extrapage::BECOME_SELLER_PAGE_BLOCK4]['epage_content'] != '' ){ ?>
				<div class="col-md-6">
					<div class="box box--white box--listing">
						<?php echo CommonHelper::renderHtml($contentBlocks[Extrapage::BECOME_SELLER_PAGE_BLOCK4]['epage_content']); ?>
						
						<?php /* 
						<div class="box__head">
							<h5 class="float--left">Industry</h5>
							<h5 class="float--right">Commission</h5>
						</div>
						<div class="box__body">
							<div class="countlist">
								<div class="countlist__item">
									<span class="float--left">Apparel & Accessories	</span>
									<span class="float--right">14%</span>
								</div>
								<div class="countlist__item">
									<span class="float--left">Appliance</span>
									<span class="float--right">24%</span>
								</div>
								<div class="countlist__item">
									<span class="float--left">Arts & Crafts</span>
									<span class="float--right">33%</span>
								</div>
								<div class="countlist__item">
									<span class="float--left">Auto & Hardware</span>
									<span class="float--right">43%</span>
								</div>
								<div class="countlist__item">
									<span class="float--left">Baby</span>
									<span class="float--right">43%</span>
								</div>
								<div class="countlist__item">
									<span class="float--left">Beauty	</span>
									<span class="float--right">33%</span>
								</div>
								<div class="countlist__item">
									<span class="float--left">Books, Media & Entertainment</span>
									<span class="float--right">63%</span>
								</div>
								<div class="countlist__item">
									<span class="float--left">Camera & Photo	</span>
									<span class="float--right">63%</span>
								</div>
								<div class="countlist__item">
									<span class="float--left">Cell Phone Accessories	</span>
									<span class="float--right">63%</span>
								</div>
							</div>
						</div> */ ?>
					</div>
				</div>
				<?php } ?>
				
			</div>
		</div>
	</section>
	<?php } ?>
	
	
	
	<?php if( isset($contentBlocks[Extrapage::BECOME_SELLER_PAGE_BLOCK5]['epage_content']) && $contentBlocks[Extrapage::BECOME_SELLER_PAGE_BLOCK5]['epage_content'] != '' ){ ?>
	<section class="section section--colcontent">
		<div class="container container--fixed">
			<?php echo CommonHelper::renderHtml($contentBlocks[Extrapage::BECOME_SELLER_PAGE_BLOCK5]['epage_content']); ?>
			<?php /* <h3 class="align--center">Minimum Requirements</h3>
			<div class="row">
				<div class="col-md-6 colcontent">
					<h6>Customer Satisfaction</h6>
					<p>eFalak is obsessed with total customer satisfaction! We go the extra mile to ensure customers are happy with their entire shopping experience and it shows in our online and customer satisfaction ratings. We're looking for sellers who strive for that same level of excellence and who demonstrate it with every sale they make. </p>
				</div>
				<div class="col-md-6 colcontent">
					<h6>Shipping</h6>
					<p>eFalak is known for our speedy order processing and shipping--we ship 100% of our orders within 72 business hours. As a condition of selling on Newegg, our sellers are also expected to ship products within 72 business hours of order confirmation. </p>
				</div>
				<div class="col-md-6 colcontent">
					<h6>Warranty and RMA</h6>
					<p>eFalak standard return policy offers our customers a 30-day refund or replacement period. RMAs are also generally processed within 72 hours of receipt of the product, provided the RMA request conforms to our requirements. </p>
				</div>
				<div class="col-md-6 colcontent">
					<h6>Content</h6>
					<p>Our content expectations for Marketplace sellers are covered in our content policy. All Marketplace sellers are required to adhere to our content policy. The content policy covers subject matter such as erroneous information, offensive content, prohibited items, miscategorization, and item description requirements. </p>
				</div>
			</div> */ ?>
		</div>   
	</section>
	<?php } ?>
	
	
	<!-- Success Stories [-->
	<?php if( $stories ){ ?>
	<section class="section section--slider">
		<div class="container container--fixed">
			<div class="row">
				<div class="table--cell">
					<div class="table__col">
						<?php if( isset($contentBlocks[Extrapage::BECOME_SELLER_PAGE_BLOCK6]['epage_content']) && $contentBlocks[Extrapage::BECOME_SELLER_PAGE_BLOCK6]['epage_content'] != '' ){ ?>
						<div class="box--content-large">
							<?php echo CommonHelper::renderHtml($contentBlocks[Extrapage::BECOME_SELLER_PAGE_BLOCK6]['epage_content']); ?>
							
							<?php /* <h3>Seller Success Stories</h3>
							<p>Earlier, product promotions where restricted to a 
							certain area and the reach was limited. Now with 
							a few clicks you can take your products to more than 
							80 million customers across India. eFalak 
							helps you to expand your business faster. Learn 
							from the seller success stories here.</p> */ ?>
							
						</div>
						<?php } ?>
					</div>
					<div class="table__col">
						<ul class="slider--stories slider--stories-js" dir="<?php echo CommonHelper::getLayoutDirection();?>">
							<?php foreach( $stories as $story ){ ?>
							<li>
								<div class=" box--content">
									<div class="box box--white">
										<p><span class="lessText"><?php echo CommonHelper::truncateCharacters(CommonHelper::renderHtml($story['sstory_content']),200,'','',true);?></span>
										<?php /* if(strlen($story['sstory_content']) > 200) { ?>
										<span class='moreText' hidden><?php echo nl2br($story['sstory_content']);?></span>
										<a class="readMore link--arrow" href="javascript:void(0);"> <?php echo Labels::getLabel('Lbl_SHOW_MORE',$siteLangId) ; ?> </a>
										<?php } */ ?>
										</p>
										<h6>— <?php echo $story['sstory_name']; echo ($story['sstory_site_domain'] != '') ? ', <span>'. $story['sstory_site_domain'].'</span>' : ''; ?> </h6>
									</div>
								</div>
							</li>
							<?php } ?>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</section>
	<?php } ?>
	<!-- ] -->
   
   <?php if(!empty($faqs)){ ?>
	<section class="section section--faqs">
		<div class="container container--fixed">
			<h3 class="align--center"><?php echo Labels::getLabel('Lbl_Frequently_Asked_Questions',$siteLangId); ?></h3>
			<div class="row">
				<div class="container--faqs">
				<?php $this->includeTemplate('_partial/faq-list.php' , array('list'=>$faqs,'siteLangId'=>$siteLangId , 'showViewAllButton' => true),false); ?>
				</div>
				<span class="gap"></span>
				<div class="align--center">
	<a href="<?php echo CommonHelper::generateUrl('Custom','faq'); ?>" class = "btn btn--primary btn--h-large"><?php echo Labels::getLabel( 'LBL_View_All', $siteLangId)?></a>
</div>
			</div>
		</div>
	</section>
   <?php } ?>

<?php if( isset($contentBlocks[Extrapage::BECOME_SELLER_PAGE_BLOCK7]['epage_content']) && $contentBlocks[Extrapage::BECOME_SELLER_PAGE_BLOCK7]['epage_content'] != '' ){ ?>
<section class="section section--parallax align--center" style="background-image:url(<?php echo CONF_WEBROOT_URL; ?>images/section-bg.jpg);">
	<div class="container container--fixed">
	   <div class="row">
			<div class="section__content">
			<?php echo CommonHelper::renderHtml($contentBlocks[Extrapage::BECOME_SELLER_PAGE_BLOCK7]['epage_content']); ?>
			<?php /* <h2>Ready to start? </h2>
			<h4>The following are required to enroll.</h4>
			<h6>W-9.	 Bank account information.  Proof of any applicable insurance.</h6>
			<span class="gap"></span>
			<a class="btn btn--primary btn--h-large" href="#">Get Started</a> */ ?>
			</div>
	   </div>
	</div>
</section>
<?php } ?>

  
<!--<script src="js/commom_function.js"></script>
<script src="js/slick.min.js"></script>-->

<script type="text/javascript">
/* home page main slider */ 
$('.slider--stories-js').slick({
    dots: false,
    arrows:false,
    autoplay:true, 
    infinite:true,
    slidesToShow:1,
    slidesToScroll: 1,  
    centerMode: true,
    centerPadding: '',
    pauseOnHover: false,
    adaptiveHeight: true,
     responsive: [
    {
      breakpoint:1050,
      settings: {
        slidesToShow: 1,
       centerMode: true,
        centerPadding: '30px',
      }
    },
    {
      breakpoint:767,
      settings: {
        slidesToShow: 1,
        centerMode: true,
        centerPadding: '50px',
      }
    } ,
    {
      breakpoint:400,
      settings: {
        slidesToShow: 1,
          centerMode: true,
        centerPadding: '10px',
      }
    } 
      
  ]  
});
var $linkMoreText = '<?php echo Labels::getLabel('Lbl_SHOW_MORE',$siteLangId); ?>';
var $linkLessText = '<?php echo Labels::getLabel('Lbl_SHOW_LESS',$siteLangId); ?>';
 /******** for faq accordians  ****************/ 

$('.accordians__trigger-js').click(function(){
  if($(this).hasClass('is-active')){
      $(this).removeClass('is-active');
      $(this).siblings('.accordians__target-js').slideUp();
      return false;
  }
 $('.accordians__trigger-js').removeClass('is-active');
 $(this).addClass("is-active");
 $('.accordians__target-js').slideUp();
 $(this).siblings('.accordians__target-js').slideDown();
});


</script>
</body>
</html>