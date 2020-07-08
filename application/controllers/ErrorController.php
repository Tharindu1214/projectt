<?php
class ErrorController extends MyAppController
{
    public function __construct($action)
    {
        parent::__construct($action);
    }

    public function index()
    {
        $this->_template->render();
    }
}
