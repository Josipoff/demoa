<?php 
	header('Content-type: text/html; charset=utf-8');

        require_once('../config/config.inc.php');
        require_once('../PSWebServiceLibrary.php');
        try
        {
                $webService_presta = new PrestaShopWebservice(PS_SHOP_PATH, PS_WS_AUTH_KEY, DEBUG);
                $opt = array('resource' => 'products');
                for($i=48;$i<70;$i++){
                    $opt['id'] = $i;
                    $xml = $webService_presta->delete($opt); 
                    echo 'Deleted Product' . $i;
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
        
 /* 
foreach ((array) $result->ProductListResult as $x_value)
{
    foreach ($x_value as $product_xml) 
	{
       Update an existing product or Create a new one 
        //$id_product = (int)Db::getInstance()->getValue('SELECT id_product FROM '._DB_PREFIX_.'product WHERE reference = \''.pSQL($product_xml->Reference).'\'');
        //$product = $id_product ? new Product((int)$id_product, true) : new Product();
      $product->reference = $product_xml->sku;
        $parameter = array("ItemNumber"=>$product_xml->ItemNumber,
			"key"=>"8770471727");
        $product_details = $webservice->GetDetails($parameter);
        var_dump($product_details);
        /*$product->price = (float)$product_xml->Price;
        $product->active = (int)$product_xml->Active_product;
        $product->weight = (float)$product_xml->Weight;
        $product->minimal_quantity = (int)$product_xml->MinOrderQty;
        $product->id_category_default = 2;
        $product->name[1] = utf8_encode($product_xml->Products_name);
        $product->description[1] = utf8_encode($product_xml->Description);
        $product->description_short[1] = utf8_encode($product_xml->Short_Description);
        $product->link_rewrite[1] = Tools::link_rewrite($product_xml->Products_name);
        if (!isset($product->date_add) || empty($product->date_add))
            $product->date_add = date('Y-m-d H:i:s');
        $product->date_upd = date('Y-m-d H:i:s');
        $id_product ? $product->updateCategories(array(2)) : $product->addToCategories(array(2));
        $product->save();

        echo 'Product <b>'.$product->name[1].'</b> '.($id_product ? 'updated' : 'created').'<br />';
         
        }
}
        /*
foreach((array) $result->ProductListResult as $x=>$x_value) 
{

	foreach ($x_value as $x_value_single) 
	{


				echo "Producto: " . $x_value_single->ItemNumber;
				echo "<br />";
				echo "SKU: " . $x_value_single->SKU;
				echo "<br />";
				echo "Color: " . $x_value_single->Color;
				echo "<br />";
				echo "Category: " . $x_value_single->Category;
				echo "<br />";
				echo "SubCategory: " . $x_value_single->SubCategory;
				echo "<br />";
				echo "BasePrice: " . $x_value_single->BasePrice;
				echo "<br />";
				echo "LowestPrice: " . $x_value_single->LowestPrice;
				echo "<br />";
				echo "Amount: " . $x_value_single->Amount;
				echo "<br />";
				echo "<br />";
				echo "<br />";


			

	}
}
	*/								
?>