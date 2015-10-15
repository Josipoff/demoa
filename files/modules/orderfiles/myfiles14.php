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
    	
        $order = new OrderCore();
        $custorders=$order->getCustomerOrders($cookie->id_customer);
	    
	    
	    global $smarty;
	    $smarty->assign('this_path',__PS_BASE_URI__.'modules/'.$orderfiles->name.'/');
	    $smarty->assign('mod',$orderfiles);
	    $smarty->assign('setup',$setup);
	    $smarty->assign('orders',$custorders);
	    
	    
	    if ($orderfiles->psversion()==4){
			echo Module::display(dirname(__FILE__).'/orderfiles.php', 'my-files14.tpl');
			
		}
	
	}	
    include(dirname(__FILE__).'/../../footer.php');
    
    