<?php

class requestsampleModuleFrontController extends ModuleFrontController
{
    public $ssl = false;

    public function initContent()
    {
        parent::initContent();
        $this->setTemplate('request-sample.tpl');
    }
}