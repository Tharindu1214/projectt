<?php
class InnovaController extends LoggedUserController
{
    public function assetmanager() 
    {
        include_once CONF_THEME_PATH . 'assetmanager/' . implode('/', func_get_args());
    }
}
