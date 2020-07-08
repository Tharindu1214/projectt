<?php
class TagsController extends AdminBaseController
{
    private $canView;
    private $canEdit;

    public function __construct($action)
    {
        $ajaxCallArray = array('deleteRecord','form','langForm','search','setup','langSetup');
        if (!FatUtility::isAjaxCall() && in_array($action, $ajaxCallArray)) {
            die($this->str_invalid_Action);
        }
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewTags($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditTags($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index()
    {
        $this->objPrivilege->canViewTags();
        $frmSearch = $this->getSearchForm();
        $this->set("frmSearch", $frmSearch);
        $this->_template->addJs('js/import-export.js');
        $this->_template->render();
    }

    private function getSearchForm()
    {
        $frm = new Form('frmTagSearch', array('id'=>'frmTagSearch'));
        $f1 = $frm->addTextBox(Labels::getLabel('LBL_Tag_Identifier', $this->adminLangId), 'tag_identifier', '', array('class'=>'search-input'));
        $fld_submit=$frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId), array('onclick'=>'clearTagSearch();'));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }

    public function search()
    {
        $this->objPrivilege->canViewTags();

        $pagesize=FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        $searchForm = $this->getSearchForm();
        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0)?1:$data['page'];
        $post = $searchForm->getFormDataFromArray($data);

        /* $tagObj = new Tag(); */
        $srch = Tag::getSearchObject();
        $srch->addFld('t.*');

        if (!empty($post['tag_identifier'])) {
            $srch->addCondition('t.tag_identifier', 'like', '%'.$post['tag_identifier'].'%');
        }

        $page = (empty($page) || $page <= 0)?1:$page;
        $page = FatUtility::int($page);
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        $srch->joinTable(
            Tag::DB_TBL . '_lang',
            'LEFT OUTER JOIN',
            'taglang_tag_id = t.tag_id AND taglang_lang_id = ' . $this->adminLangId,
            'tl'
        );
        $srch->addMultipleFields(array("tl.tag_name"));
        $srch->addOrder('tag_id', 'DESC');
        $rs = $srch->getResultSet();
        $records =array();
        if ($rs) {
            $records = FatApp::getDb()->fetchAll($rs);
        }

        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->_template->render(false, false);
    }

    public function setup()
    {
        $this->objPrivilege->canEditTags();

        $frm = $this->getForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $tag_id = $post['tag_id'];
        unset($post['tag_id']);

        $record = new Tag($tag_id);
        $record->assignValues($post);

        if (!$record->save()) {
            Message::addErrorMessage(Labels::getLabel('MSG_This_identifier_is_not_available._Please_try_with_another_one.', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $newTabLangId = 0;
        if ($tag_id > 0) {
            $languages = Language::getAllNames();
            foreach ($languages as $langId =>$langName) {
                if (!$row = Tag::getAttributesByLangId($langId, $tag_id)) {
                    $newTabLangId = $langId;
                    break;
                }
            }
        } else {
            $tag_id = $record->getMainTableRecordId();
            $newTabLangId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1);
        }

        /* update product tags association and tag string in products lang table[ */
        Tag::updateTagStrings($tag_id);
        /* ] */

        $this->set('msg', Labels::getLabel('LBL_Tag_Setup_Successful', $this->adminLangId));
        $this->set('tagId', $tag_id);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function langSetup()
    {
        $this->objPrivilege->canEditTags();
        $post=FatApp::getPostedData();

        $tag_id = $post['tag_id'];
        $lang_id = $post['lang_id'];

        if ($tag_id == 0 || $lang_id == 0) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $frm = $this->getLangForm($tag_id, $lang_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        unset($post['tag_id']);
        unset($post['lang_id']);
        $data = array(
        'taglang_lang_id'=>$lang_id,
        'taglang_tag_id'=>$tag_id,
        'tag_name'=>$post['tag_name'],
        );

        $tagObj=new Tag($tag_id);
        if (!$tagObj->updateLangData($lang_id, $data)) {
            Message::addErrorMessage($tagObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $newTabLangId=0;
        $languages=Language::getAllNames();
        foreach ($languages as $langId =>$langName) {
            if (!$row=Tag::getAttributesByLangId($langId, $tag_id)) {
                $newTabLangId = $langId;
                break;
            }
        }

        /* update product tags association and tag string in products lang table[ */
        Tag::updateTagStrings($tag_id);
        /* ] */

        $this->set('msg', Labels::getLabel('LBL_Tag_Setup_Successful', $this->adminLangId));
        $this->set('tagId', $tag_id);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function form($tag_id=0)
    {
        $this->objPrivilege->canEditTags();

        $tag_id=FatUtility::int($tag_id);
        $frm = $this->getForm($tag_id);

        if (0 < $tag_id) {
            $data = Tag::getAttributesById($tag_id, array('tag_id','tag_identifier'));
            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            $frm->fill($data);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('tag_id', $tag_id);
        $this->set('frmTag', $frm);
        $this->_template->render(false, false);
    }

    private function getForm($tag_id=0)
    {
        $this->objPrivilege->canEditTags();
        $tag_id=FatUtility::int($tag_id);

        $frm = new Form('frmTag', array('id'=>'frmTag'));
        $frm->addHiddenField('', 'tag_id', $tag_id);
        $frm->addRequiredField(Labels::getLabel('LBL_Tag_Identifier', $this->adminLangId), 'tag_identifier');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    public function langForm($tag_id=0, $lang_id=0)
    {
        $this->objPrivilege->canEditTags();

        $tag_id = FatUtility::int($tag_id);
        $lang_id = FatUtility::int($lang_id);

        if ($tag_id==0 || $lang_id==0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $tagLangFrm = $this->getLangForm($tag_id, $lang_id);

        $langData = Tag::getAttributesByLangId($lang_id, $tag_id);

        if ($langData) {
            $tagLangFrm->fill($langData);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('tag_id', $tag_id);
        $this->set('tag_lang_id', $lang_id);
        $this->set('tagLangFrm', $tagLangFrm);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->_template->render(false, false);
    }

    private function getLangForm($tag_id=0, $lang_id=0)
    {
        $frm = new Form('frmTagLang', array('id'=>'frmTagLang'));
        $frm->addHiddenField('', 'tag_id', $tag_id);
        $frm->addHiddenField('', 'lang_id', $lang_id);
        $frm->addRequiredField(Labels::getLabel('LBL_Tag_Name', $this->adminLangId), 'tag_name');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Update', $this->adminLangId));
        return $frm;
    }

    public function deleteRecord()
    {
        $this->objPrivilege->canEditTags();

        $tag_id = FatApp::getPostedData('id', FatUtility::VAR_INT, 0);
        if ($tag_id < 1) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->markAsDeleted($tag_id);

        $this->set('msg', $this->str_delete_record);
        $this->_template->render(false, false, 'json-success.php');

        //FatUtility::dieJsonSuccess($this->str_delete_record);
    }

    public function deleteSelected()
    {
        $this->objPrivilege->canEditTags();
        $tagIdsArr = FatUtility::int(FatApp::getPostedData('tag_ids'));

        if (empty($tagIdsArr)) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        foreach ($tagIdsArr as $tag_id) {
            if (1 > $tag_id) {
                continue;
            }
            $this->markAsDeleted($tag_id);
        }
        $this->set('msg', $this->str_delete_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function markAsDeleted($tag_id)
    {
        $tag_id = FatUtility::int($tag_id);
        if (1 > $tag_id) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        $tagObj = new Tag($tag_id);
        if (!$tagObj->canRecordDelete($tag_id)) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }

        /* check this tag is associated with any products, then remove binding from those products and update the product_tags_string from tbl_products_lang[ */
        $rows = Product::getProductIdsByTagId($tag_id);
        if (!empty($rows)) {
            FatApp::getDb()->deleteRecords(Product::DB_PRODUCT_TO_TAG, array( 'smt'=>'ptt_tag_id = ?', 'vals'=>array( $tag_id ) ));
            foreach ($rows as $row) {
                Tag::updateProductTagString($row['ptt_product_id']);
            }
        }
        /* ] */

        if (!$tagObj->deleteRecord(true)) {
            Message::addErrorMessage($tagObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
    }

    public function autoComplete()
    {
        /* $pagesize = 10; */
        $post = FatApp::getPostedData();
        $this->objPrivilege->canViewTags();

        $srch = Tag::getSearchObject();
        $srch->addOrder('tag_identifier');
        $srch->joinTable(
            Tag::DB_TBL . '_lang',
            'LEFT OUTER JOIN',
            'taglang_tag_id = tag_id AND taglang_lang_id = ' . $this->adminLangId
        );
        $srch->addMultipleFields(array('tag_id, tag_name, tag_identifier'));

        if (!empty($post['keyword'])) {
            $cnd = $srch->addCondition('tag_name', 'LIKE', '%' . $post['keyword'] . '%');
            $cnd->attachCondition('tag_identifier', 'LIKE', '%'. $post['keyword'] . '%', 'OR');
        }

        /* $srch->setPageSize($pagesize); */
        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $options = array();
        if ($rs) {
            $options = $db->fetchAll($rs, 'tag_id');
        }
        $json = array();
        foreach ($options as $key => $option) {
            $json[] = array(
            'id' => $key,
            'name'      => strip_tags(html_entity_decode($option['tag_name'], ENT_QUOTES, 'UTF-8')),
            'tag_identifier'    => strip_tags(html_entity_decode($option['tag_identifier'], ENT_QUOTES, 'UTF-8'))
            );
        }
        die(json_encode($json));
    }
}
