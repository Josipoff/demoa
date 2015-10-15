<?php

if (!defined('_PS_VERSION_'))
{
    exit;
}

/**
 * activationbymail
 * @category administration
 *
 * @author Dominik Cebula dominikcebula@gmail.com
 * @copyright Dominik Cebula dominikcebula@gmail.com
 * @license GNU_GPL_v2
 * @version 1.2
 */
class requestsample extends Module
{

    public function __construct()
    {
        $this->name = 'requestsample';
        $this->version = '1.2';
        $this->tab = 'administration';
        $this->author = 'Ayushi Agarwal';
        $this->need_instance = 0;

        parent::__construct();

        $this->displayName = $this->l('Request A sample by e-mail');
        $this->description = $this->l('This module allows your user to send sample request form');
    }

    public function install()
    {
        if (parent::install())
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
        if (parent::uninstall())
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function sendmail($req,$from,$id_lang,$template,$subject)
    {	
        if(Configuration::get('PRODUCT_REQUEST_EMAIL'))
                        $to = strval(Configuration::get('PRODUCT_REQUEST_EMAIL'));
        else{
            if (!Configuration::get('PS_MAIL_EMAIL_MESSAGE'))
                    $to = strval(Configuration::get('PS_SHOP_EMAIL'));
            else
            {
                    $to = new Contact((int)(Configuration::get('PS_MAIL_EMAIL_MESSAGE')));
                    $to = strval($to->email);
            }
        }
		Mail::Send($id_lang,
           $template,
           $subject,
           $req,
           $to,
           NULL,
           NULL,
           NULL,
           NULL,
           NULL,
           'modules/requestsample/mails/');
    }
    
    
}
