<html><head><title>CRUD Tutorial - Create example</title></head><body>
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
$webservice_exi = new SoapClient('http://www2.promoshop.com.mx/ws_store/service.asmx?WSDL') ;
$list = $_GET['l'];
$parameter =array("list"=>$list,
                "key"=>EXIMAGEN_KEY);
$result_xml = $webservice_exi->ProductList($parameter);
 
// Here we use the WebService to get the schema of "customers" resource

foreach ((array) $result_xml->ProductListResult as $x_value)
{
    foreach ($x_value as $product_xml) 
	{
try
{   
  if(!ProductCore::getProductIdByReference($product_xml->ItemNumber)){
	$webService = new PrestaShopWebservice(PS_SHOP_PATH, PS_WS_AUTH_KEY, DEBUG);
	$opt = array('resource' => 'products');
	if (isset($_GET['Create']))
		$xml = $webService->get(array('url' => PS_SHOP_PATH.'/api/products?schema=blank'));
	else
		$xml = $webService->get($opt);
        $resources = $xml->children()->children();
  unset($resources->position_in_category);
  
        
//Update an existing product or Create a new one 
        $resources->reference = $product_xml->ItemNumber;
        $resources->price = floatval($product_xml->BasePrice);
        $resources->wholesale_price = floatval($product_xml->LowestPrice);
        $category = CategoryCore::searchByName(1, $product_xml->Category);
        
        if(!isset($category[0]['id_category'])){
            $category_id = add_new_category($product_xml->Category,34);
        }
        else{
            $category_id = $category[0]['id_category'];
        }
        
        if($product_xml->Category != $product_xml->SubCategory){
             $sub_category = CategoryCore::searchByName(1, $product_xml->SubCategory);
            if(!isset($sub_category[0]['id_category'])){
                $sub_category_id = add_new_category($product_xml->SubCategory,$category_id);
            }
            else{
                $sub_category_id = $sub_category[0]['id_category'];
            }
            $resources->associations->categories->addChild('categories')->addChild('id',intval($sub_category_id));
            $resources->id_category_default = intval($sub_category_id);        
        }
        else{
            $resources->id_category_default = intval($category_id);
        }
        $resources->associations->categories->addChild('categories')->addChild('id',intval($category_id));
            
        
        $resources->item_number = $product_xml->ItemNumber;
        $parameter = array("ItemNumber"=>$product_xml->ItemNumber,
			"key"=>"8770471727");
        $product_details = $webservice_exi->GetDetails($parameter);
        $product_details = $product_details->GetDetailsResult->productinfopromo;
        $resources->active = 1;
        $resources->available_for_order = 1;
        $resources->show_price = 1;
        if($product_xml->Brand!=''){
            $brand_id = ManufacturerCore::getIdByName($product_xml->Brand);
            if(!$brand_id)
               $brand_id = add_new_manufacturer($product_xml->Brand);
            $resources->id_manufacturer = $brand_id;
        }
        
        $parameter = array("SKU"=>$product_xml->SKU,
                        "list" => $list,
			"key"=>"8770471727");
        $quick_quote = $webservice_exi->GetQuote($parameter);
        
        //var_dump($quick_quote);
        if(isset($quick_quote->GetQuoteResult->QuickQuote)){
        
        $quick_quote = $quick_quote->GetQuoteResult->QuickQuote;
        $resources->quick_quote = json_encode($quick_quote);
        }
        
        
        $parameter = array("ItemNumber"=>$product_xml->ItemNumber,
			"key"=>"8770471727");
        $decoration = $webservice_exi->GetDecoration($parameter);
        $decoration = $decoration->GetDecorationResult;
        $resources->decoration_details = json_encode($decoration);
        $resources->uploadable_files=1;
        $resources->customizable=1;
        $resources->property_0 = $product_details->Property01;
        $resources->property_1 = $product_details->Property02;
        $resources->property_2 = $product_details->Property03;
        $resources->property_3 = $product_details->Property04;
        $resources->property_4 = $product_details->Property05;
        $resources->property_5 = $product_details->Property06;
        $resources->property_6 = $product_details->Property07;
        $resources->property_7 = $product_details->Property08;
        $resources->property_8 = $product_details->Property09;
        $resources->property_9 = $product_details->Property10;
        $resources->property_10 = $product_details->Property11;
        $resources->property_11 = $product_details->Property12;
        $resources->color = '#0000ff';
        $resources->associations->categories->categories[0]->id =12;
        $feature_count = 1;
        if(isset($product_details->ProductoAltura)){
        $resources->associations->product_features->addChild('product_features')->addChild('id',1);
        $value = number_format(floatval($product_details->ProductoAltura),2);
        $resources->associations->product_features->product_features[$feature_count++]->addChild('id_feature_value',add_product_feature_value(1, $value));
        }
        if(isset($product_details->ProductoProfundidad)){
        $resources->associations->product_features->addChild('product_features')->addChild('id',3);
        $value = number_format(floatval($product_details->ProductoProfundidad),2);
        $resources->associations->product_features->product_features[$feature_count++]->addChild('id_feature_value',add_product_feature_value(3, $value));
        }
        if(isset($product_details->ProductoPeso)){
        $resources->associations->product_features->addChild('product_features')->addChild('id',4);
        $value = number_format(floatval($product_details->ProductoPeso),2);
        $resources->associations->product_features->product_features[$feature_count++]->addChild('id_feature_value',add_product_feature_value(4, $value));
        }
        if(isset($product_details->ProductoFrente)){
        $resources->associations->product_features->addChild('product_features')->addChild('id',2);
        $value = number_format(floatval($product_details->ProductoFrente),2);
        $resources->associations->product_features->product_features[$feature_count++]->addChild('id_feature_value',add_product_feature_value(2, $value));
        }
        if(isset($product_details->ProductoDiametro)){
        $resources->associations->product_features->addChild('product_features')->addChild('id',12);
        $value = number_format(floatval($product_details->ProductoDiametro),2);
        $resources->associations->product_features->product_features[$feature_count++]->addChild('id_feature_value',add_product_feature_value(12, $value));
        }
        if(isset($product_details->CartonAltura)){
        $resources->associations->product_features->addChild('product_features')->addChild('id',8);
        $value = number_format(floatval($product_details->CartonAltura),2);
        $resources->associations->product_features->product_features[$feature_count++]->addChild('id_feature_value',add_product_feature_value(8, $value));
        }
        if(isset($product_details->CartonPeso)){
        $resources->associations->product_features->addChild('product_features')->addChild('id',9);
        $value = number_format(floatval($product_details->CartonPeso),2);
        $resources->associations->product_features->product_features[$feature_count++]->addChild('id_feature_value',add_product_feature_value(9, $value));
        }
        if(isset($product_details->CartonFrente)){
        $resources->associations->product_features->addChild('product_features')->addChild('id',10);
        $value = number_format(floatval($product_details->CartonFrente),2);
        $resources->associations->product_features->product_features[$feature_count++]->addChild('id_feature_value',add_product_feature_value(10, $value));
        }
        if(isset($product_details->CartonProfundidad)){
        $resources->associations->product_features->addChild('product_features')->addChild('id',11);
        $value = number_format(floatval($product_details->CartonProfundidad),2);
        $resources->associations->product_features->product_features[$feature_count++]->addChild('id_feature_value',add_product_feature_value(11, $value));
        }
        if(isset($product_details->CartonPieza)){
        $resources->associations->product_features->addChild('product_features')->addChild('id',13);
        $value = number_format(floatval($product_details->CartonPieza),2);
        $resources->associations->product_features->product_features[$feature_count++]->addChild('id_feature_value',add_product_feature_value(13, $value));
        }
        
        $resources->name->language[0][0] = utf8_encode($product_details->ShortName);
        $resources->description->language[0][0] = utf8_encode($product_details->Description);
        if(isset($product_details->Video))
            $resources->youtube_link->language[0][0] = utf8_encode($product_details->Video);
        $resources->link_rewrite->language[0][0] = Tools::link_rewrite($product_details->ShortName);
        if (!isset($resources->date_add) || empty($resources->date_add))
            $resources->date_add = date('Y-m-d H:i:s');
        $resources->date_upd = date('Y-m-d H:i:s');
        
        try
	{
		$opt = array('resource' => 'products');
		if ($_GET['Create'] == 'Creating')
		{
                    $opt['postXml'] = $xml->asXML();
                    $xml = $webService->add($opt);
                    set_price_rule($xml->product->id, $product_xml->ItemNumber, $list);
                    save_product_combination($xml->product->id, $product_xml->ItemNumber,$list, $decoration,$product_xml->Color);
                    ProductCore::addCustomField($xml->product->id,1,0,1,'Upload Logo');
                    ProductCore::addAttachments($xml->product->id, 1);
                    ProductCore::updateCacheAttachment((int)$xml->product->id);
                    echo "Successfully added.";
		}
	}
	catch (PrestaShopWebserviceException $ex)
	{
		// Here we are dealing with errors
		$trace = $ex->getTrace();
		if ($trace[0]['args'][0] == 404) echo 'Bad ID';
		else if ($trace[0]['args'][0] == 401) echo 'Bad auth key';
		else echo 'Other error<br />'.$ex->getMessage();
	}
        }
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
}

function add_product_feature_value($feature_id, $value){
        $webService = new PrestaShopWebservice(PS_SHOP_PATH, PS_WS_AUTH_KEY, DEBUG);
	$opt1 = array('resource' => 'product_feature_values');
        $xml1 = $webService->get(array('url' => PS_SHOP_PATH.'/api/product_feature_values?schema=blank'));
	$resources1 = $xml1->children()->children();
        $resources1->value->language=$value;
        $resources1->id_feature=$feature_id;
        $resources1->custom=1;
        $opt1['postXml'] = $xml1->asXML();
        $xml1 = $webService -> add($opt1);
        $result1 = $xml1->children()->children();
        return $result1->{'id'};
}


function add_new_manufacturer($name){
    global $webService;
    $opt1 = array('resource' => 'manufacturers');
    $xml1 = $webService->get(array('url' => PS_SHOP_PATH.'/api/manufacturers?schema=blank'));
    $resources1 = $xml1->children()->children();
    $resources1->name=$name;
    $resources1->active=1;
    $opt1['postXml'] = $xml1->asXML();
    $xml1 = $webService -> add($opt1);
    $result1 = $xml1->children()->children();
    return $result1->{'id'};
}


function set_product_quantity($ProductId, $StokId, $AttributeId, $quantity, $intransit, $intransit_avail_date, $color_id){
	global $webService;
	$xml = $webService -> get(array('url' => PS_SHOP_PATH . '/api/stock_availables?schema=blank'));
	$resources = $xml -> children() -> children();
	$resources->id = $StokId;
	$resources->id_product  = $ProductId;
	$resources->quantity = $quantity;
	$resources->id_shop = 1;
	$resources->out_of_stock=0;
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

function set_price_rule($ProductId,$itemNumber,$list){
	global $webService,$webservice_exi;
        $parameter = array("ItemNumber"=>$itemNumber,
                        "List"=>$list,
			"key"=>"8770471727");
        $price_details = $webservice_exi->GetPrice($parameter);
        $price_details = $price_details->GetPriceResult->PriceListResult;
        if(isset($price_details->Vol1))
        savePriceRule($ProductId,$price_details->Vol1, $price_details->Price1,$price_details->LPrice);
        if(isset($price_details->Vol2))
        savePriceRule($ProductId,$price_details->Vol2, $price_details->Price2,$price_details->LPrice);
        if(isset($price_details->Vol3))
        savePriceRule($ProductId,$price_details->Vol3, $price_details->Price3,$price_details->LPrice);
        if(isset($price_details->Vol4))
        savePriceRule($ProductId,$price_details->Vol4, $price_details->Price4,$price_details->LPrice);
}

function savePriceRule($ProductId, $from_quantity, $new_price, $price){
    global $webService;
    $reduction = intval($price) - intval($new_price);
        
    $xml = $webService -> get(array('url' => PS_SHOP_PATH . '/api/specific_prices?schema=synopsis'));
	$resources = $xml -> children() -> children();
	$resources->id_product  = $ProductId;
	$resources->id_currency = 0;
	$resources->id_shop = 1;
	$resources->id_shop_group = 0;
	$resources->id_cart = 0;
	$resources->id_country = 0;
	$resources->id_group = 0;
	$resources->id_customer = 0;
	$resources->price = floatval($price);
	$resources->from_quantity = intval($from_quantity);
	$resources->reduction = floatval($reduction);
	$resources->reduction_type = 'amount';
	$resources->from='0000-00-00 00:00:00';
	$resources->to='0000-00-00 00:00:00';
        try {
		$opt = array('resource' => 'specific_prices');
		$opt['postXml'] = $xml->asXML();
                $xml = $webService->add($opt);
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



function save_product_combination($ProductId,$itemNumber,$list, $decoration,$color_name){
	global $webService,$webservice_exi,$result_xml, $count;
        $product_xml = $result_xml->ProductListResult->ProductListResult;
        $parameter = array("ItemNumber"=>$itemNumber,
			"key"=>"8770471727");
        $i=0;
        $inventory_details = $webservice_exi->GetInventory($parameter);
        if(isset($inventory_details->GetInventoryResult->InventoryData->SKU))
            $inventory_details = $inventory_details->GetInventoryResult;
        else
            $inventory_details = $inventory_details->GetInventoryResult->InventoryData;
        foreach ($inventory_details as $inventory){
            attachImages($inventory->SKU, $ProductId);
            if(isset($decoration->areasimp->ItemNumber))
                $decorationData = $decoration;
            else 
                $decorationData = $decoration->areasimp;
            foreach ($decorationData as $method){
                $xml = $webService -> get(array('url' => PS_SHOP_PATH . '/api/combinations?schema=synopsis'));
                $resources = $xml -> children() -> children();
                $resources->id_product  = $ProductId;
                $resources->quantity = $inventory->Available;
                if($i==0){
                    $resources->default_on = true;
                    $i++;
                }
                $resources->minimal_quantity = 1;
                $resources->reference = $inventory->SKU;
                $color_id = AttributeCore::getColorAttributeIdByValue($inventory->HexValue);
                $decoration_id = AttributeCore::getDecorationAttributeIdByValue($method->TecnicaFull);
                if(!$color_id){
                    $xml1 = $webService -> get(array('url' => PS_SHOP_PATH . '/api/product_option_values?schema=synopsis'));
                    $resources1 = $xml1 -> children() -> children();
                    $resources1->id_attribute_group  = 3;
                    $resources1->color  = "#".$inventory->HexValue;
                    $resources1->name->language  = $product_xml[$count-1]->Color;
                     try {
                        $opt1 = array('resource' => 'product_option_values');
                        $opt1['postXml'] = $xml1->asXML();
                        $xml1 = $webService->add($opt1);
                        $result1 = $xml1->children()->children();
                        $color_id = $result1->{'id'};
                    }catch (PrestaShopWebserviceException $ex) {
                            echo "<b>Error al setear la cantidad  ->Error : </b>".$ex->getMessage().'<br>';
                    }
                }
                
                if(!$decoration_id){
                    $xml1 = $webService -> get(array('url' => PS_SHOP_PATH . '/api/product_option_values?schema=synopsis'));
                    $resources1 = $xml1 -> children() -> children();
                    $resources1->id_attribute_group  = 4;
                    $resources1->name->language  = $method->TecnicaFull;
                     try {
                        $opt1 = array('resource' => 'product_option_values');
                        $opt1['postXml'] = $xml1->asXML();
                        $xml1 = $webService->add($opt1);
                        $result1 = $xml1->children()->children();
                        $decoration_id = $result1->{'id'};
                    }catch (PrestaShopWebserviceException $ex) {
                            echo "<b>Error al setear la cantidad  ->Error : </b>".$ex->getMessage().'<br>';
                    }
                }
                
                $resources->associations->product_option_values->product_option_values->id=$color_id;
                $resources->associations->product_option_values->addChild('product_option_values')->addChild('id',$decoration_id);
               try {
                        $opt = array('resource' => 'combinations');
                        $opt['postXml'] = $xml->asXML();
                        $xml = $webService->add($opt);
                         getIdStockAvailableAndSet($ProductId, $inventory->Available,
                   $inventory->OnTransit,$inventory->Date,$color_id);
                         $count++;
                }catch (PrestaShopWebserviceException $ex) {
                        echo "<b>Error al setear la cantidad  ->Error : </b>".$ex->getMessage().'<br>';
                }
            }
            $intransit_date = $inventory->Date;
            if($intransit_date=='N/A')
                $intransit_date = date("Y-m-d");
                StockAvailableCore::setStockInventory($ProductId, 1, 0,$inventory->Available,$color_id,
                   $inventory->OnTransit,$intransit_date);
        }
}

function attachImages($imageName, $productId){
    // change the local path where image has been downloaded "presta-api" is my local folder from where i run API script
    $img_path = PS_PRODUCT_IMG_PATH.'/img/p/images/'. $imageName.'.jpg';
    var_dump($img_path,file_exists($img_path));
    
    if (file_exists($img_path)){
        attachImagesToProducts($img_path, $productId);
    }
    else{
        for($i=1;;$i++){
            $img_path = PS_PRODUCT_IMG_PATH.'/img/p/images/'. $imageName.'_'.$i.'.jpg';
            var_dump($img_path);
             if (file_exists($img_path)){
                attachImagesToProducts($img_path, $productId);
            }else
                break;
        }
    }
}

function attachImagesToProducts($img_path, $productId){
    // change the local path where image has been downloaded "presta-api" is my local folder from where i run API script
//image will be associated with product id 4
    $url = PS_SHOP_PATH. '/api/images/products/'.$productId;

    $ch = curl_init();

    curl_setopt ($ch, CURLOPT_URL, $url);
    curl_setopt ($ch, CURLOPT_POST, true );
    // Curl_setopt ($ ch, CURLOPT_PUT, true); To edit a picture
    curl_setopt($ch, CURLOPT_USERPWD, PS_WS_AUTH_KEY. '' );
    curl_setopt ($ch, CURLOPT_POSTFIELDS, array( 'image' => '@'.$img_path));
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true );
    $Result = curl_exec ($ch);
    curl_close ($ch);
}


function add_new_category($category_name,$parent_id){
    global $webService;        
    $xml = $webService -> get(array('url' => PS_SHOP_PATH . '/api/categories?schema=synopsis'));
	$resources = $xml -> children() -> children();
        unset($resources->level_depth);
        unset($resources->nb_products_recursive);
	$resources->id_parent  = $parent_id;
	$resources->active = 1;
	$resources->name->language = $category_name;
        $resources->link_rewrite->language = Tools::link_rewrite($category_name);
        try {
		$opt = array('resource' => 'categories');
		$opt['postXml'] = $xml->asXML();
                $xml = $webService->add($opt);
                $result1 = $xml->children()->children();
                return $result1->{'id'};
	}catch (PrestaShopWebserviceException $ex) {
		echo "<b>Error al setear la cantidad  ->Error : </b>".$ex->getMessage().'<br>';
	}
}


?>
</body></html>
