<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?> <?php $this->includeTemplate('_partial/affiliate/affiliateDashboardNavigation.php');
$yesNoArr = applicationConstants::getYesNoArr($siteLangId);
$sharingFrm->addFormTagAttribute('class', 'form');
$sharingFrm->addFormTagAttribute('onsubmit', 'setUpMailAffiliateSharing(this);return false;');
$sharingFrm->developerTags['colClassPrefix'] = 'col-xs-12 col-md-';
$sharingFrm->developerTags['fld_default_col'] = 12;
?>
<main id="main-area" class="main" role="main">
    <div class="content-wrapper content-space">
        <div class="content-header justify-content-between row mb-4">
            <div class="content-header-left col-md-auto"> <?php $this->includeTemplate('_partial/dashboardTop.php'); ?> <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_Affiliate', $siteLangId); ?></h2>
            </div>
        </div>
        <div class="content-body">
            <div class="js-widget-scroll widget-scroll">
                <div class="widget widget-stats">
                    <a href="<?php echo CommonHelper::generateUrl('Account', 'credits'); ?>">
                        <div class="cards">
                            <div class="cards-header p-4">
                                <h5 class="cards-title"><?php echo Labels::getLabel('LBL_Credits', $siteLangId);?></h5>
                                <i class="icn"><svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL;?>images/retina/sprite.svg#credits" href="<?php echo CONF_WEBROOT_URL;?>images/retina/sprite.svg#Credits"></use>
                                    </svg>
                                </i>
                            </div>
                            <div class="cards-content pl-4 pr-4 ">
                                <div class="stats">
                                    <div class="stats-number">
                                        <ul>
                                            <li>
                                                <span class="total"><?php echo Labels::getLabel('LBL_Total', $siteLangId);?></span>
                                                <span class="total-numbers"><?php echo CommonHelper::displayMoneyFormat($userBalance);?></span>
                                            </li>
                                            <li>
                                                <span class="total"><?php echo Labels::getLabel('LBL_Credits_earned_today', $siteLangId);?></span>
                                                <span class="total-numbers"><?php echo CommonHelper::displayMoneyFormat($txnsSummary['total_earned']);?></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="widget widget-stats">
                    <a href="<?php echo CommonHelper::generateUrl('Account', 'credits'); ?>">
                        <div class="cards">
                            <div class="cards-header p-4">
                                <h5 class="cards-title"><?php echo Labels::getLabel('LBL_Revenue', $siteLangId);?></h5>
                                <i class="icn">
                                    <svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL;?>images/retina/sprite.svg#revenue" href="<?php echo CONF_WEBROOT_URL;?>images/retina/sprite.svg#revenue"></use>
                                    </svg>
                                </i>
                            </div>
                            <div class="cards-content pl-4 pr-4 ">
                                <div class="stats">
                                    <div class="stats-number">
                                        <ul>
                                            <li>
                                                <span class="total"><?php echo Labels::getLabel('LBL_Total_Revenue', $siteLangId);?></span>
                                                <span class="total-numbers"><?php echo CommonHelper::displayMoneyFormat($userRevenue);?></span>
                                            </li>
                                            <li>
                                                <span class="total"><?php echo Labels::getLabel('LBL_Today_Revenue', $siteLangId);?></span>
                                                <span class="total-numbers"><?php echo CommonHelper::displayMoneyFormat($todayRevenue);?></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="widget widget-stats">
                    <div class="cards d-flex -direction-col">
                        <div class="cards-header p-4">
                            <h5 class="cards-title"><?php echo Labels::getLabel('LBL_Share_and_earn_commission_on_every_purchase', $siteLangId)?></h5>
                        </div>
                        <div class="cards-content p-4 pr-4 ">
                            <div class="stats">
                                <a href="javascript:void(0)" class="-inline-link" title="<?php echo $affiliateTrackingUrl; ?>" onclick="copy($(this))"><?php echo Labels::getLabel('LBL_Click_to_copy', $siteLangId)?></a>
                            </div>
                        </div>
                    </div>
                </div> <?php if (!empty(FatApp::getConfig("CONF_FACEBOOK_APP_ID")) && !empty(FatApp::getConfig("CONF_FACEBOOK_APP_SECRET"))) {
    ?> <div class="widget widget-stats">
                    <a id="facebook_btn" href="javascript:void(0);" class="box--share box--share-fb"> <i class="fa fa-facebook"></i>
                        <h5><?php echo Labels::getLabel('L_Share_on', $siteLangId)?></h5>
                        <h2><?php echo Labels::getLabel('L_Facebook', $siteLangId)?></h2>
                        <p> <?php echo sprintf(Labels::getLabel('L_Post_your_wall_facebook', $siteLangId), '<strong>'.Labels::getLabel('L_Facebook', $siteLangId).'</strong>')?> </p>
                        <span class="ajax_message thanks-msg" id="fb_ajax"></span>
                    </a>
                </div> <?php
} ?> <?php if (false !== $twitterUrl) {
        ?> <div class="widget widget-stats">
                    <a class="box--share box--share-tw" id="twitter_btn" href="javascript:void(0);"> <i class="fa fa-twitter"></i>
                        <h5><?php echo Labels::getLabel('L_Share_on', $siteLangId)?></h5>
                        <h2><?php echo Labels::getLabel('L_Twitter', $siteLangId)?></h2>
                        <p> <?php echo sprintf(Labels::getLabel('L_Send_a_tweet_followers', $siteLangId), '<strong>'.Labels::getLabel('L_Tweet', $siteLangId).'</strong>')?> </p>
                        <span class="ajax_message thanks-msg" id="twitter_ajax"></span>
                    </a>
                </div> <?php
    } ?> <div class="widget widget-stats">
                    <a class="showbutton box--share box--share-mail" href="javascript:void(0);"> <i class="fa fa-envelope"></i>
                        <h5><?php echo Labels::getLabel('L_Share_on', $siteLangId)?></h5>
                        <h2><?php echo Labels::getLabel('L_Email', $siteLangId)?></h2>
                        <p> <?php echo Labels::getLabel('L_Email', $siteLangId)?> <?php echo Labels::getLabel('L_Your_friend_tell_them_about_yourself', $siteLangId)?> </p>
                        <span class="ajax_message thanks-msg"></span>
                    </a>
                </div>
            </div>
            <!-- <div class="row">
            <div class="col-lg-6 col-md-12">
                <div class="cards">
                    <div class="cards-header p-4">
                        <h5 class="cards-title "><?php echo Labels::getLabel('LBL_Information', $siteLangId);?></h5>
                        <div class="action">
                            <a href="<?php echo CommonHelper::generateUrl('account', 'profileInfo');?>" class="link"><?php echo Labels::getLabel('LBL_Edit', $siteLangId);?>  <i class="fa fa-pencil"></i></a>
                        </div>
                    </div>
                    <div class="cards-content pl-4 pr-4 ">
                        <div class="tabs tabs--small   tabs--scroll clearfix setactive-js">
                            <ul>
                                <li class="is-active"><a href="javascript:void(0);" onClick="personalInfo(this)"><?php echo Labels::getLabel('LBL_Personal', $siteLangId); ?></a></li>
                                <li><a href="javascript:void(0);" onClick="addressInfo(this)"><?php echo Labels::getLabel('LBL_Address_Information', $siteLangId); ?></a></li>
                            </ul>
                        </div>
                        <div class="tabs__content" id="tabListing"><?php echo Labels::getlabel('LBL_loading..', $siteLangId);?></div>
                    </div>
                </div>
            </div>
        </div> -->
            <div class="row mb-3 borderwrap showwrap" style="display:none;">
                <div class="col-lg-12 col-md-12">
                    <div class="cards">
                        <div class="cards-header p-4">
                            <h4><?php echo Labels::getLabel('L_Invite_friends_through_email', $siteLangId)?></h4> <?php echo $sharingFrm->getFormHtml(); ?> <span class="ajax_message" id="custom_ajax"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row ">
                <div class="col-lg-6 col-md-12 mb-4">
                    <div class="cards">
                        <div class="cards-header p-4">
                            <h5 class="cards-title "><?php echo Labels::getLabel('LBL_Referred_by_me', $siteLangId);?></h5> <?php if (count($user_listing) > 0) {
        ?> <div class="action">
                                <a href="<?php echo CommonHelper::generateUrl('affiliate', 'referredByMe'); ?>" class="link"><?php echo Labels::getLabel('Lbl_View_All', $siteLangId); ?></a>
                            </div> <?php
    } ?>
                        </div>
                        <div class="cards-content pl-4 pr-4 ">
                            <table class="table table--orders js-scrollable">
                                <tbody>
                                    <tr class="">
                                        <th width="40%"><?php echo Labels::getLabel('LBL_User_Detail', $siteLangId);?></th>
                                        <th width="30%"><?php echo Labels::getLabel('Lbl_Registered_on', $siteLangId);?></th>
                                        <th width="10%"><?php echo Labels::getLabel('LBL_Active', $siteLangId);?></th>
                                        <th width="20%"><?php echo Labels::getLabel('LBL_Verified', $siteLangId);?></th>
                                    </tr> <?php if (count($user_listing) > 0) {
        foreach ($user_listing as $row) {
            ?> <tr>
                                        <td>
                                            <div class="item__description">
                                                <div class="item__title"> <?php if ($row['user_name'] != '') {
                echo $row['user_name'];
            } ?> </div>
                                                <div class="item__brand"> <?php echo $row['credential_email']; ?> </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="item__description">
                                                <div class="item__date" title="<?php echo Labels::getLabel('Lbl_Registered_on', $siteLangId)?>"><?php echo FatDate::format($row['user_regdate']); ?></div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="user__status"> <?php
                                                        echo $str = isset($row['credential_active']) ? $yesNoArr[$row['credential_active']] : 'N/A'; ?> </div>
                                        </td>
                                        <td>
                                            <div class="user__verified"> <?php
                                                        echo $str = isset($row['credential_verified']) ? $yesNoArr[$row['credential_verified']] : 'N/A'; ?> </div>
                                        </td>
                                    </tr> <?php
        }
    } else {
        ?> <tr>
                                        <td colspan="3"> <?php $this->includeTemplate('_partial/no-record-found.php', array('siteLangId'=>$siteLangId), false); ?> </td>
                                    </tr> <?php
    } ?>
                                </tbody>
                                <div class="scroll-hint-icon-wrap" data-target="scrollable-icon">
                                    <span class="scroll-hint-icon">
                                        <div class="scroll-hint-text"><?php echo Labels::getLabel('LBL_Scrollable', $siteLangId);?></div>
                                    </span>
                                </div>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-12 mb-4">
                    <div class="cards">
                        <div class="cards-header p-4">
                            <h5 class="cards-title "><?php echo Labels::getLabel('LBL_Transaction_History', $siteLangId);?></h5> <?php if (count($transactions) > 0) {
        ?> <div class="action">
                                <a href="<?php echo CommonHelper::generateUrl('Account', 'credits'); ?>" class="link"><?php echo Labels::getLabel('Lbl_View_All', $siteLangId); ?></a>
                            </div> <?php
    } ?>
                        </div>
                        <div class="cards-content pl-4 pr-4 ">
                            <table class="table table--orders js-scrollable ">
                                <tbody>
                                    <tr class="">
                                        <th width="30%"><?php echo Labels::getLabel('LBL_Txn._Detail', $siteLangId);?></th>
                                        <th width="30%"><?php echo Labels::getLabel('LBL_Type', $siteLangId);?></th>
                                        <th width="10%"><?php echo Labels::getLabel('LBL_Balance', $siteLangId);?></th>
                                        <th width="30%"><?php echo Labels::getLabel('LBL_Status', $siteLangId);?></th>
                                    </tr> <?php if (count($transactions) > 0) {
        foreach ($transactions as $row) {
            ?> <tr>
                                        <td>
                                            <div class="item__description">
                                                <div class="item__date"><?php echo FatDate::format($row['utxn_date']); ?></div>
                                                <div class="item__title" title="<?php echo Labels::getLabel('Lbl_Txn._Id', $siteLangId)?>"> <?php echo Transactions::formatTransactionNumber($row['utxn_id']); ?> </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="txn__type">
                                                <div class="txn__credit"> <?php echo Labels::getLabel('Lbl_Credit', $siteLangId)?>: <?php echo CommonHelper::displayMoneyFormat($row['utxn_credit']); ?> </div>
                                                <div class="txn__debit"> <?php echo Labels::getLabel('Lbl_Debit', $siteLangId)?>: <?php echo CommonHelper::displayMoneyFormat($row['utxn_debit']); ?> </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="txn__balance"> <?php echo CommonHelper::displayMoneyFormat($row['balance']); ?> </div>
                                        </td>
                                        <td>
                                            <div class="txn__status"> <?php echo $txnStatusArr[$row['utxn_status']]; ?> </div>
                                        </td>
                                    </tr> <?php
        }
    } else {
        ?> <tr>
                                        <td colspan="4"> <?php $this->includeTemplate('_partial/no-record-found.php', array('siteLangId'=>$siteLangId), false); ?> </td>
                                    </tr> <?php
    } ?>
                                </tbody>
                                <div class="scroll-hint-icon-wrap" data-target="scrollable-icon">
                                    <span class="scroll-hint-icon">
                                        <div class="scroll-hint-text"><?php echo Labels::getLabel('LBL_Scrollable', $siteLangId);?></div>
                                    </span>
                                </div>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<script type="text/javascript">
    (function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s);
        js.id = id;
        js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=<?php echo FatApp::getConfig("CONF_FACEBOOK_APP_ID", FatUtility::VAR_STRING, ''); ?>";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));

    function facebook_redirect(response_token) {
        FB.ui({
            method: 'feed',
            name: "<?php echo sprintf(FatApp::getConfig("CONF_SOCIAL_FEED_FACEBOOK_POST_TITLE_$siteLangId", FatUtility::VAR_STRING, ''), FatApp::getConfig("CONF_WEBSITE_NAME_$siteLangId"))?>",
            link: "<?php echo $affiliateTrackingUrl?>",
            picture: "<?php echo CommonHelper::generateFullUrl('image', 'socialFeed', array($siteLangId ,''), "/")?>",
            caption: "<?php echo sprintf(FatApp::getConfig("CONF_SOCIAL_FEED_FACEBOOK_POST_CAPTION_$siteLangId", FatUtility::VAR_STRING, ''), FatApp::getConfig("CONF_WEBSITE_NAME_$siteLangId"))?>",
            description: "<?php echo str_replace(array("\n","\r","\r\n"), ' ', sprintf(FatApp::getConfig("CONF_SOCIAL_FEED_FACEBOOK_POST_DESCRIPTION_".$siteLangId, FatUtility::VAR_STRING, ''), FatApp::getConfig("CONF_WEBSITE_NAME_".$siteLangId)))?>",
        }, function(response) {
            if (response !== null && typeof response.post_id !== 'undefined') {
                $.mbsmessage(langLbl.thanksForSharing, true, 'alert--success');
                /* $("#fb_ajax").html(langLbl.thanksForSharing); */
            }
        });
    }

    function twitter_shared(name) {
        $.mbsmessage(langLbl.thanksForSharing, true, 'alert--success');
        /* $("#twitter_ajax").html(langLbl.thanksForSharing); */
    }
</script>
<script type="text/javascript">
    var newwindow;
    var intId;

    function twitter_login() {
        var screenX = typeof window.screenX != 'undefined' ? window.screenX : window.screenLeft,
            screenY = typeof window.screenY != 'undefined' ? window.screenY : window.screenTop,
            outerWidth = typeof window.outerWidth != 'undefined' ? window.outerWidth : document.body.clientWidth,
            outerHeight = typeof window.outerHeight != 'undefined' ? window.outerHeight : (document.body.clientHeight - 22),
            width = 800,
            height = 600,
            left = parseInt(screenX + ((outerWidth - width) / 2), 10),
            top = parseInt(screenY + ((outerHeight - height) / 2.5), 10),
            features = ('width=' + width + ',height=' + height + ',left=' + left + ',top=' + top);
        newwindow = window.open('<?php echo $twitterUrl; ?>', 'Login_by_twitter', features);
        if (window.focus) {
            newwindow.focus()
        }
        return false;
    }
</script>
