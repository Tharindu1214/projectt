<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
$arr_flds = array(
    'listserial'=> Labels::getLabel('LBL_S.No.', $siteLangId),
    'afile_physical_path'=>Labels::getLabel('LBL_Location', $siteLangId),
    'afile_name'    => Labels::getLabel('LBL_File_Name', $siteLangId),
    'files'    => Labels::getLabel('LBL_Files_Inside', $siteLangId),
    'action'    => Labels::getLabel('LBL_Action', $siteLangId),
);
$tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table table-responsive'));
$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $val) {
    $e = $th->appendElement('th', array(), $val);
}

$sr_no = $page==1 ? 0: $pageSize*($page-1);
foreach ($arr_listing as $sn=>$row) {
    $sr_no++;
    $tr = $tbl->appendElement('tr', array( ));

    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no);
            break;
            case 'afile_physical_path':
                $path = AttachedFile::FILETYPE_BULK_IMAGES_PATH . $row['afile_physical_path'];
                $td->appendElement('plaintext', array(), $path, true);
            break;
            case 'files':
                $fullPath = CONF_UPLOADS_PATH . AttachedFile::FILETYPE_BULK_IMAGES_PATH . $row['afile_physical_path'];
                $count = Labels::getLabel('LBL_NA', $siteLangId);
                if (file_exists($fullPath)) {
                    $allFiles = scandir($fullPath);
                    $files_count = array_diff($allFiles, array( '..', '.' ));
                    $count = count($files_count);
                }

                $td->appendElement('plaintext', array(), $count);
            break;
            case 'action':
                $ul = $td->appendElement("ul", array("class"=>"actions actions--centered"));

                $li = $ul->appendElement("li");
                $li->appendElement(
                    'a',
                    array('href'=>'javascript:void(0)', 'class'=>'button small green',
                'title'=>Labels::getLabel('LBL_Delete', $siteLangId),"onclick"=>"removeDir('".base64_encode($row['afile_physical_path'])."')"),
                    '<i class="fa fa-trash"></i>',
                    true
                );
                $li = $ul->appendElement("li");
                $li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green', 'title'=>Labels::getLabel('LBL_Download', $siteLangId),"onclick"=>"downloadPathsFile('".base64_encode($fullPath)."')"), '<i class="fa fa-download"></i>', true);
            break;
            default:
                $td->appendElement('plaintext', array(), $row[$key], true);
            break;
        }
    }
}
if (count($arr_listing) == 0) {
    $tbl->appendElement('tr')->appendElement('td', array('colspan'=>count($arr_flds)), Labels::getLabel('LBL_No_Records_Found', $siteLangId));
}
echo $tbl->getHtml();
$postedData['page']=$page;
echo FatUtility::createHiddenFormFromData($postedData, array(
        'name' => 'frmSearchPaging'
));
$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'pageSize'=>$pageSize,'recordCount'=>$recordCount,'adminLangId'=>$siteLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
