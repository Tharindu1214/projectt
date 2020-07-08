<?php
class SlidesController extends MyAppController
{
    public function track($slideId = 0)
    {
        $slideId = FatUtility::int($slideId);
        if (1 > $slideId) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl(''));
        }

        /* Track Click */
        /* Main Slides[ */
        $srchSlide = new SlideSearch($this->siteLangId);
        $srchSlide->doNotCalculateRecords();
        $srchSlide->joinPromotions($this->siteLangId, true);
        $srchSlide->addPromotionTypeCondition();
        $srchSlide->joinUserWallet();
        $srchSlide->addMinimiumWalletbalanceCondition();
        $srchSlide->addSkipExpiredPromotionAndSlideCondition();
        $srchSlide->joinBudget();
        $srchSlide->addCondition('slide_id', '=', $slideId);
        //$srchSlide->addOrder( Slides::DB_TBL_PREFIX . 'display_order');
        $srchSlide->addOrder('', 'rand()');
        $srchSlide->addMultipleFields(
            array('slide_id','slide_record_id','slide_type','IFNULL(promotion_name, promotion_identifier) as promotion_name,IFNULL(slide_title, slide_identifier) as slide_title',
            'slide_target', 'slide_url','promotion_id' ,'daily_cost','weekly_cost','monthly_cost','total_cost','promotion_cpc')
        );



        $srch = new SearchBase('('.$srchSlide->getQuery().') as t');
        $srch->doNotCalculateRecords();
        $srch->addDirectCondition(
            '((CASE
				WHEN promotion_duration='.Promotion::DAILY.' THEN promotion_budget > COALESCE(daily_cost,0)
				WHEN promotion_duration='.Promotion::WEEKLY.' THEN promotion_budget > COALESCE(weekly_cost,0)
				WHEN promotion_duration='.Promotion::MONTHLY.' THEN promotion_budget > COALESCE(monthly_cost,0)
				WHEN promotion_duration='.Promotion::DURATION_NOT_AVAILABALE.' THEN promotion_budget = -1
			  END ) )'
        );
        $srch->addMultipleFields(array('slide_id','slide_type','slide_record_id','slide_url','slide_target','slide_title','promotion_id' ,'userBalance','daily_cost','weekly_cost','monthly_cost','total_cost','promotion_budget' ,'promotion_duration','promotion_cpc'));

        $rs = $srch->getResultSet();
        $row =FatApp::getDb()->fetch($rs, 'slide_id');
        if ($row == false) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl(''));
        }

        $url  = $row['slide_url'];

        $userId = 0;

        if (UserAuthentication::isUserLogged()) {
            $userId = UserAuthentication::getLoggedUserId();
        }
        if (Promotion::isUserClickCountable($userId, $row['promotion_id'], $_SERVER['REMOTE_ADDR'], session_id())) {
            $promotionClickData = array(
            'pclick_promotion_id' => $row['promotion_id'],
            'pclick_user_id' => $userId,
            'pclick_datetime' => date('Y-m-d H:i:s'),
            'pclick_ip' => $_SERVER['REMOTE_ADDR'],
            'pclick_cost' => $row['promotion_cpc'],
            'pclick_session_id' => session_id(),
            );

            FatApp::getDb()->insertFromArray(Promotion::DB_TBL_CLICKS, $promotionClickData, false, '', $promotionClickData);

            $clickId= FatApp::getDb()->getInsertId();

            $promotionClickChargesData = array(

            'picharge_pclick_id' => $clickId,
            'picharge_datetime'  => date('Y-m-d H:i:s'),
            'picharge_cost'  => $row['promotion_cpc'],

            );

            FatApp::getDb()->insertFromArray(Promotion::DB_TBL_ITEM_CHARGES, $promotionClickChargesData, false);

            $promotionLogData = array(
            'plog_promotion_id' => $row['promotion_id'],
            'plog_date' =>  date('Y-m-d'),
            'plog_clicks' =>  1,
            );

            $onDuplicatePromotionLogData = array_merge($promotionLogData, array('plog_clicks'=>'mysql_func_plog_clicks+1'));
            FatApp::getDb()->insertFromArray(Promotion::DB_TBL_LOGS, $promotionLogData, true, array(), $onDuplicatePromotionLogData);
        }

        if (!filter_var($url, FILTER_VALIDATE_URL) === false) {
            FatApp::redirectUser($url);
        }

        FatApp::redirectUser(CommonHelper::generateUrl(''));
    }
}
