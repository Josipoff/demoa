<?php
include_once(dirname(__FILE__).'../../../orderfiles.php');
class orderfilesmyfilesModuleFrontController extends ModuleFrontController{	
	public function initContent(){
		$module=new orderfiles();
		$this->var=$module->getconf();
        parent::initContent();		
		$order = new OrderCore();
		$custorders=$order->getCustomerOrders($this->context->customer->id);
		
		
		
		$this->context->smarty->assign(array(
		'mod' => $this,
		'setup' => $this->var, 
		'orders' => $custorders,
		'link' => $this->context->link,
        'psversion' => $module->psversion(),
		'customer' => $this->context->customer));
		$this->setTemplate('my-files.tpl');
		
	}	
	
	public function currency_sign($id){
		$currency=new CurrencyCore($id);
		return $currency->sign;
	}
}