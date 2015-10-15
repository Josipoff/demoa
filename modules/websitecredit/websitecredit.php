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
class websitecredit extends Module
{

    public function __construct()
    {
        $this->name = 'websitecredit';
        $this->version = '1.0';
        $this->tab = 'administration';
        $this->author = 'Ayushi Agarwal';
        $this->need_instance = 0;

        parent::__construct();

        $this->displayName = $this->l('Website Design and Development Credits');
        $this->description = $this->l('This module allows your shop  to publish shop credits');
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
}