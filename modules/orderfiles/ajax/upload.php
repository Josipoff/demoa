<?php
require_once("../../../config/config.inc.php");
require_once("../orderfiles.php");
$module = new orderfiles();

if ($module->psversion()==5){
    if (isset(Context::getContext()->controller))
    	$controller = Context::getContext()->controller;
    else{
    	$controller = new FrontController();
        $controller->ssl=true;
    	$controller->init();
    }
} else {
    require_once(dirname(__FILE__).'/../../../init.php');
}

if(isset($_POST['auptype'])){
    if ($_POST['auptype']=="product"){
        if(isset($_FILES["file"])){
        	$ret = array();
        	$error =$_FILES["file"]["error"]; 
        	if(!is_array($_FILES["file"]["name"])) {
                if (!isset($_COOKIE['ftpr'])){
                    $cookieid=date("U").$module->generatekey(5,"abcdfghijklmnouprstuwxyz1234567890");
                    setcookie("ftpr", $cookieid, time()+86400, "/");
                    $module->insertfilestoproduct($_POST,$_FILES,$cookieid,1);
                } else {
                    $cookieid=$_COOKIE['ftpr'];
                    $module->insertfilestoproduct($_POST,$_FILES,$cookieid,1);
                }
        	} else {
        	  $fileCount = count($_FILES["file"]["name"]);
        	  for($i=0; $i < $fileCount; $i++){
                if (!isset($_COOKIE['ftpr'])){
                $cookieid=date("U").$module->generatekey(5,"abcdfghijklmnouprstuwxyz1234567890");
                setcookie("ftpr", $cookieid, time()+86400, "/");
                $module->insertfilestoproduct($_POST,$_FILES,$cookieid,1);
                } else {
                $cookieid=$_COOKIE['ftpr'];
                $module->insertfilestoproduct($_POST,$_FILES,$cookieid,1);
                }
        	  }
        	}
            echo json_encode($ret);
         }
     } elseif ($_POST['auptype']=="cart"){
        if(isset($_FILES["file"])){
        	$ret = array();
        	$error =$_FILES["file"]["error"];
        	if(!is_array($_FILES["file"]["name"])){
                if (!isset($_COOKIE['ftpr'])){
                    $cookieid=date("U").$module->generatekey(5,"abcdfghijklmnouprstuwxyz1234567890");
                    setcookie("ftpr", $cookieid, time()+86400, "/");
                    $module->insertfilestocart($_POST,$_FILES,1);
                } else {
                    $cookieid=$_COOKIE['ftpr'];
                    $module->insertfilestocart($_POST,$_FILES,1);
                }                
        	} else {
        	  
                if (!isset($_COOKIE['ftpr'])){
                    $cookieid=date("U").$module->generatekey(5,"abcdfghijklmnouprstuwxyz1234567890");
                    setcookie("ftpr", $cookieid, time()+86400, "/");
                    $module->insertfilestocart($_POST,$_FILES,1);
                } else {
                    $cookieid=$_COOKIE['ftpr'];
                    $module->insertfilestocart($_POST,$_FILES,1);
                }
        	}
            echo json_encode($ret);
         } 
     } elseif ($_POST['auptype']=="order"){
         if(isset($_FILES["file"])){
            $module->insertphotoajax($_POST,$_FILES);
         }
     }
 }
?>