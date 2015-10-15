<?php
class adminorderapprovalapprovalModuleFrontController extends ModuleFrontController
{
    public $ssl = false;

    public function initContent()
    {
        parent::initContent();

        $adminorderapproval = new adminorderapproval();
        if ($adminorderapproval->execActivation() === true)
        {
            $this->setTemplate('activation-success.tpl');
        }
        else
        {
            $this->setTemplate('activation-fail.tpl');
        }
    }
}