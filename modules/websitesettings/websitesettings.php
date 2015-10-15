<?php
if (!defined('_PS_VERSION_'))
  exit;
 
class WebsiteSettings extends Module
{
  public function __construct()
  {
    $this->name = 'websitesettings';
    $this->tab = 'administration';
    $this->version = '1.0.0';
    $this->author = 'Ayushi Agarwal';
    $this->need_instance = 0;
    $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_); 
    $this->bootstrap = true;
 
    parent::__construct();
 
    $this->displayName = $this->l('Website Settings');
    $this->description = $this->l('Website Settings');
 
    $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
 
  }
  
  
public function install()
{
  if (!parent::install())
    return false;
  
   return parent::install() && $this->registerHook('actionAdminControllerSetMedia');
}

public function uninstall()
{
  if (!parent::uninstall())
    return false;
  return true;
}

public function getContent()
{
    $output = null;
 
    if (Tools::isSubmit('submit'.$this->name))
    {
        $list_value = strval(Tools::getValue('PROFIT_MARGIN'));
        if (!$list_value  || empty($list_value) || !Validate::isFloat($list_value))
            $output .= $this->displayError( $this->l('Invalid Configuration value') );
        else
        {
            $email_value = strval(Tools::getValue('ORDER_CNF_MANAGER_EMAIL'));
            if((Tools::getValue('ADMIN_CONFIRM_ORDER')=='1' &&  (empty($email_value) || !Validate::isEmail($email_value))))
                $output .= $this->displayError( $this->l('Please enter valid Email ID'));
            else{
                $email_value = strval(Tools::getValue('PRODUCT_REQUEST_EMAIL'));
                if(empty($email_value) || !Validate::isEmail($email_value))
                      $output .= $this->displayError( $this->l('Please enter valid Email ID'));
            else{
            Configuration::updateValue('PROFIT_MARGIN', $list_value);
            Configuration::updateValue('PRODUCT_DYNAMIC_PRICE', Tools::getValue('PRODUCT_DYNAMIC_PRICE'));
            Configuration::updateValue('ORDER_CNF_MANAGER_EMAIL', Tools::getValue('ORDER_CNF_MANAGER_EMAIL'));
            Configuration::updateValue('ADMIN_CONFIRM_ORDER', Tools::getValue('ADMIN_CONFIRM_ORDER'));    
            Configuration::updateValue('PRODUCT_REQUEST_EMAIL', Tools::getValue('PRODUCT_REQUEST_EMAIL'));
            Configuration::updateValue('PRODUCT_REQUEST_SAMPLE', Tools::getValue('PRODUCT_REQUEST_SAMPLE')); 
            Configuration::updateValue('PRODUCT_DOWNLOAD_BUTTON', Tools::getValue('PRODUCT_DOWNLOAD_BUTTON'));          
            $output .= $this->displayConfirmation($this->l('Settings updated'));
            }
        }}
    }
    $this->context->controller->addJS($this->_path.'websitesettings.js');
    //return $this->display(__FILE__,'productupdate.tpl');
    return $output.$this->displayForm();
}

public function hookActionAdminControllerSetMedia($params)
{
 
    // add necessary javascript to products back office
    if($this->context->controller->controller_name == 'AdminProducts' && Tools::getValue('id_product'))
    {
        $this->context->controller->addJS($this->_path.'/js/websitesettings.js');
    }
 
}


public function displayForm()
{
    // Get default Language
    $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
     
    // Init Fields form array
    $fields_form[0]['form'] = array(
        'legend' => array(
            'title' => $this->l('Website Settings'),
        ),
        'input' => array(
            array(
                    'type' => 'switch',
                    'label' => $this->l('Dynamic Price'),
                    'name' => 'PRODUCT_DYNAMIC_PRICE',
                    'id' => 'prd_dynamic_price',
                    'required' => false,
                    'class' => 't',
                    'is_bool' => true,
                    'hint' => $this->l('Enable to Calculate price as, web service cost + store product margin'),
                    'values' => array(
                            array(
                                    'id' => 'active_on',
                                    'value' => 1,
                                    'label' => $this->l('Dynamic Price')
                            ),
                            array(
                                    'id' => 'active_off',
                                    'value' => 0,
                                    'label' => $this->l('Static Price')
                            )
                    )
            ), array(
                'type' => 'text',
                'label' => $this->l('Set Profit Margin'),
                'name' => 'PROFIT_MARGIN',
                'size' => 20,
                'required' => true
            ),
             array(
                    'type' => 'switch',
                    'label' => $this->l('Orders to be confirm by manager'),
                    'name' => 'ADMIN_CONFIRM_ORDER',
                    'required' => false,
                    'class' => 't',
                    'is_bool' => true,
                    'hint' => $this->l('Enabled: Orders needs to be confirmed by manager before Approval'),
                    'values' => array(
                            array(
                                    'id' => 'active_on',
                                    'value' => 1,
                                    'label' => $this->l('Yes')
                            ),
                            array(
                                    'id' => 'active_off',
                                    'value' => 0,
                                    'label' => $this->l('No')
                            )
                    )
            ), 
                 array(
                    'type' => 'text',
                    'label' => $this->l('Manager Email'),
                    'name' => 'ORDER_CNF_MANAGER_EMAIL',
                    'size' => 100,
                    'required' => false
                ),
            array(
                    'type' => 'switch',
                    'label' => $this->l('Request A Sample'),
                    'name' => 'PRODUCT_REQUEST_SAMPLE',
                    'required' => false,
                    'class' => 't',
                    'is_bool' => true,
                    'hint' => $this->l('Enabled: To show request a sample button on product page'),
                    'values' => array(
                            array(
                                    'id' => 'active_on',
                                    'value' => 1,
                                    'label' => $this->l('Yes')
                            ),
                            array(
                                    'id' => 'active_off',
                                    'value' => 0,
                                    'label' => $this->l('No')
                            )
                    )
            )
            ,
            array(
                    'type' => 'switch',
                    'label' => $this->l('Product Download Button'),
                    'name' => 'PRODUCT_DOWNLOAD_BUTTON',
                    'required' => false,
                    'class' => 't',
                    'is_bool' => true,
                    'hint' => $this->l('Enabled: To show download button on product page'),
                    'values' => array(
                            array(
                                    'id' => 'active_on',
                                    'value' => 1,
                                    'label' => $this->l('Yes')
                            ),
                            array(
                                    'id' => 'active_off',
                                    'value' => 0,
                                    'label' => $this->l('No')
                            )
                    )),
            
                 array(
                    'type' => 'text',
                    'label' => $this->l('Email for Request Sample/Quote'),
                    'name' => 'PRODUCT_REQUEST_EMAIL',
                    'size' => 100,
                    'required' => false
                )
        ),
        'submit' => array(
            'title' => $this->l('Update'),
            'class' => 'button'
        )
    );
     
    $helper = new HelperForm();
     
    // Module, t    oken and currentIndex
    $helper->module = $this;
    $helper->name_controller = $this->name;
    $helper->token = Tools::getAdminTokenLite('AdminModules');
    $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
     
    // Language
    $helper->default_form_language = $default_lang;
    $helper->allow_employee_form_lang = $default_lang;
     
    // Title and toolbar
    $helper->title = $this->displayName;
    $helper->show_toolbar = true;        // false -> remove toolbar
    $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
    $helper->submit_action = 'submit'.$this->name;
    $helper->toolbar_btn = array(
        'save' =>
        array(
            'desc' => $this->l('Save'),
            'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
            '&token='.Tools::getAdminTokenLite('AdminModules'),
        ),
        'back' => array(
            'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
            'desc' => $this->l('Back to list')
        )
    );
     
    // Load current value
    
    $helper->fields_value['PROFIT_MARGIN'] = Configuration::get('PROFIT_MARGIN'); 
    $helper->fields_value['ADMIN_CONFIRM_ORDER'] = Configuration::get('ADMIN_CONFIRM_ORDER'); 
    $helper->fields_value['ORDER_CNF_MANAGER_EMAIL'] = Configuration::get('ORDER_CNF_MANAGER_EMAIL'); 
    $helper->fields_value['PRODUCT_DYNAMIC_PRICE'] = Configuration::get('PRODUCT_DYNAMIC_PRICE');
    $helper->fields_value['PRODUCT_REQUEST_SAMPLE'] = Configuration::get('PRODUCT_REQUEST_SAMPLE'); 
    $helper->fields_value['PRODUCT_DOWNLOAD_BUTTON'] = Configuration::get('PRODUCT_DOWNLOAD_BUTTON'); 
    $helper->fields_value['PRODUCT_REQUEST_EMAIL'] = Configuration::get('PRODUCT_REQUEST_EMAIL');
     
    return $helper->generateForm($fields_form);
}

public function ajaxCall()
{
    global $smarty, $cookie;
}


}