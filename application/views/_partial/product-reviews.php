<?php defined('SYSTEM_INIT') or die('Invalid usage');
/* reviews processing */
$totReviews = FatUtility::int($reviews['totReviews']);
$avgRating = FatUtility::convertToType($reviews['prod_rating'], FatUtility::VAR_FLOAT);
$rated_1 = FatUtility::int($reviews['rated_1']);
$rated_2 = FatUtility::int($reviews['rated_2']);
$rated_3 = FatUtility::int($reviews['rated_3']);
$rated_4 = FatUtility::int($reviews['rated_4']);
$rated_5 = FatUtility::int($reviews['rated_5']);

$pixelToFillRight = $avgRating/5*160;
$pixelToFillRight = FatUtility::convertToType($pixelToFillRight, FatUtility::VAR_FLOAT);

$rate_5_width = $rate_4_width =$rate_3_width= $rate_2_width= $rate_1_width = 0;

if ($totReviews) {
    $rate_5_width = round(FatUtility::convertToType($rated_5/$totReviews*100, FatUtility::VAR_FLOAT), 2);
    $rate_4_width = round(FatUtility::convertToType($rated_4/$totReviews*100, FatUtility::VAR_FLOAT), 2);
    $rate_3_width = round(FatUtility::convertToType($rated_3/$totReviews*100, FatUtility::VAR_FLOAT), 2);
    $rate_2_width = round(FatUtility::convertToType($rated_2/$totReviews*100, FatUtility::VAR_FLOAT), 2);
    $rate_1_width = round(FatUtility::convertToType($rated_1/$totReviews*100, FatUtility::VAR_FLOAT), 2);
}
?>
<div class="row justify-content-between">
    <div class="col-md-5">
        <div class="section-head">
            <div class="section__heading">
                <h2><?php echo Labels::getLabel('LBl_Rating_&_Reviews', $siteLangId); ?></h2>
            </div>
        </div>
        <div class="products__rating"> <i class="icn"><svg class="svg">
            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#star-yellow" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#star-yellow"></use></svg></i> <span class="rate"><?php echo round($avgRating, 1); ?><span></span></span>
        </div>
        <p class="small"><?php echo Labels::getLabel('Lbl_Based_on', $siteLangId) ,' ', $totReviews ,' ',Labels::getLabel('Lbl_ratings', $siteLangId);?></p>
    </div>
    <?php $this->includeTemplate('_partial/product-overall-ratings.php', array('reviews'=>$reviews,'siteLangId'=>$siteLangId,'product_id'=>$product_id), false); ?>
</div>
<?php if($canSubmitFeedback || $totReviews > 0) { ?>
<div class="row mt-5">
    <?php if ($canSubmitFeedback) { ?>
    <div class="<?php echo ($totReviews > 0) ? 'col-md-3' : 'col-md-12 align--center'; ?>">
        <a onClick="rateAndReviewProduct(<?php echo $product_id; ?>)" href="javascript:void(0)" class="btn btn--primary <?php echo ($totReviews > 0) ? 'btn--block' : '' ; ?>"><?php echo Labels::getLabel('Lbl_Add_Review', $siteLangId); ?></a>
    </div>
    <?php } ?>
    <?php if ($totReviews > 0) { ?>
    <div class="col-md-3 <?php echo ($canSubmitFeedback) ? '' : 'align--center'; ?>">
        <div class="js-wrap-drop-reviews wrap-drop wrap-drop--first">
            <span><?php echo Labels::getLabel('Lbl_Most_Recent', $siteLangId); ?></span>
            <ul class="drop">
                <li class="selected"><a href="javascript:void(0);" data-sort='most_recent' onclick="getSortedReviews(this);return false;"><?php echo Labels::getLabel('Lbl_Most_Recent', $siteLangId); ?></a></li>
                <li class="selected"><a href="javascript:void(0);" data-sort='most_helpful' onclick="getSortedReviews(this);return false;"><?php echo Labels::getLabel('Lbl_Most_Helpful', $siteLangId); ?></a></li>
            </ul>
        </div>
    </div>
    <?php } ?>
</div>
<?php } ?>

<div class="listing__all"></div>
<div id="loadMoreReviewsBtnDiv" class="align--center"></div>

<script>
var $linkMoreText = '<?php echo Labels::getLabel('Lbl_SHOW_MORE', $siteLangId); ?>';
var $linkLessText = '<?php echo Labels::getLabel('Lbl_SHOW_LESS', $siteLangId); ?>';
$('#itemRatings div.progress__fill').css({'clip':'rect(0px, <?php echo $pixelToFillRight; ?>px, 160px, 0px)'});

$(document).ready(function(){
    function DropDown(el) {
        this.dd = el;
        this.placeholder = this.dd.children('span');
        this.opts = this.dd.find('ul.drop li');
        this.val = '';
        this.index = -1;
        this.initEvents();
    }

    DropDown.prototype = {
        initEvents: function () {
            var obj = this;
            obj.dd.on('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).toggleClass('active');
            });
            obj.opts.on('click', function () {
                var opt = $(this);
                obj.val = opt.text();
                obj.index = opt.index();
                obj.placeholder.text(obj.val);
                opt.siblings().removeClass('selected');
                opt.filter(':contains("' + obj.val + '")').addClass('selected');
            }).change();
        },
        getValue: function () {
            return this.val;
        },
        getIndex: function () {
            return this.index;
        }
    };

    $(function () {
        // create new variable for each menu
        var dd1 = new DropDown($('.js-wrap-drop-reviews'));
        $(document).click(function () {
            // close menu on document click
            $('.wrap-drop').removeClass('active');
        });
    });
});
</script>
