{*
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
{if $product['customizedDatas']}
{* Assign product price *}
{if ($order->getTaxCalculationMethod() == $smarty.const.PS_TAX_EXC)}
	{assign var=product_price value=($product['unit_price_tax_excl'] + $product['ecotax'])}
{else}
	{assign var=product_price value=$product['unit_price_tax_incl']}
{/if}
	<tr class="customized customized-{$product['id_order_detail']|intval} product-line-row">
		<td>
			<input type="hidden" class="edit_product_id_order_detail" value="{$product['id_order_detail']|intval}" />
			{if isset($product['image']) && $product['image']->id|intval}{$product['image_tag']}{else}--{/if}
		</td>
		<td>
			<a href="index.php?controller=adminproducts&amp;id_product={$product['product_id']|intval}&amp;updateproduct&amp;token={getAdminToken tab='AdminProducts'}">
			<span class="productName">{$product['product_name']} - {l s='Customized'}</span><br />
			{if ($product['product_reference'])}{l s='Reference number:'} {$product['product_reference']}<br />{/if}
			{if ($product['product_supplier_reference'])}{l s='Supplier reference:'} {$product['product_supplier_reference']}{/if}
			</a>
		</td>
		<td>
			<span class="product_price_show">{displayPrice price=$product_price currency=$currency->id|intval}</span>
			{if $can_edit}
			<div class="product_price_edit" style="display:none;">
				<input type="hidden" name="product_id_order_detail" class="edit_product_id_order_detail" value="{$product['id_order_detail']|intval}" />
				<div class="form-group">
					<div class="fixed-width-xl">
						<div class="input-group">
							{if $currency->format % 2}<div class="input-group-addon">{$currency->sign} {l s='tax excl.'}</div>{/if}
							<input type="text" name="product_price_tax_excl" class="edit_product_price_tax_excl edit_product_price" value="{Tools::ps_round($product['unit_price_tax_excl'], 2)}" size="5" />
							{if !$currency->format % 2}<div class="input-group-addon">{$currency->sign} {l s='tax excl.'}</div>{/if}
						</div>
					</div>
					<br/>
					<div class="fixed-width-xl">				
						<div class="input-group">
							{if $currency->format % 2}<div class="input-group-addon">{$currency->sign} {l s='tax incl.'}</div>{/if}
							<input type="text" name="product_price_tax_incl" class="edit_product_price_tax_incl edit_product_price" value="{Tools::ps_round($product['unit_price_tax_incl'], 2)}" size="5" /> 
							{if !$currency->format % 2}<div class="input-group-addon">{$currency->sign} {l s='tax incl.'}</div>{/if}
						</div>
					</div>
				</div>
			</div>
			{/if}
		</td>
		<td class="productQuantity">{$product['customizationQuantityTotal']}</td>
		{if $display_warehouse}<td>&nbsp;</td>{/if}
		{if ($order->hasBeenPaid())}<td class="productQuantity">{$product['customizationQuantityRefunded']}</td>{/if}
		{if ($order->hasBeenDelivered() || $order->hasProductReturned())}<td class="productQuantity">{$product['customizationQuantityReturned']}</td>{/if}
		{if $stock_management}<td class="">{$product['current_stock']}</td>{/if}
		<td class="total_product">
		{if ($order->getTaxCalculationMethod() == $smarty.const.PS_TAX_EXC)}
			{displayPrice price=Tools::ps_round($product['product_price'] * $product['customizationQuantityTotal'], 2) currency=$currency->id|intval}
		{else}
			{displayPrice price=Tools::ps_round($product['product_price_wt'] * $product['customizationQuantityTotal'], 2) currency=$currency->id|intval}
		{/if}
		</td>
		<td class="cancelQuantity standard_refund_fields current-edit" style="display:none" colspan="2">
			&nbsp;
		</td>
		<td class="edit_product_fields" colspan="2" style="display:none">&nbsp;</td>
		<td class="partial_refund_fields current-edit" style="text-align:left;display:none;"></td>
		{if ($can_edit && !$order->hasBeenDelivered())}
			<td class="product_action text-right">
				{* edit/delete controls *}
				<div class="btn-group">
					<button type="button" class="btn btn-default edit_product_change_link">
						<i class="icon-pencil"></i>
						{l s='Edit'}
					</button>
					<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
						<span class="caret"></span>
					</button>
					<ul class="dropdown-menu" role="menu">
						<li>
							<a href="#" class="delete_product_line">
								<i class="icon-trash"></i>
								{l s='Delete'}
							</a>
						</li>
					</ul>
				</div>
				{* Update controls *}
				<button type="button" class="btn btn-default submitProductChange" style="display: none;">
					<i class="icon-ok"></i>
					{l s='Update'}
				</button>
				<button type="button" class="btn btn-default cancel_product_change_link" style="display: none;">
					<i class="icon-remove"></i>
					{l s='Cancel'}
				</button>
			</td>
		{/if}
	</tr>
	{foreach $product['customizedDatas'] as $customizationPerAddress}
		{foreach $customizationPerAddress as $customizationId => $customization}
			 {$i=0}
                                                    {$row=0}
                                                    {$deco_data=$customization.datas[1]}
                                                         {foreach $deco_data as $data1}
                                                             {$row = $row+1}
                                                             {if $row > 2}
                                                       {if $i==0}     
                                                            {$col1=$data1.value}
                                                            {$i=$i+1}
                                                            {$row=$row+1}
                                                            {continue}
                                                        {/if}
                                                        {if $i==1}
                                                        {assign var=col1_info value=";"|explode:$col1}
                                                        {$col2=$data1.value}
                                                        {assign var=col2_info value=";"|explode:$col2}
                                                        {$inc=0}
                                                        {foreach $col1_info as $col1}
                                                          {assign var=col1_data value="-"|explode:$col1}
                                                      <tr class="customized customized-{$product['id_order_detail']|intval}">
                                                          <td> 
                                                              {$count=0}
                                                            {$file_id=''}
                                        {foreach $files['toproduct'] as $file}
                                              {if $file['deco_key']==$col1_data[1]}
                                                  <a class="upload_file_preview" id='upload_file_preview' 
                                                     filename='{$content_dir}/modules/orderfiles/productfiles/{$file['cookieid']}/{$file['filename']}'
                                                     href='#file_preview'>
                                                     <img class="file-thumbnail imgm img-thumbnail" alt="" src="{$content_dir}modules/orderfiles/productfiles/{$file['cookieid']}/{$file['filename']}" />
                                                  </a>{if $file['description']!=''}
                                                    <br />
                                                    <span style="font-weight:300">{l s='Description'}: {$file['description']}</span>
                                                    {/if}
                                                    <br />
                                                   <a target="_blank" href="{$content_dir}modules/orderfiles/download.php?t=productfiles&opt={$file['cookieid']}&f={$file['filename']}" vdh-1814015026="">
                                Download
                           </a>
                                                  {$file_id=$file['id']}
                                                  {$count=1}
                                                  {break}
                                              {/if}
                                          {/foreach}        
                                          <br />
                        <a class='upload_image_link' href='#' file_id='{$file_id}' product_id='{$product['product_id']}'
                           deco_key='{$col1_data[1]}'>Upload New Decoration</a>
                          
                           
                           
                      </td>
				<input type="hidden" class="edit_product_id_order_detail" value="{$product['id_order_detail']|intval}" />
					<div class="form-horizontal">
								<td colspan="4">
                                                                        {$col1_data[0]}
								</td>
                                                                <td></td>
                                                                <td class="cart_total">
                                                                        <span class="price">${$col2_info[$inc]}</span>
                                                                        {$inc = $inc+1}
                                                                </td>
                                                                {/foreach}
                                                                {$i=0}
                                                             {/if}
					</div>
			</tr>
                                                        {/if}{/foreach}
		{/foreach}
	{/foreach}
{/if}