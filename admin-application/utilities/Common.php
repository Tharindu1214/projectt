<?php
class Common
{
    static function setHeaderBreadCrumb($template)
    {
        $controllerName = FatApp::getController();
        $action = FatApp::getAction();

        $controller = new $controllerName('');
        $template->set('nodes', $controller->getBreadcrumbNodes($action));
        $template->set('adminLangId', CommonHelper::getlangId());
    }
}
?>
