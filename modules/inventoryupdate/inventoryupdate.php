<?php
if (!defined('_PS_VERSION_'))
  exit;
 
class InventoryUpdate extends Module
{
  public function __construct()
  {
    $this->name = 'inventoryupdate';
    $this->tab = 'administration';
    $this->version = '1.0.0';
    $this->author = 'Ayushi Agarwal';
    $this->need_instance = 0;
    $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_); 
    $this->bootstrap = true;
 
    parent::__construct();
 
    $this->displayName = $this->l('Inventory Update');
    $this->description = $this->l('Update Store Product Inventory Information.');
 
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
        $list_value = strval(Tools::getValue('PRODUCTUPDATE_LIST'));
        if (!$list_value  || empty($list_value) || !Validate::isGenericName($list_value))
            $output .= $this->displayError( $this->l('Invalid Configuration value') );
        else
        {
            Configuration::updateValue('PRODUCTUPDATE_LIST', $list_value);
            Configuration::updateValue('PRODUCTINVENTORYUPDATE_STATUS', '0');
            return $this->display(__FILE__,'inventoryupdate.tpl');
            $output .= $this->displayConfirmation($this->l('Settings updated'));
        }
    }
    //$this->context->controller->addJS($this->_path.'productupdate.js');
    //return $this->display(__FILE__,'productupdate.tpl');
    return $output.$this->displayForm();
}

public function hookActionAdminControllerSetMedia($params)
{
 
    // add necessary javascript to products back office
    if($this->context->controller->controller_name == 'AdminProducts' && Tools::getValue('id_product'))
    {
        $this->context->controller->addJS($this->_path.'/js/inventoryupdate.js');
    }
 
}


public function displayForm()
{
    // Get default Language
    $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
     
    // Init Fields form array
    $fields_form[0]['form'] = array(
        'legend' => array(
            'title' => $this->l('Product Inventory Update'),
        ),
        'input' => array(
            array(
                'type' => 'text',
                'label' => $this->l('List'),
                'name' => 'PRODUCTUPDATE_LIST',
                'size' => 20,
                'required' => true
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
    $helper->fields_value['PRODUCTUPDATE_LIST'] = Configuration::get('PRODUCTUPDATE_LIST');
     
    return $helper->generateForm($fields_form);
}



}