<?php
class TestimonialsController extends MyAppController
{
    public function __construct($action)
    {
        parent::__construct($action);
    }

    public function index()
    {
        $this->_template->render();
    }

    public function search()
    {
        $post = FatApp::getPostedData();
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : FatUtility::int($post['page']);
        $pageSize = FatApp::getConfig('conf_page_size', FatUtility::VAR_INT, 10);

        $srch = Testimonial::getSearchObject($this->siteLangId, true);
        $srch->addMultipleFields(array('t.*' , 't_l.testimonial_title' , 't_l.testimonial_text'));
        $srch->addCondition('testimoniallang_testimonial_id', 'is not', 'mysql_func_null', 'and', true);
        $srch->addOrder('testimonial_added_on', 'desc');
        $srch->setPageSize($pageSize);
        $srch->setPageNumber($page);
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);
        $this->set('page', $page);
        $this->set('pageCount', $srch->pages());
        $this->set("list", $records);

        $json['html'] = $this->_template->render(false, false, 'testimonials/search.php', true, false);
        $json['loadMoreBtnHtml'] = $this->_template->render(false, false, 'testimonials/load-more-btn.php', true, false);
        FatUtility::dieJsonSuccess($json);
    }
}
