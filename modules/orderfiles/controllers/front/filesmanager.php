<?php
include_once(dirname(__FILE__).'../../../orderfiles.php');
class orderfilesfilesmanagerModuleFrontController extends ModuleFrontController{	
	public function initContent(){
		$module=new orderfiles();
		$this->var=$module->getconf();
		parent::initContent();
        
        global $smarty;
        $ext=explode(",",Configuration::get('OF_FTYPES'));
        $smarty->assign('extensions',$ext);
		
		if (isset($_POST['addfile'])){
			if (isset($_POST['oid'])){
				$order = new OrderCore($_POST['oid']);
				if ($order->id_customer==$this->context->customer->id){
					$this->insertphoto($_POST,$_FILES);
				}
			}
		}
        
        if (isset($_POST['savefile'])){
			if ($_POST['pty']=="order"){
		        $module->updatefileorder($_POST['fid'],mysql_escape_string($_POST['description']),mysql_escape_string($_POST['title']));
			}
            if ($_POST['pty']=="cart"){
		        $module->updatefilecart($_POST['fid'],mysql_escape_string($_POST['description']),mysql_escape_string($_POST['title']));
			}
            if ($_POST['pty']=="product"){
		        $module->updatefileproduct($_POST['fid'],mysql_escape_string($_POST['description']),mysql_escape_string($_POST['title']));
			}
		}
        

		if (isset($_POST['delfile'])){
			if (isset($_POST['oid'])){
				$order = new OrderCore($_POST['oid']);
				if ($order->id_customer==$this->context->customer->id){
					$this->photodelete($_POST['fid'],$this->context->customer->id);
				}
			}
		}
        if (isset($_POST['delcartfile'])){
			if (isset($_POST['fid'])){
			 $this->cartfiledelete($_POST['fid']);
			}
		}
        
        if (isset($_POST['delproductfile'])){
			if (isset($_POST['fid'])){
			 $this->productfiledelete($_POST['fid']);
			}
		}		
		
		if (isset($_POST['oid'])){
			$order = new OrderCore($_POST['oid']);
			if ($order->id_customer==$this->context->customer->id){		
				$this->context->smarty->assign(array(
				'mod' => $this,
				'setup' => $this->var, 
				'order' => $order,
				'idorder' => $_POST['oid'],
				'files' => $this->get_files($_POST['oid'],$this->context->customer->id),
				'link' => $this->context->link,
				'customer' => $this->context->customer));
				$this->setTemplate('filesmanager.tpl');
			} else {
				$this->setTemplate('access-denied.tpl');	
			}
		} else {
			$this->setTemplate('access-denied.tpl');
		}
	}	
	
	public function currency_sign($id){
		$currency=new CurrencyCore($id);
		return $currency->sign;
	}
	
	public function get_files($order,$customer){
	    $order_detail=new OrderCore($order);;
	    $db = Db::getInstance(_PS_USE_SQL_SLAVE_); 
        $query = "SELECT * FROM `"._DB_PREFIX_."orderfiles` WHERE id_order='$order' AND (id_customer='$customer' OR id_customer='0')";
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
	
    public function insertphoto($post,$file){
        $limit=count($file['file']['name']);
        for ($i=0; $i<$limit; $i++){
            $db = Db::getInstance();
            $plik_tmp = $file['file']['tmp_name'][$i]; 
            $plik_nazwa = $file['file']['name'][$i]; 
            $plik_rozmiar = $file['file']['size'][$i];
            $plik_nazwa=strtolower(preg_replace('/[^a-zA-Z0-9\.]/', '', $plik_nazwa));
            $filetype = pathinfo($plik_nazwa, PATHINFO_EXTENSION);
            
            if (!file_exists("modules/orderfiles/files/{$post['oid']}")){
                mkdir("modules/orderfiles/files/{$post['oid']}",0777);
            }
            if(is_uploaded_file($plik_tmp)){
                $key="";
                $sciezka="modules/orderfiles/files/{$post['oid']}/";
                $plik=$plik_nazwa;
                if (file_exists("$sciezka$plik")){
                    $key=$this->generatekey(10,"abcdfghijklmnouprstuwxyz1234567890");
                    $plik="$key$plik_nazwa";
                }
                
                if (move_uploaded_file($plik_tmp, "$sciezka$plik")){
                	$post['title']=mysql_escape_string($post['title']);
                	$post['description']=mysql_escape_string($post['description']);
                    $query = "INSERT INTO `"._DB_PREFIX_."orderfiles` (title,description,filename,id_order,id_customer) VALUES ('{$post['title']}','{$post['description']}','{$key}{$plik_nazwa}','{$post['oid']}','{$this->context->customer->id}')";
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
    
    public function cartfiledelete($idcartfile){
        $db = Db::getInstance(_PS_USE_SQL_SLAVE_); 
        $query = "SELECT * FROM `"._DB_PREFIX_."orderfiles_cart` WHERE id='$idcartfile' ";
        $array = $db->ExecuteS($query);
        if (isset($array['0'])){
            $array['0']['filetype']=pathinfo($array['0']['filename'], PATHINFO_EXTENSION);            
            unlink("modules/orderfiles/cartfiles/{$array['0']['idcart']}/{$array['0']['filename']}");
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
            unlink("modules/orderfiles/productfiles/{$array['0']['cookieid']}/{$array['0']['filename']}");
            $db = Db::getInstance(); 
            $query = "DELETE FROM `"._DB_PREFIX_."orderfiles_product` WHERE id='$idproductfile'";
            $db->Execute($query);     
        }
    }    

    public function photodelete($idphoto,$idcustomer){
        $db = Db::getInstance(_PS_USE_SQL_SLAVE_); 
        $query = "SELECT * FROM `"._DB_PREFIX_."orderfiles` WHERE id='$idphoto' AND id_customer='$idcustomer'";
        $array = $db->ExecuteS($query);
        if (isset($array['0'])){
            $array['0']['filetype']=pathinfo($array['0']['filename'], PATHINFO_EXTENSION);            
            unlink("modules/orderfiles/files/{$array['0']['id_order']}/{$array['0']['filename']}");
            $db = Db::getInstance(); 
            $query = "DELETE FROM `"._DB_PREFIX_."orderfiles` WHERE id='$idphoto' AND id_customer='$idcustomer'";
            $db->Execute($query);     
        }
    }	
	
	public function extension($filename){
		return pathinfo($filename, PATHINFO_EXTENSION);
	}			
}