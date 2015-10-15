<?php

class websitecreditcreditModuleFrontController extends ModuleFrontController
{
    public $ssl = false;

    public function initContent()
    {
        parent::initContent();
        $this->context->smarty->assign(array("text2" =>PS_SHOP_PATH."/img/logo1.png"));
        $this->setTemplate('credit.tpl');
    }
}