<?php
class ProceduresController extends AdminBaseController
{

    public function createProcedures() 
    {
        $db = FatApp::getDb();
        $con = $db->getConnectionObject();
        $queries = array(
        "DROP FUNCTION IF EXISTS `GETBLOGCATCODE`",
        "CREATE FUNCTION `GETBLOGCATCODE`(`id` INT) RETURNS varchar(255) CHARSET utf8
BEGIN
    DECLARE code VARCHAR(255);
    DECLARE catid INT(11);

    SET catid = id;
    SET code = '';
    WHILE catid > 0 DO
    	SET code = CONCAT(RIGHT(CONCAT('000000', catid), 6), '_', code);
        SELECT bpcategory_parent INTO catid FROM tbl_blog_post_categories WHERE bpcategory_id = catid;
    END WHILE;
    RETURN code;
END",
        "DROP FUNCTION IF EXISTS `GETCATCODE`",
        "CREATE FUNCTION `GETCATCODE`(`id` INT) RETURNS varchar(255) CHARSET utf8
BEGIN
    DECLARE code VARCHAR(255);
    DECLARE catid INT(11);

    SET catid = id;
    SET code = '';
    WHILE catid > 0 DO
    	SET code = CONCAT(RIGHT(CONCAT('000000', catid), 6), '_', code);
        SELECT prodcat_parent INTO catid FROM tbl_product_categories WHERE prodcat_id = catid;
    END WHILE;
    RETURN code;
END",
        "DROP FUNCTION IF EXISTS `GETCATORDERCODE`",
        "CREATE FUNCTION `GETCATORDERCODE`(`id` INTEGER) RETURNS varchar(255) CHARSET utf8
BEGIN
    DECLARE code VARCHAR(255);
    DECLARE catid INT(11);
	DECLARE myorder INT(11);
    SET catid = id;
    SET code = '';
    set myorder = 0;
    WHILE catid > 0 DO
        SELECT prodcat_parent, prodcat_display_order  INTO catid, myorder FROM tbl_product_categories WHERE prodcat_id = catid;
        SET code = CONCAT(RIGHT(CONCAT('000000', myorder), 6), code);
    END WHILE;
    RETURN code;
END",
        "DROP FUNCTION IF EXISTS `GETBLOGCATORDERCODE`",
        "CREATE FUNCTION `GETBLOGCATORDERCODE`(`id` INT) RETURNS varchar(500) CHARSET utf8
BEGIN
    DECLARE code VARCHAR(255);
    DECLARE catid INT(11);
	DECLARE myorder INT(11);
    SET catid = id;
    SET code = '';
    set myorder = 0;
    WHILE catid > 0 DO
        SELECT bpcategory_parent, bpcategory_display_order  INTO catid, myorder FROM tbl_blog_post_categories WHERE bpcategory_id = catid;
        SET code = CONCAT(RIGHT(CONCAT('000000', myorder), 6), code);
    END WHILE;
    RETURN code;
END"
        );
        
        foreach ($queries as $qry) {
            if (!$con->query($qry)) {
                die($con->error);
            }
        }
        echo 'Created All the Procedures.';
    }
}    