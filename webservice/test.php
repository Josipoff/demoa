<html><head><title>Update Products</title></head><body>
<?php
/*
* 2007-2013 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2013 PrestaShop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
* PrestaShop Webservice Library
* @package PrestaShopWebservice
*/

// Here we define constants /!\ You need to replace this parameters
require_once('../config/config.inc.php');
require_once('../PSWebServiceLibrary.php');
$count = 1;
$product_added=array();
$webservice_exi = new SoapClient('http://www2.promoshop.com.mx/ws_store/service.asmx?WSDL') ;
$list = Configuration::get('PRODUCTUPDATE_LIST');
$parameter =array("list"=>$list,
                "key"=>EXIMAGEN_KEY);
//$result_xml = $webservice_exi->ProductList($parameter);
 
$webService = new PrestaShopWebservice(PS_SHOP_PATH, PS_WS_AUTH_KEY, DEBUG);
// Here we use the WebService to get the schema of "customers" resource
disableDeletedProducts();

function disableDeletedProducts(){
    global $webService, $product_added;   
    $allproducts=  ProductCore::getAllProductsIdAddedByWebservice();
    //var_dump($allproducts);
    try
    {
                $opt = array('resource' => 'products');
                $xml = $webService->get(array('url' => PS_SHOP_PATH.'/api/products/'.'322'));
                
                $resources = $xml->children()->children();
                unset($resources->manufacturer_name);  
                unset($resources->quantity);        
                $resources->active = 0;
		$resources->id=322;
                $opt['putXml'] = $xml->asXML();
                $opt['id'] = 322;
                $xml = $webService->edit($opt);
    }
    catch (PrestaShopWebserviceException $e)
    {
            // Here we are dealing with errors
            $trace = $e->getTrace();
            if ($trace[0]['args'][0] == 404) echo 'Bad ID';
            else if ($trace[0]['args'][0] == 401) echo 'Bad auth key';
            else echo 'Other error<br />'.$e->getMessage();
    }
}

?>
</body></html>