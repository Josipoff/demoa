<?php
require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../init.php');
include_once(dirname(__FILE__).'/orderfiles.php');

$orderfiles = new orderfiles();
$setup=$orderfiles->getconf();
global $cookie;

include(dirname(__FILE__).'/../../header.php');

if (!$cookie->isLogged()){
	   Tools::redirect('authentication.php?back=modules/orderfiles/myfiles14.php');
    } else {
    	
    	
    	if (isset($_POST['addfile'])){
			if (isset($_POST['oid'])){
				$order = new OrderCore($_POST['oid']);
				if ($order->id_customer==$cookie->id_customer){
					$orderfiles->insertphoto($_POST,$_FILES);
				}
			}
		}

		if (isset($_POST['delfile'])){
			if (isset($_POST['oid'])){
				$order = new OrderCore($_POST['oid']);
				if ($order->id_customer==$cookie->id_customer){
					$orderfiles->photodelete($_POST['fid'],$cookie->id_customer);
				}
			}
		}	
        
       if (isset($_POST['delcartfile'])){
			if (isset($_POST['fid'])){
			 $orderfiles->cartfiledelete($_POST['fid']);
			}
		}
        
        if (isset($_POST['delproductfile'])){
			if (isset($_POST['fid'])){
			 $orderfiles->productfiledelete($_POST['fid']);
			}
		}		
		
		if (isset($_POST['oid'])){
			$order = new OrderCore($_POST['oid']);
			if ($order->id_customer==$cookie->id_customer){
			
				global $smarty;
	    		$smarty->assign('this_path',__PS_BASE_URI__.'modules/'.$orderfiles->name.'/');
	    		$smarty->assign('mod',$orderfiles);
  				$smarty->assign('setup',$setup);
	    		$smarty->assign('order',$order);
	    		$smarty->assign('idorder',$_POST['oid']);
	    		$smarty->assign('files',$orderfiles->get_files($_POST['oid'],$cookie->id_customer));
	    
	    		/**
				$this->context->smarty->assign(array(
				'mod' => $this,
				'setup' => $this->var, 
				'order' => $order,
				'idorder' => $_POST['oid'],
				'files' => $this->get_files($_POST['oid'],$cookie->id_customer),
				'link' => $this->context->link,
				'customer' => $this->context->customer));
				**/
				
				echo Module::display(dirname(__FILE__).'/orderfiles.php', 'filesmanager14.tpl');
			} else {
				echo Module::display(dirname(__FILE__).'/orderfiles.php', 'access-denied14.tpl');
			}
		} else {
			echo Module::display(dirname(__FILE__).'/orderfiles.php', 'access-denied14.tpl');
		}
    	
	}	
    include(dirname(__FILE__).'/../../footer.php');
    
    