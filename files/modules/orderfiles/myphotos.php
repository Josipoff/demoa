<?php
require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../init.php');

include_once(dirname(__FILE__).'/orderphotos.php');


if (!$cookie->isLogged()){
	   Tools::redirect('authentication.php?back=modules/orderphotos/myphotos.php');
    }
        
    include(dirname(__FILE__).'/../../header.php');
    
    //$smarty->assign('this_path',__PS_BASE_URI__.'modules/'.$cpi->name.'/');
    //$smarty->assign('cpi',$cpi);
    
    if ($cpi->psversion()==4){
		echo Module::display(dirname(__FILE__).'/orderphotos.php', 'my-photos.tpl');
		
	}	
    include(dirname(__FILE__).'/../../footer.php');