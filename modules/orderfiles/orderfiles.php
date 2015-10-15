<?php
class orderfiles extends Module {
	function __construct(){
        ini_set("display_errors", 0);
        error_reporting(0);  
		$this->name = 'orderfiles';
		$this->tab = 'Blocks';
		$this->version = '1.6.5.9';
        $this->dir = '/modules/orderfiles/';
        $this->author="MyPresta.eu";
		parent::__construct();
        $this->trusted();
		$this->displayName = $this->l('Customer file upload');
		$this->description = $this->l('This module allows you to create an upload form for your customers to upload files to their orders');
        if ($this->psversion()==5 || $this->psversion()==6){
		  $this->tabClassName = 'orderfilestab5';
        } else {
          $this->tabClassName = 'orderfilestab';  
        }
		$this->tabParentName = 'AdminOrders';
		$this->tabname=$this->l('Files Manager');
       
        
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
                $actual_version = orderfilesUpdate::verify($this->name,$this->mkey);
            }
            if (orderfilesUpdate::version($this->version)<orderfilesUpdate::version(Configuration::get('updatev_'.$this->name))){
                $this->warning=$this->l('New version available, check <a href="http://MyPresta.eu">MyPresta.eu</a> for more informations');
            }
        }
    }
    
    function trusted(){
            if (_PS_VERSION_ <= "1.6.0.8"){
                if (isset($_GET['controller'])){
                    if ($_GET['controller']=="AdminModules"){
                        if (defined('self::CACHE_FILE_TRUSTED_MODULES_LIST')==true){
                            $context = Context::getContext();
                    		$theme = new Theme($context->shop->id_theme);
                            $xml= simplexml_load_string(file_get_contents(_PS_ROOT_DIR_.self::CACHE_FILE_TRUSTED_MODULES_LIST));
                            if ($xml){
                                $css=$xml->modules->addChild('module');
                                $css->addAttribute('name',$this->name);
                                $xmlcode=$xml->asXML();
                                if (!strpos(file_get_contents(_PS_ROOT_DIR_.self::CACHE_FILE_TRUSTED_MODULES_LIST),$this->name))
                                    file_put_contents(_PS_ROOT_DIR_.self::CACHE_FILE_TRUSTED_MODULES_LIST,$xmlcode);
                            }
                            if (defined('self::CACHE_FILE_DEFAULT_COUNTRY_MODULES_LIST')==true){
                                $xml= simplexml_load_string(file_get_contents(_PS_ROOT_DIR_.self::CACHE_FILE_DEFAULT_COUNTRY_MODULES_LIST));
                                //$xml=new SimpleXMLElement('<modules/>');
                                //$cs=$xml->addChild('modules');
                                if ($xml){
                                $css=$xml->addChild('module');
                                $css->addChild('id',0);
                                $css->addChild('name',"<![CDATA[".$this->name."]]>");
                                $xmlcode=$xml->asXML();
                                $xmlcode=str_replace('&lt;',"<",$xmlcode);
                                $xmlcode=str_replace('&gt;',">",$xmlcode);
                                if (!strpos(file_get_contents(_PS_ROOT_DIR_.self::CACHE_FILE_DEFAULT_COUNTRY_MODULES_LIST),$this->name))
                                    file_put_contents(_PS_ROOT_DIR_.self::CACHE_FILE_DEFAULT_COUNTRY_MODULES_LIST,$xmlcode);                                    
                                }
                            }
                        }
                    }
                }
            }
        }   
    
	function install(){
        if (parent::install() == false
            OR !$this->createdb()
            OR !$this->registerHook('header')
            OR !$this->registerHook('customerAccount')
            OR !$this->registerHook('myAccountBlock')
            OR !$this->registerHook('productFooter')
            OR !$this->registerHook('shoppingCart')
            OR !$this->registerHook('newOrder')
            OR !$this->registerHook('backofficefooter')
            OR !$this->registerHook('productTab') 
            OR !$this->registerHook('productTabContent')
            OR !$this->registerHook('adminOrder')
            OR !$this->registerHook('orderDetailDisplayed')
            OR !Configuration::updateValue('update_orderfiles',0)
            OR !Configuration::updateValue('updatev_orderfiles',$this->version)
            OR !Configuration::updateValue('OF_LAST_TAB','1')
            OR !Configuration::updateValue('OF_MAX_FILE_SIZE','2048')
            OR !Configuration::updateValue('OF_CUSTOMERACCOUNT','1')
            OR !Configuration::updateValue('OF_MYACCOUNTBLOCK','1')
            OR !Configuration::updateValue('OF_SHOPPINGCART','1')
            OR !Configuration::updateValue('OF_UPRODUCT','1')
            OR !Configuration::updateValue('OF_UCART','1')
            OR !Configuration::updateValue('OF_UACCOUNT','1')
            OR !Configuration::updateValue('OF_FTYPES','esp,cdr,jpg,png,tif')         
        ){
            return false;
        }
        
       	if (!isset($id_tab)) {
	      	$tab = new Tab();
            if ($this->psversion()==5 || $this->psversion()==6){
	      	    $tab->class_name = $this->tabClassName;
            } else {
                $tab->class_name = $this->tabClassName."";
            }
	      	$tab->id_parent = Tab::getIdFromClassName($this->tabParentName);
	      	$tab->module = $this->name;
	      	$languages = Language::getLanguages();
	      	foreach ($languages as $language)
		        $tab->name[$language['id_lang']] = $this->tabname;
	    	$tab->add();
    	}
		   
		return true;
	}

	
	public function uninstall(){
		if (parent::uninstall() == false) {
			return false;
		}
        if ($this->psversion()==5 || $this->psversion()==6){
    	    $id_tab = Tab::getIdFromClassName($this->tabClassName);
            $id_tab2 = Tab::getIdFromClassName("orderfilestab");
                        
        } else {
            $id_tab = Tab::getIdFromClassName($this->tabClassName);    
        }
	    if ($id_tab) {
	      $tab = new Tab($id_tab);
          $tab2 = new Tab($id_tab2);
	      $tab->delete();
          $tab2->delete();
	    }
	    return true;
	}
	
	public function checkToken(){
		return true;
	} 
   
    function createdb(){
        $db = Db::getInstance(); 
        $query = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."orderfiles` (
        `id` INT NOT NULL AUTO_INCREMENT ,
        `idproduct` INT ,
        `title` VARCHAR(250) NULL ,
        `description` TEXT NULL,
        `id_order` INT NULL ,
        `id_customer` INT NULL ,
        `filename` VARCHAR(250) NULL ,
        `adminfile` INT NOT NULL DEFAULT 0,
        PRIMARY KEY (`id`) )
        ENGINE = "._MYSQL_ENGINE_." DEFAULT CHARSET=utf8";
        $db->Execute($query);
        
        $query = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."orderfiles_cart` (
        `id` INT NOT NULL AUTO_INCREMENT ,
        `idcart` INT,
        `idproduct` INT,
        `title` VARCHAR(250) NULL ,
        `description` TEXT NULL,
        `id_customer` INT NULL ,
        `filename` VARCHAR(250) NULL ,
        PRIMARY KEY (`id`) )
        ENGINE = "._MYSQL_ENGINE_." DEFAULT CHARSET=utf8";
        $db->Execute($query);
        
        $query = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."orderfiles_product` (
        `id` INT NOT NULL AUTO_INCREMENT ,
        `idcart` INT,
        `idproduct` INT,
        `cookieid` VARCHAR(250),
        `title` VARCHAR(250) NULL,
        `description` TEXT NULL,
        `id_customer` INT NULL,
        `filename` VARCHAR(250) NULL,
        PRIMARY KEY (`id`) )
        ENGINE = "._MYSQL_ENGINE_." DEFAULT CHARSET=utf8";
        $db->Execute($query);
        return true;
    }
    
	public function psversion() {
		$version=_PS_VERSION_;
		$exp=$explode=explode(".",$version);
		return $exp[1];
	}    
    
  
    public function getconf(){
    	$array['OF_LAST_TAB']=Configuration::get('OF_LAST_TAB');
        $array['OF_MAX_FILE_SIZE']=Configuration::get('OF_MAX_FILE_SIZE');
        $array['OF_CUSTOMERACCOUNT']=Configuration::get('OF_CUSTOMERACCOUNT');
        $array['OF_MYACCOUNTBLOCK']=Configuration::get('OF_MYACCOUNTBLOCK');
        $array['OF_SHOPPINGCART']=Configuration::get('OF_SHOPPINGCART');
        
        return $array;
    }
    
    public function getContent(){
        $output="";
        
        if (Tools::isSubmit('selecttab')){
            Configuration::updateValue('OF_LAST_TAB',"$_POST[selecttab]");
        }

        if (Tools::isSubmit('submit_general_settings')){
            $ext="";
            $extensions=explode(",",Tools::getValue('OF_FTYPES'));
            foreach ($extensions AS $k=>$v){
                $ext.=str_replace(".","",trim($v)).",";
            }
            Configuration::updateValue('OF_FTYPES',substr($ext,"0",-1));
            Configuration::updateValue('OF_MAX_FILE_SIZE',"$_POST[OF_max_file_size]");
            $output.='<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('Settings Saved').'" />'.$this->l('Settings Saved').'</div>';
        }
        
        if (Tools::isSubmit('submit_upload_settings')){
            
            Configuration::updateValue('OF_AJAXUPLOAD',(Tools::getValue('OF_AJAXUPLOAD')==1 ? 1:0));
            Configuration::updateValue('OF_CERTPROD',(Tools::getValue('OF_CERTPROD')==1 ? 1:0));
            Configuration::updateValue('OF_CERTPROD_CART',(Tools::getValue('OF_CERTPROD_CART')==1 ? 1:0));
            Configuration::updateValue('OF_CERTPROD_ID',trim(str_replace(" ","",Tools::getValue('OF_CERTPROD_ID'))));
            Configuration::updateValue('OF_PTAB',(Tools::getValue('OF_PFORM')==1 ? 1:0));
            Configuration::updateValue('OF_PFOOTER',(Tools::getValue('OF_PFORM')==2 ? 1:0));
            Configuration::updateValue('OF_SCART',(Tools::getValue('OF_SCART')==1 ? 1:0));
            Configuration::updateValue('OF_CACCOUNT',(Tools::getValue('OF_CACCOUNT')==1 ? 1:0));
            Configuration::updateValue('OF_UNLOGGED',(Tools::getValue('OF_UNLOGGED')==1 ? 1:0));
            Configuration::updateValue('OF_NOF',(Tools::getValue('OF_NOF')==1 || Tools::getValue('OF_NOF')==2 || Tools::getValue('OF_NOF')==3 ? Tools::getValue('OF_NOF'):0));
            
            Configuration::updateValue('OF_NOF_PRODUCTS',Tools::getValue('OF_NOF_PRODUCTS'));
            Configuration::updateValue('OF_NOF_ORDER',Tools::getValue('OF_NOF_ORDER'));
            
            $output.='<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('Settings Saved').'" />'.$this->l('Settings Saved').'</div>';
        }        
              
        return $output.$this->displayForm();
    }
    
	public function currency_sign($id){
		$currency=new CurrencyCore($id);
		return $currency->sign;
	} 



public function inconsistency(){
    $prefix = _DB_PREFIX_;
    $engine = _MYSQL_ENGINE_;    
  

        $table['orderfiles']['adminfile']['type']='int';
        $table['orderfiles']['adminfile']['length']=10;
        $table['orderfiles']['adminfile']['default']=0;
    
        $return='';
        
        
        //orderfiles
        foreach (Db::getInstance()->executeS("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA ='"._DB_NAME_."' AND TABLE_NAME='"._DB_PREFIX_."orderfiles'") AS $key => $column){
            $return[$column['COLUMN_NAME']]="1";
        }
        foreach ($table['orderfiles'] as $key => $field){
            if (!isset($return[$key])){
                $error[$key]['type']="0";
                $error[$key]['message']=$this->l('Database inconsistency, column does not exist');
                if ($field['default']!="X"){
                    if ($this->runStatement("ALTER TABLE `${prefix}orderfiles` ADD COLUMN `".$key."` ".$field['type']."(".$field['length'].") NULL DEFAULT '".$field['default']."'")){
                        $error[$key]['fixed']=$this->l('... FIXED!');
                    } else {
                        $error[$key]['fixed']=$this->l('... ERROR!');
                    }
                } else {
                     if ($this->runStatement("ALTER TABLE `${prefix}orderfiles` ADD COLUMN `".$key."` ".$field['type'])){
                         $error[$key]['fixed']=$this->l('... FIXED!');
                     } else {
                         $error[$key]['fixed']=$this->l('... ERROR!');
                     }
                }
                if (isset($field['config'])){
                    Configuration::updateValue($field['config'],'1');
                }
            } else {
                $error[$key]['type']="1";
                $error[$key]['message']=$this->l('OK!');
                $error[$key]['fixed']=$this->l('');
                if (isset($field['config'])){
                    Configuration::updateValue($field['config'],'1');
                }
            }
        }
        
        
        $form.='<table class="inconsistency"><tr><td colspan="4" style="text-align:center">'.$this->l('Database Consistency').'</td></tr>';
        foreach ($error as $column => $info){
            $form.="<tr><td class='inconsistency".$info['type']."'></td><td>".$column."</td><td>".$info['message']."</td><td>".$info['fixed']."</td></tr>";
        }
        $form.="</table>";
        
        return $form;
        
    }
       
	public function displayForm(){
	   $var=$this->getconf();
       if ($var['OF_LAST_TAB']==1){$selected1="active";}else{$selected1="";}    
       if ($var['OF_LAST_TAB']==2){$selected2="active";}else{$selected2="";}  
       if ($var['OF_LAST_TAB']==1){
        $form='
        <div id="module_general_settings" class="'.$selected1.'" style="vertical-align:top;">
            <fieldset id="fieldset_module_general_settings" style="width:400px; display:inline-block; vertical-align:top;">
                <legend style="display:inline-block;"><img src="'.$this->_path.'logo.gif" alt="" title="" />'.$this->l('Module General Settings').'</legend>
                <form action="'.$_SERVER['REQUEST_URI'].'" method="post">                               
                    <h3 style="margin-bottom:0px; padding-bottom:0px;">'.$this->l('File settings').'</h3>
                    <hr style="margin-top:5px;">
                    <label>'.$this->l('Max file size').'</label>
                    <div class="margin-form">
                        <input style="float:left;width:80px" type="text" name="OF_max_file_size" id="OF_max_file_size" value="'.Configuration::get('OF_MAX_FILE_SIZE').'">						
        				<p class="clear">'.$this->l('Set up the max file size (in kilobytes). example: 1024 = 1mb').'</p>
                    </div>
                    <label style="width:120px;">'.$this->l('Accepted filetypes').'</label>
                    <div class="margin-form" style="padding-left:140px;">
                        <input style="width:240px" type="text" name="OF_FTYPES" id="OF_FTYPES" value="'.Configuration::get('OF_FTYPES').'">						
        				<p class="clear">'.$this->l('setup the accepted file types, separate with commas').'</p>
                        <p class="clear">'.$this->l('Leave this field blank if you want to accept all file extensions').'</p>
                    </div>                                          
                <center><input type="submit" name="submit_general_settings" value="'.$this->l('Save General Settings').'" class="button" /></center>                                                                                               
                </form>
            </fieldset>
            <script>
            
            </script>
            <fieldset id="fieldset_module_upload_settings" style="vertical-align:top; width:400px; margin-left:10px; display:inline-block;">
                <legend style="display:inline-block;"><img src="'.$this->_path.'logo.gif" alt="" title="" />'.$this->l('Upload form').'</legend>
                <form action="'.$_SERVER['REQUEST_URI'].'" method="post">
                   <h3 style="margin-bottom:0px; padding-bottom:0px;">'.$this->l('Accepted number of files').'</h3>  
                   '.$this->l('this feature works only with AJAX uploader').'                             
                    <div class="margin-form" style="padding-left:5px; border:1px solid #c0c0c0;">
                        <input value="0" type="radio" name="OF_NOF" '.(Configuration::get('OF_NOF')==0 ? 'checked="checked"':'').'/>
                        '.$this->l('As many files as customer want').'
                    </div>
                    <div class="margin-form" style="padding-left:5px; border:1px solid #c0c0c0; margin-top:5px;">
                        <input value="1" type="radio" name="OF_NOF" '.(Configuration::get('OF_NOF')==1 ? 'checked="checked"':'').'/>
                        '.$this->l('Equal to the number of products in cart').'
                    </div>                    
                    <div class="margin-form" style="padding-left:5px; border:1px solid #c0c0c0; margin-top:5px;">
                        <input onchange="if (this.checked) { $(\'#of_nof3\').show(); } else { $(\'#of_nof3\').hide();}" value="3" type="radio" name="OF_NOF" '.(Configuration::get('OF_NOF')==3 ? 'checked="checked"':'').'/>
                        '.$this->l('Specific amout per order').'
                        <div class="margin-form" style="'.(Configuration::get('OF_NOF')==3 ? '':'display:none;').' padding-left:0px;" id="of_nof3">
                            <input type="text" name="OF_NOF_ORDER" value="'.Configuration::get('OF_NOF_ORDER').'"/>
                            <br />
                            '.$this->l('Define accepted number of files per order').'<br />
                        </div>
                    </div>
                    
                    
                    <h3 style="margin-bottom:0px; padding-bottom:0px;">'.$this->l('Unlogged users').'</h3>                               
                    <div class="margin-form" style="padding-left:0px;">
                        <input value="1" type="checkbox" name="OF_UNLOGGED" '.(Configuration::get('OF_UNLOGGED')==1 ? 'checked="checked"':'').'/>
                        '.$this->l('Disable possibility to upload for unlogged customers').'
                    </div>
                    <h3 style="margin-bottom:0px; padding-bottom:0px;">'.$this->l('Ajax upload (with progress bar)').'</h3>                               
                    <div class="margin-form" style="padding-left:0px;">
                        <input value="1" type="checkbox" name="OF_AJAXUPLOAD" '.(Configuration::get('OF_AJAXUPLOAD')==1 ? 'checked="checked"':'').'/>
                        '.$this->l('Enable ajax upload with progress bar').'
                    </div>
                    <h3 style="margin-bottom:0px; padding-bottom:0px;">'.$this->l('Product page').'</h3>
                    <div class="margin-form" style="padding-left:0px;">
                        <input value="0" type="radio" name="OF_PFORM" '.(Configuration::get('OF_PFORM')==0 ? 'checked="checked"':'').'/>
                        '.$this->l('Hide on product page').'
                    </div>                               
                    <div class="margin-form" style="padding-left:0px;">
                        <input value="1" type="radio" name="OF_PFORM" '.(Configuration::get('OF_PTAB')==1 ? 'checked="checked"':'').'/>
                        '.$this->l('Display upload form in PRODUCT TAB').'
                    </div>
                    <div class="margin-form" style="padding-left:0px;">
                        <input value="2" type="radio" name="OF_PFORM" '.(Configuration::get('OF_PFOOTER')==1 ? 'checked="checked"':'').'/>
                        '.$this->l('Display upload form in PRODUCT FOOTER').'
                    </div>
                    <div class="margin-form" style="padding-left:0px;">
                        <input  onchange="if (this.checked) { $(\'#of_certprod_id_block\').show(); } else { $(\'#of_certprod_id_block\').hide();} "  value="1" type="checkbox" name="OF_CERTPROD" '.(Configuration::get('OF_CERTPROD')==1 ? 'checked="checked"':'').'/>
                        '.$this->l('Display upload form only for certain products').'
                    </div>
                    <div class="margin-form" style="'.(Configuration::get('OF_CERTPROD')==1 ? '':'display:none;').' padding-left:0px;" id="of_certprod_id_block">
                        <input type="text" name="OF_CERTPROD_ID" value="'.Configuration::get('OF_CERTPROD_ID').'"/>
                        <br />
                        '.$this->l('Insert ID of products, separate them by commas').'   <br />
                        <input type="checkbox" name="OF_CERTPROD_CART" value="1" '.(Configuration::get('OF_CERTPROD_CART')==1 ? 'checked="true"':'').'/>
                        '.$this->l('display upload form (in cart) only if cart contains selected products').'
                    </div>
                    
                    
                    <h3 style="margin-bottom:0px; padding-bottom:0px;">'.$this->l('Shopping cart').'</h3>
                    <div class="margin-form" style="padding-left:0px;">
                        <input value="1" type="checkbox" name="OF_SCART" '.(Configuration::get('OF_SCART')==1 ? 'checked="checked"':'').'/>
                        '.$this->l('Display upload form in shopping cart').'
                    </div>   
                    <h3 style="margin-bottom:0px; padding-bottom:0px;">'.$this->l('Customer Account').'</h3>
                    <div class="margin-form" style="padding-left:0px;">
                        <input value="1" type="checkbox" name="OF_CACCOUNT" '.(Configuration::get('OF_CACCOUNT')==1 ? 'checked="checked"':'').'/>
                        '.$this->l('Display upload form in customer account').'
                    </div>                                 
                <center><input type="submit" name="submit_upload_settings" value="'.$this->l('Save Settings').'" class="button" /></center>                                                                                               
                </form>
            </fieldset>
        </div>';
        } elseif ($var['OF_LAST_TAB']==2) {
            $form=$this->inconsistency();
        } else {
            $form="";
        }
       
                
    
        return '
        <form name="selectform1" id="selectform1" action="'.$_SERVER['REQUEST_URI'].'" method="post"><input type="hidden" name="selecttab" value="1"></form>
        <form name="selectform2" id="selectform2" action="'.$_SERVER['REQUEST_URI'].'" method="post"><input type="hidden" name="selecttab" value="2"></form>

        '."<div id='cssmenu'>
            <ul>
               <li class='active'><a><span>v".$this->version."</span></a></li>
               <li class=\"$selected1\"><a href='#'/><span onclick=\"selectform1.submit();\">".$this->l('Module General Settings')."</span></a></li>
               <li class=\"$selected2\"><a href='#'/><span onclick=\"selectform2.submit();\">".$this->l('Upgrade Check')."</span></a></li>
               <li style='position:relative; display:inline-block; float:right; '><a href='http://mypresta.eu' target='_blank' title='prestashop modules'><img src='../modules/orderfiles/logo-white.png' alt='prestashop modules' style=\"position:absolute; top:17px; right:16px;\"/></a></li>
               <li style=\"float:right;\"><a href='http://mypresta.eu/contact.html' target='_blank'><span>".$this->l('Support')."</span></a></li>
               <li style=\"float:right;\"><a href='http://mypresta.eu/modules/ordering-process/customer-files-upload.html' target='_blank'><span>".$this->l('Updates')."</span></a></li>
            </ul>
            </div>".'
            <link href="'.$this->_path.'/css.css" rel="stylesheet" type="text/css" />'.$form;                        
	}    

    public function runStatement($statement){
       if (@!Db :: getInstance()->Execute($statement)) {
        return false;
	   }
        return true;
    }
    
    
    public function boupload($oid){
        return '
        <table class="table order" style="width:100%;">
            <tr>
                <th colspan="4">
                    <h3>'.$this->l('Upload files to this order as an administrator').'</h3>
                </th>
            </tr>
            <tr>
                <td style="width:200px;">
                    '.$this->l('File').'
                </td>
                <td>
                    '.$this->l('Title').'
                </td>
                <td>
                    '.$this->l('Description').'
                </td>
                <td></td>
            </tr>
            <tr>
                <form action="" method="POST" enctype="multipart/form-data">
                    <td><input type="file" name="file[]" /></td>
                    <td><input type="text" name="title" /></td>
                    <td><textarea name="description"></textarea></td>
                    <td><input type="hidden" name="filemanager"/>
                    <input type="hidden" name="oid" value="'.$oid.'">
                    <input type="submit" name="addfile" class="button" value="'.$this->l('upload').'"></td>
                </form>
            </tr>
        </table>
        ';
    }
    
  	public function orderpage_orderfiles($oid){
  	    $ret='';
  		$files=$this->get_files($oid);
	  		foreach ($files['toorder'] as $key=>$file){
		      
              if ($file['adminfile']==1){
		          $adminfile='<span class="uploadedbyadmin" style="position: relative; background: rgb(237, 238, 166); top: -10px; left: -10px; padding: 5px; width: '.($this->psversion()==6 ? '278px;':'289px;').' display: block;">'.$this->l('Uploaded by admin').'</span>';
		      } else {
		          $adminfile='<span class="uploadedbyadmin" style="position: relative; background: rgb(173, 245, 171); top: -10px; left: -10px; padding: 5px; width: '.($this->psversion()==6 ? '278px;':'289px;').' display: block;">'.$this->l('Uploaded by customer').'</span>';
		      }
              
		      if ($this->psversion()==4 || $this->psversion()==5 || $this->psversion()==6){
			  $ret.= '<div style=" border-radius:5px; position:relative; -webkit-border-radius:5px; -moz-border-radius:5px; vertical-align:top; text-align:left; border:1px solid #c0c0c0; background:#f7f7f7; display:inline-block; margin:5px; width:280px; padding:10px; ">
                     '.$adminfile.'
					  <div style="display:block; clear:both;">
						  <div style="display:inline-block; float:left; width:24px;">
						  	<img src="'._MODULE_DIR_.'orderfiles/img/file.png" style="width:24px; height:auto;"/>
						  </div>
						  <div style="display:inline-block; float:left; width: 222px; margin-left:10px; overflow:hidden; ">
							  <b>'.$file['title'].'</b> <span title="'.$file['filename'].'">'.substr($file['filename'],0,10).(strlen($file['filename'])>10 ? '...':'').'</span><br/>
							  '.$file['description'].'
						  </div>
					  </div>
					  <div style="overflow:hidden; display:block; clear:both; width:100%; vertical-align:top; position:relative; margin-top:10px; padding-bottom:30px;">
					  	<div style="display:inline-block; float:left;">
		                     <a href="'._MODULE_DIR_.'orderfiles/download.php?t=files&opt='.$oid.'&f='.$file['filename'].'" target="_blank" class="button" style="position:absolute; left:0px; bottom:0px;">'.$this->l('download').'</a>
		  				</div>
						<div style="display:inline-block; float:left;">    
						  <form style="position:absolute; right:0px; bottom:0px;" style="margin:0px;padding:0px;"  method="post" action=""\><input type="hidden" name="oid" value="'.$oid.'"/><input type="hidden" name="filemanager"><input type="hidden" name="fid" value="'.$file['id'].'">
							  <input type="submit" name="deletefile" value="'.$this->l('Delete').'" class="button extra" style="position:relative; right:0px; bottom:0px;"/>
						  </form>
						</div>  
					  </div>
		  		  </div>';
                }             
	  		} 
            
            foreach ($files['tocart'] as $key=>$file){
              $adminfile='<span class="uploadedbyadmin" style="position: relative; background: rgb(173, 245, 171); top: -10px; left: -10px; padding: 5px; width: '.($this->psversion()==6 ? '278px;':'289px;').' display: block;">'.$this->l('Uploaded by customer').'</span>';
		      if ($this->psversion()==4 || $this->psversion()==5 || $this->psversion()==6){
			  $ret.= '<div style="border-radius:5px; position:relative; -webkit-border-radius:5px; -moz-border-radius:5px; vertical-align:top; text-align:left; border:1px solid #c0c0c0; background:#f7f7f7; display:inline-block; margin:5px; width:280px; padding:10px; ">
                     '.$adminfile.'
					  <div style="display:block; clear:both;">
						  <div style="display:inline-block; float:left; width:24px;">
						  	<img src="'._MODULE_DIR_.'orderfiles/img/file.png" style="width:24px; height:auto;"/>
						  </div>
						  <div style="display:inline-block; float:left; width: 222px; margin-left:10px; overflow:hidden;">
							  <b>'.$file['title'].' '.$file['product']->name.'</b> <span title="'.$file['filename'].'">'.substr($file['filename'],0,10).(strlen($file['filename'])>10 ? '...':'').'</span><br/>
							  '.$file['description'].'
						  </div>
					  </div>
					  <div style="overflow:hidden; display:block; clear:both; width:100%; vertical-align:top; position:relative; margin-top:10px; padding-bottom:30px;">
					  	<div style="display:inline-block; float:left;">
		  				  <a href="'._MODULE_DIR_.'orderfiles/download.php?t=cartfiles&opt='.$file['idcart'].'&f='.$file['filename'].'" target="_blank" class="button" style="position:absolute; left:0px; bottom:0px;">'.$this->l('download').'</a>
		  				</div>
						<div style="display:inline-block; float:left;">    
						  <form style="position:absolute; right:0px; bottom:0px;" style="margin:0px;padding:0px;"  method="post" action="index.php?controller=orderfilestab5&token='.$_GET['token'].'"\><input type="hidden" name="oid" value="'.$oid.'"/><input type="hidden" name="filemanager"><input type="hidden" name="fid" value="'.$file['id'].'">
							  <input type="submit" name="deletecartfile" value="'.$this->l('Delete').'" class="button extra" style="position:relative; right:0px; bottom:0px;"/>
						  </form>
						</div>  
					  </div>
		  		  </div>';
                }              
	  		}
            
            foreach ($files['toproduct'] as $key=>$file){
              $adminfile='<span class="uploadedbyadmin" style="position: relative; background: rgb(173, 245, 171); top: -10px; left: -10px; padding: 5px; width: '.($this->psversion()==6 ? '279px;':'288px;').' display: block;">'.$this->l('Uploaded by customer').'</span>';
		      if ($this->psversion()==4 || $this->psversion()==5 || $this->psversion()==6){
			  $ret.= '<div style="border-radius:5px; position:relative; -webkit-border-radius:5px; -moz-border-radius:5px; vertical-align:top; text-align:left; border:1px solid #c0c0c0; background:#f7f7f7; display:inline-block; margin:5px; width:280px; padding:10px; ">
                     '.$adminfile.'
					  <div style="display:block; clear:both;">
						  <div style="display:inline-block; float:left; width:24px;">
						  	<img src="'._MODULE_DIR_.'orderfiles/img/file.png" style="width:24px; height:auto;"/>
						  </div>
						  <div style="display:inline-block; float:left; width: 222px; margin-left:10px; overflow:hidden;">
							  <b>'.$file['title'].' '.$file['product']->name.'</b> <span title="'.$file['filename'].'">'.substr($file['filename'],0,10).(strlen($file['filename'])>10 ? '...':'').'</span><br/>
							  '.$file['description'].'
						  </div>
					  </div>
					  <div style="overflow:hidden; display:block; clear:both; width:100%; vertical-align:top; position:relative; margin-top:10px; padding-bottom:30px;">
					  	<div style="display:inline-block; float:left;">
		  				  <a href="'._MODULE_DIR_.'orderfiles/download.php?t=productfiles&opt='.$file['cookieid'].'&f='.$file['filename'].'" target="_blank" class="button" style="position:absolute; left:0px; bottom:0px;">'.$this->l('download').'</a>
		  				</div>
						<div style="display:inline-block; float:left;">    
						  <form style="position:absolute; right:0px; bottom:0px;" style="margin:0px;padding:0px;"  method="post" action="index.php?controller=orderfilestab5&token='.$_GET['token'].'"\><input type="hidden" name="oid" value="'.$oid.'"/><input type="hidden" name="filemanager"><input type="hidden" name="fid" value="'.$file['id'].'">
							  <input type="submit" name="deleteproductfile" value="'.$this->l('Delete').'" class="button extra" style="position:relative; right:0px; bottom:0px;"/>
						  </form>
						</div>  
					  </div>
		  		  </div>';
                }
	  		}
            if ($ret==''){
                $ret.='
                <div class="bootstrap">
            		<div class="alert alert-warning">
            			<h4>'.$this->l('no files uploaded').'</h4>
            		</div>
            	</div>';
            }            
        return $ret;
  	}    
    
    
    public function adminorderpage_orderfiles($oid){
  	    $ret='';
            $files=$this->get_files($oid);            
            return $files;
  	}    
    
        
        public function hookadminorder($params){
        if (isset($_POST['deletefile']) && isset($_POST['fid'])){
  			$msg.=$this->delete($_POST['fid']);	
  		}
        
        if (isset($_POST['deletecartfile']) && isset($_POST['fid'])){
  			$msg.=$this->deletecartfile($_POST['fid']);	
  		}
        
        if (isset($_POST['deleteproductfile']) && isset($_POST['fid'])){
  			$msg.=$this->deleteproductfile($_POST['fid']);	
  		}
  		        
        if (isset($_POST['addfile'])){
                if (isset($_POST['oid'])){
                        $order = new OrderCore($_POST['oid']);
                        $this->insertphotoasadmin($_POST,$_FILES);
                }
	    } 
        return '';
    }

    public function delete($idphoto){
        $db = Db::getInstance(_PS_USE_SQL_SLAVE_); 
        $query = "SELECT * FROM `"._DB_PREFIX_."orderfiles` WHERE id='$idphoto'";
        $array = $db->ExecuteS($query);
        if (isset($array['0'])){
            $array['0']['filetype']=pathinfo($array['0']['filename'], PATHINFO_EXTENSION);            
            unlink("../modules/orderfiles/files/{$array['0']['id_order']}/{$array['0']['filename']}");
            $db = Db::getInstance(); 
            $query = "DELETE FROM `"._DB_PREFIX_."orderfiles` WHERE id='$idphoto'";
            $db->Execute($query);     
        }
    }
    
    public function deletecartfile($idfile){
        $db = Db::getInstance(_PS_USE_SQL_SLAVE_); 
        $query = "SELECT * FROM `"._DB_PREFIX_."orderfiles_cart` WHERE id='$idfile'";
        $array = $db->ExecuteS($query);
        if (isset($array['0'])){
            $array['0']['filetype']=pathinfo($array['0']['filename'], PATHINFO_EXTENSION);            
            unlink("../modules/orderfiles/cartfiles/{$array['0']['idcart']}/{$array['0']['filename']}");
            $db = Db::getInstance(); 
            $query = "DELETE FROM `"._DB_PREFIX_."orderfiles_cart` WHERE id='$idfile'";
            $db->Execute($query);     
        }
    }    
    
    public function deleteproductfile($idfile){
        $db = Db::getInstance(_PS_USE_SQL_SLAVE_); 
        $query = "SELECT * FROM `"._DB_PREFIX_."orderfiles_product` WHERE id='$idfile'";
        $array = $db->ExecuteS($query);
        if (isset($array['0'])){
            $array['0']['filetype']=pathinfo($array['0']['filename'], PATHINFO_EXTENSION);            
            unlink("../modules/orderfiles/productfiles/{$array['0']['cookieid']}/{$array['0']['filename']}");
            $db = Db::getInstance(); 
            $query = "DELETE FROM `"._DB_PREFIX_."orderfiles_product` WHERE id='$idfile'";
            $db->Execute($query);     
        }
    }  
    
    public function hookorderDetailDisplayed($params)
    {
       if ($this->psversion()!=4){
           if (Tools::getValue('id_order', 'false') != 'false'){
    			$order = new OrderCore(Tools::getValue('id_order'));
    			if ($order->id_customer==$this->context->customer->id){
    			    $this->var=$this->getconf();
    				$this->context->smarty->assign(array(
    				'mod' => $this,
    				'setup' => $this->var, 
    				'order' => $order,
    				'idorder' => Tools::getValue('id_order'),
    				'files' => $this->get_files(Tools::getValue('id_order'),$this->context->customer->id),
    				'link' => $this->context->link,
    				'customer' => $this->context->customer));
    				return $this->display(__FILE__, 'filesmanager-orderhistory.tpl');
    			} else {
    				return $this->display(__FILE__, 'access-denied.tpl');	
    			}
    		} else {
    			return $this->display(__FILE__, 'access-denied.tpl');
    		} 
        }
    }
    
    public function hookcustomerAccount($params){
        $var=$this->getconf();
        if ($var['OF_CUSTOMERACCOUNT']==1){
            if ($this->psversion()==6){
        		if ($this->context->customer->isLogged()==1){
   	        		return $this->display(__FILE__, 'my-account-16.tpl');
   	        	}
   	        }
            
        	if ($this->psversion()==5){
        		if ($this->context->customer->isLogged()==1){
   	        		return $this->display(__FILE__, 'my-account.tpl');
   	        	}
   	        }
   	        global $cookie;
        	if ($this->psversion()==4){
        		if ($cookie->isLogged()==1){
   	        		return $this->display(__FILE__, 'my-account-14.tpl');
   	        	}
   	        }             
        }
    }
    
    public function hookshoppingcart($params){
            $hide=1;       
            if ($this->psversion()==4 || $this->psversion()==3){
                global $cookie;
                $this->context = new StdClass();
                $this->context->cookie=$cookie;
                if (!isset($this->context->cart)){
                    $this->context->cart=new Cart($cookie->id_cart);
                }
             }
             
             if (isset($_POST['upload_new_file_cart'])){
                $this->insertfilestocart($_POST,$_FILES);
             }
             
             if (isset($_POST['remove_cartfile'])){
                $this->removefilecart($_POST['idcartfile']);
             }
             
             if (isset($_POST['remove_productfile'])){
                $this->removefileproduct($_POST['idproductfile']);
             }

             if (isset($_POST['update_productfile'])){
                $this->updatefileproduct($_POST['idproductfile'],$_POST['description']);
             }
             
             if (isset($_POST['update_cartfile'])){
                $this->updatefilecart($_POST['idcartfile'],$_POST['description']);
             }             
            if (isset($_POST['changecartfile'])){
                $this->changefilecart($_POST['idproduct'],$_POST['changecartfile']);
            }
            if (isset($_POST['changeproductfile'])){
                $this->changefileproduct($_POST['idproduct'],$_POST['changeproductfile']);
            }              
            //print_r($this->context->cart->getProducts());
            
            
            
            //HIDE UPLOAD FORM IF SELECTED PRODUCTS AREN'T IN CART
            $arrayofproducts=explode(",",Configuration::get('OF_CERTPROD_ID'));
            if (Configuration::get("OF_CERTPROD_CART")==1){
                foreach ($this->context->cart->getProducts() as $id=>$product){
                    if (in_array($product['id_product'],$arrayofproducts)){
                        $hide=0;
                    }
                }
            } else {
                $hide=0;
            }
            
            global $smarty;
            $smarty->assign('idcart', $this->context->cart->id);
            $smarty->assign('hideuploadform', $hide);
            $smarty->assign('ofproducts', $this->context->cart->getProducts());
            $smarty->assign('files', $this->getcartfiles($this->context->cart->id));
            if (isset($_COOKIE['ftpr'])){
                $this->updateproductfilescart($_COOKIE['ftpr']);
                $smarty->assign('files_product', $this->getcookiefiles($_COOKIE['ftpr']));
            } else {
                $smarty->assign('files_product', NULL);
            }
            $smarty->assign('maxFileCount',$this->NumberFiles($this->getcartfiles($this->context->cart->id),$this->getcookiefiles($_COOKIE['ftpr'])));
            $ext=explode(",",Configuration::get('OF_FTYPES'));
            $smarty->assign('extensions',$ext);
        
        if (configuration::get('OF_SCART')==1){           
            $var=$this->getconf();
            if ($var['OF_SHOPPINGCART']==1){
            	if ($this->psversion()==5 || $this->psversion()==6){
       	            return $this->display(__FILE__, 'shopping-cart.tpl');
       	        }
       	      
            	if ($this->psversion()==4){
       	            return $this->display(__FILE__, 'views/templates/hook/shopping-cart.tpl');
       	        }             
            }
        }
    }
    
    public function hookNewOrder($params){
        setcookie("ftpr", "", time()-86400864001, "/");
    }
    
    public function hookHeader($params){
        if (Configuration::get('OF_AJAXUPLOAD')==1){
            if ($this->psversion()==5 || $this->psversion()==6){
                $this->context->controller->addCSS(($this->_path).'css/ajaxuploader.css', 'all');
                //$this->context->controller->addJS(($this->_path).'js/jquery.min.js', 'all');
                $this->context->controller->addJS(($this->_path).'js/jquery.uploadfile.min.js', 'all');
                //$this->context->controller->addJS(($this->_path).'js/ajaxuploader.js', 'all');
            } 
            if ($this->psversion()==4){
                Tools::addCSS($this->_path.'css/ajaxuploader.css', 'all');
                Tools::addJS($this->_path.'js/jquery.min.js', 'all');
                Tools::addJS($this->_path.'js/jquery.uploadfile.min.js', 'all');
            }
        }
    }
    
    function hookProductTab($params){
        if (configuration::get('OF_PTAB')==1){
            global $smarty;
            $smarty->assign('ver',$this->psversion());
            if (configuration::get("OF_CERTPROD")==1){
                if (isset($_GET['id_product'])){
                    $arrayofproducts=explode(",",Configuration::get('OF_CERTPROD_ID'));
                    if (in_array($_GET['id_product'],$arrayofproducts)){
                        return $this->display(__FILE__, 'views/templates/front/tab.tpl');
                    }
                }
            } else {
                return $this->display(__FILE__, 'views/templates/front/tab.tpl');
            }
        }
	}
        
        function hookProductFile($params){
            if (configuration::get('OF_PTAB')==1){
            global $smarty;
            $smarty->assign('ver',$this->psversion());
            if (configuration::get("OF_CERTPROD")==1){
                if (isset($_GET['id_product'])){
                    $arrayofproducts=explode(",",Configuration::get('OF_CERTPROD_ID'));
                    if (in_array($_GET['id_product'],$arrayofproducts)){
                        return $this->display(__FILE__, 'views/templates/front/tab.tpl');
                    }
                }
            } else {
                return $this->display(__FILE__, 'views/templates/front/tab.tpl');
            }
        }
      }
        
       function hookProductFileContent($params){
            if (configuration::get('OF_PTAB')==1){
            global $smarty;
            $smarty->assign('ver',$this->psversion());
            $smarty->assign('TabContent',1);
            if (configuration::get("OF_CERTPROD")==1){
                if (isset($_GET['id_product'])){
                    $arrayofproducts=explode(",",Configuration::get('OF_CERTPROD_ID'));
                    if (in_array($_GET['id_product'],$arrayofproducts)){
                        if ($this->psversion()==4 || $this->psversion()==3){
                                global $cookie;
                                $this->context = new StdClass();
                                $this->context->cookie=$cookie;
                                $this->context->cart=new Cart($cookie->id_cart);
                            }
                            if (isset($_POST['upload_new_file_product'])){
                                if (!isset($_COOKIE['ftpr'])){
                                    $cookieid=date("U").$this->generatekey(5,"abcdfghijklmnouprstuwxyz1234567890");
                                    setcookie("ftpr", $cookieid, time()+86400, "/");
                                    $this->insertfilestoproduct($_POST,$_FILES,$cookieid);
                                } else {
                                    $cookieid=$_COOKIE['ftpr'];
                                    $this->insertfilestoproduct($_POST,$_FILES,$cookieid);
                                }
                             } else {
                                if (isset($_COOKIE['ftpr'])){
                                    $cookieid=$_COOKIE['ftpr'];
                                } else {
                                    $cookieid='x';
                                }
                            }
                             
                             if (isset($_POST['remove_productfile'])){
                                $this->removefileproduct($_POST['idproductfile']);
                             }
                             if (isset($_POST['remove_cartfile'])){
                                $this->removefilecart($_POST['idcartfile']);
                             }
                             
                             if (isset($_POST['update_productfile'])){
                                $this->updatefileproduct($_POST['idproductfile'],$_POST['description']);
                             }
                             if (isset($_POST['update_cartfile'])){
                                $this->updatefilecart($_POST['idcartfile'],$_POST['description']);
                             }
                             
                            
                            //print_r($this->context->cart->getProducts());
                            global $smarty;
                            $smarty->assign('maxFileCount',$this->NumberFiles($this->getcartfilesbyproduct($this->context->cart->id,Tools::getValue('id_product')),$this->getproductfiles(Tools::getValue('id_product'),$cookieid)));
                            $smarty->assign('thisistab','1');
                            $smarty->assign('idproduct', Tools::getValue('id_product'));
                            $smarty->assign('products', $this->context->cart->getProducts());       
                            $smarty->assign('files', $this->getproductfiles(Tools::getValue('id_product'),$cookieid));
                            $ext=explode(",",Configuration::get('OF_FTYPES'));
                            $smarty->assign('extensions',$ext);
                            if (isset($_COOKIE['ftpr'])){
                                $this->updateproductfilescart($_COOKIE['ftpr']);
                                $smarty->assign('files_cart', $this->getcartfilesbyproduct($this->context->cart->id,Tools::getValue('id_product')));
                            } else {
                                $smarty->assign('files_cart', NULL);
                            }
                            
                            
                            $var=$this->getconf();
                            if (configuration::get('OF_PTAB')==1){
                            	if ($this->psversion()==5 || $this->psversion()==6){
                       	            return $this->display(__FILE__, 'product-files.tpl');
                       	        }
                       	      
                            	if ($this->psversion()==4){
                       	            return $this->display(__FILE__, 'views/templates/hook/product-files.tpl');
                       	        }             
                            }
                        }
                    }
            } else { 
                if ($this->psversion()==4 || $this->psversion()==3){
                        global $cookie;
                        $this->context = new StdClass();
                        $this->context->cookie=$cookie;
                        $this->context->cart=new Cart($cookie->id_cart);
                    }
                    if (isset($_POST['upload_new_file_product'])){
                        if (!isset($_COOKIE['ftpr'])){
                            $cookieid=date("U").$this->generatekey(5,"abcdfghijklmnouprstuwxyz1234567890");
                            setcookie("ftpr", $cookieid, time()+86400, "/");
                            $this->insertfilestoproduct($_POST,$_FILES,$cookieid);
                        } else {
                            $cookieid=$_COOKIE['ftpr'];
                            $this->insertfilestoproduct($_POST,$_FILES,$cookieid);
                        }
                     } else {
                        if (isset($_COOKIE['ftpr'])){
                            $cookieid=$_COOKIE['ftpr'];
                        } else {
                            $cookieid='x';
                        }
                    }
                     
                             if (isset($_POST['remove_productfile'])){
                                $this->removefileproduct($_POST['idproductfile']);
                             }
                             if (isset($_POST['remove_cartfile'])){
                                $this->removefilecart($_POST['idcartfile']);
                             }
                             
                             if (isset($_POST['update_productfile'])){
                                $this->updatefileproduct($_POST['idproductfile'],$_POST['description']);
                             }
                             if (isset($_POST['update_cartfile'])){
                                $this->updatefilecart($_POST['idcartfile'],$_POST['description']);
                             }                     
                     
                    
                    //print_r($this->context->cart->getProducts());
                    global $smarty;
                    $smarty->assign('maxFileCount',$this->NumberFiles($this->getcartfilesbyproduct($this->context->cart->id,Tools::getValue('id_product')),$this->getproductfiles(Tools::getValue('id_product'),$cookieid)));
                    $smarty->assign('thisistab','1');
                    $smarty->assign('idproduct', Tools::getValue('id_product'));
                    $smarty->assign('products', $this->context->cart->getProducts());       
                    $smarty->assign('files', $this->getproductfiles(Tools::getValue('id_product'),$cookieid));
                    $ext=explode(",",Configuration::get('OF_FTYPES'));
                    $smarty->assign('extensions',$ext);
                    if (isset($_COOKIE['ftpr'])){
                        $this->updateproductfilescart($_COOKIE['ftpr']);
                        $smarty->assign('files_cart', $this->getcartfilesbyproduct($this->context->cart->id,Tools::getValue('id_product')));
                    } else {
                        $smarty->assign('files_cart', NULL);
                    }
                    
                    
                    $var=$this->getconf();
                    if (configuration::get('OF_PTAB')==1){
                    	if ($this->psversion()==5 || $this->psversion()==6){
               	            return $this->display(__FILE__, 'product-files.tpl');
               	        }
               	      
                    	if ($this->psversion()==4){
               	            return $this->display(__FILE__, 'views/templates/hook/product-files.tpl');
               	        }             
                    }
                }
            }
        }
    
    public function hookProductTabContent($params){
        if (configuration::get('OF_PTAB')==1){
            global $smarty;
            $smarty->assign('ver',$this->psversion());
            $smarty->assign('TabContent',1);
            if (configuration::get("OF_CERTPROD")==1){
                if (isset($_GET['id_product'])){
                    $arrayofproducts=explode(",",Configuration::get('OF_CERTPROD_ID'));
                    if (in_array($_GET['id_product'],$arrayofproducts)){
                        if ($this->psversion()==4 || $this->psversion()==3){
                                global $cookie;
                                $this->context = new StdClass();
                                $this->context->cookie=$cookie;
                                $this->context->cart=new Cart($cookie->id_cart);
                            }
                            if (isset($_POST['upload_new_file_product'])){
                                if (!isset($_COOKIE['ftpr'])){
                                    $cookieid=date("U").$this->generatekey(5,"abcdfghijklmnouprstuwxyz1234567890");
                                    setcookie("ftpr", $cookieid, time()+86400, "/");
                                    $this->insertfilestoproduct($_POST,$_FILES,$cookieid);
                                } else {
                                    $cookieid=$_COOKIE['ftpr'];
                                    $this->insertfilestoproduct($_POST,$_FILES,$cookieid);
                                }
                             } else {
                                if (isset($_COOKIE['ftpr'])){
                                    $cookieid=$_COOKIE['ftpr'];
                                } else {
                                    $cookieid='x';
                                }
                            }
                             
                             if (isset($_POST['remove_productfile'])){
                                $this->removefileproduct($_POST['idproductfile']);
                             }
                             if (isset($_POST['remove_cartfile'])){
                                $this->removefilecart($_POST['idcartfile']);
                             }
                             
                             if (isset($_POST['update_productfile'])){
                                $this->updatefileproduct($_POST['idproductfile'],$_POST['description']);
                             }
                             if (isset($_POST['update_cartfile'])){
                                $this->updatefilecart($_POST['idcartfile'],$_POST['description']);
                             }
                             
                            
                            //print_r($this->context->cart->getProducts());
                            global $smarty;
                            $smarty->assign('maxFileCount',$this->NumberFiles($this->getcartfilesbyproduct($this->context->cart->id,Tools::getValue('id_product')),$this->getproductfiles(Tools::getValue('id_product'),$cookieid)));
                            $smarty->assign('thisistab','1');
                            $smarty->assign('idproduct', Tools::getValue('id_product'));
                            $smarty->assign('products', $this->context->cart->getProducts());       
                            $smarty->assign('files', $this->getproductfiles(Tools::getValue('id_product'),$cookieid));
                            $ext=explode(",",Configuration::get('OF_FTYPES'));
                            $smarty->assign('extensions',$ext);
                            if (isset($_COOKIE['ftpr'])){
                                $this->updateproductfilescart($_COOKIE['ftpr']);
                                $smarty->assign('files_cart', $this->getcartfilesbyproduct($this->context->cart->id,Tools::getValue('id_product')));
                            } else {
                                $smarty->assign('files_cart', NULL);
                            }
                            
                            
                            $var=$this->getconf();
                            if (configuration::get('OF_PTAB')==1){
                            	if ($this->psversion()==5 || $this->psversion()==6){
                       	            return $this->display(__FILE__, 'product-files.tpl');
                       	        }
                       	      
                            	if ($this->psversion()==4){
                       	            return $this->display(__FILE__, 'views/templates/hook/product-files.tpl');
                       	        }             
                            }
                        }
                    }
            } else { 
                if ($this->psversion()==4 || $this->psversion()==3){
                        global $cookie;
                        $this->context = new StdClass();
                        $this->context->cookie=$cookie;
                        $this->context->cart=new Cart($cookie->id_cart);
                    }
                    if (isset($_POST['upload_new_file_product'])){
                        if (!isset($_COOKIE['ftpr'])){
                            $cookieid=date("U").$this->generatekey(5,"abcdfghijklmnouprstuwxyz1234567890");
                            setcookie("ftpr", $cookieid, time()+86400, "/");
                            $this->insertfilestoproduct($_POST,$_FILES,$cookieid);
                        } else {
                            $cookieid=$_COOKIE['ftpr'];
                            $this->insertfilestoproduct($_POST,$_FILES,$cookieid);
                        }
                     } else {
                        if (isset($_COOKIE['ftpr'])){
                            $cookieid=$_COOKIE['ftpr'];
                        } else {
                            $cookieid='x';
                        }
                    }
                     
                             if (isset($_POST['remove_productfile'])){
                                $this->removefileproduct($_POST['idproductfile']);
                             }
                             if (isset($_POST['remove_cartfile'])){
                                $this->removefilecart($_POST['idcartfile']);
                             }
                             
                             if (isset($_POST['update_productfile'])){
                                $this->updatefileproduct($_POST['idproductfile'],$_POST['description']);
                             }
                             if (isset($_POST['update_cartfile'])){
                                $this->updatefilecart($_POST['idcartfile'],$_POST['description']);
                             }                     
                     
                    
                    //print_r($this->context->cart->getProducts());
                    global $smarty;
                    $smarty->assign('maxFileCount',$this->NumberFiles($this->getcartfilesbyproduct($this->context->cart->id,Tools::getValue('id_product')),$this->getproductfiles(Tools::getValue('id_product'),$cookieid)));
                    $smarty->assign('thisistab','1');
                    $smarty->assign('idproduct', Tools::getValue('id_product'));
                    $smarty->assign('products', $this->context->cart->getProducts());       
                    $smarty->assign('files', $this->getproductfiles(Tools::getValue('id_product'),$cookieid));
                    $ext=explode(",",Configuration::get('OF_FTYPES'));
                    $smarty->assign('extensions',$ext);
                    if (isset($_COOKIE['ftpr'])){
                        $this->updateproductfilescart($_COOKIE['ftpr']);
                        $smarty->assign('files_cart', $this->getcartfilesbyproduct($this->context->cart->id,Tools::getValue('id_product')));
                    } else {
                        $smarty->assign('files_cart', NULL);
                    }
                    
                    
                    $var=$this->getconf();
                    if (configuration::get('OF_PTAB')==1){
                    	if ($this->psversion()==5 || $this->psversion()==6){
               	            return $this->display(__FILE__, 'product-files.tpl');
               	        }
               	      
                    	if ($this->psversion()==4){
               	            return $this->display(__FILE__, 'views/templates/hook/product-files.tpl');
               	        }             
                    }
                }
            }
        }

    public function NumberFiles($cart=null,$product=null){
        if (configuration::get("OF_NOF")==0){
            return "";            
        } elseif (Configuration::get("OF_NOF")==1) {
            if ($this->psversion()==4 || $this->psversion()==3){
                global $cookie;
                $this->context = new StdClass();
                $this->context->cookie=$cookie;
                $this->context->cart=new Cart($cookie->id_cart);
            }
            $products_in_cart=$this->context->cart->getProducts();
            //print_r(count($products_in_cart)."||||||||||||||||||||");
            return "maxFileCount:".(count($products_in_cart)-(count($cart)+count($product))).",";
            
        } elseif (Configuration::get("OF_NOF")==2) {
            
        } elseif (Configuration::get("OF_NOF")==3) {
            return "maxFileCount:".((Configuration::get("OF_NOF_ORDER")-(count($cart)+count($product)))>=0 ? Configuration::get("OF_NOF_ORDER")-(count($cart)+count($product)):0).",";
        }
    }
    
    public function hookProductFooter($params){
    
        
        if (configuration::get("OF_PFOOTER")==1){
            if (configuration::get("OF_CERTPROD")==1){
                if (isset($_GET['id_product'])){
                    $arrayofproducts=explode(",",Configuration::get('OF_CERTPROD_ID'));
                    if (in_array($_GET['id_product'],$arrayofproducts)){
                        if ($this->psversion()==4 || $this->psversion()==3){
                            global $cookie;
                            $this->context = new StdClass();
                            $this->context->cookie=$cookie;
                            $this->context->cart=new Cart($cookie->id_cart);
                        }
                        if (isset($_POST['upload_new_file_product'])){
                            if (!isset($_COOKIE['ftpr'])){
                                $cookieid=date("U").$this->generatekey(5,"abcdfghijklmnouprstuwxyz1234567890");
                                setcookie("ftpr", $cookieid, time()+86400, "/");
                                $this->insertfilestoproduct($_POST,$_FILES,$cookieid);
                            } else {
                                $cookieid=$_COOKIE['ftpr'];
                                $this->insertfilestoproduct($_POST,$_FILES,$cookieid);
                            }
                         } else {
                            if (isset($_COOKIE['ftpr'])){
                                $cookieid=$_COOKIE['ftpr'];
                            } else {
                                $cookieid='x';
                            }
                        }
                         
                             if (isset($_POST['remove_productfile'])){
                                $this->removefileproduct($_POST['idproductfile']);
                             }
                             if (isset($_POST['remove_cartfile'])){
                                $this->removefilecart($_POST['idcartfile']);
                             }
                         
                             if (isset($_POST['update_productfile'])){
                                $this->updatefileproduct($_POST['idproductfile'],$_POST['description']);
                             }
                             if (isset($_POST['update_cartfile'])){
                                $this->updatefilecart($_POST['idcartfile'],$_POST['description']);
                             }                         
                         
                        
                        //print_r($this->context->cart->getProducts());
                        global $smarty;
                        $smarty->assign('maxFileCount',$this->NumberFiles($this->getcartfilesbyproduct($this->context->cart->id,Tools::getValue('id_product')),$this->getproductfiles(Tools::getValue('id_product'),$cookieid)));
                        $smarty->assign('thisistab','0');
                        $smarty->assign('idproduct', Tools::getValue('id_product'));
                        $smarty->assign('products', $this->context->cart->getProducts());       
                        $smarty->assign('files', $this->getproductfiles(Tools::getValue('id_product'),$cookieid));
                        $ext=explode(",",Configuration::get('OF_FTYPES'));
                        $smarty->assign('extensions',$ext);
                        if (isset($_COOKIE['ftpr'])){
                            $this->updateproductfilescart($_COOKIE['ftpr']);
                            $smarty->assign('files_cart', $this->getcartfilesbyproduct($this->context->cart->id,Tools::getValue('id_product')));
                        } else {
                            $smarty->assign('files_cart', NULL);
                        }
                        
                        $var=$this->getconf();
                        if (configuration::get('OF_PFOOTER')==1){
                        	if ($this->psversion()==5 || $this->psversion()==6){
                   	            return $this->display(__FILE__, 'product-files.tpl');
                   	        }
                   	      
                        	if ($this->psversion()==4){
                   	            return $this->display(__FILE__, 'views/templates/hook/product-files.tpl');
                   	        }             
                        }
                    }
                }
            } else {
                if ($this->psversion()==4 || $this->psversion()==3){
                    global $cookie;
                    $this->context = new StdClass();
                    $this->context->cookie=$cookie;
                    $this->context->cart=new Cart($cookie->id_cart);
                }
                if (isset($_POST['upload_new_file_product'])){
                    if (!isset($_COOKIE['ftpr'])){
                        $cookieid=date("U").$this->generatekey(5,"abcdfghijklmnouprstuwxyz1234567890");
                        setcookie("ftpr", $cookieid, time()+86400, "/");
                        $this->insertfilestoproduct($_POST,$_FILES,$cookieid);
                    } else {
                        $cookieid=$_COOKIE['ftpr'];
                        $this->insertfilestoproduct($_POST,$_FILES,$cookieid);
                    }
                 } else {
                    if (isset($_COOKIE['ftpr'])){
                        $cookieid=$_COOKIE['ftpr'];
                    } else {
                        $cookieid='x';
                    }
                }
                 
                             if (isset($_POST['remove_productfile'])){
                                $this->removefileproduct($_POST['idproductfile']);
                             }
                             if (isset($_POST['remove_cartfile'])){
                                $this->removefilecart($_POST['idcartfile']);
                             }
                             
                             if (isset($_POST['update_productfile'])){
                                $this->updatefileproduct($_POST['idproductfile'],$_POST['description']);
                             }
                             if (isset($_POST['update_cartfile'])){
                                $this->updatefilecart($_POST['idcartfile'],$_POST['description']);
                             }
                                       
                 
                
                //print_r($this->context->cart->getProducts());
                global $smarty;
                $smarty->assign('thisistab','0');
                $smarty->assign('maxFileCount',$this->NumberFiles($this->getcartfilesbyproduct($this->context->cart->id,Tools::getValue('id_product')),$this->getproductfiles(Tools::getValue('id_product'),$cookieid))); 
                $smarty->assign('idproduct', Tools::getValue('id_product'));
                $smarty->assign('products', $this->context->cart->getProducts());       
                $smarty->assign('files', $this->getproductfiles(Tools::getValue('id_product'),$cookieid));
                $ext=explode(",",Configuration::get('OF_FTYPES'));
                $smarty->assign('extensions',$ext);
                if (isset($_COOKIE['ftpr'])){
                    $this->updateproductfilescart($_COOKIE['ftpr']);
                    $smarty->assign('files_cart', $this->getcartfilesbyproduct($this->context->cart->id,Tools::getValue('id_product')));
                } else {
                    $smarty->assign('files_cart', NULL);
                }
                
                
                $var=$this->getconf();
                if (configuration::get('OF_PFOOTER')==1){
                	if ($this->psversion()==5 || $this->psversion()==6){
           	            return $this->display(__FILE__, 'product-files.tpl');
           	        }
           	      
                	if ($this->psversion()==4){
           	            return $this->display(__FILE__, 'views/templates/hook/product-files.tpl');
           	        }             
                }
            }
        }
    }   
     
    
    public function hookmyAccountBlock($params){
        if (configuration::get('OF_CACCOUNT')==1){
        $var=$this->getconf();
        if ($var['OF_MYACCOUNTBLOCK']==1){
        	if ($this->psversion()==5 || $this->psversion()==6){
        		if ($this->context->customer->isLogged()==1){
   	        		return $this->display(__FILE__, 'my-account.tpl');
   	        	}
   	        }
   	        global $cookie;
        	if ($this->psversion()==4){
        		if ($cookie->isLogged()==1){
   	        		return $this->display(__FILE__, 'my-account-14.tpl');
   	        	}
   	        }            
        }
        }
    } 

    public function insertfilestocart($post,$file,$ajax=0){
        if ($this->psversion()==4 || $this->psversion()==3){
            global $cookie;
            $this->context = new StdClass();
            $this->context->cookie=$cookie;
            $this->context->cart=new Cart($cookie->id_cart);
        }

         
        $limit=count($file['file']['name']);
        for ($i=0; $i<$limit; $i++){
          if ($limit<=1){
            $db = Db::getInstance();
                if ($ajax==0){
                $plik_tmp = $file['file']['tmp_name'][$i]; 
                $plik_nazwa = $file['file']['name'][$i]; 
                $plik_rozmiar = $file['file']['size'][$i];
                } else {
                $plik_tmp = $file['file']['tmp_name']; 
                $plik_nazwa = $file['file']['name']; 
                $plik_rozmiar = $file['file']['size'];    
                }
            $plik_nazwa=strtolower(preg_replace('/[^a-zA-Z0-9\.]/', '', $plik_nazwa));
            $filetype = pathinfo($plik_nazwa, PATHINFO_EXTENSION);
          } else {
            $db = Db::getInstance();
            $plik_tmp = $file['file']['tmp_name'][$i]; 
            $plik_nazwa = $file['file']['name'][$i]; 
            $plik_rozmiar = $file['file']['size'][$i];
            $plik_nazwa=strtolower(preg_replace('/[^a-zA-Z0-9\.]/', '', $plik_nazwa));
            $filetype = pathinfo($plik_nazwa, PATHINFO_EXTENSION);            
          }
            if ($ajax==0){
                $prefix="modules/orderfiles/";
                $prefix_move="modules/orderfiles/";
            } elseif ($ajax==1) {
                $prefix="../";
                $prefix_move="";
            }
            if (!file_exists("{$prefix}cartfiles/{$post['idcart']}")){
                mkdir("{$prefix}cartfiles/{$post['idcart']}",0777);
            }
            
            if(is_uploaded_file($plik_tmp)){
                $key="";
                $sciezka="{$prefix}cartfiles/{$post['idcart']}/";
                $plik=$plik_nazwa;
                if (file_exists("$sciezka$plik")){
                    $key=$this->generatekey(10,"abcdfghijklmnouprstuwxyz1234567890");
                    $plik="$key"."_"."$plik_nazwa";
                }
                
                if (move_uploaded_file($plik_tmp, "$sciezka$plik")){
                	//$post['title']=mysql_real_escape_string($post['title']);
                	//$post['description']=mysql_real_escape_string($post['description']);
                    $query = "INSERT INTO `"._DB_PREFIX_."orderfiles_cart` (idcart,idproduct,filename,description) VALUES ('{$post['idcart']}','{$post['idproduct']}','{$plik}','{$post['description']}')";
                	$db->Execute($query);     
                }    			
            }  
        }
    }
    
    
        public function insertfilestoproduct($post,$file,$cookieid,$ajax=0){
        $query = "SELECT * FROM `"._DB_PREFIX_."orderfiles_product`  where deco_key='{$post['deco_key']}'";
        
        if(isset($_POST['product_quantity'])){
            $this->context->smarty->assign("product_quantity",$_POST['product_quantity']);
        }
        $db = Db::getInstance();
        $result = $db->executeS($query); 
        if(empty($result)){
        if ($this->psversion()==4 || $this->psversion()==3){
            global $cookie;
            $this->context = new StdClass();
            $this->context->cookie=$cookie;
            $this->context->cart=new Cart($cookie->id_cart);
         }
         
        $limit=count($file['file']['name']);
        for ($i=0; $i<$limit; $i++){
            if ($limit<=1){
                $db = Db::getInstance();
                if ($ajax==0){
                $plik_tmp = $file['file']['tmp_name'][$i]; 
                $plik_nazwa = $file['file']['name'][$i]; 
                $plik_rozmiar = $file['file']['size'][$i];
                } else {
                $plik_tmp = $file['file']['tmp_name']; 
                $plik_nazwa = $file['file']['name']; 
                $plik_rozmiar = $file['file']['size'];    
                }
                $plik_nazwa=strtolower(preg_replace('/[^a-zA-Z0-9\.]/', '', $plik_nazwa));
            } else {
                $db = Db::getInstance();
                $plik_tmp = $file['file']['tmp_name'][$i]; 
                $plik_nazwa = $file['file']['name'][$i]; 
                $plik_rozmiar = $file['file']['size'][$i];
                $plik_nazwa=strtolower(preg_replace('/[^a-zA-Z0-9\.]/', '', $plik_nazwa));
                $filetype = pathinfo($plik_nazwa, PATHINFO_EXTENSION);
            }
            if ($ajax==0){
                $prefix="modules/orderfiles/";
                $prefix_move="modules/orderfiles/";
            } elseif ($ajax==1) {
                $prefix="../";
                $prefix_move="../";
            }
            if (!file_exists("{$prefix}productfiles/$cookieid")){
                mkdir("{$prefix}productfiles/$cookieid",0777);
            }
            if(is_uploaded_file($plik_tmp)){
                
                $key="";
                $sciezka="{$prefix_move}productfiles/$cookieid/";
                
                $plik=$plik_nazwa;
                if (file_exists("$sciezka$plik")){
                    $key=$this->generatekey(10,"abcdfghijklmnouprstuwxyz1234567890");
                    $plik="$key"."_"."$plik_nazwa";
                }
                
                if (move_uploaded_file($plik_tmp, "$sciezka$plik")){
                	//$post['title']=mysql_real_escape_string($post['title']);
                	//$post['description']=mysql_real_escape_string($post['description']);
                    $query = "INSERT INTO `"._DB_PREFIX_."orderfiles_product` (idproduct,filename,cookieid,description,deco_key) VALUES ('{$post['idproduct']}','{$plik}','{$cookieid}','{$post['description']}','{$post['deco_key']}')";
                	$db->Execute($query);     
                }    			
            }  
        }}
    }

    public function changefilecart($idproduct,$idcartfile){
            $db = Db::getInstance(); 
            $query = "UPDATE `"._DB_PREFIX_."orderfiles_cart` SET idproduct='$idproduct' WHERE id='$idcartfile' ";
            $db->Execute($query);
    }
    public function changefileproduct($idproduct,$idproductfile){
            $db = Db::getInstance(); 
            $query = "UPDATE `"._DB_PREFIX_."orderfiles_product` SET idproduct='$idproduct' WHERE id='$idproductfile' ";
            $db->Execute($query);
    }  
    
    public function updatefileorder($id,$description,$title=NULL){
            $db = Db::getInstance(); 
            $query = "UPDATE `"._DB_PREFIX_."orderfiles` SET description='$description' WHERE id='$id' ";
            $db->Execute($query);
            if (!$title==NULL){
                $query = "UPDATE `"._DB_PREFIX_."orderfiles` SET title='$title' WHERE id='$id' ";
                $db->Execute($query);   
            }
    }      

    public function updatefilecart($idcartfile,$description,$title=NULL){
            $db = Db::getInstance(); 
            $query = "UPDATE `"._DB_PREFIX_."orderfiles_cart` SET description='$description' WHERE id='$idcartfile' ";
            $db->Execute($query);
            if (!$title==NULL){
                $query = "UPDATE `"._DB_PREFIX_."orderfiles_cart` SET title='$title' WHERE id='$idcartfile' ";
                $db->Execute($query);   
            }
    }
    
    public function updatefileproduct($idproductfile,$description,$title=NULL){
            $db = Db::getInstance(); 
            $query = "UPDATE `"._DB_PREFIX_."orderfiles_product` SET description='$description' WHERE id='$idproductfile' ";
            $db->Execute($query);
            if (!$title==NULL){
                $query = "UPDATE `"._DB_PREFIX_."orderfiles_product` SET title='$title' WHERE id='$idproductfile' ";
                $db->Execute($query);   
            }
    }    
        
    public function removefilecart($idcartfile){
        $db = Db::getInstance(_PS_USE_SQL_SLAVE_); 
        $query = "SELECT * FROM `"._DB_PREFIX_."orderfiles_cart` WHERE id='$idcartfile'";
        $array = $db->ExecuteS($query);
        if (isset($array['0'])){
            $array['0']['filetype']=pathinfo($array['0']['filename'], PATHINFO_EXTENSION);            
            unlink("modules/orderfiles/cartfiles/{$array['0']['idcart']}/{$array['0']['filename']}");
            $db = Db::getInstance(); 
            $query = "DELETE FROM `"._DB_PREFIX_."orderfiles_cart` WHERE id='$idcartfile' ";
            $db->Execute($query);     
        }
    }

    public function removefileproduct($idproductfile){
        $db = Db::getInstance(_PS_USE_SQL_SLAVE_); 
        $query = "SELECT * FROM `"._DB_PREFIX_."orderfiles_product` WHERE id='$idproductfile'";
        $array = $db->ExecuteS($query);
        if (isset($array['0'])){
            $array['0']['filetype']=pathinfo($array['0']['filename'], PATHINFO_EXTENSION);            
            unlink("modules/orderfiles/productfiles/{$array['0']['cookieid']}/{$array['0']['filename']}");
            $db = Db::getInstance(); 
            $query = "DELETE FROM `"._DB_PREFIX_."orderfiles_product` WHERE id='$idproductfile' ";
            $db->Execute($query);     
        }
    }    
    
    public function getcartfiles($idcart){
        if ($this->psversion()==4 || $this->psversion()==3){
            global $cookie;
            $this->context = new StdClass();
            $this->context->cookie=$cookie;
            if (!isset($this->context->cart)){
                $this->context->cart=new Cart($cookie->id_cart);
            }
        }
        $db = Db::getInstance(_PS_USE_SQL_SLAVE_); 
        $query = "SELECT * FROM `"._DB_PREFIX_."orderfiles_cart` WHERE idcart='$idcart'";
        $array = $db->ExecuteS($query);
        foreach ($array as $key=>$value){
            $array[$key]['product']=new Product($value['idproduct'],false, $this->context->cookie->id_lang);
        }
        return $array;
    }
    
    public function getcartfilesbyproduct($idcart,$idproduct){
        if ($this->psversion()==4 || $this->psversion()==3){
            global $cookie;
            $this->context = new StdClass();
            $this->context->cookie=$cookie;
            if (!isset($this->context->cart)){
                $this->context->cart=new Cart($cookie->id_cart);
            }
        }
        $db = Db::getInstance(_PS_USE_SQL_SLAVE_); 
        $query = "SELECT * FROM `"._DB_PREFIX_."orderfiles_cart` WHERE idcart='$idcart' AND idproduct='$idproduct'";
        $array = $db->ExecuteS($query);
        foreach ($array as $key=>$value){
            $array[$key]['product']=new Product($value['idproduct'],false, $this->context->cookie->id_lang);
        }
        return $array;
    }
    
    public function getproductfiles($idproduct,$cookieid){
        if ($this->psversion()==4 || $this->psversion()==3){
            global $cookie;
            $this->context = new StdClass();
            $this->context->cookie=$cookie;
            if (!isset($this->context->cart)){
                $this->context->cart=new Cart($cookie->id_cart);
            }
        }
        $db = Db::getInstance(_PS_USE_SQL_SLAVE_); 
       		//$query = "SELECT * FROM `"._DB_PREFIX_."orderfiles_product` WHERE idproduct='$idproduct' AND cookieid='$cookieid'";
		/*change by nilesh on 10-07-2015*/
		$query = "SELECT * FROM `"._DB_PREFIX_."orderfiles_product` WHERE idproduct='$idproduct'";
        $array = $db->ExecuteS($query);
        foreach ($array as $key=>$value){
            $array[$key]['product']=new Product($value['idproduct'],false, $this->context->cookie->id_lang);
        }
        return $array;
    }
    
    public function updateproductfilescart($cookieid){
        if ($this->psversion()==4 || $this->psversion()==3){
            global $cookie;
            $this->context = new StdClass();
            $this->context->cookie=$cookie;
            if (!isset($this->context->cart)){
                $this->context->cart=new Cart($cookie->id_cart);
            }
        }
        
        $query = "UPDATE `"._DB_PREFIX_."orderfiles_product` SET idcart='{$this->context->cart->id}' WHERE cookieid='$cookieid'";
        Db::getInstance()->execute($query);
    }
    
    public function getcookiefiles($cookieid){
        if ($this->psversion()==4 || $this->psversion()==3){
            global $cookie;
            $this->context = new StdClass();
            $this->context->cookie=$cookie;
        }
        $db = Db::getInstance(_PS_USE_SQL_SLAVE_); 
        $query = "SELECT * FROM `"._DB_PREFIX_."orderfiles_product` WHERE cookieid='$cookieid'";
        $array = $db->ExecuteS($query);
        foreach ($array as $key=>$value){
            $array[$key]['product']=new Product($value['idproduct'],false, $this->context->cookie->id_lang);
        }
        return $array;
    }
    
    public function hookbackofficefooter($params){
        echo '<link rel="stylesheet" href="'.$this->_path.'css/backoffice.css" />';
        if ($this->psversion()==4 && !isset($_GET['id_order'])){
            echo '<script src="'.$this->_path.'js/14orders.js"></script>';
        }
        if ($this->psversion()==5 && !isset($_GET['id_order'])){
            echo '<script src="'.$this->_path.'js/15orders.js"></script>';
        }
        if ($this->psversion()==6 && !isset($_GET['id_order'])){
            echo '<script src="'.$this->_path.'js/16orders.js"></script>';
        }
    }
    
    
    
    public function insertphotoasadmin($post,$file){        
           $limit=count($file['file']['name']);
            if (!isset($_COOKIE['ftpr'])){
                $cookieid=date("U").$this->generatekey(5,"abcdfghijklmnouprstuwxyz1234567890");
                setcookie("ftpr", $cookieid, time()+86400, "/");
            } else {
                $cookieid=$_COOKIE['ftpr'];
            }
        for ($i=0; $i<=$limit; $i++){
            global $cookie;
            $db = Db::getInstance();
            $plik_tmp = $file['file']['tmp_name'][$i]; 
            $plik_nazwa = $file['file']['name'][$i]; 
            $plik_rozmiar = $file['file']['size'][$i];
            $plik_nazwa=strtolower(preg_replace('/[^a-zA-Z0-9\.]/', '', $plik_nazwa));            
            $filetype = pathinfo($plik_nazwa, PATHINFO_EXTENSION);
            
            if (!file_exists("../modules/orderfiles/productfiles/{$cookieid}")){
                $re = mkdir("../modules/orderfiles/productfiles/{$cookieid}",0777);
            }
            $re = is_uploaded_file($plik_tmp);
            if($re){
                $key="";
                $sciezka="../modules/orderfiles/productfiles/{$cookieid}/";
                $plik=$plik_nazwa;
                if (file_exists("$sciezka$plik")){
                    $key=$this->generatekey(10,"abcdfghijklmnouprstuwxyz1234567890");
                    $plik="$key$plik_nazwa";
                }
                $re=move_uploaded_file($plik_tmp, "$sciezka$plik");
                if ($re){                    
                	$post['title']=mysql_escape_string($post['title']);
                	$post['description']=mysql_escape_string($post['description']);
                        if($post['id']=='')
                        $query = "INSERT INTO `"._DB_PREFIX_."orderfiles_product` (idcart, idproduct,filename,cookieid,description,deco_key) VALUES ('{$post['oid']}','{$post['idproduct']}','{$key}{$plik_nazwa}','{$cookieid}','{$post['description']}','{$post['deco_key']}')";
                	else
                            $query = "UPDATE `"._DB_PREFIX_."orderfiles_product` SET filename='".$key.$plik_nazwa."',description='".$post['description']."',cookieid='".$cookieid."' WHERE id=".$post['id'];
                	$db->Execute($query);     
                }	
            }
        }
    }
    
 public function insertphotofromorder($post,$file){        
           $limit=count($file['file']['name']);
            if (!isset($_COOKIE['ftpr'])){
                $cookieid=date("U").$this->generatekey(5,"abcdfghijklmnouprstuwxyz1234567890");
                setcookie("ftpr", $cookieid, time()+86400, "/");
            } else {
                $cookieid=$_COOKIE['ftpr'];
            }
        for ($i=0; $i<=$limit; $i++){
            global $cookie;
            $db = Db::getInstance();
            $plik_tmp = $file['file']['tmp_name'][$i]; 
            $plik_nazwa = $file['file']['name'][$i]; 
            $plik_rozmiar = $file['file']['size'][$i];
            $plik_nazwa=strtolower(preg_replace('/[^a-zA-Z0-9\.]/', '', $plik_nazwa));            
            $filetype = pathinfo($plik_nazwa, PATHINFO_EXTENSION);
            
            if (!file_exists("modules/orderfiles/productfiles/{$cookieid}")){
                $re = mkdir("modules/orderfiles/productfiles/{$cookieid}",0777);
            }
            $re = is_uploaded_file($plik_tmp);
            if($re){
                $key="";
                $sciezka="modules/orderfiles/productfiles/{$cookieid}/";
                $plik=$plik_nazwa;
                if (file_exists("$sciezka$plik")){
                    $key=$this->generatekey(10,"abcdfghijklmnouprstuwxyz1234567890");
                    $plik="$key$plik_nazwa";
                }
                $re=move_uploaded_file($plik_tmp, "$sciezka$plik");
                if ($re){                    
                	$post['title']=mysql_escape_string($post['title']);
                	$post['description']=mysql_escape_string($post['description']);
                        if($post['id']=='')
                        $query = "INSERT INTO `"._DB_PREFIX_."orderfiles_product` (idcart, idproduct,filename,cookieid,description,deco_key) VALUES ('{$post['oid']}','{$post['idproduct']}','{$key}{$plik_nazwa}','{$cookieid}','{$post['description']}','{$post['deco_key']}')";
                	else
                            $query = "UPDATE `"._DB_PREFIX_."orderfiles_product` SET filename='".$key.$plik_nazwa."',description='".$post['description']."', cookieid='".$cookieid."' WHERE id=".$post['id'];
                	$db->Execute($query);     
                }	
            }
        }
    }
    

/**
 * 
 * 1.4
 * 
 */
  	
	public function get_files($order,$customer){
	   if ($this->psversion()==4 || $this->psversion()==3){
            global $cookie;
            $this->context = new StdClass();
            $this->context->cookie=$cookie;
            if (!isset($this->context->cart)){
                $this->context->cart=new Cart($cookie->id_cart);
            }
        }
	    $order_detail=new OrderCore($order);
	    $db = Db::getInstance(_PS_USE_SQL_SLAVE_); 
        $query = "SELECT * FROM `"._DB_PREFIX_."orderfiles` WHERE id_order='$order'";
        $array['toorder'] = $db->ExecuteS($query);
        $query = "SELECT * FROM `"._DB_PREFIX_."orderfiles_cart` WHERE idcart='".$order_detail->id_cart."'";
        $array['tocart'] = $db->ExecuteS($query);
        foreach ($array['tocart'] as $key=>$value){
            $array['tocart'][$key]['product']=new Product($value['idproduct'],false, $this->context->cookie->id_lang);
        }
        
        $query = "SELECT * FROM `"._DB_PREFIX_."orderfiles_product` WHERE idcart='".$order_detail->id_cart."'";
        $array['toproduct'] = $db->ExecuteS($query);
        foreach ($array['toproduct'] as $key=>$value){
            $array['toproduct'][$key]['product']=new Product($value['idproduct'],false, $this->context->cookie->id_lang);
        }
        return $array;
	}
    
    protected function getBaseLink($id_shop = null, $ssl = null)
	{
		static $force_ssl = null;
		
		if ($ssl === null)
		{
			if ($force_ssl === null)
				$force_ssl = (Configuration::get('PS_SSL_ENABLED') && Configuration::get('PS_SSL_ENABLED_EVERYWHERE'));
			$ssl = $force_ssl;
		}

		if (Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') && $id_shop !== null)
			$shop = new Shop($id_shop);
		else
			$shop = Context::getContext()->shop;

		$base = (($ssl && $this->ssl_enable) ? 'https://'.$shop->domain_ssl : 'http://'.$shop->domain);

		return $base.$shop->getBaseURI();
	}
    
    public function insertphotoajax($post,$file){
            global $cookie;
            $db = Db::getInstance();
            $plik_tmp = $file['file']['tmp_name']; 
            $plik_nazwa = $file['file']['name']; 
            $plik_rozmiar = $file['file']['size'];
            $plik_nazwa=strtolower(preg_replace('/[^a-zA-Z0-9\.]/', '', $plik_nazwa));
            $filetype = pathinfo($plik_nazwa, PATHINFO_EXTENSION);
            if (!file_exists("../files/{$post['oid']}")){
                mkdir("../files/{$post['oid']}",0777);
            }
            if(is_uploaded_file($plik_tmp)){
                $key="";
                $sciezka="../files/{$post['oid']}/";
                $plik=$plik_nazwa;
                if (file_exists("$sciezka$plik")){
                    $key=$this->generatekey(10,"abcdfghijklmnouprstuwxyz1234567890");
                    $plik="$key$plik_nazwa";
                }
                if (move_uploaded_file($plik_tmp, "$sciezka$plik")){
                    $query = "INSERT INTO `"._DB_PREFIX_."orderfiles` (title,description,filename,id_order,id_customer) VALUES ('','','{$key}{$plik_nazwa}','{$post['oid']}','{$cookie->id_customer}')";
                	$db->Execute($query);
                    $query2 = "SELECT id FROM `"._DB_PREFIX_."orderfiles` WHERE filename='".($key.$plik_nazwa)."' AND id_order='".$post['oid']."'";
                    $returnr=Db::getInstance()->getRow($query2);
                    
                    $link = new Link();
                    if ($this->psversion()==5 || $this->psversion()==6){
                    echo '
                    <div class="bootstrap">
            			<div style="border:1px solid #c0c0c0; padding:10px; position:relative; display:block; clear:both; overflow:hidden; margin-bottom:10px;">
            				<img src="'._MODULE_DIR_.'orderfiles/img/file.png" style="display:inline-block; float:left; margin-right:10px;"/>
            				<div style="display:inline-block; float:left; padding-bottom:30px;">
            					<b>'.($key.$plik_nazwa).' - <a href="'.$this->getBaseLink().'modules/orderfiles/download.php?t=files&opt='.$post['oid'].'&f='.$key.$plik_nazwa.'"><strong>'.$this->l('download').'</strong></a>
            					<p style="margin-top:5px; display:block; clear:both; width:420px; line-height:20px;">&nbsp;</p>
            					<form method="post" action="'.$link->getModuleLink('orderfiles', 'filesmanager').'">
            						<input type="hidden" name="oid" value="'.$post['oid'].'"/>
            						<input type="hidden" name="fid" value="'.$returnr['id'].'"/>
                                    <input type="hidden" name="pty" value="order"/>
                                    <div style="position:absolute; right:10px; bottom:10px;">
                                        <input type="submit" name="editfile" value="'.$this->l('Edit').'" class="button"/>
                                        <input type="submit" name="delfile" value="'.$this->l('Delete').'" class="button"/>
                                    </div>
            					</form>
            				</div>
            			</div>
                    </div>';
                    } else {
                    echo '<div class="warning" style="position:relative; display:block; clear:both; overflow:hidden; margin-bottom:10px;">
        				<img src="'._MODULE_DIR_.'orderfiles/img/file.png" style="display:inline-block; float:left; margin-right:10px;"/>
        				<div style="display:inline-block; float:left; padding-bottom:30px;">
        					<b>'.($key.$plik_nazwa).'</b>
        					<p style="margin-top:5px; display:block; clear:both; width:420px; line-height:20px;"></p>
        					<form method="post" action="">
        						<input type="hidden" name="oid" value="'.$post['oid'].'"/>
        						<input type="hidden" name="fid" value="'.$returnr['id'].'"/>
        						<input type="submit" name="delfile" value="'.$this->l('Delete').'" class="button" style="position:absolute; right:10px; bottom:10px;"/>
        					</form>
        				</div>
        			</div>';                        
                    }
                        
                }	
            } 
    }
 

        
        
    public function insertphoto($post,$file){
        $limit=count($file['file']['name']);
        for ($i=0; $i<$limit; $i++){
            global $cookie;
            $db = Db::getInstance();
            $plik_tmp = $file['file']['tmp_name'][$i]; 
            $plik_nazwa = $file['file']['name'][$i]; 
            $plik_rozmiar = $file['file']['size'][$i];
            $plik_nazwa=strtolower(preg_replace('/[^a-zA-Z0-9\.]/', '', $plik_nazwa));
            $filetype = pathinfo($plik_nazwa, PATHINFO_EXTENSION);
            
            if (!file_exists("files/{$post['oid']}")){
                mkdir("files/{$post['oid']}",0777);
            }
            if(is_uploaded_file($plik_tmp)){
                $key="";
                $sciezka="files/{$post['oid']}/";
                $plik=$plik_nazwa;
                if (file_exists("$sciezka$plik")){
                    $key=$this->generatekey(10,"abcdfghijklmnouprstuwxyz1234567890");
                    $plik="$key$plik_nazwa";
                }
                
                if (move_uploaded_file($plik_tmp, "$sciezka$plik")){
                	$post['title']=mysql_escape_string($post['title']);
                	$post['description']=mysql_escape_string($post['description']);
                    $query = "INSERT INTO `"._DB_PREFIX_."orderfiles` (title,description,filename,id_order,id_customer) VALUES ('{$post['title']}','{$post['description']}','{$key}{$plik_nazwa}','{$post['oid']}','{$cookie->id_customer}')";
                	$db->Execute($query);     
                }	
            } 
        }
    }
	
    public function generatekey($length = 5, $chars) {
        $last="";
        $validCharacters = $chars;
        $validCharNumber = strlen($validCharacters);
        $result = "";
        for ($i = 0; $i < $length; $i++) {
            $index = mt_rand(0, $validCharNumber - 1);
            while ($last==$index){
                $index = mt_rand(0, $validCharNumber - 1);        
            }
            $result .= $validCharacters[$index];
            $last = $index;
        }
        return $result;
    }

    public function photodelete($idphoto,$idcustomer){
        $db = Db::getInstance(_PS_USE_SQL_SLAVE_); 
        $query = "SELECT * FROM `"._DB_PREFIX_."orderfiles` WHERE id='$idphoto' AND id_customer='$idcustomer'";
        $array = $db->ExecuteS($query);
        if (isset($array['0'])){
            $array['0']['filetype']=pathinfo($array['0']['filename'], PATHINFO_EXTENSION);            
            unlink("files/{$array['0']['id_order']}/{$array['0']['filename']}");
            $db = Db::getInstance(); 
            $query = "DELETE FROM `"._DB_PREFIX_."orderfiles` WHERE id='$idphoto' AND id_customer='$idcustomer'";
            $db->Execute($query);     
        }
    }
    
    
        public function cartfiledelete($idcartfile){
        $db = Db::getInstance(_PS_USE_SQL_SLAVE_); 
        $query = "SELECT * FROM `"._DB_PREFIX_."orderfiles_cart` WHERE id='$idcartfile' ";
        $array = $db->ExecuteS($query);
        if (isset($array['0'])){
            $array['0']['filetype']=pathinfo($array['0']['filename'], PATHINFO_EXTENSION);            
            unlink("cartfiles/{$array['0']['idcart']}/{$array['0']['filename']}");
            $db = Db::getInstance(); 
            $query = "DELETE FROM `"._DB_PREFIX_."orderfiles_cart` WHERE id='$idcartfile'";
            $db->Execute($query);     
        }
    }
    
    public function productfiledelete($idproductfile){
        $db = Db::getInstance(_PS_USE_SQL_SLAVE_); 
        $query = "SELECT * FROM `"._DB_PREFIX_."orderfiles_product` WHERE id='$idproductfile' ";
        $array = $db->ExecuteS($query);
        if (isset($array['0'])){
            $array['0']['filetype']=pathinfo($array['0']['filename'], PATHINFO_EXTENSION);            
            unlink("productfiles/{$array['0']['cookieid']}/{$array['0']['filename']}");
            $db = Db::getInstance(); 
            $query = "DELETE FROM `"._DB_PREFIX_."orderfiles_product` WHERE id='$idproductfile'";
            $db->Execute($query);     
        }
    } 
	
	public function extension($filename){
		return pathinfo($filename, PATHINFO_EXTENSION);
	}

    
}

class orderfilesUpdate extends orderfiles {  
    public static function _isCurl(){
        return function_exists('curl_version');
    }
    public static function version($version){
        $version=(int)str_replace(".","",$version);
        if (strlen($version)==3){$version=(int)$version."0";}
        if (strlen($version)==2){$version=(int)$version."00";}
        if (strlen($version)==1){$version=(int)$version."000";}
        if (strlen($version)==0){$version=(int)$version."0000";}
        return (int)$version;
    }
    public static function verify($module,$key){
        if (ini_get("allow_url_fopen")) {
             if (function_exists("file_get_contents")){
                $actual_version = @file_get_contents('http://dev.mypresta.eu/update/get.php?module='.$module."&lic=$key&u=".self::encrypt(_PS_BASE_URL_.__PS_BASE_URI__));
             }
        }
        Configuration::updateValue("update_".$module,date("U"));
        Configuration::updateValue("updatev_".$module,$actual_version); 
        return $actual_version;
    }
    public static function encrypt($string){
        return base64_encode($string);
    }
}
?>
