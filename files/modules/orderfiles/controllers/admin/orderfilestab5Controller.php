<?php
include(_PS_MODULE_DIR_.'orderfiles/orderfiles.php');

class orderfilestab5Controller extends ModuleAdminController {
    public function __construct(){
        $this->orderfiles=new orderfiles();
  	    parent::__construct();
    }
    
    public function renderList(){
        $msg='';
  		if (isset($_POST['deletefile']) && isset($_POST['fid'])){
  			$msg.=$this->delete($_POST['fid']);	
  		}
        
        if (isset($_POST['deletecartfile']) && isset($_POST['fid'])){
  			$msg.=$this->deletecartfile($_POST['fid']);	
  		}
        
        if (isset($_POST['deleteproductfile']) && isset($_POST['fid'])){
  			$msg.=$this->deleteproductfile($_POST['fid']);	
  		}
  		
  		if (isset($_POST['filemanager']) && isset($_POST['oid'])){
  			$msg.=$this->filemanager($_POST['oid']);
		} else {
			
			$msg.=$this->orderlist();
		}        
        return $this->header().$msg;
    }
  

  	
  	public function header(){
  		$ret = '
        <style>
        .of_toolbar {
            background: url("../modules/orderfiles/img/nav-bg.png") repeat-x top;
            background-color: #464646;
            border: 1px solid #323232;
            margin-bottom: 10px;
            padding: 5px 0;
            border-radius: 3px;
            -moz-border-radius: 3px;
            -webkit-border-radius: 3px;
            -o-border-radius: 3px;
            position: relative;    
        }
        .of_toolbar h3{
            color: #cfcfcf;
            font-size: 1.6em;
            font-weight: normal;
            line-height: 49px;
            margin: 0;
            padding: 0;
            text-shadow: 0 1px 0 #292929;
            margin-left: 10px;
        }
        </style>
			<div class="toolbar-placeholder">
				<div class="of_toolbar">
					<div class="pageTitle">
						<h3>
							<span id="current_obj" style="font-weight: normal;">									
								<span class="breadcrumb item-0 ">'.$this->l('Order Files Uploader').'</span>
							</span>
						</h3>
					</div>
				</div>
			</div>';
        return $ret;
  	}

    public function delete($idphoto){
        $db = Db::getInstance(_PS_USE_SQL_SLAVE_); 
        $query = "SELECT * FROM `"._DB_PREFIX_."orderfiles` WHERE id='$idphoto'";
        $array = $db->ExecuteS($query);
        if (isset($array['0'])){
            $array['0']['filetype']=pathinfo($array['0']['filename'], PATHINFO_EXTENSION);            
            unlink("../modules/orderfiles/files/{$array['0']['id_order']}/{$array['0']['filename']}");
            $db = Db::getInstance(); 
            $query = "DELETE FROM `"._DB_PREFIX_."orderfiles` WHERE id='$idphoto'";
            $db->Execute($query);     
        }
    }
    
    public function deletecartfile($idfile){
        $db = Db::getInstance(_PS_USE_SQL_SLAVE_); 
        $query = "SELECT * FROM `"._DB_PREFIX_."orderfiles_cart` WHERE id='$idfile'";
        $array = $db->ExecuteS($query);
        if (isset($array['0'])){
            $array['0']['filetype']=pathinfo($array['0']['filename'], PATHINFO_EXTENSION);            
            unlink("../modules/orderfiles/cartfiles/{$array['0']['idcart']}/{$array['0']['filename']}");
            $db = Db::getInstance(); 
            $query = "DELETE FROM `"._DB_PREFIX_."orderfiles_cart` WHERE id='$idfile'";
            $db->Execute($query);     
        }
    }    
    
    public function deleteproductfile($idfile){
        $db = Db::getInstance(_PS_USE_SQL_SLAVE_); 
        $query = "SELECT * FROM `"._DB_PREFIX_."orderfiles_product` WHERE id='$idfile'";
        $array = $db->ExecuteS($query);
        if (isset($array['0'])){
            $array['0']['filetype']=pathinfo($array['0']['filename'], PATHINFO_EXTENSION);            
            unlink("../modules/orderfiles/productfiles/{$array['0']['cookieid']}/{$array['0']['filename']}");
            $db = Db::getInstance(); 
            $query = "DELETE FROM `"._DB_PREFIX_."orderfiles_product` WHERE id='$idfile'";
            $db->Execute($query);     
        }
    }  
    
  	public function orderlistedit($oid=NULL){
        $ret='';
	  	$ordercore=new OrderCore($oid);
	  	$customer=new CustomerCore($ordercore->id_customer);
	  	if (!($oid==NULL)){
	  	    $ret.= '<h2>'.$this->l('Order').'</h2>';
	  	}
            if ($this->orderfiles->psversion()==5 || $this->orderfiles->psversion()==6){
				$ret.= '
                <table id="order-list" class="table order" style="width:100%">
					<thead>
						<tr>
							<th class="first_item">'.$this->l('Customer').'</th>
							<th class="item">'.$this->l('Order ID').'</th>
							<th class="item">'.$this->l('Date').'</th>
							<th class="item">'.$this->l('Total price').'</th>
							<th class="item">'.$this->l('Payment').'</th>
						</tr>
					</thead>
					<tbody>
						<tr >
							<td class="history_price">'.$customer->lastname.' '.$customer->firstname.'<br/>'.$customer->email.'</td>
							<td class="history_link bold">'.$oid.'</td>
							<td class="history_date bold">'.$ordercore->date_add.'</td>
							<td class="history_price"><span class="price">'.$ordercore->total_paid.' '.$this->orderfiles->currency_sign($ordercore->id_currency).'</span></td>
							<td class="history_method">'.$ordercore->payment.'</td>
						</tr>
					</tbody>
				</table>';
            }
        return $ret;
  	}
  	
  	public function orderlist($oid=NULL){
            $ret='';
	  		$ordercore=new OrderCore($oid);
	  		$orders=$ordercore->getOrdersWithInformations();
	  		if (!($oid==NULL)){
	  			$ret.= '<h2>'.$this->l('Order list').'</h2>';
	  		}
            
            $ret.= '
                <table id="order-list" class="table order" style="width:100%; margin-top:15px;">
					<thead>
						<tr>
							<th class="first_item">'.$this->l('Order Reference').'</th>
							<th class="item">'.$this->l('Date').'</th>
							<th class="item">'.$this->l('Total price').'</th>
							<th class="item">'.$this->l('Payment').'</th>
							<th class="item">'.$this->l('Manage uploaded files').'</th>
						</tr>
					</thead>';
            
            foreach ($orders as $key=>$order){
	  		   if ($this->orderfiles->psversion()==5 || $this->orderfiles->psversion()==6){
				$ret.= '
					<tbody>
						<tr class="first_item ">
							<td class="history_link bold">'.$order['reference'].'</td>
							<td class="history_date bold">'.$order['date_add'].'</td>
							<td class="history_price"><span class="price">'.$order['total_paid'].' '.$this->orderfiles->currency_sign($order['id_currency']).'</span></td>
							<td class="history_method">'.$order['payment'].'</td>
							<td class="history_method"><form method="post" action="index.php?controller=orderfilestab5&token='.$_GET['token'].'"\><input type="hidden" name="oid" value="'.$order['id_order'].'"/><input type="submit" name="filemanager" value="'.$this->l('Manage files').'" class="button"/></form></td>
						</tr>
					</tbody>';
                }
	  		   if ($this->orderfiles->psversion()==4){
				$ret.= '
					<tbody>
						<tr class="first_item ">
							<td class="history_link bold">'.$order['id_order'].'</td>
							<td class="history_date bold">'.$order['date_add'].'</td>
							<td class="history_price"><span class="price">'.$order['total_paid'].' '.$this->orderfiles->currency_sign($order['id_currency']).'</span></td>
							<td class="history_method">'.$order['payment'].'</td>
							<td class="history_method"><form method="post" action="index.php?tab=orderfilestab5&token='.$_GET['token'].'"\><input type="hidden" name="oid" value="'.$order['id_order'].'"/><input type="submit" name="filemanager" value="'.$this->l('Manage files').'" class="button"/></form></td>
						</tr>
					</tbody>';
                }                
			}
        $ret.= '</table>';
        
        return $ret;
  	}
  	
	public function extension($filename){
		return pathinfo($filename, PATHINFO_EXTENSION);
	}
	
  	public function get_files($order){
 		$db = Db::getInstance(_PS_USE_SQL_SLAVE_); 
        $query = "SELECT * FROM `"._DB_PREFIX_."orderfiles` WHERE id_order='$order'";
        $array['toorder'] = $db->ExecuteS($query);
        $order_detail=new OrderCore($order);
	    $db = Db::getInstance(_PS_USE_SQL_SLAVE_); 
        $query = "SELECT * FROM `"._DB_PREFIX_."orderfiles` WHERE id_order='$order'";
        $array['toorder'] = $db->ExecuteS($query);
        $query = "SELECT * FROM `"._DB_PREFIX_."orderfiles_cart` WHERE idcart='".$order_detail->id_cart."'";
        $array['tocart'] = $db->ExecuteS($query);
        foreach ($array['tocart'] as $key=>$value){
            $array['tocart'][$key]['product']=new Product($value['idproduct'],false, (int)Configuration::get('PS_LANG_DEFAULT'));
        }
        
        $query = "SELECT * FROM `"._DB_PREFIX_."orderfiles_product` WHERE idcart='".$order_detail->id_cart."'";
        $array['toproduct'] = $db->ExecuteS($query);
        foreach ($array['toproduct'] as $key=>$value){
            $array['toproduct'][$key]['product']=new Product($value['idproduct'],false, (int)Configuration::get('PS_LANG_DEFAULT'));
        }
        return $array;
	}
  	
  	public function lastfiles(){
  	    $ret=''; 
  		$db = Db::getInstance(_PS_USE_SQL_SLAVE_); 
        $query = "SELECT * FROM `"._DB_PREFIX_."orderfiles` ORDER BY id DESC LIMIT 6";
        $array = $db->ExecuteS($query);
        if (count($array)>0){
        	$ret.= '<h4>'.$this->l('Last files').'</h4>';
        	foreach ($array as $key=>$file){
        		$ordercore=new OrderCore($file['id_order']);
        		$orders=$ordercore->getOrdersWithInformations();
        		$ret.= '
				<div style="margin:5px; padding:5px; display:inline-block; border:1px solid #c0c0c0;">
				<a href="../modules/orderfiles/files/'.$file['id_order'].'/'.$file['filename'].'" target="_blank">
				'.$file['filename'].'<br/><b>'.$orders[0]['email'].'</b>
				</a>
				</div>';        		
        	}
        }
        return $ret;
  	}
    
    public function boupload($oid){
        return '
        <table class="table order" style="width:100%;">
            <tr>
                <th colspan="4">
                    <h3>'.$this->l('Upload files to this order as an administrator').'</h3>
                </th>
            </tr>
            <tr>
                <td>
                    '.$this->l('File').'
                </td>
                <td>
                    '.$this->l('Title').'
                </td>
                <td>
                    '.$this->l('Description').'
                </td>
                <td></td>
            </tr>
            <tr>
                <form action="" method="POST" enctype="multipart/form-data">
                    <td><input type="file" name="file[]" /></td>
                    <td><input type="text" name="title" /></td>
                    <td><textarea name="description"></textarea></td>
                    <td><input type="hidden" name="filemanager"/>
                    <input type="hidden" name="oid" value="'.$_POST['oid'].'">
                    <input type="submit" name="addfile" class="button" value="'.$this->l('upload').'"></td>
                </form>
            </tr>
        </table>
        ';
    }
  	
  	public function orderfiles($oid){
  	    $ret='';
  		$files=$this->get_files($oid);
	  		foreach ($files['toorder'] as $key=>$file){
		      if ($file['adminfile']==1){
		          $adminfile='<span class="uploadedbyadmin" style="position: relative; background: rgb(237, 238, 166); top: -10px; left: -10px; padding: 10px; width: 280px; display: block;">'.$this->l('Uploaded by admin').'</span>';
		      } else {
		          $adminfile='';
		      }
		      if ($this->orderfiles->psversion()==5 || $this->orderfiles->psversion()==6){
			  $ret.= '<div style="border-radius:5px; position:relative; -webkit-border-radius:5px; -moz-border-radius:5px; vertical-align:top; text-align:left; border:1px solid #c0c0c0; background:#f7f7f7; display:inline-block; margin:5px; width:280px; padding:10px; ">
                     '.$adminfile.'
					  <div style="display:block; clear:both;">
						  <div style="display:inline-block; float:left; width:48px;">
						  	<img src="'._MODULE_DIR_.'orderfiles/img/file.png" />
						  </div>
						  <div style="display:inline-block; float:left; width: 222px; margin-left:10px; overflow:hidden;">
							  <b>'.$file['title'].'</b> (<i>'.$file['filename'].'</i>)<br/>
							  '.$file['description'].'
						  </div>
					  </div>
					  <div style="overflow:hidden; display:block; clear:both; width:100%; vertical-align:top; position:relative; margin-top:10px; padding-bottom:30px;">
					  	<div style="display:inline-block; float:left;">
		                     <a href="'._MODULE_DIR_.'orderfiles/download.php?t=files&opt='.$oid.'&f='.$file['filename'].'" target="_blank" class="button" style="position:absolute; left:0px; bottom:0px;">'.$this->l('download').'</a>
		  				</div>
						<div style="display:inline-block; float:left;">    
						  <form style="position:absolute; right:0px; bottom:0px;" style="margin:0px;padding:0px;"  method="post" action="index.php?controller=orderfilestab5&token='.$_GET['token'].'"\><input type="hidden" name="oid" value="'.$oid.'"/><input type="hidden" name="filemanager"><input type="hidden" name="fid" value="'.$file['id'].'">
							  <input type="submit" name="deletefile" value="'.$this->l('Delete').'" class="button extra" style="position:relative; right:0px; bottom:0px;"/>
						  </form>
						</div>  
					  </div>
		  		  </div>';
                }             
	  		} 
            if ($ret==''){
                $ret.='
                <div class="bootstrap">
            		<div class="alert alert-warning">
            			<h4>'.$this->l('no files uploaded').'</h4>
            		</div>
            	</div>';
            }
            foreach ($files['tocart'] as $key=>$file){
		      if ($this->orderfiles->psversion()==5 || $this->orderfiles->psversion()==6){
			  $ret.= '<div style="border-radius:5px; position:relative; -webkit-border-radius:5px; -moz-border-radius:5px; vertical-align:top; text-align:left; border:1px solid #c0c0c0; background:#f7f7f7; display:inline-block; margin:5px; width:280px; padding:10px; ">
					  <div style="display:block; clear:both;">
						  <div style="display:inline-block; float:left; width:48px;">
						  	<img src="'._MODULE_DIR_.'orderfiles/img/file.png" />
						  </div>
						  <div style="display:inline-block; float:left; width: 222px; margin-left:10px; overflow:hidden;">
							  <b>'.$file['product']->name.'</b> (<i>'.$file['filename'].'</i>)<br/>
							  '.$file['description'].'
						  </div>
					  </div>
					  <div style="overflow:hidden; display:block; clear:both; width:100%; vertical-align:top; position:relative; margin-top:10px; padding-bottom:30px;">
					  	<div style="display:inline-block; float:left;">
		  				  <a href="'._MODULE_DIR_.'orderfiles/download.php?t=cartfiles&opt='.$file['idcart'].'&f='.$file['filename'].'" target="_blank" class="button" style="position:absolute; left:0px; bottom:0px;">'.$this->l('download').'</a>
		  				</div>
						<div style="display:inline-block; float:left;">    
						  <form style="position:absolute; right:0px; bottom:0px;" style="margin:0px;padding:0px;"  method="post" action="index.php?controller=orderfilestab5&token='.$_GET['token'].'"\><input type="hidden" name="oid" value="'.$oid.'"/><input type="hidden" name="filemanager"><input type="hidden" name="fid" value="'.$file['id'].'">
							  <input type="submit" name="deletecartfile" value="'.$this->l('Delete').'" class="button extra" style="position:relative; right:0px; bottom:0px;"/>
						  </form>
						</div>  
					  </div>
		  		  </div>';
                }              
	  		}
            
            foreach ($files['toproduct'] as $key=>$file){
		      if ($this->orderfiles->psversion()==5 || $this->orderfiles->psversion()==6){
			  $ret.= '<div style="border-radius:5px; position:relative; -webkit-border-radius:5px; -moz-border-radius:5px; vertical-align:top; text-align:left; border:1px solid #c0c0c0; background:#f7f7f7; display:inline-block; margin:5px; width:280px; padding:10px; ">
					  <div style="display:block; clear:both;">
						  <div style="display:inline-block; float:left; width:48px;">
						  	<img src="'._MODULE_DIR_.'orderfiles/img/file.png" />
						  </div>
						  <div style="display:inline-block; float:left; width: 222px; margin-left:10px; overflow:hidden;">
							  <b>'.$file['product']->name.'</b> (<i>'.$file['filename'].'</i>)<br/>
							  '.$file['description'].'
						  </div>
					  </div>
					  <div style="overflow:hidden; display:block; clear:both; width:100%; vertical-align:top; position:relative; margin-top:10px; padding-bottom:30px;">
					  	<div style="display:inline-block; float:left;">
		  				  <a href="'._MODULE_DIR_.'orderfiles/download.php?t=productfiles&opt='.$file['cookieid'].'&f='.$file['filename'].'" target="_blank" class="button" style="position:absolute; left:0px; bottom:0px;">'.$this->l('download').'</a>
		  				</div>
						<div style="display:inline-block; float:left;">    
						  <form style="position:absolute; right:0px; bottom:0px;" style="margin:0px;padding:0px;"  method="post" action="index.php?controller=orderfilestab5&token='.$_GET['token'].'"\><input type="hidden" name="oid" value="'.$oid.'"/><input type="hidden" name="filemanager"><input type="hidden" name="fid" value="'.$file['id'].'">
							  <input type="submit" name="deleteproductfile" value="'.$this->l('Delete').'" class="button extra" style="position:relative; right:0px; bottom:0px;"/>
						  </form>
						</div>  
					  </div>
		  		  </div>';
                }
	  		}
        return $ret;
  	}



    public function generatekey($length = 5, $chars) {
        $last="";
        $validCharacters = $chars;
        $validCharNumber = strlen($validCharacters);
        $result = "";
        for ($i = 0; $i < $length; $i++) {
            $index = mt_rand(0, $validCharNumber - 1);
            while ($last==$index){
                $index = mt_rand(0, $validCharNumber - 1);        
            }
            $result .= $validCharacters[$index];
            $last = $index;
        }
        return $result;
    }

    
    public function insertphoto($post,$file){
        $limit=count($file['file']['name']);
        for ($i=0; $i<=$limit; $i++){
            global $cookie;
            $db = Db::getInstance();
            $plik_tmp = $file['file']['tmp_name'][$i]; 
            $plik_nazwa = $file['file']['name'][$i]; 
            $plik_rozmiar = $file['file']['size'][$i];
            $plik_nazwa=strtolower(preg_replace('/[^a-zA-Z0-9\.]/', '', $plik_nazwa));
            $filetype = pathinfo($plik_nazwa, PATHINFO_EXTENSION);
            
            if (!file_exists("../modules/orderfiles/files/{$post['oid']}")){
                mkdir("../modules/orderfiles/files/{$post['oid']}",0777);
            }




            
            if(is_uploaded_file($plik_tmp)){
                $key="";
                $sciezka="../modules/orderfiles/files/{$post['oid']}/";
                $plik=$plik_nazwa;
                if (file_exists("$sciezka$plik")){
                    $key=$this->generatekey(10,"abcdfghijklmnouprstuwxyz1234567890");
                    $plik="$key$plik_nazwa";
                }
                
                if (move_uploaded_file($plik_tmp, "$sciezka$plik")){                    
                	$post['title']=mysql_escape_string($post['title']);
                	$post['description']=mysql_escape_string($post['description']);
                    $query = "INSERT INTO `"._DB_PREFIX_."orderfiles` (adminfile, title,description,filename,id_order,id_customer) VALUES ('1','{$post['title']}','{$post['description']}','{$key}{$plik_nazwa}','{$post['oid']}','{$cookie->id_customer}')";
                	$db->Execute($query);     
                }	
            } 
        }
    }    
  	
  	public function filemanager($oid){
        if (isset($_POST['addfile'])){
			if (isset($_POST['oid'])){
				$order = new OrderCore($_POST['oid']);
				$this->insertphoto($_POST,$_FILES);
			}
	    } 
     
  	    $ret='';
  		$ret.=$this->orderlistedit($oid);
  		$ret.= '<h2>'.$this->l('Manage uploaded files').'</h2>';
  		$ret.="<div style=\"clear:both; display:block; margin-bottom:20px;\">".$this->orderfiles($oid)."</div>";
        $ret.=$this->boupload($oid);
        return $ret;
  	}	
}
?>