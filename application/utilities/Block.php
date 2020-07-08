<?php
class Block
{
    public static function loginPageRight($template)
    {
        $db = FatApp::getDb();
        $siteLangId = CommonHelper::getLangId();
        $template->set('siteLangId', $siteLangId);
    }
}
