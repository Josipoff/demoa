<?php

if(!defined('_PS_VERSION_')) exit;

if(!isset($GLOBALS['magictoolbox'])) {
    $GLOBALS['magictoolbox'] = array();
    $GLOBALS['magictoolbox']['filters'] = array();
    $GLOBALS['magictoolbox']['isProductScriptIncluded'] = false;
    $GLOBALS['magictoolbox']['standardTool'] = '';
    $GLOBALS['magictoolbox']['selectorImageType'] = '';
}

if(!isset($GLOBALS['magictoolbox']['magicscroll'])) {
    $GLOBALS['magictoolbox']['magicscroll'] = array();
    $GLOBALS['magictoolbox']['magicscroll']['headers'] = false;
}

class MagicScroll extends Module {

    //PrestaShop v1.5 or above
    public $isPrestaShop15x = false;

    //PrestaShop v1.6 or above
    public $isPrestaShop16x = false;

    //Smarty v3 template engine
    public $isSmarty3 = false;

    //Smarty 'getTemplateVars' function name
    public $getTemplateVars = 'getTemplateVars';

    //Suffix was added to default images types since version 1.5.1.0
    public $imageTypeSuffix = '';

    public function __construct() {

        $this->name = 'magicscroll';
        $this->tab = 'Tools';
        $this->version = '5.6.0';
        $this->author = 'Magic Toolbox';


        $this->module_key = '0da9dca768b05e93d1cde8b495070296';

        parent::__construct();

        $this->displayName = 'Magic Scroll';
        $this->description = "Effortlessly scroll through images and/or text on your web pages.";

        $this->confirmUninstall = 'All magicscroll settings would be deleted. Do you really want to uninstall this module ?';

        $this->isPrestaShop15x = version_compare(_PS_VERSION_, '1.5', '>=');
        $this->isPrestaShop16x = version_compare(_PS_VERSION_, '1.6', '>=');

        $this->isSmarty3 = $this->isPrestaShop15x || Configuration::get('PS_FORCE_SMARTY_2') === "0";
        if($this->isSmarty3) {
            //Smarty v3 template engine
            $this->getTemplateVars = 'getTemplateVars';
        } else {
            //Smarty v2 template engine
            $this->getTemplateVars = 'get_template_vars';
        }

        $this->imageTypeSuffix = version_compare(_PS_VERSION_, '1.5.1.0', '>=') ? '_default' : '';

    }

    public function install() {
        $homeHookID = $this->isPrestaShop15x ? ($this->isPrestaShop16x ? Hook::getIdByName('displayTopColumn') : Hook::getIdByName('displayHome')) : Hook::get('home');
        $headerHookID = $this->isPrestaShop15x ? Hook::getIdByName('displayHeader') : Hook::get('header');
        if(   !parent::install()
           OR !$this->registerHook($this->isPrestaShop15x ? 'displayHeader' : 'header')
           OR !$this->registerHook($this->isPrestaShop15x ? 'displayFooterProduct' : 'productFooter')
           OR !$this->registerHook($this->isPrestaShop15x ? 'displayFooter' : 'footer')
           OR !$this->installDB()
           OR !$this->fixCSS()
           OR !$this->registerHook($this->isPrestaShop15x ? ($this->isPrestaShop16x ? 'displayTopColumn' : 'displayHome') : 'home')
           OR !$this->updatePosition($homeHookID, 0, 1)
           OR !$this->createImageFolder('magicscroll')
           OR !$this->updatePosition($headerHookID, 0, 1)
          )
          return false;

        $this->sendStat('install');

        return true;
    }

    private function createImageFolder($imageFolderName) {
        if(!is_dir(_PS_IMG_DIR_.$imageFolderName)) {
            if(!mkdir(_PS_IMG_DIR_.$imageFolderName, 0755)) {
                return false;
            }
        }
        return true;
    }

    private function installDB() {
        if(!Db::getInstance()->Execute('CREATE TABLE `'._DB_PREFIX_.'magicscroll_settings` (
                                        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                                        `block` VARCHAR(32) NOT NULL,
                                        `name` VARCHAR(32) NOT NULL,
                                        `value` TEXT,
                                        `default_value` TEXT,
                                        `enabled` TINYINT(1) UNSIGNED NOT NULL,
                                        `default_enabled` TINYINT(1) UNSIGNED NOT NULL,
                                        PRIMARY KEY (`id`)
                                        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;'
                                      )
            OR !$this->fillDB()
            OR !$this->fixDefaultValues()
            OR !Db::getInstance()->Execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'magicscroll_images` (
                                            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                                            `order` INT UNSIGNED DEFAULT 0,
                                            `name` VARCHAR(64) NOT NULL DEFAULT \'\',
                                            `ext` VARCHAR(16) NOT NULL DEFAULT \'\',
                                            `title` VARCHAR(64) NOT NULL DEFAULT \'\',
                                            `description` TEXT,
                                            `link` VARCHAR(256) NOT NULL DEFAULT \'\',
                                            `lang` INT(10) UNSIGNED DEFAULT 0,
                                            `enabled` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
                                            PRIMARY KEY (`id`)
                                            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;'
                                          )
          ) return false;

        return true;
    }

    private function fixCSS() {

        //fix url's in css files
        $fileContents = file_get_contents(dirname(__FILE__).'/magicscroll.css');
        $toolPath = _MODULE_DIR_.'magicscroll';
        $pattern = '/url\(\s*(?:\'|")?(?!'.preg_quote($toolPath, '/').')\/?([^\)\s]+?)(?:\'|")?\s*\)/is';
        $replace = 'url('.$toolPath.'/$1)';
        $fixedFileContents = preg_replace($pattern, $replace, $fileContents);
        if($fixedFileContents != $fileContents) {
            //file_put_contents(dirname(__FILE__).'/magicscroll.css', $fixedFileContents);
            $fp = fopen(dirname(__FILE__).'/magicscroll.css', 'w+');
            if($fp) {
                fwrite($fp, $fixedFileContents);
                fclose($fp);
            }
        }

        return true;
    }

    private function sendStat($action = '') {

        //NOTE: don't send from working copy
        if('working' == 'v5.6.0' || 'working' == 'v1.0.29') {
            return;
        }

        $hostname = 'www.magictoolbox.com';
        $url = $_SERVER['HTTP_HOST'].preg_replace('/\/$/i', '', __PS_BASE_URI__);
        $url = urlencode(urldecode($url));
        $platformVersion = defined('_PS_VERSION_') ? _PS_VERSION_ : '';
        $path = "api/stat/?action={$action}&tool_name=magicscroll&license=trial&tool_version=v1.0.29&module_version=v5.6.0&platform_name=prestashop&platform_version={$platformVersion}&url={$url}";
        $handle = @fsockopen($hostname, 80, $errno, $errstr, 30);
        if($handle) {
            $headers  = "GET /{$path} HTTP/1.1\r\n";
            $headers .= "Host: {$hostname}\r\n";
            $headers .= "Connection: Close\r\n\r\n";
            fwrite($handle, $headers);
            fclose($handle);
        }

    }

    public function fixDefaultValues() {
        $result = true;
        if(version_compare(_PS_VERSION_, '1.5.1.0', '>=')) {
            $sql = 'UPDATE `'._DB_PREFIX_.'magicscroll_settings` SET `value`=CONCAT(value, \'_default\') WHERE `name`=\'thumb-image\' OR `name`=\'selector-image\' OR `name`=\'large-image\'';
            $result = Db::getInstance()->Execute($sql);
        }
        if($this->isPrestaShop16x) {
            $sql = 'UPDATE `'._DB_PREFIX_.'magicscroll_settings` SET `value`=\'home_default\', `enabled`=1 WHERE `name`=\'thumb-image\' AND (`block`=\'homefeatured\' OR `block`=\'blocknewproducts_home\' OR `block`=\'blockbestsellers_home\')';
            $result = Db::getInstance()->Execute($sql);
            $sql = 'UPDATE `'._DB_PREFIX_.'magicscroll_settings` SET `value`=\'0\', `enabled`=1 WHERE `name`=\'width\' AND `block`=\'homefeatured\'';
            $result = Db::getInstance()->Execute($sql);
        }
        return $result;
    }

    public function uninstall() {
        if(version_compare(_PS_VERSION_, '1.5.5.0', '>=')) {
            $this->_clearCache('*');
        }
        if(!parent::uninstall() OR !$this->uninstallDB()) return false;
        $this->sendStat('uninstall');
        return true;
    }

    private function uninstallDB() {
        return  Db::getInstance()->Execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'magicscroll_settings`;')
                //AND Db::getInstance()->Execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'magicscroll_images`;')
                ;
    }

    public function disable($forceAll = false) {
        if(version_compare(_PS_VERSION_, '1.5.5.0', '>=')) {
            $this->_clearCache('*');
        }
        return parent::disable($forceAll);
    }

    public function enable($forceAll = false) {
        if(version_compare(_PS_VERSION_, '1.5.5.0', '>=')) {
            $this->_clearCache('*');
        }
        return parent::enable($forceAll);
    }

    public function _clearCache($template, $cache_id = NULL, $compile_id = NULL) {

        $this->name = 'homefeatured';//NOTE: spike to clear cache for 'homefeatured.tpl'
        parent::_clearCache('homefeatured.tpl');
        parent::_clearCache('tab.tpl', 'homefeatured-tab');

        $this->name = 'blockbestsellers';
        parent::_clearCache('blockbestsellers.tpl');
        parent::_clearCache('blockbestsellers-home.tpl', 'blockbestsellers-home');
        parent::_clearCache('blockbestsellers.tpl', 'blockbestsellers_col');
        parent::_clearCache('tab.tpl', 'blockbestsellers-tab');

        $this->name = 'blocknewproducts';
        parent::_clearCache('blocknewproducts.tpl');
        parent::_clearCache('blocknewproducts_home.tpl', 'blocknewproducts-home');
        parent::_clearCache('tab.tpl', 'blocknewproducts-tab');

        $this->name = 'blockspecials';
        parent::_clearCache('blockspecials.tpl');

        $this->name = 'magicscroll';

    }

    public function getImagesTypes() {
        if(!isset($GLOBALS['magictoolbox']['imagesTypes'])) {
            $GLOBALS['magictoolbox']['imagesTypes'] = array('original');
            // get image type values
            $sql = 'SELECT name FROM `'._DB_PREFIX_.'image_type` ORDER BY `id_image_type` ASC';
            $result = Db::getInstance()->ExecuteS($sql);
            foreach($result as $row) {
                $GLOBALS['magictoolbox']['imagesTypes'][] = $row['name'];
            }
        }
        return $GLOBALS['magictoolbox']['imagesTypes'];
    }

    public function getContent() {

        $action = Tools::getValue('magicscroll-submit-action', false);

        if($action == 'reset') {
            Db::getInstance()->Execute(
                'UPDATE `'._DB_PREFIX_.'magicscroll_settings` SET `value`=`default_value`, `enabled`=`default_enabled`'
            );
        }

        $tool = $this->loadTool();
        $paramsMap = $this->getParamsMap();

        $_imagesTypes = array(
            'thumb'
        );

        foreach($_imagesTypes as $name) {
            foreach($this->getBlocks() as $blockId => $blockLabel) {
                if($tool->params->paramExists($name.'-image', $blockId)) {
                    $tool->params->setValues($name.'-image', $this->getImagesTypes(), $blockId);
                }
            }
        }

        $paramData = $tool->params->getParam('enable-effect', 'homeslideshow');
        $paramData['label'] = 'Show slideshow on home page';
        $paramData['description'] = '<h2>Slideshow shortcodes</h2>'.
            'In order to show slideshow on any CMS page just insert slideshow shortcode <b>[magicscroll]</b>.<br />'.
            'If you want to show slideshow with specific images only, please use shortcode <b>[magicscroll id=1,2,5]</b> where 1, 2 and 5 are numbers of images from the ID column.';

        $tool->params->appendParams(array('enable-effect' => $paramData), 'homeslideshow');



        //debug_log($_GET);
        //debug_log($_POST);

        $params = Tools::getValue('magicscroll', false);

        //NOTE: save settings
        if($action == 'save' && $params) {
            foreach($paramsMap as $blockId => $groups) {
                foreach($groups as $group) {
                    foreach($group as $param => $required) {
                        if(isset($params[$blockId][$param])) {
                            $valueToSave = $value = trim($params[$blockId][$param]);
                            switch($tool->params->getType($param)) {
                                case 'num':
                                    $valueToSave = $value = intval($value);
                                    break;
                                case 'array':
                                    if(!in_array($value, $tool->params->getValues($param))) $valueToSave = $value = $tool->params->getDefaultValue($param);
                                    break;
                                case 'text':
                                    $valueToSave = pSQL($value);
                                    break;
                            }
                            Db::getInstance()->Execute(
                                'UPDATE `'._DB_PREFIX_.'magicscroll_settings` SET `value`=\''.$valueToSave.'\', `enabled`=1 WHERE `block`=\''.$blockId.'\' AND `name`=\''.$param.'\''
                            );
                            $tool->params->setValue($param, $value, $blockId);
                        } else {
                            Db::getInstance()->Execute(
                                'UPDATE `'._DB_PREFIX_.'magicscroll_settings` SET `enabled`=0 WHERE `block`=\''.$blockId.'\' AND `name`=\''.$param.'\''
                            );
                            if($tool->params->paramExists($param, $blockId)) {
                                $tool->params->removeParam($param, $blockId);
                            };
                        }
                    }
                }
            }
            if(version_compare(_PS_VERSION_, '1.5.5.0', '>=')) {
                $this->_clearCache('*');
            }
        }

        $imageFilePath = _PS_IMG_DIR_.'magicscroll/';
        $imagesTypes = ImageType::getImagesTypes();

        //NOTE: upload images
        if($action == 'upload' && isset($_FILES['magicscroll-image-files']['tmp_name'])
                               && is_array($_FILES['magicscroll-image-files']['tmp_name'])
                               && count($_FILES['magicscroll-image-files']['tmp_name'])) {
            $errors = array();
            $imageResizeMethod = 'imageResize';
            //NOTE: __autoload function in Prestashop 1.3.x leads to PHP fatal error because ImageManager class does not exists
            //      can not use class_exists('ImageManager', false) because ImageManager class will not load where it is needed
            //      so check version before
            if($this->isPrestahop15x && class_exists('ImageManager') && method_exists('ImageManager', 'resize')) {
                $imageResizeMethod = array('ImageManager', 'resize');
            }

            foreach($_FILES['magicscroll-image-files']['tmp_name'] as $key => $tempName) {
                if(!empty($tempName) && file_exists($tempName)) {
                    if(!$tmpName = tempnam(_PS_TMP_IMG_DIR_, 'PS') OR !move_uploaded_file($tempName, $tmpName)) {
                        $errors[] = 'An error occurred during the image upload.';
                    } else {
                        preg_match('/^(.*?)\.([^\.]*)$/is', $_FILES['magicscroll-image-files']['name'][$key], $matches);
                        list(, $imageFileName, $imageFileExt) = $matches;
                        $imageSuffix = 0;
                        while(file_exists($imageFilePath.$imageFileName.($imageSuffix?'-'.$imageSuffix:'').'.'.$imageFileExt)) {
                            $imageSuffix++;
                        }
                        $imageFileName = $imageFileName.($imageSuffix?'-'.$imageSuffix:'');
                        if(!call_user_func($imageResizeMethod, $tmpName, $imageFilePath.$imageFileName.'.'.$imageFileExt, NULL, NULL, $imageFileExt)) {
                            $errors[] = 'An error occurred while copying image.';
                        } else {
                            foreach($imagesTypes as $k => $imageType) {
                                if(!call_user_func($imageResizeMethod, $tmpName, $imageFilePath.$imageFileName.'-'.stripslashes($imageType['name']).'.'.$imageFileExt, $imageType['width'], $imageType['height'], $imageFileExt)) {
                                    $errors[] = 'An error occurred while copying resized image ('.stripslashes($imageType['name']).').';
                                }
                            }
                        }
                    }
                    @unlink($tmpName);
                    Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'magicscroll_images` (`name`, `ext`, `title`, `description`, `link`, `lang`, `enabled`, `order`) VALUES (\''.$imageFileName.'\', \''.$imageFileExt.'\', \'\', \'\', \'\', 0, 1, 0)');
                    Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'magicscroll_images` SET `order`=LAST_INSERT_ID() WHERE `id`=LAST_INSERT_ID()');
                }
            }
        }

        $imagesUpdateData = Tools::getValue('images-update-data', false);
        if(!$imagesUpdateData) $imagesUpdateData = array();
        // save images data
        if($action == 'save' && !empty($imagesUpdateData)) {
            foreach($imagesUpdateData as $imageId => $imageData) {
                if(intval($imageData['delete'])) {
                    $sql = 'SELECT `name`, `ext` FROM `'._DB_PREFIX_.'magicscroll_images` WHERE `id`='.intval($imageId);
                    $result = Db::getInstance()->ExecuteS($sql);
                    $result = $result[0];
                    foreach($imagesTypes as $k => $imageType) {
                        @unlink($imageFilePath.$result['name'].'-'.stripslashes($imageType['name']).'.'.$result['ext']);
                    }
                    @unlink($imageFilePath.$result['name'].'.'.$result['ext']);
                    Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'magicscroll_images` WHERE `id`='.intval($imageId));
                } else {
                    Db::getInstance()->Execute(
                        'UPDATE `'._DB_PREFIX_.'magicscroll_images` SET '.
                            '`order`='.intval($imageData['order']).
                            ', `title`=\''.$imageData['title'].'\''.
                            ', `description`=\''.pSQL(htmlspecialchars($imageData['description'])).'\''.
                            ', `link`=\''.$imageData['link'].'\''.
                            ', `lang`=\''.$imageData['lang'].'\''.
                            ', `enabled`='.(isset($imageData['exclude']) ? '0' : '1').
                            ' WHERE `id`='.intval($imageId)
                    );
                }
            }
        }


        include(dirname(__FILE__).'/admin/magictoolbox.settings.editor.class.php');
        $settings = new MagictoolboxSettingsEditorClass(dirname(__FILE__));
        $settings->paramsMap = $this->getParamsMap();
        $settings->core = $this->loadTool();
        $settings->profiles = $this->getBlocks();
        //$settings->pathToJS = dirname(__FILE__);
        $settings->action = htmlentities($_SERVER['REQUEST_URI']);
        $settings->resourcesURL = _MODULE_DIR_.'magicscroll/admin/';
        $settings->namePrefix = 'magicscroll';


        $settings->languagesData = Db::getInstance()->ExecuteS('SELECT id_lang as id, iso_code as code, active FROM `'._DB_PREFIX_.'lang` ORDER BY `id_lang` ASC');

        $activeTab = Tools::getValue('magicscroll-active-tab', false);
        if($activeTab) $settings->activeTab = $activeTab;

        $settings->imageBaseUrl = _PS_IMG_.'magicscroll/';

        $result = Db::getInstance()->ExecuteS('SELECT * FROM `' . _DB_PREFIX_ . 'magicscroll_images` ORDER BY `order`');
        if($result) $settings->customSlideshowImagesData = $result;
        foreach($settings->customSlideshowImagesData as &$imageData) {
            $imageData['name'] = $imageData['name'].'-home'.$this->imageTypeSuffix.'.'.$imageData['ext'];
            $imageData['exclude'] = 1 - (int)$imageData['enabled'];
        }



        $html = $settings->getHTML();
        return $html;
    }

    public function loadTool($profile = false, $force = false) {
        if(!isset($GLOBALS['magictoolbox']['magicscroll']['class']) || $force) {
            require_once(dirname(__FILE__).'/magicscroll.module.core.class.php');
            $GLOBALS['magictoolbox']['magicscroll']['class'] = new MagicScrollModuleCoreClass();
            $tool = &$GLOBALS['magictoolbox']['magicscroll']['class'];
            // load current params
            $sql = 'SELECT `name`, `value`, `block` FROM `'._DB_PREFIX_.'magicscroll_settings` WHERE `enabled`=1';
            $result = Db::getInstance()->ExecuteS($sql);
            foreach($result as $row) {
                $tool->params->setValue($row['name'], $row['value'], $row['block']);
            }
            // load translates
            $GLOBALS['magictoolbox']['magicscroll']['translates'] = $this->getMessages();
            $translates = & $GLOBALS['magictoolbox']['magicscroll']['translates'];
            foreach($this->getBlocks() as $block => $label) {
                // prepare image types
                foreach(array('large', 'selector', 'thumb') as $name) {
                    if($tool->params->checkValue($name.'-image', 'original', $block)) {
                        $tool->params->setValue($name.'-image', false, $block);
                    }
                }
            }

            if($tool->type == 'standard' && $tool->params->checkValue('magicscroll', 'yes', 'product')) {
                require_once(dirname(__FILE__).'/magicscroll.module.core.class.php');
                $GLOBALS['magictoolbox']['magicscroll']['magicscroll'] = new MagicScrollModuleCoreClass();
                $scroll = &$GLOBALS['magictoolbox']['magicscroll']['magicscroll'];
                $scroll->params->setScope('MagicScroll');
                $scroll->params->appendParams($tool->params->getParams('product'));//!!!!!!!!!!!!!
                $scroll->params->setValue('direction', $scroll->params->checkValue('template', array('left', 'right')) ? 'bottom' : 'right');
            }

        }

        $tool = &$GLOBALS['magictoolbox']['magicscroll']['class'];

        if($profile) {
            $tool->params->setProfile($profile);
        }

        return $tool;

    }

    public function hookHeader($params) {
        global $smarty;

        if(!$this->isPrestaShop15x) {
            ob_start();
        }

        $headers = '';
        $tool = $this->loadTool();
        $tool->params->resetProfile();

        $page = $smarty->{$this->getTemplateVars}('page_name');
        switch($page) {
            case 'product':
            case 'index':
            case 'category':
            case 'manufacturer':
            case 'search':
                break;
            case 'best-sales':
                $page = 'bestsellerspage';
                break;
            case 'new-products':
                $page = 'newproductpage';
                break;
            case 'prices-drop':
                $page = 'specialspage';
                break;
            default:
                $page = '';
        }
        //old check if(preg_match('/\/prices-drop.php$/is', $GLOBALS['_SERVER']['SCRIPT_NAME']))

        if($tool->params->checkValue('include-headers-on-all-pages', 'Yes', 'default') && ($GLOBALS['magictoolbox']['magicscroll']['headers'] = true)
           || $tool->params->profileExists($page) && !$tool->params->checkValue('enable-effect', 'No', $page)
           || $page == 'index' && !$tool->params->checkValue('enable-effect', 'No', 'homeslideshow')
           || $page == 'index' && !$tool->params->checkValue('enable-effect', 'No', 'homefeatured') && parent::isInstalled('homefeatured') && parent::getInstanceByName('homefeatured')->active
           || $page == 'index' && !$tool->params->checkValue('enable-effect', 'No', 'blocknewproducts_home') && parent::isInstalled('blocknewproducts') && parent::getInstanceByName('blocknewproducts')->active
           || $page == 'index' && !$tool->params->checkValue('enable-effect', 'No', 'blockbestsellers_home') && parent::isInstalled('blockbestsellers') && parent::getInstanceByName('blockbestsellers')->active
           || !$tool->params->checkValue('enable-effect', 'No', 'blockviewed') && parent::isInstalled('blockviewed') && parent::getInstanceByName('blockviewed')->active
           || !$tool->params->checkValue('enable-effect', 'No', 'blockspecials') && parent::isInstalled('blockspecials') && parent::getInstanceByName('blockspecials')->active
           || (!$tool->params->checkValue('enable-effect', 'No', 'blocknewproducts') || ($page == 'index' && !$tool->params->checkValue('enable-effect', 'No', 'blocknewproducts_home'))) && parent::isInstalled('blocknewproducts') && parent::getInstanceByName('blocknewproducts')->active
           || (!$tool->params->checkValue('enable-effect', 'No', 'blockbestsellers') || ($page == 'index' && !$tool->params->checkValue('enable-effect', 'No', 'blockbestsellers_home'))) && parent::isInstalled('blockbestsellers') && parent::getInstanceByName('blockbestsellers')->active
          ) {
            // include headers
            $headers = $tool->getHeadersTemplate(_MODULE_DIR_.'magicscroll');
            $headers .= '<script type="text/javascript" src="'._MODULE_DIR_.'magicscroll/common.js"></script>';
            if($page == 'product' && !$tool->params->checkValue('enable-effect', 'No', 'product')) {
                $headers .= '
<script type="text/javascript">
</script>';
                if(!$GLOBALS['magictoolbox']['isProductScriptIncluded']) {
                    $headers .= '<script type="text/javascript" src="'._MODULE_DIR_.'magicscroll/product.js"></script>';
                    $GLOBALS['magictoolbox']['isProductScriptIncluded'] = true;
                }
                //<style type="text/css"></style>';
            }
            if($page == 'index') {
                $headers .= '
<script type="text/javascript">
    var isPrestaShop16x = '.($this->isPrestaShop16x ? 'true' : 'false').';
    $(document).ready(function() {

        //NOTE: fix, because Prestashop adds class only for ul.tab-pane
        $(\'#index .tab-pane\').removeClass(\'active\');
        $(\'#index .tab-pane:first\').addClass(\'active\');

        if(isPrestaShop16x && typeof(MagicScroll) != \'undefined\') {
            var tabsToInit = {};
            $(\'#home-page-tabs li:not(li.active) a\').each(function(index) {
                tabsToInit[this.href.replace(/^.*?#([^#]+)$/, \'$1\')] = index;
            });
            $(\'#home-page-tabs a[data-toggle="tab"]\').on(\'shown.bs.tab\', function (e) {
                var key = e.target.href.replace(/^.*?#([^#]+)$/, \'$1\');
                if(typeof(tabsToInit[key]) != \'undefined\') {
                    var scrollEl = $(\'div#\'+key+\' .MagicScroll[id]\').get(0);
                    if(scrollEl) {
                        MagicScroll.refresh(scrollEl.id);
                    }
                    delete tabsToInit[key];    
                }
            });
        }
    });
</script>
';
            }
            /*
                Commented as discussion in issue #0021547
            */
            /*
            $headers .= '
            <!--[if !(IE 8)]>
            <style type="text/css">
                #center_column, #left_column, #right_column {overflow: hidden !important;}
            </style>
            <![endif]-->
            ';*/

            if($this->isSmarty3) {
                //Smarty v3 template engine
                $smarty->registerFilter('output', array(Module::getInstanceByName('magicscroll'), 'parseTemplateCategory'));
            } else {
                //Smarty v2 template engine
                $smarty->register_outputfilter(array(Module::getInstanceByName('magicscroll'), 'parseTemplateCategory'));
            }
            $GLOBALS['magictoolbox']['filters']['magicscroll'] = 'parseTemplateCategory';

            // presta create new class every time when hook called
            // so we need save our data in the GLOBALS
            $GLOBALS['magictoolbox']['magicscroll']['cookie'] = $params['cookie'];
            $GLOBALS['magictoolbox']['magicscroll']['productsViewed'] = (isset($params['cookie']->viewed) AND !empty($params['cookie']->viewed)) ? explode(',', $params['cookie']->viewed) : array();

            $headers = '<!-- MAGICSCROLL HEADERS START -->'.$headers.'<!-- MAGICSCROLL HEADERS END -->';

        }

        return $headers;

    }

    public function hookProductFooter($params) {
        //we need save this data in the GLOBALS for compatible with some Prestashop module which reset the $product smarty variable
        $GLOBALS['magictoolbox']['magicscroll']['product'] = array('id' => $params['product']->id, 'name' => $params['product']->name, 'link_rewrite' => $params['product']->link_rewrite);
        return '';
    }

    public function hookFooter($params) {

        if(!$this->isPrestaShop15x) {

            $contents = ob_get_contents();
            ob_end_clean();

            $matches = array();
            $lang = isset($params['cart']->id_lang) ? $params['cart']->id_lang : 0;
            if(preg_match_all('/\[magicscroll(?:\sid=(\d+(?:,\d+)*))?\]/', $contents, $matches, PREG_SET_ORDER)) {
                foreach($matches as $match) {
                    $contents = str_replace($match[0], $this->getCustomSlideshow(empty($match[1]) ? '' : $match[1], $lang, false), $contents);
                }
                $GLOBALS['magictoolbox']['magicscroll']['headers'] = true;
            }

            if($GLOBALS['magictoolbox']['magicscroll']['headers'] == false) {
                $contents = preg_replace('/<\!-- MAGICSCROLL HEADERS START -->.*?<\!-- MAGICSCROLL HEADERS END -->/is', '', $contents);
            } else {
                $contents = preg_replace('/<\!-- MAGICSCROLL HEADERS (START|END) -->/is', '', $contents);
            }

            echo $contents;

        }

        return '';

    }

    public function hookDisplayTopColumn($params) {
        $page = $params['smarty']->{$this->getTemplateVars}('page_name');
        return $page == 'index' ? $this->hookHome($params) : '';
    }

    public function hookHome($params) {
        $tool = $this->loadTool();
        $tool->params->setProfile('homeslideshow');
        if($tool->params->checkValue('enable-effect', 'No')) return '';
        $lang = isset($params['cart']->id_lang) ? $params['cart']->id_lang : 0;
        $slideshow = $this->getCustomSlideshow('', $lang, true);
        if(!empty($slideshow)) $GLOBALS['magictoolbox']['magicscroll']['headers'] = true;
        return $slideshow;
    }

    public function getCustomSlideshow($ids = '', $lang = 0, $enabledOnly = false) {
        $slideshow = '';
        $tool = $this->loadTool();
        $tool->params->setProfile('homeslideshow');
        if(empty($ids)) {
            $where = '';
            $order = 'ORDER BY `order`';
        } else {
            $where = '`id` IN ('.$ids.') AND ';
            $order = 'ORDER BY FIELD(`id`,'.$ids.')';
        }
        $where .= $enabledOnly ? '`enabled`=1 AND ' : '';
        $where .= $lang ? '(`lang`=0 OR `lang`='.$lang.') ' : '`lang`=0 ';
        $sql = 'SELECT * FROM `'._DB_PREFIX_.'magicscroll_images` WHERE '.$where.$order;
        $result = Db::getInstance()->ExecuteS($sql);
        if(is_array($result) && count($result)) {
            $imagesData = array();
            $thumbSuffix = $tool->params->getValue('selector-image');
            $thumbSuffix = $thumbSuffix ? '-'.$thumbSuffix : '';
            $imgSuffix = $tool->params->getValue('thumb-image');
            $imgSuffix = $imgSuffix ? '-'.$imgSuffix : '';
            $fullscreenSuffix = $tool->params->getValue('large-image');
            $fullscreenSuffix = $fullscreenSuffix ? '-'.$fullscreenSuffix : '';
            foreach($result as $row) {
                $imagesData[$row['id']]['link'] = $row['link'];
                $imagesData[$row['id']]['title'] = $row['title'];
                $imagesData[$row['id']]['description'] = htmlspecialchars_decode($row['description']);
                $imagesData[$row['id']]['thumb'] = _PS_IMG_.'magicscroll/'.$row['name'].$thumbSuffix.'.'.$row['ext'];
                $imagesData[$row['id']]['img'] = _PS_IMG_.'magicscroll/'.$row['name'].$imgSuffix.'.'.$row['ext'];
                $imagesData[$row['id']]['fullscreen'] = _PS_IMG_.'magicscroll/'.$row['name'].$fullscreenSuffix.'.'.$row['ext'];
            }
            $slideshow = '<div class="MagicToolboxContainer">'.$tool->getMainTemplate($imagesData, array('id' => 'customSlideshow'.md5($where))).'</div>';
        }
        return $slideshow;
    }

    private static $outputMatches = array();

    public function prepareOutput($output, $index = 'DEFAULT') {

        if(!isset(self::$outputMatches[$index])) {
            preg_match_all('/<div [^>]*?class="[^"]*?MagicToolboxContainer[^"]*?".*?<\/div>\s/is', $output, self::$outputMatches[$index]);
            foreach(self::$outputMatches[$index][0] as $key => $match) {
                $output = str_replace($match, 'MAGICSCROLL_MATCH_'.$index.'_'.$key.'_', $output);
            }
        } else {
            foreach(self::$outputMatches[$index][0] as $key => $match) {
                $output = str_replace('MAGICSCROLL_MATCH_'.$index.'_'.$key.'_', $match, $output);
            }
            unset(self::$outputMatches[$index]);
        }
        return $output;

    }


    public function parseTemplateCategory($output, $smarty) {
        if($this->isSmarty3) {
            //Smarty v3 template engine
            //$currentTemplate = substr(basename($smarty->_current_file), 0, -4);
            $currentTemplate = substr(basename($smarty->template_resource), 0, -4);
            if($currentTemplate == 'breadcrumb') {
                $currentTemplate = 'product';
            } elseif($currentTemplate == 'pagination') {
                $currentTemplate = 'category';
            }
        } else {
            //Smarty v2 template engine
            $currentTemplate = $smarty->currentTemplate;
        }

        if($this->isPrestaShop15x && $currentTemplate == 'layout') {

            $matches = array();
            $lang = intval($GLOBALS['magictoolbox']['magicscroll']['cookie']->id_lang);
            if(preg_match_all('/\[magicscroll(?:\sid=(\d+(?:,\d+)*))?\]/', $output, $matches, PREG_SET_ORDER)) {
                foreach($matches as $match) {
                    $output = str_replace($match[0], $this->getCustomSlideshow(empty($match[1]) ? '' : $match[1], $lang, false), $output);
                }
                $GLOBALS['magictoolbox']['magicscroll']['headers'] = true;
            }

            if(version_compare(_PS_VERSION_, '1.5.5.0', '>=')) {
                //NOTE: because we do not know whether the effect is applied to the blocks in the cache
                $GLOBALS['magictoolbox']['magicscroll']['headers'] = true;
            }
            //NOTE: full contents in prestashop 1.5.x
            if($GLOBALS['magictoolbox']['magicscroll']['headers'] == false) {
                $output = preg_replace('/<\!-- MAGICSCROLL HEADERS START -->.*?<\!-- MAGICSCROLL HEADERS END -->/is', '', $output);
            } else {
                $output = preg_replace('/<\!-- MAGICSCROLL HEADERS (START|END) -->/is', '', $output);
            }
            return $output;
        }

        switch($currentTemplate) {
            case 'search':
            case 'manufacturer':
                //$currentTemplate = 'manufacturer';
                break;
            case 'best-sales':
                $currentTemplate = 'bestsellerspage';
                break;
            case 'new-products':
                $currentTemplate = 'newproductpage';
                break;
            case 'prices-drop':
                $currentTemplate = 'specialspage';
                break;
            case 'blockbestsellers-home':
                $currentTemplate = 'blockbestsellers_home';
                break;
            case 'product-list'://for 'Layered navigation block'
                if(strpos($_SERVER['REQUEST_URI'], 'blocklayered-ajax.php') !== false) {
                    $currentTemplate = 'category';
                }
                break;
        }

        $tool = $this->loadTool();
        if(!$tool->params->profileExists($currentTemplate) || $tool->params->checkValue('enable-effect', 'No', $currentTemplate)) {
            return $output;
        }
        $tool->params->setProfile($currentTemplate);

        global $link;
        $cookie = &$GLOBALS['magictoolbox']['magicscroll']['cookie'];
        if(method_exists($link, 'getImageLink')) {
            $_link = &$link;
        } else {
            //for Prestashop ver 1.1
            $_link = &$this;
        }

        $output = self::prepareOutput($output);

        switch($currentTemplate) {
            case 'homefeatured':
                $categoryID = $this->isPrestaShop15x ? Context::getContext()->shop->getCategory() : 1;
                $category = new Category($categoryID);
                $nb = intval(Configuration::get('HOME_FEATURED_NBR'));//Number of product displayed
                $products = $category->getProducts(intval($cookie->id_lang), 1, ($nb ? $nb : 10));
                if(!is_array($products)) break;
                $pCount = count($products);
                if(!$pCount) break;
                $GLOBALS['magictoolbox']['magicscroll']['headers'] = true;
                if($pCount < $tool->params->getValue('items')) {
                    $tool->params->setValue('items', $pCount);
                }
                $productImagesData = array();
                $useLink = $tool->params->checkValue('link-to-product-page', 'Yes');
                foreach($products as $p_key => $product) {
                    $productImagesData[$p_key]['link'] = $useLink?$link->getProductLink($product['id_product'], $product['link_rewrite'], isset($product['category']) ? $product['category'] : null):'';
                    $productImagesData[$p_key]['title'] = $product['name'];
                    $productImagesData[$p_key]['img'] = $_link->getImageLink($product['link_rewrite'], $product['id_image'], $tool->params->getValue('thumb-image'));
                }
                $magicscroll = $tool->getMainTemplate($productImagesData, array("id" => "homefeaturedMagicScroll"));
                if($this->isPrestaShop16x) {
                    $magicscroll = '<div id="homefeatured" class="MagicToolboxContainer homefeatured tab-pane">'.$magicscroll.'</div>';
                }
                $pattern = '<ul[^>]*?>.*?<\/ul>';
                $output = preg_replace('/'.$pattern.'/is', $magicscroll, $output);
                break;
            case 'category':
            case 'manufacturer':
            case 'newproductpage':
            case 'bestsellerspage':
            case 'specialspage':
            case 'search':
                $products = $smarty->{$this->getTemplateVars}('products');
                if(!is_array($products)) break;
                $pCount = count($products);
                if(!$pCount) break;
                $GLOBALS['magictoolbox']['magicscroll']['headers'] = true;
                if($pCount < $tool->params->getValue('items')) {
                    $tool->params->setValue('items', $pCount);
                }
                $magicscroll = array();
                $left_block_pattern = '(?:<div[^>]*?class="left_block"[^>]*>[^<]*'.
                                        '((?:<p[^>]*?class="compare"[^>]*>[^<]*'.
                                        '<input[^>]*>[^<]*'.
                                        '<label[^>]*>[^<]*<\/label>[^<]*'.
                                        '<\/p>[^<]*)?)'.
                                        '<\/div>[^<]*)?';
                foreach($products as $product) {
                    $lrw = $product['link_rewrite'];
                    $pattern = preg_quote($_link->getImageLink($lrw, $product['id_image'], 'home'.$this->imageTypeSuffix), '/');
                    $pattern = str_replace('\-home'.$this->imageTypeSuffix, '\-[^"]*?', $pattern);
                    $pattern = '/<li[^>]*?class="[^"]*?ajax_block_product[^"]*"[^>]*>[^<]*'.
                               $left_block_pattern.
                               '<div[^>]*?class="center_block"[^>]*>((?:[^<]*<span[^>]*>[^<]*<\/span>)?[^<]*'.
                               '<a[^>]*>[^<]*<img[^>]*?src="'.$pattern.'"[^>]*>[^<]*(?:<span[^>]*?class="new"[^>]*>.*?<\/span>[^<]*)?<\/a>.*?)'.
                               '<\/div>[^<]*'.
                               '<div[^>]*?class="right_block"[^>]*>(.*?)<\/div>(?:[^<]*<br[^>]*>)?[^<]*'.
                               '<\/li>/is';
                    $matches = array();
                    if(preg_match($pattern, $output, $matches)) {
                        $left_block = !empty($matches[1]) ? '<div class="left_block">'.$matches[1].'</div>' : '';
                        $magicscroll[] = '<div>'.$left_block.$matches[2].'<div class="bottom_block">'.$matches[3].'</div></div>';
                    }
                }
                if(!empty($magicscroll)) {
                    $tool->params->setValue('item-tag', 'div');
                    $options = $tool->getOptionsTemplate('categoryMagicScroll');
                    $additionalClass = '';
                    if($tool->params->checkValue('scroll-style', 'with-borders')) {
                        $additionalClass = ' msborder';
                    }
                    if($this->isPrestaShop15x) {
                        $additionalClass .= ' prestashop15x';
                    } else {
                        $additionalClass .= ' prestashop14x';
                    }
                    $magicscroll = $options.'<div id="categoryMagicScroll" class="MagicScroll'.$additionalClass.'">'.implode('', $magicscroll).'</div>';
                    $magicscroll = strtr($magicscroll, array('\\' => '\\\\', '$' => '\$'));
                    $output = preg_replace('/<ul[^>]*?id="product_list"[^>]*>.*?<\/ul>/is', $magicscroll, $output);
                    $tool->params->setValue('item-tag', 'a');
                }
                break;
            case 'product':
                if(!isset($GLOBALS['magictoolbox']['magicscroll']['product'])) {
                    //for skip loyalty module product.tpl
                    break;
                }

                $images = $smarty->{$this->getTemplateVars}('images');
                $pCount = count($images);
                if(!$pCount) break;
                if($pCount < $tool->params->getValue('items')) {
                    $tool->params->setValue('items', $pCount);
                }

                //$product = $smarty->tpl_vars['product'];
                //get some data from $GLOBALS for compatible with Prestashop modules which reset the $product smarty variable
                $product = &$GLOBALS['magictoolbox']['magicscroll']['product'];

                $cover = $smarty->{$this->getTemplateVars}('cover');
                if(!isset($cover['id_image'])) {
                    break;
                }
                $coverImageIds = is_numeric($cover['id_image']) ? $product['id'].'-'.$cover['id_image'] : $cover['id_image'];


                $productImagesData = array();
                $ids = array();
                foreach($images as $image) {
                    $id_image = intval($image['id_image']);
                    $ids[] = $id_image;
                    //if($image['cover']) $coverID = $id_image;
                    $productImagesData[$id_image]['title'] = /*$product['name']*/$image['legend'];
                    $productImagesData[$id_image]['img'] = $_link->getImageLink($product['link_rewrite'], intval($product['id']).'-'.$id_image, $tool->params->getValue('thumb-image'));
                }

                $GLOBALS['magictoolbox']['magicscroll']['headers'] = true;

                $magicscroll = $tool->getMainTemplate($productImagesData, array("id" => "productMagicScroll"));

                $magicscroll .= '<script type="text/javascript">magictoolboxImagesOrder = ['.implode(',', $ids).'];</script>';

                //need img#bigpic for blockcart module
                $magicscroll = '<div style="width:0px;height:0px;overflow:hidden;visibility:hidden;"><img id="bigpic" src="'.$productImagesData[$ids[0]]['img'].'" /></div>'.$magicscroll;

                /*
                $imagePatternTemplate = '<img [^>]*?src="[^"]*?__SRC__"[^>]*>';
                $patternTemplate = '<a [^>]*>[^<]*'.$imagePatternTemplate.'[^<]*<\/a>|'.$imagePatternTemplate;
                $patternTemplate = '<span [^>]*?id="view_full_size"[^>]*>[^<]*'.
                                   '(?:<span [^>]*?class="[^"]*"[^>]*>[^<]*<\/span>[^<]*)*'.
                                   '(?:'.$patternTemplate.')[^<]*'.
                                   '(?:<span [^>]*?class="[^"]*?span_link[^"]*"[^>]*>.*?<\/span>[^<]*)*'.
                                   '<\/span>|'.$patternTemplate;
                //NOTE: added support custom theme #53897
                $patternTemplate = $patternTemplate.'|'.
                    '<div [^>]*?id="wrap"[^>]*>[^<]*'.
                    '<a [^>]*>[^<]*'.
                    '<span [^>]*?id="view_full_size"[^>]*>[^<]*'.
                    $imagePatternTemplate.'[^<]*'.
                    '<\/span>[^<]*'.
                    '<\/a>[^<]*'.
                    '<\/div>[^<]*'.
                    '<div [^>]*?class="[^"]*?zoom-b[^"]*"[^>]*>[^<]*'.
                    '<a [^>]*>[^<]*<\/a>[^<]*'.
                    '<\/div>';
                //NOTE: added support custom theme #54204
                $patternTemplate = $patternTemplate.'|'.
                    '<span [^>]*?id="view_full_size"[^>]*>[^<]*'.
                    '<a [^>]*>[^<]*'.
                    '<img [^>]*>[^<]*'.
                    $imagePatternTemplate.'[^<]*'.
                    '<span [^>]*?class="[^"]*?mask[^"]*"[^>]*>.*?<\/span>[^<]*'.
                    '<\/a>[^<]*'.
                    '<\/span>[^<]*';

                $patternTemplate = '(?:'.$patternTemplate.')';

                //$patternTemplate = '(<div[^>]*?id="image-block"[^>]*>[^<]*)'.$patternTemplate;//NOTE: we need this to determine the main image
                //NOTE: added support custom theme #53897
                $patternTemplate = '(<div [^>]*?(?:id="image-block"|class="[^"]*?image[^"]*")[^>]*>[^<]*)'.$patternTemplate;

                $srcPattern = preg_quote($_link->getImageLink($product['link_rewrite'], $coverImageIds, 'large'.$this->imageTypeSuffix), '/');
                $pattern = str_replace('__SRC__', $srcPattern, $patternTemplate);

                $replaced = 0;
                $output = preg_replace('/'.$pattern.'/is', '$1'.$magicscroll, $output, -1, $replaced);
                if(!$replaced) {
                    $iTypes = $this->getImagesTypes();
                    foreach($iTypes as $iType) {
                        if($iType != 'large'.$this->imageTypeSuffix) {

                            $srcPattern = preg_quote($_link->getImageLink($product['link_rewrite'], $coverImageIds, $iType), '/');
                            $noImageSrcPattern = preg_quote($img_prod_dir.$lang_iso.'-default-'.$iType.'.jpg', '/');
                            $pattern = str_replace('__SRC__', $srcPattern, $patternTemplate);
                            $output = preg_replace('/'.$pattern.'/is', '$1'.$magicscroll, $output, -1, $replaced);
                            if($replaced) break;
                        }
                    }
                }
                */

                //NOTE: common pattern to match div#image-block tag
                $pattern =  '(<div\b[^>]*?(?:\bid\s*+=\s*+"image-block"|\bclass\s*+=\s*+"[^"]*?\bimage\b[^"]*+")[^>]*+>)'.
                            '('.
                            '(?:'.
                                '[^<]++'.
                                '|'.
                                '<(?!/?div\b|!--)'.
                                '|'.
                                '<!--.*?-->'.
                                '|'.
                                '<div\b[^>]*+>'.
                                    '(?2)'.
                                '</div\s*+>'.
                            ')*+'.
                            ')'.
                            '</div\s*+>';
                //$replaced = 0;
                //preg_match_all('%'.$pattern.'%is', $output, $__matches, PREG_SET_ORDER);
                //NOTE: limit = 1 because pattern can be matched with other products, located below the main product
                $output = preg_replace('%'.$pattern.'%is', '$1'.$magicscroll.'</div>', $output, 1/*, $replaced*/);

                //remove selectors
                //$output = preg_replace('/<div [^>]*?id="thumbs_list"[^>]*>.*?<\/div>/is', '', $output);
                //NOTE: added support custom theme #53897
                $output = preg_replace('/<div [^>]*?(?:id="thumbs_list"|class="[^"]*?image-additional[^"]*")[^>]*>.*?<\/div>/is', '', $output);

                //NOTE: div#views_block is parent for div#thumbs_list
                $output = preg_replace('/<div [^>]*?id="views_block"[^>]*>.*?<\/div>/is', '', $output);

                //#resetImages link
                $output = preg_replace('/<\!-- thumbnails -->[^<]*<p[^>]*><a[^>]+reset[^>]+>.*?<\/a><\/p>/is', '<!-- thumbnails -->', $output);
                //remove "View full size" link
                $output = preg_replace('/<li>[^<]*<span[^>]*?id="view_full_size"[^>]*?>[^<]*<\/span>[^<]*<\/li>/is', '', $output);
                //remove "Display all pictures" link
                $output = preg_replace('/<p[^>]*>[^<]*<span[^>]*?id="wrapResetImages"[^>]*>.*?<\/span>[^<]*<\/p>/is', '', $output);
                break;
            case 'blockspecials':
                if(version_compare(_PS_VERSION_, '1.4', '<')) {
                    $products = $this->getAllSpecial(intval($cookie->id_lang));
                } else {
                    $products = Product::getPricesDrop((int)($cookie->id_lang), 0, 10, false, 'position', 'asc');
                }
                if(!is_array($products)) break;
                $pCount = count($products);
                if(!$pCount) break;
                $GLOBALS['magictoolbox']['magicscroll']['headers'] = true;
                if($pCount < $tool->params->getValue('items')) {
                    $tool->params->setValue('items', $pCount);
                }
                $productImagesData = array();
                $useLink = $tool->params->checkValue('link-to-product-page', 'Yes');

                foreach($products as $p_key => $product) {
                    if($useLink && (!Tools::getValue('id_product', false) || (Tools::getValue('id_product', false) != $product['id_product']))) {
                        $productImagesData[$p_key]['link'] = $link->getProductLink($product['id_product'], $product['link_rewrite'], isset($product['category']) ? $product['category'] : null);
                    } else {
                        $productImagesData[$p_key]['link'] = '';
                    }
                    $productImagesData[$p_key]['title'] = $product['name'];
                    $productImagesData[$p_key]['img'] = $_link->getImageLink($product['link_rewrite'], $product['id_image'], $tool->params->getValue('thumb-image'));
                }

                $magicscroll = $tool->getMainTemplate($productImagesData, array("id" => "blockspecialsMagicScroll"));
                $pattern = '<ul[^>]*?>.*?<\/ul>';
                $output = preg_replace('/'.$pattern.'/is', $magicscroll, $output);
                break;
            case 'blockviewed':
                $productsViewed = $GLOBALS['magictoolbox']['magicscroll']['productsViewed'];
                $pCount = count($productsViewed);
                if(!$pCount) break;
                $GLOBALS['magictoolbox']['magicscroll']['headers'] = true;
                if($pCount < $tool->params->getValue('items')) {
                    $tool->params->setValue('items', $pCount);
                }
                $productImagesData = array();
                $useLink = $tool->params->checkValue('link-to-product-page', 'Yes');

                foreach($productsViewed as $id_product) {
                    $productViewedObj = new Product(intval($id_product), false, intval($cookie->id_lang));
                    if (!Validate::isLoadedObject($productViewedObj) OR !$productViewedObj->active)
                        continue;
                    else {
                        $images = $productViewedObj->getImages(intval($cookie->id_lang));
                        foreach($images as $image) {
                            if($image['cover']) {
                                $productViewedObj->cover = $productViewedObj->id.'-'.$image['id_image'];
                                $productViewedObj->legend = $image['legend'];
                                break;
                            }
                        }
                        if(!isset($productViewedObj->cover)) {
                            $productViewedObj->cover = Language::getIsoById($cookie->id_lang).'-default';
                            $productViewedObj->legend = '';
                        }
                        $lrw = $productViewedObj->link_rewrite;
                        if($useLink && (!Tools::getValue('id_product', false) || (Tools::getValue('id_product', false) != $id_product))) {
                            $productImagesData[$id_product]['link'] = $link->getProductLink($id_product, $lrw, $productViewedObj->category);
                        } else {
                            $productImagesData[$id_product]['link'] = '';
                        }
                        $productImagesData[$id_product]['title'] = $productViewedObj->name;
                        $productImagesData[$id_product]['img'] = $_link->getImageLink($lrw, $productViewedObj->cover, $tool->params->getValue('thumb-image'));
                    }
                }
                $magicscroll = $tool->getMainTemplate($productImagesData, array("id" => "blockviewedMagicScroll"));
                $pattern = '<ul[^>]*?>.*?<\/ul>';
                $output = preg_replace('/'.$pattern.'/is', $magicscroll, $output);
                break;
            case 'blockbestsellers':
            case 'blockbestsellers_home':
            case 'blocknewproducts':
            case 'blocknewproducts_home':
                if(in_array($currentTemplate, array('blockbestsellers', 'blockbestsellers_home'))) {
                    $nb_products = $tool->params->getValue('max-number-of-products', $currentTemplate);
                    //$products = $smarty->{$this->getTemplateVars}('best_sellers');
                    //to get with description etc.
                    $products = ProductSale::getBestSales(intval($cookie->id_lang), 0, $nb_products);
                } else {
                    $products = $smarty->{$this->getTemplateVars}('new_products');
                }
                if(!is_array($products)) break;
                $pCount = count($products);
                if(!$pCount || !$products) break;
                $GLOBALS['magictoolbox']['magicscroll']['headers'] = true;
                if($pCount < $tool->params->getValue('items')) {
                    $tool->params->setValue('items', $pCount);
                }
                $productImagesData = array();
                $useLink = $tool->params->checkValue('link-to-product-page', 'Yes');
                foreach($products as $p_key => $product) {
                    if($useLink && (!Tools::getValue('id_product', false) || (Tools::getValue('id_product', false) != $product['id_product']))) {
                        $productImagesData[$p_key]['link'] = $link->getProductLink($product['id_product'], $product['link_rewrite'], isset($product['category']) ? $product['category'] : null);
                    } else {
                        $productImagesData[$p_key]['link'] = '';
                    }
                    $productImagesData[$p_key]['title'] = $product['name'];
                    $productImagesData[$p_key]['img'] = $_link->getImageLink($product['link_rewrite'], $product['id_image'], $tool->params->getValue('thumb-image'));
                }
                $magicscroll = $tool->getMainTemplate($productImagesData, array("id" => $currentTemplate."MagicScroll"));
                if($this->isPrestaShop16x) {
                    if($currentTemplate == 'blockbestsellers_home') {
                        $magicscroll = '<div id="blockbestsellers" class="MagicToolboxContainer blockbestsellers tab-pane">'.$magicscroll.'</div>';
                    } else if($currentTemplate == 'blocknewproducts_home') {
                        $magicscroll = '<div id="blocknewproducts" class="MagicToolboxContainer blocknewproducts tab-pane active">'.$magicscroll.'</div>';
                    }
                }
                $pattern = '<ul[^>]*?>.*?<\/ul>';
                $output = preg_replace('/'.$pattern.'/is', $magicscroll, $output);
                break;
        }

        return self::prepareOutput($output);

    }

    public function getAllSpecial($id_lang, $beginning = false, $ending = false) {

        $currentDate = date('Y-m-d');
        $result = Db::getInstance()->ExecuteS('
        SELECT p.*, pl.`description`, pl.`description_short`, pl.`link_rewrite`, pl.`meta_description`, pl.`meta_keywords`, pl.`meta_title`, pl.`name`, p.`ean13`,
            i.`id_image`, il.`legend`, t.`rate`
        FROM `'._DB_PREFIX_.'product` p
        LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = '.intval($id_lang).')
        LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = p.`id_product` AND i.`cover` = 1)
        LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.intval($id_lang).')
        LEFT JOIN `'._DB_PREFIX_.'tax` t ON t.`id_tax` = p.`id_tax`
        WHERE (`reduction_price` > 0 OR `reduction_percent` > 0)
        '.((!$beginning AND !$ending) ?
            'AND (`reduction_from` = `reduction_to` OR (`reduction_from` <= \''.$currentDate.'\' AND `reduction_to` >= \''.$currentDate.'\'))'
        :
            ($beginning ? 'AND `reduction_from` <= \''.$beginning.'\'' : '').($ending ? 'AND `reduction_to` >= \''.$ending.'\'' : '')).'
        AND p.`active` = 1
        ORDER BY RAND()');

        if (!$result)
            return false;

        foreach ($result as $row)
            $rows[] = Product::getProductProperties($id_lang, $row);

        return $rows;
    }

    //for Prestashop ver 1.1
    public function getImageLink($name, $ids, $type = null) {
        return _THEME_PROD_DIR_.$ids.($type ? '-'.$type : '').'.jpg';
    }


    public function getProductDescription($id_product, $id_lang) {
        $sql = 'SELECT `description` FROM `'._DB_PREFIX_.'product_lang` WHERE `id_product` = '.(int)($id_product).' AND `id_lang` = '.(int)($id_lang);
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql);
        return isset($result[0]['description'])? $result[0]['description'] : '';
    }

    function fillDB() {
		$sql = 'INSERT INTO `'._DB_PREFIX_.'magicscroll_settings` (`block`, `name`, `value`, `default_value`, `enabled`, `default_enabled`) VALUES
				(\'default\', \'thumb-image\', \'large\', \'large\', 1, 1),
				(\'default\', \'link-to-product-page\', \'Yes\', \'Yes\', 1, 1),
				(\'default\', \'include-headers-on-all-pages\', \'No\', \'No\', 1, 1),
				(\'default\', \'scroll-style\', \'default\', \'default\', 1, 1),
				(\'default\', \'show-image-title\', \'Yes\', \'Yes\', 1, 1),
				(\'default\', \'loop\', \'continue\', \'continue\', 1, 1),
				(\'default\', \'speed\', \'5000\', \'5000\', 1, 1),
				(\'default\', \'width\', \'0\', \'0\', 1, 1),
				(\'default\', \'height\', \'0\', \'0\', 1, 1),
				(\'default\', \'item-width\', \'0\', \'0\', 1, 1),
				(\'default\', \'item-height\', \'0\', \'0\', 1, 1),
				(\'default\', \'step\', \'3\', \'3\', 1, 1),
				(\'default\', \'items\', \'3\', \'3\', 1, 1),
				(\'default\', \'arrows\', \'outside\', \'outside\', 1, 1),
				(\'default\', \'arrows-opacity\', \'60\', \'60\', 1, 1),
				(\'default\', \'arrows-hover-opacity\', \'100\', \'100\', 1, 1),
				(\'default\', \'slider-size\', \'10%\', \'10%\', 1, 1),
				(\'default\', \'slider\', \'false\', \'false\', 1, 1),
				(\'default\', \'direction\', \'right\', \'right\', 1, 1),
				(\'default\', \'duration\', \'1000\', \'1000\', 1, 1),
				(\'product\', \'thumb-image\', \'large\', \'large\', 0, 0),
				(\'product\', \'enable-effect\', \'No\', \'No\', 1, 1),
				(\'product\', \'scroll-style\', \'default\', \'default\', 0, 0),
				(\'product\', \'show-image-title\', \'Yes\', \'Yes\', 0, 0),
				(\'product\', \'loop\', \'continue\', \'continue\', 0, 0),
				(\'product\', \'speed\', \'5000\', \'5000\', 0, 0),
				(\'product\', \'width\', \'0\', \'0\', 0, 0),
				(\'product\', \'height\', \'0\', \'0\', 0, 0),
				(\'product\', \'item-width\', \'0\', \'0\', 0, 0),
				(\'product\', \'item-height\', \'0\', \'0\', 0, 0),
				(\'product\', \'step\', \'1\', \'1\', 1, 1),
				(\'product\', \'items\', \'1\', \'1\', 1, 1),
				(\'product\', \'arrows\', \'inside\', \'inside\', 1, 1),
				(\'product\', \'arrows-opacity\', \'60\', \'60\', 0, 0),
				(\'product\', \'arrows-hover-opacity\', \'100\', \'100\', 0, 0),
				(\'product\', \'slider-size\', \'10%\', \'10%\', 0, 0),
				(\'product\', \'slider\', \'false\', \'false\', 0, 0),
				(\'product\', \'direction\', \'right\', \'right\', 0, 0),
				(\'product\', \'duration\', \'1000\', \'1000\', 0, 0),
				(\'category\', \'thumb-image\', \'large\', \'large\', 0, 0),
				(\'category\', \'enable-effect\', \'No\', \'No\', 1, 1),
				(\'category\', \'link-to-product-page\', \'Yes\', \'Yes\', 0, 0),
				(\'category\', \'scroll-style\', \'default\', \'default\', 0, 0),
				(\'category\', \'show-image-title\', \'Yes\', \'Yes\', 0, 0),
				(\'category\', \'loop\', \'continue\', \'continue\', 0, 0),
				(\'category\', \'speed\', \'5000\', \'5000\', 0, 0),
				(\'category\', \'width\', \'0\', \'0\', 0, 0),
				(\'category\', \'height\', \'0\', \'0\', 0, 0),
				(\'category\', \'item-width\', \'150\', \'150\', 1, 1),
				(\'category\', \'item-height\', \'450\', \'450\', 1, 1),
				(\'category\', \'step\', \'3\', \'3\', 0, 0),
				(\'category\', \'items\', \'3\', \'3\', 0, 0),
				(\'category\', \'arrows\', \'outside\', \'outside\', 0, 0),
				(\'category\', \'arrows-opacity\', \'60\', \'60\', 0, 0),
				(\'category\', \'arrows-hover-opacity\', \'100\', \'100\', 0, 0),
				(\'category\', \'slider-size\', \'10%\', \'10%\', 0, 0),
				(\'category\', \'slider\', \'false\', \'false\', 0, 0),
				(\'category\', \'direction\', \'right\', \'right\', 0, 0),
				(\'category\', \'duration\', \'1000\', \'1000\', 0, 0),
				(\'manufacturer\', \'thumb-image\', \'large\', \'large\', 0, 0),
				(\'manufacturer\', \'enable-effect\', \'No\', \'No\', 1, 1),
				(\'manufacturer\', \'link-to-product-page\', \'Yes\', \'Yes\', 0, 0),
				(\'manufacturer\', \'scroll-style\', \'default\', \'default\', 0, 0),
				(\'manufacturer\', \'show-image-title\', \'Yes\', \'Yes\', 0, 0),
				(\'manufacturer\', \'loop\', \'continue\', \'continue\', 0, 0),
				(\'manufacturer\', \'speed\', \'5000\', \'5000\', 0, 0),
				(\'manufacturer\', \'width\', \'0\', \'0\', 0, 0),
				(\'manufacturer\', \'height\', \'0\', \'0\', 0, 0),
				(\'manufacturer\', \'item-width\', \'150\', \'150\', 1, 1),
				(\'manufacturer\', \'item-height\', \'450\', \'450\', 1, 1),
				(\'manufacturer\', \'step\', \'3\', \'3\', 0, 0),
				(\'manufacturer\', \'items\', \'3\', \'3\', 0, 0),
				(\'manufacturer\', \'arrows\', \'outside\', \'outside\', 0, 0),
				(\'manufacturer\', \'arrows-opacity\', \'60\', \'60\', 0, 0),
				(\'manufacturer\', \'arrows-hover-opacity\', \'100\', \'100\', 0, 0),
				(\'manufacturer\', \'slider-size\', \'10%\', \'10%\', 0, 0),
				(\'manufacturer\', \'slider\', \'false\', \'false\', 0, 0),
				(\'manufacturer\', \'direction\', \'right\', \'right\', 0, 0),
				(\'manufacturer\', \'duration\', \'1000\', \'1000\', 0, 0),
				(\'newproductpage\', \'thumb-image\', \'large\', \'large\', 0, 0),
				(\'newproductpage\', \'enable-effect\', \'No\', \'No\', 1, 1),
				(\'newproductpage\', \'link-to-product-page\', \'Yes\', \'Yes\', 0, 0),
				(\'newproductpage\', \'scroll-style\', \'default\', \'default\', 0, 0),
				(\'newproductpage\', \'show-image-title\', \'Yes\', \'Yes\', 0, 0),
				(\'newproductpage\', \'loop\', \'continue\', \'continue\', 0, 0),
				(\'newproductpage\', \'speed\', \'5000\', \'5000\', 0, 0),
				(\'newproductpage\', \'width\', \'0\', \'0\', 0, 0),
				(\'newproductpage\', \'height\', \'0\', \'0\', 0, 0),
				(\'newproductpage\', \'item-width\', \'150\', \'150\', 1, 1),
				(\'newproductpage\', \'item-height\', \'450\', \'450\', 1, 1),
				(\'newproductpage\', \'step\', \'3\', \'3\', 0, 0),
				(\'newproductpage\', \'items\', \'3\', \'3\', 0, 0),
				(\'newproductpage\', \'arrows\', \'outside\', \'outside\', 0, 0),
				(\'newproductpage\', \'arrows-opacity\', \'60\', \'60\', 0, 0),
				(\'newproductpage\', \'arrows-hover-opacity\', \'100\', \'100\', 0, 0),
				(\'newproductpage\', \'slider-size\', \'10%\', \'10%\', 0, 0),
				(\'newproductpage\', \'slider\', \'false\', \'false\', 0, 0),
				(\'newproductpage\', \'direction\', \'right\', \'right\', 0, 0),
				(\'newproductpage\', \'duration\', \'1000\', \'1000\', 0, 0),
				(\'blocknewproducts\', \'thumb-image\', \'home\', \'home\', 1, 1),
				(\'blocknewproducts\', \'enable-effect\', \'Yes\', \'Yes\', 1, 1),
				(\'blocknewproducts\', \'link-to-product-page\', \'Yes\', \'Yes\', 0, 0),
				(\'blocknewproducts\', \'scroll-style\', \'default\', \'default\', 0, 0),
				(\'blocknewproducts\', \'show-image-title\', \'Yes\', \'Yes\', 0, 0),
				(\'blocknewproducts\', \'loop\', \'continue\', \'continue\', 0, 0),
				(\'blocknewproducts\', \'speed\', \'5000\', \'5000\', 0, 0),
				(\'blocknewproducts\', \'width\', \'0\', \'0\', 0, 0),
				(\'blocknewproducts\', \'height\', \'0\', \'0\', 0, 0),
				(\'blocknewproducts\', \'item-width\', \'0\', \'0\', 0, 0),
				(\'blocknewproducts\', \'item-height\', \'0\', \'0\', 0, 0),
				(\'blocknewproducts\', \'step\', \'3\', \'3\', 0, 0),
				(\'blocknewproducts\', \'items\', \'3\', \'3\', 0, 0),
				(\'blocknewproducts\', \'arrows\', \'outside\', \'outside\', 0, 0),
				(\'blocknewproducts\', \'arrows-opacity\', \'60\', \'60\', 0, 0),
				(\'blocknewproducts\', \'arrows-hover-opacity\', \'100\', \'100\', 0, 0),
				(\'blocknewproducts\', \'slider-size\', \'10%\', \'10%\', 0, 0),
				(\'blocknewproducts\', \'slider\', \'false\', \'false\', 0, 0),
				(\'blocknewproducts\', \'direction\', \'bottom\', \'bottom\', 1, 1),
				(\'blocknewproducts\', \'duration\', \'1000\', \'1000\', 0, 0),
				(\'blocknewproducts_home\', \'thumb-image\', \'large\', \'large\', 0, 0),
				(\'blocknewproducts_home\', \'enable-effect\', \'Yes\', \'Yes\', 1, 1),
				(\'blocknewproducts_home\', \'link-to-product-page\', \'Yes\', \'Yes\', 0, 0),
				(\'blocknewproducts_home\', \'scroll-style\', \'default\', \'default\', 0, 0),
				(\'blocknewproducts_home\', \'show-image-title\', \'Yes\', \'Yes\', 0, 0),
				(\'blocknewproducts_home\', \'loop\', \'continue\', \'continue\', 0, 0),
				(\'blocknewproducts_home\', \'speed\', \'5000\', \'5000\', 0, 0),
				(\'blocknewproducts_home\', \'width\', \'0\', \'0\', 0, 0),
				(\'blocknewproducts_home\', \'height\', \'0\', \'0\', 0, 0),
				(\'blocknewproducts_home\', \'item-width\', \'0\', \'0\', 0, 0),
				(\'blocknewproducts_home\', \'item-height\', \'0\', \'0\', 0, 0),
				(\'blocknewproducts_home\', \'step\', \'3\', \'3\', 0, 0),
				(\'blocknewproducts_home\', \'items\', \'3\', \'3\', 0, 0),
				(\'blocknewproducts_home\', \'arrows\', \'outside\', \'outside\', 0, 0),
				(\'blocknewproducts_home\', \'arrows-opacity\', \'60\', \'60\', 0, 0),
				(\'blocknewproducts_home\', \'arrows-hover-opacity\', \'100\', \'100\', 0, 0),
				(\'blocknewproducts_home\', \'slider-size\', \'10%\', \'10%\', 0, 0),
				(\'blocknewproducts_home\', \'slider\', \'false\', \'false\', 0, 0),
				(\'blocknewproducts_home\', \'direction\', \'right\', \'right\', 0, 0),
				(\'blocknewproducts_home\', \'duration\', \'1000\', \'1000\', 0, 0),
				(\'bestsellerspage\', \'thumb-image\', \'large\', \'large\', 0, 0),
				(\'bestsellerspage\', \'enable-effect\', \'No\', \'No\', 1, 1),
				(\'bestsellerspage\', \'link-to-product-page\', \'Yes\', \'Yes\', 0, 0),
				(\'bestsellerspage\', \'scroll-style\', \'default\', \'default\', 0, 0),
				(\'bestsellerspage\', \'show-image-title\', \'Yes\', \'Yes\', 0, 0),
				(\'bestsellerspage\', \'loop\', \'continue\', \'continue\', 0, 0),
				(\'bestsellerspage\', \'speed\', \'5000\', \'5000\', 0, 0),
				(\'bestsellerspage\', \'width\', \'0\', \'0\', 0, 0),
				(\'bestsellerspage\', \'height\', \'0\', \'0\', 0, 0),
				(\'bestsellerspage\', \'item-width\', \'150\', \'150\', 1, 1),
				(\'bestsellerspage\', \'item-height\', \'450\', \'450\', 1, 1),
				(\'bestsellerspage\', \'step\', \'3\', \'3\', 0, 0),
				(\'bestsellerspage\', \'items\', \'3\', \'3\', 0, 0),
				(\'bestsellerspage\', \'arrows\', \'outside\', \'outside\', 0, 0),
				(\'bestsellerspage\', \'arrows-opacity\', \'60\', \'60\', 0, 0),
				(\'bestsellerspage\', \'arrows-hover-opacity\', \'100\', \'100\', 0, 0),
				(\'bestsellerspage\', \'slider-size\', \'10%\', \'10%\', 0, 0),
				(\'bestsellerspage\', \'slider\', \'false\', \'false\', 0, 0),
				(\'bestsellerspage\', \'direction\', \'right\', \'right\', 0, 0),
				(\'bestsellerspage\', \'duration\', \'1000\', \'1000\', 0, 0),
				(\'blockbestsellers\', \'thumb-image\', \'home\', \'home\', 1, 1),
				(\'blockbestsellers\', \'max-number-of-products\', \'5\', \'5\', 1, 1),
				(\'blockbestsellers\', \'enable-effect\', \'Yes\', \'Yes\', 1, 1),
				(\'blockbestsellers\', \'link-to-product-page\', \'Yes\', \'Yes\', 0, 0),
				(\'blockbestsellers\', \'scroll-style\', \'default\', \'default\', 0, 0),
				(\'blockbestsellers\', \'show-image-title\', \'Yes\', \'Yes\', 0, 0),
				(\'blockbestsellers\', \'loop\', \'continue\', \'continue\', 0, 0),
				(\'blockbestsellers\', \'speed\', \'5000\', \'5000\', 0, 0),
				(\'blockbestsellers\', \'width\', \'0\', \'0\', 0, 0),
				(\'blockbestsellers\', \'height\', \'0\', \'0\', 0, 0),
				(\'blockbestsellers\', \'item-width\', \'0\', \'0\', 0, 0),
				(\'blockbestsellers\', \'item-height\', \'0\', \'0\', 0, 0),
				(\'blockbestsellers\', \'step\', \'3\', \'3\', 0, 0),
				(\'blockbestsellers\', \'items\', \'3\', \'3\', 0, 0),
				(\'blockbestsellers\', \'arrows\', \'outside\', \'outside\', 0, 0),
				(\'blockbestsellers\', \'arrows-opacity\', \'60\', \'60\', 0, 0),
				(\'blockbestsellers\', \'arrows-hover-opacity\', \'100\', \'100\', 0, 0),
				(\'blockbestsellers\', \'slider-size\', \'10%\', \'10%\', 0, 0),
				(\'blockbestsellers\', \'slider\', \'false\', \'false\', 0, 0),
				(\'blockbestsellers\', \'direction\', \'bottom\', \'bottom\', 1, 1),
				(\'blockbestsellers\', \'duration\', \'1000\', \'1000\', 0, 0),
				(\'blockbestsellers_home\', \'thumb-image\', \'large\', \'large\', 0, 0),
				(\'blockbestsellers_home\', \'max-number-of-products\', \'8\', \'8\', 1, 1),
				(\'blockbestsellers_home\', \'enable-effect\', \'Yes\', \'Yes\', 1, 1),
				(\'blockbestsellers_home\', \'link-to-product-page\', \'Yes\', \'Yes\', 0, 0),
				(\'blockbestsellers_home\', \'scroll-style\', \'default\', \'default\', 0, 0),
				(\'blockbestsellers_home\', \'show-image-title\', \'Yes\', \'Yes\', 0, 0),
				(\'blockbestsellers_home\', \'loop\', \'continue\', \'continue\', 0, 0),
				(\'blockbestsellers_home\', \'speed\', \'5000\', \'5000\', 0, 0),
				(\'blockbestsellers_home\', \'width\', \'0\', \'0\', 0, 0),
				(\'blockbestsellers_home\', \'height\', \'0\', \'0\', 0, 0),
				(\'blockbestsellers_home\', \'item-width\', \'0\', \'0\', 0, 0),
				(\'blockbestsellers_home\', \'item-height\', \'0\', \'0\', 0, 0),
				(\'blockbestsellers_home\', \'step\', \'3\', \'3\', 0, 0),
				(\'blockbestsellers_home\', \'items\', \'3\', \'3\', 0, 0),
				(\'blockbestsellers_home\', \'arrows\', \'outside\', \'outside\', 0, 0),
				(\'blockbestsellers_home\', \'arrows-opacity\', \'60\', \'60\', 0, 0),
				(\'blockbestsellers_home\', \'arrows-hover-opacity\', \'100\', \'100\', 0, 0),
				(\'blockbestsellers_home\', \'slider-size\', \'10%\', \'10%\', 0, 0),
				(\'blockbestsellers_home\', \'slider\', \'false\', \'false\', 0, 0),
				(\'blockbestsellers_home\', \'direction\', \'right\', \'right\', 0, 0),
				(\'blockbestsellers_home\', \'duration\', \'1000\', \'1000\', 0, 0),
				(\'specialspage\', \'thumb-image\', \'large\', \'large\', 0, 0),
				(\'specialspage\', \'enable-effect\', \'No\', \'No\', 1, 1),
				(\'specialspage\', \'link-to-product-page\', \'Yes\', \'Yes\', 0, 0),
				(\'specialspage\', \'scroll-style\', \'default\', \'default\', 0, 0),
				(\'specialspage\', \'show-image-title\', \'Yes\', \'Yes\', 0, 0),
				(\'specialspage\', \'loop\', \'continue\', \'continue\', 0, 0),
				(\'specialspage\', \'speed\', \'5000\', \'5000\', 0, 0),
				(\'specialspage\', \'width\', \'0\', \'0\', 0, 0),
				(\'specialspage\', \'height\', \'0\', \'0\', 0, 0),
				(\'specialspage\', \'item-width\', \'150\', \'150\', 1, 1),
				(\'specialspage\', \'item-height\', \'450\', \'450\', 1, 1),
				(\'specialspage\', \'step\', \'3\', \'3\', 0, 0),
				(\'specialspage\', \'items\', \'3\', \'3\', 0, 0),
				(\'specialspage\', \'arrows\', \'outside\', \'outside\', 0, 0),
				(\'specialspage\', \'arrows-opacity\', \'60\', \'60\', 0, 0),
				(\'specialspage\', \'arrows-hover-opacity\', \'100\', \'100\', 0, 0),
				(\'specialspage\', \'slider-size\', \'10%\', \'10%\', 0, 0),
				(\'specialspage\', \'slider\', \'false\', \'false\', 0, 0),
				(\'specialspage\', \'direction\', \'right\', \'right\', 0, 0),
				(\'specialspage\', \'duration\', \'1000\', \'1000\', 0, 0),
				(\'blockspecials\', \'thumb-image\', \'home\', \'home\', 1, 1),
				(\'blockspecials\', \'enable-effect\', \'Yes\', \'Yes\', 1, 1),
				(\'blockspecials\', \'link-to-product-page\', \'Yes\', \'Yes\', 0, 0),
				(\'blockspecials\', \'scroll-style\', \'default\', \'default\', 0, 0),
				(\'blockspecials\', \'show-image-title\', \'Yes\', \'Yes\', 0, 0),
				(\'blockspecials\', \'loop\', \'continue\', \'continue\', 0, 0),
				(\'blockspecials\', \'speed\', \'5000\', \'5000\', 0, 0),
				(\'blockspecials\', \'width\', \'0\', \'0\', 0, 0),
				(\'blockspecials\', \'height\', \'0\', \'0\', 0, 0),
				(\'blockspecials\', \'item-width\', \'0\', \'0\', 0, 0),
				(\'blockspecials\', \'item-height\', \'0\', \'0\', 0, 0),
				(\'blockspecials\', \'step\', \'3\', \'3\', 0, 0),
				(\'blockspecials\', \'items\', \'3\', \'3\', 0, 0),
				(\'blockspecials\', \'arrows\', \'outside\', \'outside\', 0, 0),
				(\'blockspecials\', \'arrows-opacity\', \'60\', \'60\', 0, 0),
				(\'blockspecials\', \'arrows-hover-opacity\', \'100\', \'100\', 0, 0),
				(\'blockspecials\', \'slider-size\', \'10%\', \'10%\', 0, 0),
				(\'blockspecials\', \'slider\', \'false\', \'false\', 0, 0),
				(\'blockspecials\', \'direction\', \'bottom\', \'bottom\', 1, 1),
				(\'blockspecials\', \'duration\', \'1000\', \'1000\', 0, 0),
				(\'blockviewed\', \'thumb-image\', \'home\', \'home\', 1, 1),
				(\'blockviewed\', \'enable-effect\', \'Yes\', \'Yes\', 1, 1),
				(\'blockviewed\', \'link-to-product-page\', \'Yes\', \'Yes\', 0, 0),
				(\'blockviewed\', \'scroll-style\', \'default\', \'default\', 0, 0),
				(\'blockviewed\', \'show-image-title\', \'Yes\', \'Yes\', 0, 0),
				(\'blockviewed\', \'loop\', \'continue\', \'continue\', 0, 0),
				(\'blockviewed\', \'speed\', \'5000\', \'5000\', 0, 0),
				(\'blockviewed\', \'width\', \'0\', \'0\', 0, 0),
				(\'blockviewed\', \'height\', \'0\', \'0\', 0, 0),
				(\'blockviewed\', \'item-width\', \'0\', \'0\', 0, 0),
				(\'blockviewed\', \'item-height\', \'0\', \'0\', 0, 0),
				(\'blockviewed\', \'step\', \'3\', \'3\', 0, 0),
				(\'blockviewed\', \'items\', \'3\', \'3\', 0, 0),
				(\'blockviewed\', \'arrows\', \'outside\', \'outside\', 0, 0),
				(\'blockviewed\', \'arrows-opacity\', \'60\', \'60\', 0, 0),
				(\'blockviewed\', \'arrows-hover-opacity\', \'100\', \'100\', 0, 0),
				(\'blockviewed\', \'slider-size\', \'10%\', \'10%\', 0, 0),
				(\'blockviewed\', \'slider\', \'false\', \'false\', 0, 0),
				(\'blockviewed\', \'direction\', \'bottom\', \'bottom\', 1, 1),
				(\'blockviewed\', \'duration\', \'1000\', \'1000\', 0, 0),
				(\'homefeatured\', \'thumb-image\', \'home\', \'home\', 1, 1),
				(\'homefeatured\', \'enable-effect\', \'Yes\', \'Yes\', 1, 1),
				(\'homefeatured\', \'link-to-product-page\', \'Yes\', \'Yes\', 0, 0),
				(\'homefeatured\', \'scroll-style\', \'default\', \'default\', 0, 0),
				(\'homefeatured\', \'show-image-title\', \'Yes\', \'Yes\', 0, 0),
				(\'homefeatured\', \'loop\', \'continue\', \'continue\', 0, 0),
				(\'homefeatured\', \'speed\', \'5000\', \'5000\', 0, 0),
				(\'homefeatured\', \'width\', \'535\', \'535\', 1, 1),
				(\'homefeatured\', \'height\', \'0\', \'0\', 0, 0),
				(\'homefeatured\', \'item-width\', \'0\', \'0\', 0, 0),
				(\'homefeatured\', \'item-height\', \'0\', \'0\', 0, 0),
				(\'homefeatured\', \'step\', \'3\', \'3\', 0, 0),
				(\'homefeatured\', \'items\', \'3\', \'3\', 0, 0),
				(\'homefeatured\', \'arrows\', \'outside\', \'outside\', 0, 0),
				(\'homefeatured\', \'arrows-opacity\', \'60\', \'60\', 0, 0),
				(\'homefeatured\', \'arrows-hover-opacity\', \'100\', \'100\', 0, 0),
				(\'homefeatured\', \'slider-size\', \'10%\', \'10%\', 0, 0),
				(\'homefeatured\', \'slider\', \'false\', \'false\', 0, 0),
				(\'homefeatured\', \'direction\', \'right\', \'right\', 0, 0),
				(\'homefeatured\', \'duration\', \'1000\', \'1000\', 0, 0),
				(\'homeslideshow\', \'thumb-image\', \'large\', \'large\', 0, 0),
				(\'homeslideshow\', \'enable-effect\', \'Yes\', \'Yes\', 1, 1),
				(\'homeslideshow\', \'scroll-style\', \'default\', \'default\', 0, 0),
				(\'homeslideshow\', \'show-image-title\', \'Yes\', \'Yes\', 0, 0),
				(\'homeslideshow\', \'loop\', \'continue\', \'continue\', 0, 0),
				(\'homeslideshow\', \'speed\', \'5000\', \'5000\', 0, 0),
				(\'homeslideshow\', \'width\', \'1100\', \'1100\', 1, 1),
				(\'homeslideshow\', \'height\', \'0\', \'0\', 0, 0),
				(\'homeslideshow\', \'item-width\', \'0\', \'0\', 0, 0),
				(\'homeslideshow\', \'item-height\', \'0\', \'0\', 0, 0),
				(\'homeslideshow\', \'step\', \'3\', \'3\', 0, 0),
				(\'homeslideshow\', \'items\', \'3\', \'3\', 0, 0),
				(\'homeslideshow\', \'arrows\', \'outside\', \'outside\', 0, 0),
				(\'homeslideshow\', \'arrows-opacity\', \'60\', \'60\', 0, 0),
				(\'homeslideshow\', \'arrows-hover-opacity\', \'100\', \'100\', 0, 0),
				(\'homeslideshow\', \'slider-size\', \'10%\', \'10%\', 0, 0),
				(\'homeslideshow\', \'slider\', \'false\', \'false\', 0, 0),
				(\'homeslideshow\', \'direction\', \'right\', \'right\', 0, 0),
				(\'homeslideshow\', \'duration\', \'1000\', \'1000\', 0, 0),
				(\'search\', \'thumb-image\', \'large\', \'large\', 0, 0),
				(\'search\', \'enable-effect\', \'No\', \'No\', 1, 1),
				(\'search\', \'link-to-product-page\', \'Yes\', \'Yes\', 0, 0),
				(\'search\', \'scroll-style\', \'default\', \'default\', 0, 0),
				(\'search\', \'show-image-title\', \'Yes\', \'Yes\', 0, 0),
				(\'search\', \'loop\', \'continue\', \'continue\', 0, 0),
				(\'search\', \'speed\', \'5000\', \'5000\', 0, 0),
				(\'search\', \'width\', \'0\', \'0\', 0, 0),
				(\'search\', \'height\', \'0\', \'0\', 0, 0),
				(\'search\', \'item-width\', \'150\', \'150\', 1, 1),
				(\'search\', \'item-height\', \'450\', \'450\', 1, 1),
				(\'search\', \'step\', \'3\', \'3\', 0, 0),
				(\'search\', \'items\', \'3\', \'3\', 0, 0),
				(\'search\', \'arrows\', \'outside\', \'outside\', 0, 0),
				(\'search\', \'arrows-opacity\', \'60\', \'60\', 0, 0),
				(\'search\', \'arrows-hover-opacity\', \'100\', \'100\', 0, 0),
				(\'search\', \'slider-size\', \'10%\', \'10%\', 0, 0),
				(\'search\', \'slider\', \'false\', \'false\', 0, 0),
				(\'search\', \'direction\', \'right\', \'right\', 0, 0),
				(\'search\', \'duration\', \'1000\', \'1000\', 0, 0)';
		if($this->isPrestaShop16x) {
			$sql = preg_replace('/\r\n\s*..(?:category|manufacturer|newproductpage|bestsellerspage|specialspage|search)\b[^\r]*+/i', '', $sql);
			$sql = rtrim($sql, ',');
		}
		if(!$this->isPrestaShop16x) {
			$sql = preg_replace('/\r\n\s*..(?:blockbestsellers_home|blocknewproducts_home)\b[^\r]*+/i', '', $sql);
			$sql = rtrim($sql, ',');
		}
		return Db::getInstance()->Execute($sql);
	}

	function getBlocks() {
		$blocks = array(
			'default' => 'Defaults',
			'product' => 'Product page',
			'category' => 'Category page',
			'manufacturer' => 'Manufacturers page',
			'newproductpage' => 'New products page',
			'blocknewproducts' => 'New products sidebar',
			'blocknewproducts_home' => 'New products block',
			'bestsellerspage' => 'Bestsellers page',
			'blockbestsellers' => 'Bestsellers sidebar',
			'blockbestsellers_home' => 'Bestsellers block',
			'specialspage' => 'Specials page',
			'blockspecials' => 'Specials sidebar',
			'blockviewed' => 'Viewed sidebar',
			'homefeatured' => 'Featured block',
			'homeslideshow' => 'Home page/custom slideshow',
			'search' => 'Search page'
		);
		if($this->isPrestaShop16x) {
			unset($blocks['category'], $blocks['manufacturer'], $blocks['newproductpage'], $blocks['bestsellerspage'], $blocks['specialspage'], $blocks['search']);
		}
		if(!$this->isPrestaShop16x) {
			unset($blocks['blockbestsellers_home'], $blocks['blocknewproducts_home']);
		}
		return $blocks;
	}

	function getMessages() {
		return array(
			'default' => array(
				'message' => array(
					'title' => 'Defaults message (under Magic Scroll)',
					'translate' => $this->l('Defaults message (under Magic Scroll)')
				)
			),
			'product' => array(
				'message' => array(
					'title' => 'Product page message (under Magic Scroll)',
					'translate' => $this->l('Product page message (under Magic Scroll)')
				)
			),
			'category' => array(
				'message' => array(
					'title' => 'Category page message (under Magic Scroll)',
					'translate' => $this->l('Category page message (under Magic Scroll)')
				)
			),
			'manufacturer' => array(
				'message' => array(
					'title' => 'Manufacturers page message (under Magic Scroll)',
					'translate' => $this->l('Manufacturers page message (under Magic Scroll)')
				)
			),
			'newproductpage' => array(
				'message' => array(
					'title' => 'New products page message (under Magic Scroll)',
					'translate' => $this->l('New products page message (under Magic Scroll)')
				)
			),
			'blocknewproducts' => array(
				'message' => array(
					'title' => 'New products sidebar message (under Magic Scroll)',
					'translate' => $this->l('New products sidebar message (under Magic Scroll)')
				)
			),
			'blocknewproducts_home' => array(
				'message' => array(
					'title' => 'New products block message (under Magic Scroll)',
					'translate' => $this->l('New products block message (under Magic Scroll)')
				)
			),
			'bestsellerspage' => array(
				'message' => array(
					'title' => 'Bestsellers page message (under Magic Scroll)',
					'translate' => $this->l('Bestsellers page message (under Magic Scroll)')
				)
			),
			'blockbestsellers' => array(
				'message' => array(
					'title' => 'Bestsellers sidebar message (under Magic Scroll)',
					'translate' => $this->l('Bestsellers sidebar message (under Magic Scroll)')
				)
			),
			'blockbestsellers_home' => array(
				'message' => array(
					'title' => 'Bestsellers block message (under Magic Scroll)',
					'translate' => $this->l('Bestsellers block message (under Magic Scroll)')
				)
			),
			'specialspage' => array(
				'message' => array(
					'title' => 'Specials page message (under Magic Scroll)',
					'translate' => $this->l('Specials page message (under Magic Scroll)')
				)
			),
			'blockspecials' => array(
				'message' => array(
					'title' => 'Specials sidebar message (under Magic Scroll)',
					'translate' => $this->l('Specials sidebar message (under Magic Scroll)')
				)
			),
			'blockviewed' => array(
				'message' => array(
					'title' => 'Viewed sidebar message (under Magic Scroll)',
					'translate' => $this->l('Viewed sidebar message (under Magic Scroll)')
				)
			),
			'homefeatured' => array(
				'message' => array(
					'title' => 'Featured block message (under Magic Scroll)',
					'translate' => $this->l('Featured block message (under Magic Scroll)')
				)
			),
			'homeslideshow' => array(
				'message' => array(
					'title' => 'Home page/custom slideshow message (under Magic Scroll)',
					'translate' => $this->l('Home page/custom slideshow message (under Magic Scroll)')
				)
			),
			'search' => array(
				'message' => array(
					'title' => 'Search page message (under Magic Scroll)',
					'translate' => $this->l('Search page message (under Magic Scroll)')
				)
			)
		);
	}

	function getParamsMap() {
		$map = array(
			'default' => array(
				'Image type' => array(
					'thumb-image' => true
				),
				'Miscellaneous' => array(
					'link-to-product-page' => true,
					'include-headers-on-all-pages' => true
				),
				'Scroll' => array(
					'scroll-style' => true,
					'show-image-title' => true,
					'loop' => true,
					'speed' => true,
					'width' => true,
					'height' => true,
					'item-width' => true,
					'item-height' => true,
					'step' => true,
					'items' => true
				),
				'Scroll Arrows' => array(
					'arrows' => true,
					'arrows-opacity' => true,
					'arrows-hover-opacity' => true
				),
				'Scroll Slider' => array(
					'slider-size' => true,
					'slider' => true
				),
				'Scroll effect' => array(
					'direction' => true,
					'duration' => true
				)
			),
			'product' => array(
				'Enable effect' => array(
					'enable-effect' => true
				),
				'Image type' => array(
					'thumb-image' => false
				),
				'Scroll' => array(
					'scroll-style' => false,
					'show-image-title' => false,
					'loop' => false,
					'speed' => false,
					'width' => false,
					'height' => false,
					'item-width' => false,
					'item-height' => false,
					'step' => false,
					'items' => false
				),
				'Scroll Arrows' => array(
					'arrows' => false,
					'arrows-opacity' => false,
					'arrows-hover-opacity' => false
				),
				'Scroll Slider' => array(
					'slider-size' => false,
					'slider' => false
				),
				'Scroll effect' => array(
					'direction' => false,
					'duration' => false
				)
			),
			'category' => array(
				'Enable effect' => array(
					'enable-effect' => true
				),
				'Miscellaneous' => array(
					'link-to-product-page' => false
				),
				'Scroll' => array(
					'scroll-style' => false,
					'show-image-title' => false,
					'loop' => false,
					'speed' => false,
					'width' => false,
					'height' => false,
					'item-width' => false,
					'item-height' => false,
					'step' => false,
					'items' => false
				),
				'Scroll Arrows' => array(
					'arrows' => false,
					'arrows-opacity' => false,
					'arrows-hover-opacity' => false
				),
				'Scroll Slider' => array(
					'slider-size' => false,
					'slider' => false
				),
				'Scroll effect' => array(
					'direction' => false,
					'duration' => false
				)
			),
			'manufacturer' => array(
				'Enable effect' => array(
					'enable-effect' => true
				),
				'Miscellaneous' => array(
					'link-to-product-page' => false
				),
				'Scroll' => array(
					'scroll-style' => false,
					'show-image-title' => false,
					'loop' => false,
					'speed' => false,
					'width' => false,
					'height' => false,
					'item-width' => false,
					'item-height' => false,
					'step' => false,
					'items' => false
				),
				'Scroll Arrows' => array(
					'arrows' => false,
					'arrows-opacity' => false,
					'arrows-hover-opacity' => false
				),
				'Scroll Slider' => array(
					'slider-size' => false,
					'slider' => false
				),
				'Scroll effect' => array(
					'direction' => false,
					'duration' => false
				)
			),
			'newproductpage' => array(
				'Enable effect' => array(
					'enable-effect' => true
				),
				'Miscellaneous' => array(
					'link-to-product-page' => false
				),
				'Scroll' => array(
					'scroll-style' => false,
					'show-image-title' => false,
					'loop' => false,
					'speed' => false,
					'width' => false,
					'height' => false,
					'item-width' => false,
					'item-height' => false,
					'step' => false,
					'items' => false
				),
				'Scroll Arrows' => array(
					'arrows' => false,
					'arrows-opacity' => false,
					'arrows-hover-opacity' => false
				),
				'Scroll Slider' => array(
					'slider-size' => false,
					'slider' => false
				),
				'Scroll effect' => array(
					'direction' => false,
					'duration' => false
				)
			),
			'blocknewproducts' => array(
				'Enable effect' => array(
					'enable-effect' => true
				),
				'Image type' => array(
					'thumb-image' => false
				),
				'Miscellaneous' => array(
					'link-to-product-page' => false
				),
				'Scroll' => array(
					'scroll-style' => false,
					'show-image-title' => false,
					'loop' => false,
					'speed' => false,
					'width' => false,
					'height' => false,
					'item-width' => false,
					'item-height' => false,
					'step' => false,
					'items' => false
				),
				'Scroll Arrows' => array(
					'arrows' => false,
					'arrows-opacity' => false,
					'arrows-hover-opacity' => false
				),
				'Scroll Slider' => array(
					'slider-size' => false,
					'slider' => false
				),
				'Scroll effect' => array(
					'direction' => false,
					'duration' => false
				)
			),
			'blocknewproducts_home' => array(
				'Enable effect' => array(
					'enable-effect' => true
				),
				'Image type' => array(
					'thumb-image' => false
				),
				'Miscellaneous' => array(
					'link-to-product-page' => false
				),
				'Scroll' => array(
					'scroll-style' => false,
					'show-image-title' => false,
					'loop' => false,
					'speed' => false,
					'width' => false,
					'height' => false,
					'item-width' => false,
					'item-height' => false,
					'step' => false,
					'items' => false
				),
				'Scroll Arrows' => array(
					'arrows' => false,
					'arrows-opacity' => false,
					'arrows-hover-opacity' => false
				),
				'Scroll Slider' => array(
					'slider-size' => false,
					'slider' => false
				),
				'Scroll effect' => array(
					'direction' => false,
					'duration' => false
				)
			),
			'bestsellerspage' => array(
				'Enable effect' => array(
					'enable-effect' => true
				),
				'Miscellaneous' => array(
					'link-to-product-page' => false
				),
				'Scroll' => array(
					'scroll-style' => false,
					'show-image-title' => false,
					'loop' => false,
					'speed' => false,
					'width' => false,
					'height' => false,
					'item-width' => false,
					'item-height' => false,
					'step' => false,
					'items' => false
				),
				'Scroll Arrows' => array(
					'arrows' => false,
					'arrows-opacity' => false,
					'arrows-hover-opacity' => false
				),
				'Scroll Slider' => array(
					'slider-size' => false,
					'slider' => false
				),
				'Scroll effect' => array(
					'direction' => false,
					'duration' => false
				)
			),
			'blockbestsellers' => array(
				'Enable effect' => array(
					'enable-effect' => true
				),
				'Image type' => array(
					'thumb-image' => false
				),
				'Miscellaneous' => array(
					'max-number-of-products' => true,
					'link-to-product-page' => false
				),
				'Scroll' => array(
					'scroll-style' => false,
					'show-image-title' => false,
					'loop' => false,
					'speed' => false,
					'width' => false,
					'height' => false,
					'item-width' => false,
					'item-height' => false,
					'step' => false,
					'items' => false
				),
				'Scroll Arrows' => array(
					'arrows' => false,
					'arrows-opacity' => false,
					'arrows-hover-opacity' => false
				),
				'Scroll Slider' => array(
					'slider-size' => false,
					'slider' => false
				),
				'Scroll effect' => array(
					'direction' => false,
					'duration' => false
				)
			),
			'blockbestsellers_home' => array(
				'Enable effect' => array(
					'enable-effect' => true
				),
				'Image type' => array(
					'thumb-image' => false
				),
				'Miscellaneous' => array(
					'max-number-of-products' => true,
					'link-to-product-page' => false
				),
				'Scroll' => array(
					'scroll-style' => false,
					'show-image-title' => false,
					'loop' => false,
					'speed' => false,
					'width' => false,
					'height' => false,
					'item-width' => false,
					'item-height' => false,
					'step' => false,
					'items' => false
				),
				'Scroll Arrows' => array(
					'arrows' => false,
					'arrows-opacity' => false,
					'arrows-hover-opacity' => false
				),
				'Scroll Slider' => array(
					'slider-size' => false,
					'slider' => false
				),
				'Scroll effect' => array(
					'direction' => false,
					'duration' => false
				)
			),
			'specialspage' => array(
				'Enable effect' => array(
					'enable-effect' => true
				),
				'Miscellaneous' => array(
					'link-to-product-page' => false
				),
				'Scroll' => array(
					'scroll-style' => false,
					'show-image-title' => false,
					'loop' => false,
					'speed' => false,
					'width' => false,
					'height' => false,
					'item-width' => false,
					'item-height' => false,
					'step' => false,
					'items' => false
				),
				'Scroll Arrows' => array(
					'arrows' => false,
					'arrows-opacity' => false,
					'arrows-hover-opacity' => false
				),
				'Scroll Slider' => array(
					'slider-size' => false,
					'slider' => false
				),
				'Scroll effect' => array(
					'direction' => false,
					'duration' => false
				)
			),
			'blockspecials' => array(
				'Enable effect' => array(
					'enable-effect' => true
				),
				'Image type' => array(
					'thumb-image' => false
				),
				'Miscellaneous' => array(
					'link-to-product-page' => false
				),
				'Scroll' => array(
					'scroll-style' => false,
					'show-image-title' => false,
					'loop' => false,
					'speed' => false,
					'width' => false,
					'height' => false,
					'item-width' => false,
					'item-height' => false,
					'step' => false,
					'items' => false
				),
				'Scroll Arrows' => array(
					'arrows' => false,
					'arrows-opacity' => false,
					'arrows-hover-opacity' => false
				),
				'Scroll Slider' => array(
					'slider-size' => false,
					'slider' => false
				),
				'Scroll effect' => array(
					'direction' => false,
					'duration' => false
				)
			),
			'blockviewed' => array(
				'Enable effect' => array(
					'enable-effect' => true
				),
				'Image type' => array(
					'thumb-image' => false
				),
				'Miscellaneous' => array(
					'link-to-product-page' => false
				),
				'Scroll' => array(
					'scroll-style' => false,
					'show-image-title' => false,
					'loop' => false,
					'speed' => false,
					'width' => false,
					'height' => false,
					'item-width' => false,
					'item-height' => false,
					'step' => false,
					'items' => false
				),
				'Scroll Arrows' => array(
					'arrows' => false,
					'arrows-opacity' => false,
					'arrows-hover-opacity' => false
				),
				'Scroll Slider' => array(
					'slider-size' => false,
					'slider' => false
				),
				'Scroll effect' => array(
					'direction' => false,
					'duration' => false
				)
			),
			'homefeatured' => array(
				'Enable effect' => array(
					'enable-effect' => true
				),
				'Image type' => array(
					'thumb-image' => false
				),
				'Miscellaneous' => array(
					'link-to-product-page' => false
				),
				'Scroll' => array(
					'scroll-style' => false,
					'show-image-title' => false,
					'loop' => false,
					'speed' => false,
					'width' => false,
					'height' => false,
					'item-width' => false,
					'item-height' => false,
					'step' => false,
					'items' => false
				),
				'Scroll Arrows' => array(
					'arrows' => false,
					'arrows-opacity' => false,
					'arrows-hover-opacity' => false
				),
				'Scroll Slider' => array(
					'slider-size' => false,
					'slider' => false
				),
				'Scroll effect' => array(
					'direction' => false,
					'duration' => false
				)
			),
			'homeslideshow' => array(
				'Enable effect' => array(
					'enable-effect' => true
				),
				'Slideshow images' => array(
				),
				'Image type' => array(
					'thumb-image' => false
				),
				'Scroll' => array(
					'scroll-style' => false,
					'show-image-title' => false,
					'loop' => false,
					'speed' => false,
					'width' => false,
					'height' => false,
					'item-width' => false,
					'item-height' => false,
					'step' => false,
					'items' => false
				),
				'Scroll Arrows' => array(
					'arrows' => false,
					'arrows-opacity' => false,
					'arrows-hover-opacity' => false
				),
				'Scroll Slider' => array(
					'slider-size' => false,
					'slider' => false
				),
				'Scroll effect' => array(
					'direction' => false,
					'duration' => false
				)
			),
			'search' => array(
				'Enable effect' => array(
					'enable-effect' => true
				),
				'Miscellaneous' => array(
					'link-to-product-page' => false
				),
				'Scroll' => array(
					'scroll-style' => false,
					'show-image-title' => false,
					'loop' => false,
					'speed' => false,
					'width' => false,
					'height' => false,
					'item-width' => false,
					'item-height' => false,
					'step' => false,
					'items' => false
				),
				'Scroll Arrows' => array(
					'arrows' => false,
					'arrows-opacity' => false,
					'arrows-hover-opacity' => false
				),
				'Scroll Slider' => array(
					'slider-size' => false,
					'slider' => false
				),
				'Scroll effect' => array(
					'direction' => false,
					'duration' => false
				)
			)
		);
		if($this->isPrestaShop16x) {
			unset($map['category'], $map['manufacturer'], $map['newproductpage'], $map['bestsellerspage'], $map['specialspage'], $map['search']);
		}
		if(!$this->isPrestaShop16x) {
			unset($map['blockbestsellers_home'], $map['blocknewproducts_home']);
		}
		return $map;
	}

}
