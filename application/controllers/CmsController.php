<?php
class CmsController extends MyAppController
{
    public function __construct($action)
    {
        parent::__construct($action);
    }

    public function view($cPageId, $isAppUser = false)
    {
        $cPageId = FatUtility::int($cPageId);
        $srch = ContentPage::getSearchObject($this->siteLangId);
        $srch->addMultipleFields(array('cpage_id', 'IFNULL(cpage_title, cpage_identifier) as cpage_title','cpage_layout','cpage_image_title','cpage_image_content','cpage_content' ));
        $srch->addCondition('cpage_id', '=', $cPageId);
        $cPage = FatApp::getDb()->fetch($srch->getResultset());
        if ($cPage == false) {
            FatUtility::exitWithErrorCode(404);
        }
        $blockData = array();
        if ($cPage['cpage_layout']==ContentPage::CONTENT_PAGE_LAYOUT1_TYPE) {
            $srch = new searchBase(ContentPage::DB_TBL_CONTENT_PAGES_BLOCK_LANG);
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
            $srch->addMultipleFields(array("cpblocklang_text", 'cpblocklang_block_id'));
            $srch->addCondition('cpblocklang_cpage_id', '=', $cPageId);
            $srch->addCondition('cpblocklang_lang_id', '=', $this->siteLangId);
            $srchRs = $srch->getResultSet();
            $blockData = FatApp::getDb()->fetchAll($srchRs, 'cpblocklang_block_id');
        }
        $this->set('blockData', $blockData);
        $this->set('cPage', $cPage);
        if ($isAppUser) {
            $this->set('isAppUser', $isAppUser);
            $this->_template->render(false, false);
        } else {
            $this->_template->render();
        }
    }

    public function getBreadcrumbNodes($action)
    {
        $nodes = array();
        $parameters = FatApp::getParameters();

        if (!empty($parameters) && $action == 'view') {
            $cPageId = reset($parameters);
            $cPageId = FatUtility::int($cPageId);
            $cPage = ContentPage::getAllAttributesById($cPageId, $this->siteLangId);
            $title = isset($cPage['cpage_title'])?$cPage['cpage_title']:$cPage['cpage_identifier'];
        }
        switch ($action) {
        default:
            $nodes[] = array('title'=>$title);
            break;
        }
        return $nodes;
    }

    public function fatActionCatchAll($action)
    {
        FatUtility::exitWithErrorCode(404);
    }
}
