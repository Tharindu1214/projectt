<?php
class AdvertiserBaseController extends LoggedUserController
{
    public function __construct($action)
    {
        parent::__construct($action);

        if (!User::isAdvertiser()) {
            Message::addErrorMessage(Labels::getLabel("LBL_Unauthorised_access", $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('account'));
        }
        $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'] = 'Ad';
        $this->set('bodyClass', 'is--dashboard');
    }
}
