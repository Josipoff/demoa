<?php

/**
 * This module displays attributes on the product page in a table using radio buttons and checkboxes. 
 * @author Nethercott Constructions <info@nethercottconstructions.com>
 */
class ProductAttributesNC extends Module
{
	function __construct()
	{
		$this->name = 'productattributesnc';
		$this->tab = 'Products';
		$this->version = '1.3';

		parent::__construct(); // The parent construct is required for translations

		$this->page = basename(__FILE__, '.php');
		$this->displayName = $this->l('Product Attributes');
		$this->description = $this->l('Displays product attributes using radio buttons and checkboxes');
	}

	function install()
	{
		if (!parent::install() OR 
			!$this->registerHook('header') OR !$this->registerHook('productfooter') OR 
			Configuration::updateValue('PRODUCT_ATTRIB_NC_PRICE_FORMAT', 1) == false OR
			Configuration::updateValue('PRODUCT_ATTRIB_NC_HIDE_ZEROS', 0) == false OR
			Configuration::updateValue('PRODUCT_ATTRIB_NC_FULL_PRICE', 0) == false OR
			Configuration::updateValue('PRODUCT_ATTRIB_NC_DISPLAY_WEIGHT', 1) == false OR
			Configuration::updateValue('PRODUCT_ATTRIB_NC_DISPLAY_IMAGE', 1) == false OR
			Configuration::updateValue('PRODUCT_ATTRIB_NC_DISPLAY_DESC', 1) == false)
			return false;
		return true;
	}
	
	function uninstall()
	{
		if (!Configuration::deleteByName('PRODUCT_ATTRIB_NC_PRICE_FORMAT') OR 
			!Configuration::deleteByName('PRODUCT_ATTRIB_NC_HIDE_ZEROS') OR
			!Configuration::deleteByName('PRODUCT_ATTRIB_NC_FULL_PRICE') OR
			!Configuration::deleteByName('PRODUCT_ATTRIB_NC_DISPLAY_WEIGHT') OR
			!Configuration::deleteByName('PRODUCT_ATTRIB_NC_DISPLAY_IMAGE') OR
			!Configuration::deleteByName('PRODUCT_ATTRIB_NC_DISPLAY_DESC') OR
			!parent::uninstall())
			return false;
		return true;
	}
	
	public function getContent()
	{
		$output = '<h2><a style="color: #268ccd" href="http://www.nethercottconstructions.com">'.$this->displayName.'</a></h2>';
		if (Tools::isSubmit('submitProductAttributesNC'))
		{
			$priceFormat = intval(Tools::getValue('priceFormat'));
			$hideZeros = intval(Tools::getValue('hideZeros'));
			$fullPrice = intval(Tools::getValue('fullPrice'));
			$displayWeight = intval(Tools::getValue('displayWeight'));
			$displayQuantity = intval(Tools::getValue('displayQuantity'));
			$displayImage = intval(Tools::getValue('displayImage'));
			$displayDescription = intval(Tools::getValue('displayDescription'));
			if ($priceFormat != 0 AND $priceFormat != 1 AND $priceFormat != 2 AND $priceFormat != 3)
				$output .= '<div class="alert error">'.$this->l('Price format: Invalid choice.').'</div>';
			else if ($hideZeros != 0 AND $hideZeros != 1)
				$output .= '<div class="alert error">'.$this->l('Hide zero differences: Invalid choice.').'</div>';
			else if ($fullPrice != 0 AND $fullPrice != 1)
				$output .= '<div class="alert error">'.$this->l('Display full price/weight: Invalid choice.').'</div>';
			else if ($displayWeight != 0 AND $displayWeight != 1)
				$output .= '<div class="alert error">'.$this->l('Display weight: Invalid choice.').'</div>';
			else if ($displayImage != 0 AND $displayImage != 1)
				$output .= '<div class="alert error">'.$this->l('Display image: Invalid choice.').'</div>';
			else if ($displayDescription != 0 AND $displayDescription != 1)
				$output .= '<div class="alert error">'.$this->l('Display description: Invalid choice.').'</div>';
			else
			{
				Configuration::updateValue('PRODUCT_ATTRIB_NC_PRICE_FORMAT', intval($priceFormat));
				Configuration::updateValue('PRODUCT_ATTRIB_NC_HIDE_ZEROS', intval($hideZeros));
				Configuration::updateValue('PRODUCT_ATTRIB_NC_FULL_PRICE', intval($fullPrice));
				Configuration::updateValue('PRODUCT_ATTRIB_NC_DISPLAY_WEIGHT', intval($displayWeight));
				Configuration::updateValue('PRODUCT_ATTRIB_NC_DISPLAY_IMAGE', intval($displayImage));
				Configuration::updateValue('PRODUCT_ATTRIB_NC_DISPLAY_DESC', intval($displayDescription));
				$output .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" /> '.$this->l('Settings updated').'</div>';
			}
		}
		return $output.$this->displayForm();
	}

	public function displayForm()
	{
		return '
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
			<fieldset>
				<legend><a href="http://www.nethercottconstructions.com"><img src="'.$this->_path.'logo.gif" alt="" title="" /></a>'.$this->l('Settings').'</legend>
				<label>'.$this->l('Price format').'</label>

				<div class="margin-form">
					<select name="priceFormat" id="priceFormat">
						<option value="0" '.(Configuration::get('PRODUCT_ATTRIB_NC_PRICE_FORMAT') == 0 ? 'selected' : '').'>'.$this->l('None').'</option>
						<option value="1" '.(Configuration::get('PRODUCT_ATTRIB_NC_PRICE_FORMAT') == 1 ? 'selected' : '').'>'.$this->l('Tax included').'</option>
						<option value="2" '.(Configuration::get('PRODUCT_ATTRIB_NC_PRICE_FORMAT') == 2 ? 'selected' : '').'>'.$this->l('Tax excluded').'</option>
						<option value="3" '.(Configuration::get('PRODUCT_ATTRIB_NC_PRICE_FORMAT') == 3 ? 'selected' : '').'>'.$this->l('Both').'</option>
					</select>								
					<p class="clear">'.$this->l('Whether to include tax in the price or display the price at all').'</p>

				</div>
				
				<label>'.$this->l('Hide zero differences').'</label>

				<div class="margin-form">
					<input type="radio" name="hideZeros" id="hideZeros_on" value="1" '.(Tools::getValue('hideZeros', Configuration::get('PRODUCT_ATTRIB_NC_HIDE_ZEROS')) ? 'checked="checked" ' : '').'/>
					<label class="t" for="hideZeros_on"> <img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" /></label>
					<input type="radio" name="hideZeros" id="hideZeros_off" value="0" '.(!Tools::getValue('hideZeros', Configuration::get('PRODUCT_ATTRIB_NC_HIDE_ZEROS')) ? 'checked="checked" ' : '').'/>
					<label class="t" for="hideZeros_off"> <img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" /></label>
					<p class="clear">'.$this->l('Hide price when there is no price difference').'</p>					
				</div>

				<label>'.$this->l('Display full price/weight').'</label>

				<div class="margin-form">
					<input type="radio" name="fullPrice" id="fullPrice_on" value="1" '.(Tools::getValue('fullPrice', Configuration::get('PRODUCT_ATTRIB_NC_FULL_PRICE')) ? 'checked="checked" ' : '').'/>
					<label class="t" for="fullPrice_on"> <img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" /></label>
					<input type="radio" name="fullPrice" id="fullPrice_off" value="0" '.(!Tools::getValue('fullPrice', Configuration::get('PRODUCT_ATTRIB_NC_FULL_PRICE')) ? 'checked="checked" ' : '').'/>
					<label class="t" for="fullPrice_off"> <img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" /></label>
					<p class="clear">'.$this->l('Display the full price and weight instead of just differences').'</p>					
				</div>

				<label>'.$this->l('Display weight').'</label>

				<div class="margin-form">
					<input type="radio" name="displayWeight" id="displayWeight_on" value="1" '.(Tools::getValue('displayWeight', Configuration::get('PRODUCT_ATTRIB_NC_DISPLAY_WEIGHT')) ? 'checked="checked" ' : '').'/>
					<label class="t" for="displayWeight_on"> <img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" /></label>
					<input type="radio" name="displayWeight" id="displayWeight_off" value="0" '.(!Tools::getValue('displayWeight', Configuration::get('PRODUCT_ATTRIB_NC_DISPLAY_WEIGHT')) ? 'checked="checked" ' : '').'/>
					<label class="t" for="displayWeight_off"> <img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" /></label>
					<p class="clear">'.$this->l('Display the weight impact of attributes').'</p>					
				</div>

				<label>'.$this->l('Display image').'</label>

				<div class="margin-form">
					<input type="radio" name="displayImage" id="displayImage_on" value="1" '.(Tools::getValue('displayImage', Configuration::get('PRODUCT_ATTRIB_NC_DISPLAY_IMAGE')) ? 'checked="checked" ' : '').'/>
					<label class="t" for="displayImage_on"> <img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" /></label>
					<input type="radio" name="displayImage" id="displayImage_off" value="0" '.(!Tools::getValue('displayImage', Configuration::get('PRODUCT_ATTRIB_NC_DISPLAY_IMAGE')) ? 'checked="checked" ' : '').'/>
					<label class="t" for="displayImage_off"> <img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" /></label>
					<p class="clear">'.$this->l('Display the attribute textures as images for the attributes').'</p>					
				</div>

				<label>'.$this->l('Display description').'</label>

				<div class="margin-form">
					<input type="radio" name="displayDescription" id="displayDescription_on" value="1" '.(Tools::getValue('displayDescription', Configuration::get('PRODUCT_ATTRIB_NC_DISPLAY_DESC')) ? 'checked="checked" ' : '').'/>
					<label class="t" for="displayDescription_on"> <img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" /></label>
					<input type="radio" name="displayDescription" id="displayDescription_off" value="0" '.(!Tools::getValue('displayDescription', Configuration::get('PRODUCT_ATTRIB_NC_DISPLAY_DESC')) ? 'checked="checked" ' : '').'/>
					<label class="t" for="displayDescription_off"> <img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" /></label>
					<p class="clear">'.$this->l('Display the descripion of attributes').'</p>					
				</div>

			<center><input type="submit" name="submitProductAttributesNC" value="'.$this->l('Save').'" class="button" /></center>
			</fieldset>
		</form>';
	}	

	function hookProductFooter($params)
	{
		global $smarty, $cookie;

		$priceFormat = Configuration::get('PRODUCT_ATTRIB_NC_PRICE_FORMAT');
		$fullPrice = Configuration::get('PRODUCT_ATTRIB_NC_FULL_PRICE');
		
		$product = new Product(intval($_GET['id_product']), true, intval($cookie->id_lang));

		if ($priceFormat > 1)
		{
			// Tax
			$tax_datas = Db::getInstance()->getRow('
			SELECT p.`id_tax`, t.`rate`
			FROM `'._DB_PREFIX_.'product` p
			LEFT JOIN `'._DB_PREFIX_.'tax` AS t ON t.`id_tax` = p.`id_tax`
			WHERE p.`id_product` = '.intval($product->id));
			$tax = floatval(Tax::getApplicableTax(intval($tax_datas['id_tax']), floatval($tax_datas['rate'])));
		}

		/* Attributes / Groups & colors */
		$colors = array();
		$attributesGroups = Db::getInstance()->ExecuteS('
			SELECT ag.`id_attribute_group`, agl.`name` AS group_name, agl.`public_name` AS public_group_name, a.`id_attribute`, al.`name` AS attribute_name,
			a.`color` AS attribute_color, pa.`id_product_attribute`, pa.`quantity`, pa.`price`, pa.`ecotax`, pa.`weight`, pa.`default_on`, pa.`reference`
			FROM `'._DB_PREFIX_.'product_attribute` pa
			LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac ON pac.`id_product_attribute` = pa.`id_product_attribute`
			LEFT JOIN `'._DB_PREFIX_.'attribute` a ON a.`id_attribute` = pac.`id_attribute`
			LEFT JOIN `'._DB_PREFIX_.'attribute_group` ag ON ag.`id_attribute_group` = a.`id_attribute_group`
			LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON a.`id_attribute` = al.`id_attribute`
			LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl ON ag.`id_attribute_group` = agl.`id_attribute_group`
			WHERE pa.`id_product` = '.intval($product->id).'
			AND al.`id_lang` = '.intval($cookie->id_lang).'
			AND agl.`id_lang` = '.intval($cookie->id_lang).'
			ORDER BY agl.`public_name`');		

		if (Db::getInstance()->numRows())
		{
			foreach ($attributesGroups AS $k => $row)
			{
				/* Color management */
				if (isset($row['attribute_color']) AND $row['attribute_color'] AND $row['id_attribute_group'] == $product->id_color_default)
				{
					$colors[$row['id_attribute']]['value'] = $row['attribute_color'];
					$colors[$row['id_attribute']]['name'] = $row['attribute_name'];
				}

				$groups[$row['id_attribute_group']]['attributes'][$row['id_attribute']] = $row['attribute_name'];
				
				if (strpos($row['public_group_name'], '|') !== FALSE)
				{
					$label = explode('|', $row['public_group_name'], 2);
					$groups[$row['id_attribute_group']]['label'] = $label[0];
					$groups[$row['id_attribute_group']]['name'] = $label[1];
				}
				else
					$groups[$row['id_attribute_group']]['name'] = $row['public_group_name'];
				if ($row['default_on'])
					$groups[$row['id_attribute_group']]['default'] = intval($row['id_attribute']);
				if (!isset($groups[$row['id_attribute_group']]['attributes_quantity'][$row['id_attribute']]))
					$groups[$row['id_attribute_group']]['attributes_quantity'][$row['id_attribute']] = 0;
				$groups[$row['id_attribute_group']]['attributes_quantity'][$row['id_attribute']] += intval($row['quantity']);
			}
			
			$smarty->assign(array(
				'col_img_dir' => _PS_COL_IMG_DIR_,								  
				'reductionPrice' => $product->reduction_price,
				'reductionPercent' => $product->reduction_percent,
				'reductionFrom' => $product->reduction_from,
				'reductionTo' => $product->reduction_to,
				'groupReduction' => (100 - Group::getReduction(intval($cookie->id_customer))) / 100,				
				'groups' => $groups,
				'attributeImpacts' => Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'attribute_impact` WHERE `id_product` = ' . intval($_GET['id_product'])),
				'fullPrice' => $fullPrice,
				'price' => $fullPrice ? number_format($product->price * (1 + ($tax * 0.01)), 2, '.', '') : 0,
				'priceFormat' => $priceFormat,
				'hideZeros' => Configuration::get('PRODUCT_ATTRIB_NC_HIDE_ZEROS'),
				'weight' => $product->weight,
				'weightUnit' => Configuration::get('PS_WEIGHT_UNIT'),
				'displayWeight' => Configuration::get('PRODUCT_ATTRIB_NC_DISPLAY_WEIGHT'),
				'displayImage' => Configuration::get('PRODUCT_ATTRIB_NC_DISPLAY_IMAGE'),
				'displayDescription' => Configuration::get('PRODUCT_ATTRIB_NC_DISPLAY_DESC')));
			
			if ($priceFormat > 1)
				$smarty->assign(array('tax' => 1 - $tax / 100));
		}
		return $this->display(__FILE__, 'productattributesnc.tpl');
	}
	
	function hookHeader($params)
	{
		return $this->display(__FILE__, 'productattributesnc-header.tpl');
	}
	
	function hookProductTab($params)
	{
		return $this->display(__FILE__, 'productattributesnc-tab.tpl');
	}
	
	function hookProductTabContent($params)
	{
		return $this->display(__FILE__, 'productattributesnc-tabcontent.tpl');
	}	
}