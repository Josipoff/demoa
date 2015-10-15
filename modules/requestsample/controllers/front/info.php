<?php

class requestsampleinfoModuleFrontController extends ModuleFrontController
{
    public $ssl = false;

    public function initContent()
    {
        parent::initContent();
        if (Tools::isSubmit('submitMessage'))
	{
            $message = Tools::getValue('message'); // Html entities is not usefull, iscleanHtml check there is no bad html tags.
            $phone = Tools::getValue('tel');
            $mobile = Tools::getValue('mobile');
            if (!($from = trim(Tools::getValue('from'))) || !Validate::isEmail($from))
                    $this->errors[] = Tools::displayError('Invalid email address.');
            else if (!$message)
                    $this->errors[] = Tools::displayError('The message cannot be blank.');
            else if (!Validate::isCleanHtml($message))
                    $this->errors[] = Tools::displayError('Invalid message');
            else if (!Validate::isPhoneNumber($phone))
                    $this->errors[] = Tools::displayError('Invalid phone number.'); 
            else if (!Validate::isPhoneNumber($mobile))
                    $this->errors[] = Tools::displayError('Invalid Mobile number.');         
				//		var_dump($this->errors,empty($this->errors));
            if(empty($this->errors)){
                $id_product = Tools::getValue('product_id');
                //var_dump($id_product);
                $product = new Product($id_product);
                //var_dump($product);
                $product_name = '';
                $item_number = '';
                if (Validate::isLoadedObject($product) && isset($product->name[(int)$this->context->language->id]))
                {
                    $product_name = $product->name[(int)$this->context->language->id];
                    $item_number = $product->item_number;
                }
                $data =  array('{name}' => Tools::getValue('name'),
                         '{phone}' => $phone,
                         '{mobile}' => $mobile,
                         '{message}' => $message,
                         '{item_number}' => $item_number,
                         '{product}' => $product_name,
                         '{date}' => date('Y-m-d H:i:s'),
                         '{email}' => $from);
                
                
                $sampleObj = new requestsample();
                $sampleObj->sendmail($data,$from, (int)$this->context->language->id,'sample_request','New Request for Sample');
            $this->context->smarty->assign('confirmation',1);
            }
        }
            $this->context->smarty->assign('product_id',$_GET['pr_id']);
            $this->setTemplate('form.tpl');
    }
}