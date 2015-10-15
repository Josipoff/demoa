<?php
if (isset($_GET['t']) && isset($_GET['opt']) && isset($_GET['f'])){
    if ($_GET['t']=="files" || $_GET['t']=="cartfiles" || $_GET['t']=="productfiles"){
        if (!preg_match('/[^A-Za-z0-9]/', $_GET['opt'])){
            if (file_exists($_GET['t']."/".$_GET['opt']."/".$_GET['f'])){
                header("Content-Description: File Transfer");  
                header("Content-Disposition: attachment; filename=\"".$_GET['f']."\""); 
                readfile ($_GET['t']."/".$_GET['opt']."/".$_GET['f']); 
            }
        }
    }
} 
?>