<?php
if (!defined('_PS_VERSION_'))
  exit;
 
class ConvertToPDF extends Module
{
  public function __construct()
  {
    $this->name = 'converttopdf';
    $this->tab = 'front_office_features';
    $this->version = '1.0.0';
    $this->author = 'Ayushi Agarwal';
    $this->need_instance = 0;
    $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_); 
    $this->bootstrap = true;
 
    parent::__construct();
 
    $this->displayName = $this->l('Convert to PDF');
    $this->description = $this->l('Convert Product Catalog to PDF');
 
    $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
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

public function hookdisplayconverttopdf()
{
  $this->context->controller->addJS(($this->_path).'converttopdf.js');
  $this->context->controller->addJS(($this->_path).'dist/jspdf.min.js');
  $this->context->controller->addJS(($this->_path).'dist/jspdf.plugin.autotable.js');
    $url = PS_SHOP_PATH.'/modules/converttopdf/converttopdf-ajax.php';
    $this->smarty->assign('url',$url);
    return $this->display(__FILE__, 'converttopdf.tpl');
}
   
public function createPDF($url){
    $url = 'http://clients.skiify.com/eximagen/en/boligrafos-plasticos/430-boligrafo-pilot.html';
    require_once('/html2pdf/html2fpdf.php');
    // Create new HTML2PDF class for an A4 page, measurements in mm
    $pdf = new HTML2FPDF('P','mm','A4');
    $buffer = file_get_contents($url);
    var_dump($buffer);
    // Optional top margin
    $pdf->SetTopMargin(1);
    $pdf->AddPage();
    // Control the x-y position of the html
    $pdf->SetXY(0,0);
    $pdf->WriteHTML($buffer);

    // The 'D' arg forces the browser to download the file 
    $pdf->Output('MyFile.pdf','D');
}

public function ajaxCall()
{
    return $this->createPDF(Tools::getValue('url'));
}
}