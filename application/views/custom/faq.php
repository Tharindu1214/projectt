<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
$frm->setFormTagAttribute('id', 'frmSearchFaqs');
$frm->setFormTagAttribute('onSubmit', 'searchFaqs(this);return false;');
$frm->getField('question')->setFieldTagAttribute('placeholder', Labels::getLabel('Lbl_Search', $siteLangId));
$frm->getField('question')->setFieldTagAttribute('class', "faq-input no-focus");
?>
<div id="body" class="body">
    <div class="bg--second pt-5 pb-5">
        <div class="container">
            <div class="row align-items-center justify-content-center">
                <div class="col-md-6">
                    <div class="section-head section--white--head section--head--center mb-0">
                        <div class="section__heading">
                            <h2><?php echo Labels::getLabel('LBL_Frequently_Asked_Questions', $siteLangId);?></h2>
                        </div>
                    </div>
                    <div class="faqsearch">
                        <?php
                            echo $frm->getFormTag();
                            echo $frm->getFieldHtml('question');
                        ?>
                        </form>
                        <?php echo $frm->getExternalJs(); ?>
                        <!-- <input class="faq-input no-focus" type="text" placeholder="Search" /> -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <section class="section bg--white">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <?php if ($recordCount > 0) { ?>
                    <div class="faq-filters mb-4" id="categoryPanel"></div>
                    <?php } ?>
                    <ul class="faqlist" id="listing"></ul>
                </div>
            </div>
        </div>
    </section>
<script>
    var $linkMoreText = '<?php echo Labels::getLabel('Lbl_SHOW_MORE', $siteLangId); ?>';
    var $linkLessText = '<?php echo Labels::getLabel('Lbl_SHOW_LESS', $siteLangId); ?>';
</script>
<script>
    var clics = 0;
    $(document).ready(function() {
        $('.faqanswer').hide();
        $('#faqcloseall').hide();
        $(document).on("click", 'h3', function() {
            $(this).next('.faqanswer').toggle(function() {
                $(this).next('.faqanswer');
            }, function() {
                $(this).next('.faqanswer').fadeIn('fast');
            });
            if ($(this).hasClass('faqclose')) {
                $(this).removeClass('faqclose');
            } else {
                $(this).addClass('faqclose');
            };
            if ($('.faqclose').length >= 3) {
                $('#faqcloseall').fadeIn('fast');
            } else {
                $('#faqcloseall').hide();
                var yolo = $('.faqclose').length
                console.log(yolo);
            }
        }); //Close Function Click
    }); //Close Function Ready
    $(document).on("click", '#faqcloseall', function() {
        $('.faqanswer').fadeOut(200);
        $('h3').removeClass('faqclose');
        $('#faqcloseall').fadeOut('fast');
    });
    //search box
    $(function() {
        $(document).on("keyup", '.faq-input', function() {
            // Get user input from search box
            var filter_text = $(this).val();
            var replaceWith = "<span class='js--highlightText'>"+filter_text+"</span>";
            var re = new RegExp(filter_text, 'g');

            $('.faqlist h3').each(function() {
                if ('' !== filter_text) {
                    if ($(this).text().toLowerCase().indexOf(filter_text) >= 0) {
                        var content = $(this).text();
                        $(this).siblings( ".faqanswer" ).slideDown();
                        $(this).html(content.replace(re, replaceWith));
                    } else {
                        $(this).text($(this).text());
                        $(this).siblings( ".faqanswer" ).slideUp();
                    }
                } else {
                    $(this).text($(this).text());
                    $('.faqlist h3').siblings( ".faqanswer" ).slideUp();
                }
            })
        });
    });
</script>
