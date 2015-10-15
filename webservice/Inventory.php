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
// Here we use the WebService to get the schema of "customers" resource
Configuration::updateValue('PRODUCTUPDATE_STATUS', '0');
$webService = new PrestaShopWebservice(PS_SHOP_PATH, PS_WS_AUTH_KEY, DEBUG);
        $count=1;
$allproducts=  ProductCore::getAllProductsInfoAddedByWebservice();
foreach($allproducts as $product){
    $parameter = array("ItemNumber"=>$product['reference'],
                    "key"=>EXIMAGEN_KEY);
    $decoration = $webservice_exi->GetDecoration($parameter);
    $decoration = $decoration->GetDecorationResult;
    save_product_combination($product['id_product'], $product['reference'],$list, $decoration);
    $count++;
    Configuration::updateValue('PRODUCTUPDATE_STATUS', $count/4);
}
    Configuration::updateValue('PRODUCTUPDATE_STATUS', 100);

function set_product_quantity($ProductId, $StokId, $AttributeId, $quantity, $intransit, $intransit_avail_date, $color_id){
	global $webService;
	$xml = $webService -> get(array('url' => PS_SHOP_PATH . '/api/stock_availables?schema=blank'));
	$resources = $xml -> children() -> children();
	$resources->id = $StokId;
	$resources->id_product  = $ProductId;
	$resources->quantity = $quantity;
	$resources->id_shop = 1;
	$resources->out_of_stock=2;
	$resources->depends_on_stock = 0;
        //unset($resources->intransit_quantity);
	$resources->intransit = (int)$intransit;
        $resources->intransit_available_date=$intransit_avail_date;
	$resources->color_id = $color_id;
	$resources->id_product_attribute=$AttributeId;
	try {
		$opt = array('resource' => 'stock_availables');
		$opt['putXml'] = $xml->asXML();
		$opt['id'] = $StokId ;
		$xml = $webService->edit($opt);
	}catch (PrestaShopWebserviceException $ex) {
		echo "<b>Error al setear la cantidad  ->Error : </b>".$ex->getMessage().'<br>';
	}
}


function getIdStockAvailableAndSet($ProductId, $quantity, $intransit=0, $intransit_date="0000-00-00", $color="#000000"){
    	global $webService;
	$opt['resource'] = 'products';
	$opt['id'] = $ProductId;
	$xml = $webService->get($opt);
        $attribute_id=0;
	foreach ($xml->product->associations->stock_availables->stock_availables as $item) {
	   //echo "ID: ".$item->id."<br>";
	   //echo "Id Attribute: ".$item->id_product_attribute."<br>";
             $attribute_id = $item->id_product_attribute;
	  } 
          if($intransit_date=='N/A')
              $intransit_date=date("Y-m-d");          
          if($attribute_id!=0)
            set_product_quantity($ProductId, $item->id,$attribute_id, $quantity,$intransit, $intransit_date, $color);
	
}



function save_product_combination($ProductId,$itemNumber,$list, $decoration){
        global $webService,$webservice_exi,$result_xml, $count;
        StockAvailableCore::deleteStockInventoryBYProductID($ProductId);
        
        $opt = array('resource' => 'combinations');
        
        $parameter = array("ItemNumber"=>$itemNumber,
			"key"=>EXIMAGEN_KEY);
        $i=0;
        $inventory_details = $webservice_exi->GetInventory($parameter);
		$color_array = array();
		if(isset($inventory_details->GetInventoryResult->InventoryData->SKU))
            $inventory_details = $inventory_details->GetInventoryResult;
        else
            $inventory_details = $inventory_details->GetInventoryResult->InventoryData;
        foreach ($inventory_details as $inventory){
                $color_id = AttributeCore::getColorAttributeIdByValue($inventory->HexValue);
                         getIdStockAvailableAndSet($ProductId, $inventory->Available,
                   $inventory->OnTransit,$inventory->Date,$color_id);
                
            $intransit_date = $inventory->Date;
            if($intransit_date=='N/A')
                $intransit_date = date("Y-m-d");
                StockAvailableCore::setStockInventory($ProductId, 1, 0,$inventory->Available,$color_id,
                   $inventory->OnTransit,$intransit_date);
        }
}


?>
</body></html>
