<?php

class adminorderapprovalcreditModuleFrontController extends ModuleFrontController
{
    public $ssl = false;

    public function initContent()
    {
        parent::initContent();
        $this->context->smarty->assign(array("page_title"=>base64_decode("V2Vic2l0ZSBDcmVkaXRz"),
            "text1" => base64_decode("PGJyIC8+PGJyIC8+PGg0PldlYnNpdGUgRGVzaWduIGFuZCBEZXZlbG9wZWQgQnk6PC9oND48YnIgLz48YSBocmVmPSJodHRwOi8vd3d3LnNraWlmeS5jb20iIHRpdGxlPSJTa2lpZnkgU29sdXRpb25zIiAgdGFyZ2V0PSJfYmxhbmsiPjxpbWcgYWx0PSJTa2lpZnkgU29sdXRpb25zIiB0aXRsZT0iU2tpaWZ5IFNvbHV0aW9ucyIgc3JjPSI="),
            "text2" =>PS_SHOP_PATH.base64_decode("L2ltZy9sb2dvMS5wbmc="),
            "text3"=>base64_decode("IiAvPjxiciAvPlNraWlmeSBTb2x1dGlvbnMgPC9hPg==")));
        $this->setTemplate('credit.tpl');
    }
}