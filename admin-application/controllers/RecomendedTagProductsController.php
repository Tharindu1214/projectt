<?php
class RecomendedTagProductsController extends AdminBaseController
{
    private $canView;
    private $canEdit;
    
    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewRecomendedTagProducts($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditRecomendedTagProducts($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);        
    }
    
    public function index()
    {
        $this->objPrivilege->canViewRecomendedTagProducts();
        $searchFrm = $this->getSearchForm();                    
        $this->set("searchFrm", $searchFrm);    
        $this->_template->render();
    }
    
    public function search()
    {
        $this->objPrivilege->canViewRecomendedTagProducts();
        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);                
        $searchForm = $this->getSearchForm();
        $data = FatApp::getPostedData();
        $post = $searchForm->getFormDataFromArray($data);

        $data['page'] = isset($data['page']) ? FatUtility::int($data['page']) : 0;
        $page = ( empty($data['page']) || $data['page'] <= 0 ) ? 1 : $data['page'];
        
        $srch = new SearchBase('tbl_tag_product_recommendation', 'tpr');
        $srch->joinTable(Tag::DB_TBL, 'INNER JOIN', 't.tag_id = tpr.tpr_tag_id', 't');
        $srch->joinTable(Tag::DB_LANG_TBL, 'LEFT OUTER JOIN', 't_l.taglang_tag_id = t.tag_id and t_l.taglang_lang_id = '.$this->adminLangId, 't_l');
        $srch->joinTable(Product::DB_TBL, 'INNER JOIN', 'p.product_id = tpr.tpr_product_id', 'p');
        $srch->joinTable(Product::DB_LANG_TBL, 'LEFT OUTER JOIN', 'p_l.productlang_product_id = p.product_id and p_l.productlang_lang_id = '.$this->adminLangId, 'p_l');
        $srch->addMultipleFields(array('tpr.*', 'IFNULL(t_l.tag_name,t.tag_identifier) as tag_name', 'IFNULL(p_l.product_name,p.product_identifier) as product_name'));
        //$src->addCondition('p.product_active','=', applicationConstants::ACTIVE);
        
        $keyword = FatApp::getPostedData('keyword', FatUtility::VAR_STRING);
        if(!empty($keyword) ) {
            $cnd = $srch->addCondition('tag_name', 'LIKE', '%' . $keyword . '%');
            $cnd->attachCondition('product_name', 'LIKE', '%' . $keyword . '%');
        }
        
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        /* echo $srch->getQuery(); die; */
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetchAll($rs);
        
        $this->set("arr_listing", $row);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->_template->render(false, false);
    }
    
    public function update()
    {
        $this->objPrivilege->canEditRecomendedWeightages();
        $post = FatApp::getPostedData();
        
        $product_id = FatUtility::int($post['product_id']);
        $tag_id = FatUtility::int($post['tag_id']);
        
        $data = array();
        if(isset($post['tpr_custom_weightage'])) {
            $data['tpr_custom_weightage']= $post['tpr_custom_weightage']; 
        }
        
        if(isset($post['tpr_custom_weightage_valid_till'])) {
            $data['tpr_custom_weightage_valid_till']= $post['tpr_custom_weightage_valid_till']; 
        }
                
        if(!FatApp::getDb()->updateFromArray('tbl_tag_product_recommendation', $data, array('smt' => 'tpr_product_id = ? and tpr_tag_id = ?', 'vals' => array( $product_id,$tag_id ) )) ) {
            Message::addErrorMessage(FatApp::getDb()->getError());
            FatUtility::dieJsonError(Message::getHtml());    
        }
        
        $this->set('msg', $this->str_setup_successful);    
        $this->_template->render(false, false, 'json-success.php');
    }
    
    private function getSearchForm()
    {
        $frm = new Form('frmSearch');        
        $f1 = $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword', '');
        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId), array('onclick'=>'clearSearch();'));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }
}
