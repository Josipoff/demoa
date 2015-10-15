<?php

if (!defined('_PS_VERSION_'))

  exit;

 

class CustomQuote extends Module

{

  public function __construct()

  {

    $this->name = 'customquote';

    $this->tab = 'front_office_features';

    $this->version = '1.0.0';

    $this->author = 'Ayushi Agarwal';

    $this->need_instance = 0;

    $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_); 

    $this->bootstrap = true;

 

    parent::__construct();

 

    $this->displayName = $this->l('Custom Quote');

    $this->description = $this->l('Description of my module.');

 

    $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

 

    if (!Configuration::get('CUSTOMQUOTE_NAME'))      

      $this->warning = $this->l('No name provided');

  }

  

  

public function install()

{

  if (!parent::install())

    return false;

  

   return parent::install() && $this->registerHook('ProductCustomQuote') && $this->registerHook('Header');

}



public function uninstall()

{

  if (!parent::uninstall())

    return false;

  return true;

}



public function hookdisplayProductcustomquote($params)

{

  global $cookie;       

  if (isset($_COOKIE['ftpr'])){
        $cookieid=$_COOKIE['ftpr'];

    } else {

        $cookieid='x';

    }

  $this->context->controller->addCSS($this->_path.'customquote.css', 'all');

  $this->context->controller->addJS(($this->_path).'customquote.js');

  $orderfiles = new orderfiles();

 // var_dump($orderfiles->getproductfiles(Tools::getValue('id_product'),$cookieid));

  $this->context->smarty->assign(

      array(

          'my_module_name' => Configuration::get('CUSTOMQUOTE_NAME'),

          'my_module_link' => $this->context->link->getModuleLink('customquote', 'display'),

          'customizationFields' => $params['customizationFields'],

          'files'=> $orderfiles->getproductfiles(Tools::getValue('id_product'),$cookieid),

          'shop_path'=> PS_SHOP_PATH

      )

  );

  return $this->display(__FILE__, 'customquote.tpl');

}

   

public function hookDisplayRightColumn($params)

{

  return $this->hookDisplayLeftColumn($params);

}

   

public function hookHeader()

{

  $this->context->controller->addCSS($this->_path.'customquote.css', 'all');

  $this->context->controller->addJS(($this->_path).'customquote.js');

		

}  



public function ajaxCall()

{

    global $smarty, $cookie;

    $webservice_exi = new SoapClient(EXIMAGEN_WEBSERVICE) ;

    $list = EXIMAGEN_LIST;

    $parameter =array("Quantity"=>Tools::getValue('quantity'),
                    "ItemNumber"=>Tools::getValue('item_no'),
                    "Technique"=>Tools::getValue('technique'),
                    "Area"=>Tools::getValue('area'),
                    "Colors"=>Tools::getValue('color'),
                    "List"=>$list,
                    "Size"=>Tools::getValue('size'),
                    "key"=>EXIMAGEN_KEY);

		try
		{
			$result_xml = $webservice_exi->CustomQuoteCalc($parameter);
		}
		catch (SoapFault $exception) 
		{ 
			echo 'EXCEPTION='.$exception; 
		}    

    return json_encode($result_xml->CustomQuoteCalcResult);
}



public function loadDeco()

{

    if ($id_product = (int)Tools::getValue('product_id'))

	$product = new Product($id_product, true, $this->context->language->id, $this->context->shop->id);

    $this->context->smarty->assign(array('product'=>$product,

        'idproduct'=>$id_product));

    

    $data = $this->display(__FILE__, 'customquote_admin.tpl');

    return $data;

}





}