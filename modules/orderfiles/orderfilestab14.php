<?php
include(_PS_MODULE_DIR_.'orderfiles/orderfiles.php');

class orderfilestab extends AdminTab{
  	public function __construct(){
  	 $module=new orderfiles();
  	 if ($module->psversion()==5){
      		$this->context=Context::getContext();
      		$this->multishop_context=$this->context->shop->id;
      		$this->multishop_context_group=$this->context->shop->id_shop_group;
        }
        $this->orderfiles = new orderfiles();
        return parent::__construct();
  	}

  	public function display(){
  		$this->header();
  		if (isset($_POST['deletefile']) && isset($_POST['fid'])){
  			$this->delete($_POST['fid']);	
  		}
  		
  		if (isset($_POST['filemanager']) && isset($_POST['oid'])){
  			$this->filemanager($_POST['oid']);
		} else {
			$this->lastfiles();
			$this->orderlist();
		}
  	}
  	
  	public function header(){
  		echo'
			<div class="toolbar-placeholder">
				<div class="toolbarBox toolbarHead">
					<div class="pageTitle">
						<h3>
							<span id="current_obj" style="font-weight: normal;">									
								<span class="breadcrumb item-0 ">'.$this->l('Order Files Uploader').'</span>
							</span>
						</h3>
					</div>
				</div>
			</div>';
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

  	public function orderlistedit($oid=NULL){
	  		$ordercore=new OrderCore($oid);
	  		$customer=new CustomerCore($ordercore->id_customer);
	  		if (!($oid==NULL)){
	  			echo '<h2>'.$this->l('Order').'</h2>';
	  		}
            if ($this->orderfiles->psversion()==5){
				echo '<table id="order-list" class="table order" style="width:100%">
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
            if ($this->orderfiles->psversion()==4){
				echo '<table id="order-list" class="table order" style="width:100%; margin-bottom:20px;">
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
            
  	}
  	
  	public function orderlist($oid=NULL){
	  		$ordercore=new OrderCore($oid);
	  		$orders=$ordercore->getOrdersWithInformations();
	  		if (!($oid==NULL)){
	  			echo '<h2>'.$this->l('Order list').'</h2>';
	  		}
            
            echo '
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
	  		   if ($this->orderfiles->psversion()==5){
				echo '
					<tbody>
						<tr class="first_item ">
							<td class="history_link bold">'.$order['reference'].'</td>
							<td class="history_date bold">'.$order['date_add'].'</td>
							<td class="history_price"><span class="price">'.$order['total_paid'].' '.$this->orderfiles->currency_sign($order['id_currency']).'</span></td>
							<td class="history_method">'.$order['payment'].'</td>
							<td class="history_method"><form method="post" action="index.php?controller=orderfilestab&token='.$_GET['token'].'"\><input type="hidden" name="oid" value="'.$order['id_order'].'"/><input type="submit" name="filemanager" value="'.$this->l('Manage files').'" class="button"/></form></td>
						</tr>
					</tbody>';
                }
	  		   if ($this->orderfiles->psversion()==4){
				echo '
					<tbody>
						<tr class="first_item ">
							<td class="history_link bold">'.$order['id_order'].'</td>
							<td class="history_date bold">'.$order['date_add'].'</td>
							<td class="history_price"><span class="price">'.$order['total_paid'].' '.$this->orderfiles->currency_sign($order['id_currency']).'</span></td>
							<td class="history_method">'.$order['payment'].'</td>
							<td class="history_method"><form method="post" action="index.php?tab=orderfilestab&token='.$_GET['token'].'"\><input type="hidden" name="oid" value="'.$order['id_order'].'"/><input type="submit" name="filemanager" value="'.$this->l('Manage files').'" class="button"/></form></td>
						</tr>
					</tbody>';
                }                
			}
        echo '</table>';
  	}
  	
	public function extension($filename){
		return pathinfo($filename, PATHINFO_EXTENSION);
	}
	
  	public function get_files($order){
 		$db = Db::getInstance(_PS_USE_SQL_SLAVE_); 
        $query = "SELECT * FROM `"._DB_PREFIX_."orderfiles` WHERE id_order='$order'";
        $array = $db->ExecuteS($query);
        return $array;
	}
  	
  	public function lastfiles(){
  		$db = Db::getInstance(_PS_USE_SQL_SLAVE_); 
        $query = "SELECT * FROM `"._DB_PREFIX_."orderfiles` ORDER BY id DESC LIMIT 6";
        $array = $db->ExecuteS($query);
        if (count($array)>0){
        	echo '<h4>'.$this->l('Last files').'</h4>';
        	foreach ($array as $key=>$file){
        		$ordercore=new OrderCore($file['id_order']);
        		$orders=$ordercore->getOrdersWithInformations();
        		echo '
				<div style="margin:5px; padding:5px; display:inline-block; border:1px solid #c0c0c0;">
				<a href="../modules/orderfiles/files/'.$file['id_order'].'/'.$file['filename'].'" target="_blank">
				'.$file['filename'].'<br/><b>'.$orders[0]['email'].'</b>
				</a>
				</div>';        		
        	}
        }
  	}
  	
  	public function orderfiles($oid){
  		$files=$this->get_files($oid);
  		if (count($files)>0){
	  		foreach ($files as $key=>$file){
		      if ($this->orderfiles->psversion()==5){
			  echo '<div style="border-radius:5px; position:relative; -webkit-border-radius:5px; -moz-border-radius:5px; vertical-align:top; text-align:left; border:1px solid #c0c0c0; background:#f7f7f7; display:inline-block; margin:5px; width:280px; padding:10px; ">
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
						  <form style="position:absolute; right:0px; bottom:0px;" style="margin:0px;padding:0px;"  method="post" action="index.php?controller=orderfilestab&token='.$_GET['token'].'"\><input type="hidden" name="oid" value="'.$oid.'"/><input type="hidden" name="filemanager"><input type="hidden" name="fid" value="'.$file['id'].'">
							  <input type="submit" name="deletefile" value="'.$this->l('Delete').'" class="button extra" style="position:relative; right:0px; bottom:0px;"/>
						  </form>
						</div>  
					  </div>
		  		  </div>';
                }
                if ($this->orderfiles->psversion()==4){
			     echo '<div style="border-radius:5px; position:relative; -webkit-border-radius:5px; -moz-border-radius:5px; vertical-align:top; text-align:left; border:1px solid #c0c0c0; background:#f7f7f7; display:inline-block; margin:5px; width:280px; padding:10px; ">
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
						  <form style="position:absolute; right:0px; bottom:0px;" style="margin:0px;padding:0px;"  method="post" action="index.php?tab=orderfilestab&token='.$_GET['token'].'"\><input type="hidden" name="oid" value="'.$oid.'"/><input type="hidden" name="filemanager"><input type="hidden" name="fid" value="'.$file['id'].'">
							  <input type="submit" name="deletefile" value="'.$this->l('Delete').'" class="button extra" style="position:relative; right:0px; bottom:0px;"/>
						  </form>
						</div>  
					  </div>
		  		  </div>';
                }                
	  		} 
  		} else {
	  		echo '<div class="warning">'.$this->l('no files uploaded').'</div>';
  		}
  	}
  	
  	public function filemanager($oid){
  		$this->orderlistedit($oid);
  		echo '<h2>'.$this->l('Mange uploaded files').'</h2>';
  		$this->orderfiles($oid);
  	}
  	
  	
}
?>