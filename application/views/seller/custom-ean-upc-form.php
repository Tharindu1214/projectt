<div class="tabs tabs--small tabs--scroll clearfix align-items-center">
    <?php require_once(CONF_THEME_PATH.'_partial/seller/customCatalogProductNavigationLinks.php'); ?>
</div>
<div class="cards">
    <div class="cards-content pt-3 pl-4 pr-4 ">
        <div class="row">
            <div class="col-md-12">
                <div class="form__subcontent">
                    <?php if (!empty($productOptions)) {?>
                    <form name="upcFrm" onSubmit="setupEanUpcCode(<?php echo $preqId;?>,this); return(false);" class="form">
                        <table width="100%" class="table table-responsive" cellpadding="0" cellspacing="0">
                            <thead>
                                <tr>
                                    <th><?php echo Labels::getLabel('LBL_Sr.', $siteLangId);?></th>
                                    <?php
                                foreach ($productOptions as $option) {
                                    echo "<th>".$option['option_name']."</th>";
                                }
                                ?>
                                    <th><?php echo Labels::getLabel('LBL_EAN/UPC_code', $siteLangId);?></th>
                                </tr>
                                <?php
                                $arr  = array();
                                $count = 0;
                                foreach ($optionCombinations as $optionValueId=>$optionValue) {
                                    $count++;
                                    $arr = explode('|', $optionValue);
                                    $key = str_replace('|', ',', $optionValueId); ?>
                                <tr>
                                    <td><?php echo $count; ?></td>
                                    <?php
                                    foreach ($arr as $val) {
                                        echo "<td>".$val."</td>";
                                    } ?>
                                    <td><input type="text" id="code<?php echo $optionValueId?>" name="code<?php echo $optionValueId?>" value="<?php echo (isset($upcCodeData[$optionValueId]))?$upcCodeData[$optionValueId]:''; ?>"
                                            onBlur="validateEanUpcCode(this.value)"></td>
                                </tr>
                                <?php
                                }?>
                                <tr>
                                    <td></td>
                                    <td colspan="<?php echo count($arr);?>"></td>
                                    <td><input type="submit" name="submit" value="<?php echo Labels::getLabel('LBL_Update', $siteLangId);?>"></td>
                                </tr>
                            </thead>
                        </table>
                    </form>
                    <?php }?>
                </div>
            </div>
        </div>
    </div>
</div>
