<?php
class BuyerBaseController extends LoggedUserController
{
    public function __construct($action)
    {
        parent::__construct($action);

        if (!User::isBuyer() || UserAuthentication::isGuestUserLogged()) {
            Message::addErrorMessage(Labels::getLabel("LBL_Unauthorised_access", $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('account'));
        }
        $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'] = 'B';
        $this->set('bodyClass', 'is--dashboard');
    }
}
