<?php

if (!defined('_PS_VERSION_'))
{
    exit;
}

/**
 * activationbymail
 * @category administration
 *
 * @author Ayushi Agarwa ayushi.agarwal112@gmail.com
 * @copyright Ayushi Agarwal ayushi.agarwal112@gmail.com
 * @license GNU_GPL_v2
 * @version 1.2
 */
class adminorderapproval extends Module
{

    public function __construct()
    {
        $this->name = 'adminorderapproval';
        $this->version = '1.0';
        $this->tab = 'administration';
        $this->author = 'Ayushi Agarwal';
        $this->need_instance = 0;

        parent::__construct();

        $this->displayName = $this->l('Admin Order Approval by e-mail');
        $this->description = $this->l('This module allows your shop  manager to validate order');
    }

    public function install()
    {
        if (parent::install() &&
            Db::getInstance()->Execute('alter table ' . _DB_PREFIX_ . 'orders add activation_link char(32)')
        )
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function uninstall()
    {
        if (parent::uninstall() &&
            Db::getInstance()->Execute('alter table ' . _DB_PREFIX_ . 'orders drop activation_link')
        )
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function sendAdminApprovalMail($orderId,$data)
    {

        global $cookie;
        $id_lang = $cookie->id_lang;
        $activation_link = md5(uniqid(rand(), true));
        $link = $this->context->link->getModuleLink($this->name, 'approval') . '?link=' . $activation_link;
        
        $sql = sprintf("update %sorders set activation_link='%s' where id_order=%d",
                       _DB_PREFIX_, $activation_link, $orderId);
        Db::getInstance()->Execute($sql);

        $order = new Order($orderId);
        $order->getFields();
        $products = $this->context->cart->getProducts();
        $manager_email = Configuration::get('ORDER_CNF_MANAGER_EMAIL');
        $data['link'] = $link;
        Mail::Send($id_lang,
                   'admin_approval',
                   $this->l('Order Approval'),
                   $data,
                   $manager_email,
                   NULL,
                   NULL,
                   NULL,
                   NULL,
                   NULL,
                   'modules/adminorderapproval/mails/');
				   return true;
    }

    private function isMD5($str)
    {
        for ($i = 0; $i < strlen($str); $i++)
        {
            if (!(($str[$i] >= 'a' && $str[$i] <= 'z') || ($str[$i] >= '0' && $str[$i] <= '9')))
            {
                return false;
            }
        }
        return true;
    }

    public function execActivation()
    {
        $link = Tools::getValue('link');
        if ($this->isMD5($link))
        {
            return $this->approveOrderForValidLink($link);
        }
        else
        {
            return false;
        }
    }

    private function approveOrderForValidLink($link)
    {
        $orderID = Order::getOrderIDbyActivationLink($link);
		if (!orderID)
        {
            return false;
        }
        else
        {            
            $order = new Order($orderID); 
            if(Validate::isLoadedObject($order))
            {
			    if($order->getCurrentState() == 13) // if the order is shipped
                {
                    $new_history = new OrderHistory();
                    $new_history->id_order = (int)$order->id;
                    $new_history->changeIdOrderState(18, $order); // 15: Design Approval
                    $new_history->addWithemail(true);   
                }return true;
            }
            else
            {
                return false;
            }
        }
    }
}
