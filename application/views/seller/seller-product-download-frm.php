<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="tabs tabs--small tabs--scroll clearfix">
    <?php require_once('sellerCatalogProductTop.php');?>
</div>
<div class="cards">
    <div class="cards-content pt-3 pl-4 pr-4 ">
        <div class="row">
            <div class="col-md-12">
                <?php $selprodDownloadFrm->setFormTagAttribute('id', 'frmDownload');
                $selprodDownloadFrm->setFormTagAttribute('class', 'form form--horizontal');
                $selprodDownloadFrm->developerTags['colClassPrefix'] = 'col-xl-8 col-';
                $selprodDownloadFrm->developerTags['fld_default_col'] = 12;

                $langFld = $selprodDownloadFrm->getField('lang_id');
                $langFld->setWrapperAttribute('class', 'lang_fld');

                $downloadableLinkFld = $selprodDownloadFrm->getField('selprod_downloadable_link');
                $downloadableLinkFld->setWrapperAttribute('class', 'downloadable_link_fld');

                $downloadableFileFld = $selprodDownloadFrm->getField('downloadable_file');
                $downloadableFileFld->setWrapperAttribute('class', 'downloadable_file_fld');
                $downloadableFileFld->setFieldTagAttribute('onchange', 'setUpSellerProductDownloads('.applicationConstants::DIGITAL_DOWNLOAD_FILE.'); return false;');

                $submitButton = $selprodDownloadFrm->getField('btn_submit');
                $submitButton->setWrapperAttribute('class', 'submit_button');
                $submitButton->setFieldTagAttribute('onClick', 'setUpSellerProductDownloads('.applicationConstants::DIGITAL_DOWNLOAD_FILE.'); return false;');

                echo $selprodDownloadFrm->getFormHtml(); ?>
            </div>
            <div class="col-md-12 filesList">
                <?php
                $arr_flds = array(
                    'listserial'=>Labels::getLabel('LBL_Sr_No.', $siteLangId),
                    'afile_name' => Labels::getLabel('LBL_File', $siteLangId),
                    'afile_lang_id' => Labels::getLabel('LBL_Language', $siteLangId),
                    'action' => Labels::getLabel('LBL_Action', $siteLangId),
                );

                $tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table table--orders'));
                $th = $tbl->appendElement('thead')->appendElement('tr', array('class' => ''));
                foreach ($arr_flds as $val) {
                    $e = $th->appendElement('th', array(), $val);
                }

                $sr_no = 0;
                foreach ($attachments as $sn => $row) {
                    $sr_no++;
                    $tr = $tbl->appendElement('tr');

                    foreach ($arr_flds as $key=>$val) {
                        $td = $tr->appendElement('td');
                        switch ($key) {
                            case 'listserial':
                                $td->appendElement('plaintext', array(), $sr_no, true);
                            break;
                            case 'afile_lang_id':
                                $lang_name = Labels::getLabel('LBL_All', $siteLangId);
                                if ($row['afile_lang_id'] > 0) {
                                    $lang_name = $languages[$row['afile_lang_id']];
                                }
                                $td->appendElement('plaintext', array(), $lang_name, true);
                            break;
                            case 'afile_name':
                                $fileName = '<a target="_blank" href="'.CommonHelper::generateUrl('seller', 'downloadDigitalFile', array($row['afile_id'],$row['afile_record_id'])).'">'.$row[$key].'</a>';
                                $td->appendElement('plaintext', array(), $fileName, true);
                            break;
                            case 'action':
                                $ul = $td->appendElement("ul", array("class"=>"actions"), '', true);

                                $li = $ul->appendElement("li");
                                $li->appendElement(
                                    "a",
                                    array('title' => Labels::getLabel('LBL_Product_Images', $siteLangId),
                                'onclick' => 'deleteDigitalFile('.$row['afile_record_id'].','.$row['afile_id'].')', 'href'=>'javascript:void(0)'),
                                    '<i class="fa fa-trash"></i>',
                                    true
                                );

                            break;
                            default:
                                $td->appendElement('plaintext', array(), $row[$key], true);
                            break;
                        }
                    }
                }
                if (!empty($attachments)) {
                    echo $tbl->getHtml();
                }
                ?>
            </div>
        </div>
    </div>
</div>
<?php echo FatUtility::createHiddenFormFromData(array('product_id'=>$product_id,'product_type'=>$product_type), array('name' => 'frmSearchSellerProducts'));?>

<script type="text/javascript">
    var DIGITAL_DOWNLOAD_FILE = <?php echo applicationConstants::DIGITAL_DOWNLOAD_FILE; ?>;
    var DIGITAL_DOWNLOAD_LINK = <?php echo applicationConstants::DIGITAL_DOWNLOAD_LINK; ?>;

    $(document).on("change", "select[name='download_type']", function(){
        if ($(this).val() == DIGITAL_DOWNLOAD_FILE) {
            $(".lang_fld").show();
            $(".downloadable_file_fld").show();
            $(".filesList").show();
            $(".downloadable_link_fld").hide();
            $(".submit_button").hide();
        } else {
            $(".lang_fld").hide();
            $(".downloadable_file_fld").hide();
            $(".filesList").hide();
            $(".downloadable_link_fld").show();
            $(".submit_button").show();
        }
    });
    $("select[name='download_type']").trigger("change");
</script>
