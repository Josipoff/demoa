<?php 
/**
 * @author MyPresta.eu | Milos "VEKIA" Myszczuk <support@mypresta.eu>
 * All rights reserved! Copying, duplication strictly prohibited
 * http://www.mypresta.eu - prestashop modules
 * 
 */
 
class mib extends Module {
	function __construct(){
        ini_set("display_errors", 0);
        error_reporting(0);
		$this->name = 'mib';
		$this->tab = 'advertising_marketing';
        $this->author = 'MyPresta.eu';
		$this->version = '1.1.3.0';
		parent::__construct();
        $this->trusted();
		$this->displayName = $this->l('Manufacturers Images Block');
		$this->description = $this->l('Module creates block with manufacturer pictures with links');
   
        //automatic update notification code
        $this->mkey="nlc";       
        if (@file_exists('../modules/'.$this->name.'/key.php'))
            @require_once ('../modules/'.$this->name.'/key.php');
        else if (@file_exists(dirname(__FILE__) . $this->name.'/key.php'))
            @require_once (dirname(__FILE__) . $this->name.'/key.php');
        else if (@file_exists('modules/'.$this->name.'/key.php'))
            @require_once ('modules/'.$this->name.'/key.php');                        
        $this->checkforupdates();            
    }
    
    function checkforupdates(){
        if (isset($_GET['controller']) OR isset($_GET['tab'])){
            if (Configuration::get('update_'.$this->name) < (date("U")-86400)){
                $actual_version = mibUpdate::verify($this->name,$this->mkey,$this->version);
            }
            if (mibUpdate::version($this->version)<mibUpdate::version(Configuration::get('updatev_'.$this->name))){
                $this->warning=$this->l('New version available, check http://MyPresta.eu for more informations');
            }
        }            
    }
    
        function trusted(){
            if (_PS_VERSION_ >= "1.6.0.8"){
                if (isset($_GET['controller'])){
                    if ($_GET['controller']=="AdminModules"){
                        if (defined('self::CACHE_FILE_TRUSTED_MODULES_LIST')==true){
                            $context = Context::getContext();
                    		$theme = new Theme($context->shop->id_theme);
                    		// Save the 2 arrays into XML files
                    		$trusted_xml = new SimpleXMLElement('<modules_list/>');
                    		$trusted_xml->addAttribute('theme', $theme->name);
                    		$modules = $trusted_xml->addChild('modules');
                    		$modules->addAttribute('type', 'trusted');
                    		$module = $modules->addChild('module');
                    		$module->addAttribute('name', $this->name);
                            $success = file_put_contents( _PS_ROOT_DIR_.self::CACHE_FILE_TRUSTED_MODULES_LIST, $trusted_xml->asXML());
                            
                        }
                    }
                }
            }
        }          

	public static function psversion($part=1) {
		$version=_PS_VERSION_;
		$exp=$explode=explode(".",$version);
        if ($part==1)
		  return $exp[1];
        if ($part==2)
		  return $exp[2];
        if ($part==3)
		  return $exp[3];
	}

	function install(){
	    if ($this->psversion()==5 || $this->psversion()==6){
            if (parent::install() == false 
                OR !Configuration::updateValue('update_'.$this->name,'0')
                OR !$this->registerHook('leftColumn')
                OR !$this->registerHook('header')
            ){
                return false;
            }
        }
        return true;
	}
    
    public function hookHeader($params){
        $this->context->controller->addCSS(($this->_path).'mib.css', 'all');
    }

    public function hookHome($params){
        $manufacturers = Manufacturer::getManufacturers();
        foreach ($manufacturers as &$manufacturer){
            $manufacturer['image'] = $this->context->language->iso_code.'-default';
            if (file_exists(_PS_MANU_IMG_DIR_.$manufacturer['id_manufacturer'].'-'.ImageType::getFormatedName('medium').'.jpg'))
                $manufacturer['image'] = $manufacturer['id_manufacturer'];
                $manufacturer['image_url'] = $manufacturer['id_manufacturer'].'-'.ImageType::getFormatedName('medium').'.jpg';
        }
        $this->smarty->assign(array(
		  'manufacturers' => $manufacturers
        ));
        return $this->display(__FILE__, 'mib.tpl');
    }
    
    public function hookleftColumn($params){
        $manufacturers = Manufacturer::getManufacturers();
        foreach ($manufacturers as &$manufacturer){
            $manufacturer['image'] = $this->context->language->iso_code.'-default';
            if (file_exists(_PS_MANU_IMG_DIR_.$manufacturer['id_manufacturer'].'-'.ImageType::getFormatedName('medium').'.jpg'))
                $manufacturer['image'] = $manufacturer['id_manufacturer'];
                $manufacturer['image_url'] = $manufacturer['id_manufacturer'].'-'.ImageType::getFormatedName('medium').'.jpg';
        }
        $this->smarty->assign(array(
		  'manufacturers' => $manufacturers
        ));
        return $this->display(__FILE__, 'mib.tpl');        
    }

    public function hookrightColumn($params){
        $manufacturers = Manufacturer::getManufacturers();
        foreach ($manufacturers as &$manufacturer){
            $manufacturer['image'] = $this->context->language->iso_code.'-default';
            if (file_exists(_PS_MANU_IMG_DIR_.$manufacturer['id_manufacturer'].'-'.ImageType::getFormatedName('medium').'.jpg'))
                $manufacturer['image'] = $manufacturer['id_manufacturer'];
                $manufacturer['image_url'] = $manufacturer['id_manufacturer'].'-'.ImageType::getFormatedName('medium').'.jpg';
        }
        $this->smarty->assign(array(
		  'manufacturers' => $manufacturers
        ));
        return $this->display(__FILE__, 'mib.tpl');  
    }
    
    public function hookTop($params){
        $manufacturers = Manufacturer::getManufacturers();
        foreach ($manufacturers as &$manufacturer){
            $manufacturer['image'] = $this->context->language->iso_code.'-default';
            if (file_exists(_PS_MANU_IMG_DIR_.$manufacturer['id_manufacturer'].'-'.ImageType::getFormatedName('medium').'.jpg'))
                $manufacturer['image'] = $manufacturer['id_manufacturer'];
                $manufacturer['image_url'] = $manufacturer['id_manufacturer'].'-'.ImageType::getFormatedName('medium').'.jpg';
        }
        $this->smarty->assign(array(
		  'manufacturers' => $manufacturers
        ));
        return $this->display(__FILE__, 'mib.tpl');  
    }
    
    public function hookFooter($params){
        $manufacturers = Manufacturer::getManufacturers();
        foreach ($manufacturers as &$manufacturer){
            $manufacturer['image'] = $this->context->language->iso_code.'-default';
            if (file_exists(_PS_MANU_IMG_DIR_.$manufacturer['id_manufacturer'].'-'.ImageType::getFormatedName('medium').'.jpg'))
                $manufacturer['image'] = $manufacturer['id_manufacturer'];
                $manufacturer['image_url'] = $manufacturer['id_manufacturer'].'-'.ImageType::getFormatedName('medium').'.jpg';
        }
        $this->smarty->assign(array(
		  'manufacturers' => $manufacturers
        ));
        return $this->display(__FILE__, 'mib.tpl');  
    }    
    
 
    public function msg_saved(){
        return "<div class=\"conf confirm\">".$this->l('Saved')."</div>";
    }
    
	public function getContent(){
	   	$output=""; 
        
        if (Tools::isSubmit('selecttab')){
            Configuration::updateValue('mib_lasttab',"{$_POST['selecttab']}");
        }

       	$output.="";
        return $output.$this->displayForm();
    }
        
        public function displayForm(){            

	    return '
        
        <style>
            .language_flags {text-align:left;}
            #topmenu-horizontal-module {overflow:hidden; background-color: #F8F8F8; border: 1px solid #CCCCCC; margin-bottom: 10px; padding: 10px 0; border-radius: 3px; -moz-border-radius: 3px; -webkit-border-radius: 3px;}
            #topmenu-horizontal-module .addnew, #topmenu-horizontal-module .btnpro {-webkit-border-radius:4px; -moz-border-radius:4px; -border-radius:4px; padding:5px; margin-right:10px; cursor:pointer; width:52px; height:52px; display:inline-block; float:right; text-align:center; border:1px dotted #c0c0c0; }
            #topmenu-horizontal-module .addnew:hover, #topmenu-horizontal-module .btnpro:hover {border:1px solid #bfbfbf; background:#f3f3f3;}
            #topmenu-horizontal-module span.img {margin:auto; width:32px; height:32px; display:block;}
            #topmenu-horizontal-module span.txt {margin-top:3px; width:52px; display:block; text-align:center;}
            #topmenu-horizontal-module .addnew span.img {background:url(\''._MODULE_DIR_.$this->name.'/img/add.png\') no-repeat center;}
            #topmenu-horizontal-module .save span.img {background:url(\''._MODULE_DIR_.$this->name.'/img/on.png\') no-repeat center;}
            #topmenu-horizontal-module .back span.img {background:url(\''._MODULE_DIR_.$this->name.'/img/back.png\') no-repeat center;}
            
                .slides {margin:0px; padding:0px;}
                .slides li { font-size:15px!important; list-style: none; margin: 0 0 4px 0; padding: 15px 10px; background-color: #F4E6C9; border: #CCCCCC solid 1px; color:#000;}
                .slides li:hover {border:1px #000 dashed; cursor:move;}
                .slides li .name {font-size:18px!important;}
                .slides li .nb {color:#FFF; background:#000; padding:5px 10px; font-size:18px; font-weight:bold; margin-right:10px; }

                .activate {display:inline-block; float:right; padding-right:5px;  cursor:pointer; position:relative; top:2px;}
                .activate img {max-width:50px; height:auto;}
                .remove {opacity:0.3; position:relative; top:-1px; width:24px; height:24px; display:inline-block; float:right; background:url("../modules/'.$this->name.'/img/trash.png") top no-repeat; cursor:pointer;}
                .edit {margin-right:6px; opacity:0.3; position:relative;  width:24px; height:24px; display:inline-block; float:right; background:url("../modules/'.$this->name.'/img/edit.png") top no-repeat; cursor:pointer;}
                
                .remove:hover, .edit:hover, .activate:hover { opacity:1.0; }
                .edit,.remove {margin-right:5px;}
                
                
        </style>   
        <form name="selectform1" id="selectform1" action="'.$_SERVER['REQUEST_URI'].'" method="post"><input type="hidden" name="selecttab" value="1"></form>
        <form name="selectform2" id="selectform2" action="'.$_SERVER['REQUEST_URI'].'" method="post"><input type="hidden" name="selecttab" value="2"></form>
        <form name="selectform3" id="selectform3" action="'.$_SERVER['REQUEST_URI'].'" method="post"><input type="hidden" name="selecttab" value="3"></form>
        <form name="selectform4" id="selectform4" action="'.$_SERVER['REQUEST_URI'].'" method="post"><input type="hidden" name="selecttab" value="4"></form>
        '."<div id='cssmenu'>
            <ul>
               <li class='bgver'><a><span>v".$this->version."</span></a></li>
               <li class='".(Configuration::get('mib_lasttab')==1 ? 'active':'')."'><a href='#' onclick=\"selectform1.submit()\"><span>".$this->l('Configuration')."</span></a></li>
               <li style='position:relative; display:inline-block; float:right; width:65px;'><a href='http://mypresta.eu' target='_blank' title='prestashop modules'><img src='../modules/".$this->name."/logo-white.png' alt='prestashop modules' style=\"position:absolute; top:17px; right:16px;\"/></a></li>
               <li style='position:relative; display:inline-block; float:right;'><a href='http://mypresta.eu/contact.html' target='_blank'><span>".$this->l('Support')."</span></a></li>
               <li style='position:relative; display:inline-block; float:right;'><a href='http://mypresta.eu/modules/advertising-and-marketing/rewards-voucher-codes-after-orders.html' target='_blank'><span>".$this->l('Updates')."</span></a></li>
            </ul>
        </div>".'<link href="../modules/'.$this->name.'/css.css" rel="stylesheet" type="text/css" /><iframe src="//apps.facepages.eu/somestuff/whatsgoingon.html" width="100%" height="150" border="0" style="border:none;"></iframe><div style="float:left; text-align:left; display:inline-block; margin-top:5px;">'.$this->l('like us on Facebook').'</br><iframe src="//www.facebook.com/plugins/like.php?href=http%3A%2F%2Ffacebook.com%2Fmypresta&amp;send=false&amp;layout=button_count&amp;width=120&amp;show_faces=true&amp;font=verdana&amp;colorscheme=light&amp;action=like&amp;height=21&amp;appId=276212249177933" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:120px; height:21px; margin-top:10px;" allowtransparency="true"></iframe>
        </div>
        <div style="float:left; text-align:left; display:inline-block; margin-top:5px;">
        <form target="_blank" action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top" style="margin-top:15px;">
        <input type="hidden" name="cmd" value="_s-xclick">
        <input type="hidden" name="hosted_button_id" value="CRTHNBD2U8KPW">
        <input type="image" src="https://www.paypalobjects.com/en_GB/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal – The safer, easier way to pay online.">
        <img alt="" border="0" src="https://www.paypalobjects.com/pl_PL/i/scr/pixel.gif" width="1" height="1">
        </form> 
        </div>
        '.'<div style="float:right; text-align:right; display:inline-block; margin-top:5px; font-size:10px;">
        '.$this->l('Proudly developed by').' <a href="http://mypresta.eu" style="font-weight:bold; color:#B73737">MyPresta<font style="color:black;">.eu</font></a>
       ';        
	} 
  
}

class mibUpdate extends mib {  
    public static function version($version){
        $version=(int)str_replace(".","",$version);
        if (strlen($version)==3){$version=(int)$version."0";}
        if (strlen($version)==2){$version=(int)$version."00";}
        if (strlen($version)==1){$version=(int)$version."000";}
        if (strlen($version)==0){$version=(int)$version."0000";}
        return (int)$version;
    }
    
    public static function encrypt($string){
        return base64_encode($string);
    }
    
    public static function verify($module,$key,$version){
        if (ini_get("allow_url_fopen")) {
             if (function_exists("file_get_contents")){
                $actual_version = @file_get_contents('http://dev.mypresta.eu/update/get.php?module='.$module."&version=".self::encrypt($version)."&lic=$key&u=".self::encrypt(_PS_BASE_URL_.__PS_BASE_URI__));
             }
        }
        Configuration::updateValue("update_".$module,date("U"));
        Configuration::updateValue("updatev_".$module,$actual_version); 
        return $actual_version;
    }
}
?>