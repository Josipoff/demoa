<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class OrderDetailController extends OrderDetailControllerCore
{
 
    public function postProcess()
    {
        parent::postProcess();
 
        if (Tools::isSubmit('markAsReceived'))
        {
            $idOrder = (int)(Tools::getValue('id_order'));
            $order = new Order($idOrder);
 
            if(Validate::isLoadedObject($order))
            {
                if($order->getCurrentState() == 15) // if the order is shipped
                {
                    $new_history = new OrderHistory();
                    $new_history->id_order = (int)$order->id;
                    $new_history->changeIdOrderState(16, $order); // 16: Ready for Production
                    //var_dump($order,$new_history);
                    $myfile = fopen(PS_PRODUCT_IMG_PATH."/orders/".$order->reference.".txt", "w") or die("Unable to open file!");
                    $txt = "Order Confirmed\n Order Reference: ".$order->reference;
                    fwrite($myfile, $txt);
                    fclose($myfile);
                    $new_history->addWithemail(true);    
                }
 
                $this->context->smarty->assign('receipt_confirmation', true);
 
            } else $this->_errors[] = Tools::displayError('Error: Invalid order number');
             
        }       
 
    }
 
 
}