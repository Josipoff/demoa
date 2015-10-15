<?php 
	require_once(dirname(__FILE__).'/../config/config.inc.php');

	$webservice = new SoapClient('http://www2.promoshop.com.mx/ws_store/service.asmx?WSDL') ;
	$parameter =array("ItemNumber"=>"DE70005",
			"key"=>"8770471727");



	$result = $webservice->GetDetails($parameter);
        var_dump($result);
/*
foreach((array) $result->GetQuoteResult as $x=>$x_value) 
{



				echo "Area: " . $x_value->Area;
				echo "<br />";
				echo "Tecica: " . $x_value->Tecnica;
				echo "<br />";
				echo "Superficie: " . $x_value->Superficie;
				echo "<br />";
				echo "Tintas: " . $x_value->Tintas;
				echo "<br />";
				echo "Impresiones: " . $x_value->Impresiones;
				echo "<br />";
				echo "Largo: " . $x_value->Largo;
				echo "<br />";
				echo "Ancho: " . $x_value->Ancho;
				echo "<br />";
				echo "TintasMax: " . $x_value->TintasMax;
				echo "<br />";
				echo "<br />";



	foreach ($x_value->PricesArray->Prices as $x_value_single) 
	{
	

		echo "Precio: " . $x_value_single->Piezas;
		echo "&emsp;";
		echo "Precio: " . $x_value_single->Precio;
		echo "&emsp;";
		echo "Precio: " . $x_value_single->PrecioImp;

				echo "<br />";

			

	}
}

*/
									
?>