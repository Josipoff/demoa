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
{if !$opc}
	{addJsDef currencySign=$currencySign|html_entity_decode:2:"UTF-8"}
	{addJsDef currencyRate=$currencyRate|floatval}
	{addJsDef currencyFormat=$currencyFormat|intval}
	{addJsDef currencyBlank=$currencyBlank|intval}
	{addJsDefL name=txtProduct}{l s='product' js=1}{/addJsDefL}
	{addJsDefL name=txtProducts}{l s='products' js=1}{/addJsDefL}
	{capture name=path}{l s='Your payment method'}{/capture}
	<h1 class="page-heading">{l s='Please choose your payment method'}</h1>
{else}
	<h1 class="page-heading step-num"><span>3</span> {l s='Please choose your payment method'}</h1>
{/if}

{if !$opc}
	{assign var='current_step' value='payment'}
	{include file="$tpl_dir./order-steps.tpl"}
	{include file="$tpl_dir./errors.tpl"}
{else}
	<div id="opc_payment_methods" class="opc-main-block">
		<div id="opc_payment_methods-overlay" class="opc-overlay" style="display: none;"></div>
{/if}
		<div class="paiement_block">
			<div id="HOOK_TOP_PAYMENT">{$HOOK_TOP_PAYMENT}</div>
				{if $HOOK_PAYMENT}
					{if !$opc}
						<div id="order-detail-content" class="table_block table-responsive">
							<table id="cart_summary" class="table table-bordered">
								<thead>
									<tr>
										<th class="cart_product first_item">{l s='Product'}</th>
										<th class="cart_description item">{l s='Description'}</th>
										{if $PS_STOCK_MANAGEMENT}
											<th class="cart_availability item">{l s='Avail.'}</th>
										{/if}
										<th class="cart_unit item">{l s='Unit price'}</th>
										<th class="cart_quantity item">{l s='Qty'}</th>
										<th class="cart_total last_item">{l s='Total'}</th>
									</tr>
								</thead>
								<tfoot>
									{if $use_taxes}
										{if $priceDisplay}
											<tr class="cart_total_price">
												<td colspan="4" class="text-right">{if $display_tax_label}{l s='Total products (tax excl.)'}{else}{l s='Total products'}{/if}</td>
												<td colspan="2" class="price" id="total_product">{displayPrice price=$total_products}</td>
											</tr>
										{else}
											<tr class="cart_total_price">
												<td colspan="4" class="text-right">{if $display_tax_label}{l s='Total products (tax incl.)'}{else}{l s='Total products'}{/if}</td>
												<td colspan="2" class="price" id="total_product">{displayPrice price=$total_products_wt}</td>
											</tr>
										{/if}
									{else}
										<tr class="cart_total_price">
											<td colspan="4" class="text-right">{l s='Total products'}</td>
											<td colspan="2" class="price" id="total_product">{displayPrice price=$total_products}</td>
										</tr>
									{/if}
									<tr class="cart_total_voucher" {if $total_wrapping == 0}style="display:none"{/if}>
										<td colspan="4" class="text-right">
											{if $use_taxes}
												{if $priceDisplay}
													{if $display_tax_label}{l s='Total gift wrapping (tax excl.):'}{else}{l s='Total gift wrapping cost:'}{/if}
												{else}
													{if $display_tax_label}{l s='Total gift wrapping (tax incl.)'}{else}{l s='Total gift wrapping cost:'}{/if}
												{/if}
											{else}
												{l s='Total gift wrapping cost:'}
											{/if}
										</td>
										<td colspan="2" class="price-discount price" id="total_wrapping">
											{if $use_taxes}
												{if $priceDisplay}
													{displayPrice price=$total_wrapping_tax_exc}
												{else}
													{displayPrice price=$total_wrapping}
												{/if}
											{else}
												{displayPrice price=$total_wrapping_tax_exc}
											{/if}
										</td>
									</tr>
									{if $total_shipping_tax_exc <= 0 && !isset($virtualCart)}
										<tr class="cart_total_delivery">
											<td colspan="4" class="text-right">{l s='Shipping:'}</td>
											<td colspan="2" class="price" id="total_shipping">{l s='Free Shipping!'}</td>
										</tr>
									{else}
										{if $use_taxes && $total_shipping_tax_exc != $total_shipping}
											{if $priceDisplay}
												<tr class="cart_total_delivery" {if $shippingCost <= 0} style="display:none"{/if}>
													<td colspan="4" class="text-right">{if $display_tax_label}{l s='Total shipping (tax excl.)'}{else}{l s='Total shipping'}{/if}</td>
													<td colspan="2" class="price" id="total_shipping">{displayPrice price=$shippingCostTaxExc}</td>
												</tr>
											{else}
												<tr class="cart_total_delivery"{if $shippingCost <= 0} style="display:none"{/if}>
													<td colspan="4" class="text-right">{if $display_tax_label}{l s='Total shipping (tax incl.)'}{else}{l s='Total shipping'}{/if}</td>
													<td colspan="2" class="price" id="total_shipping" >{displayPrice price=$shippingCost}</td>
												</tr>
											{/if}
										{else}
											<tr class="cart_total_delivery"{if $shippingCost <= 0} style="display:none"{/if}>
												<td colspan="4" class="text-right">{l s='Total shipping'}</td>
												<td colspan="2" class="price" id="total_shipping" >{displayPrice price=$shippingCostTaxExc}</td>
											</tr>
										{/if}
									{/if}
									<tr class="cart_total_voucher" {if $total_discounts == 0}style="display:none"{/if}>
										<td colspan="4" class="text-right">
											{if $use_taxes}
												{if $priceDisplay}
													{if $display_tax_label}{l s='Total vouchers (tax excl.)'}{else}{l s='Total vouchers'}{/if}
												{else}
													{if $display_tax_label}{l s='Total vouchers (tax incl.)'}{else}{l s='Total vouchers'}{/if}
												{/if}
											{else}
												{l s='Total vouchers'}
											{/if}
										</td>
										<td colspan="2" class="price-discount price" id="total_discount">
											{if $use_taxes}
												{if $priceDisplay}
													{displayPrice price=$total_discounts_tax_exc*-1}
												{else}
													{displayPrice price=$total_discounts*-1}
												{/if}
											{else}
												{displayPrice price=$total_discounts_tax_exc*-1}
											{/if}
										</td>
									</tr>
									{if $use_taxes && $show_taxes}
										{if $priceDisplay && $total_tax != 0}
											<tr class="cart_total_tax">
												<td colspan="4" class="text-right">{l s='Tax'}</td>
												<td colspan="2" class="price" id="total_tax" >{displayPrice price=$total_tax}</td>
											</tr>
										{/if}
										<tr class="cart_total_price">
											<td colspan="4" class="total_price_container text-right"><span>{l s='Total'}</span></td>
											<td colspan="2" class="price" id="total_price_container">
												<span id="total_price">{displayPrice price=$total_price}</span>
											</td>
										</tr>
									{else}
									<tr class="cart_total_price">
										{if $voucherAllowed}
											<td colspan="2" id="cart_voucher" class="cart_voucher">
												<div id="cart_voucher" class="table_block">
													{if isset($errors_discount) && $errors_discount}
														<ul class="alert alert-danger">
														{foreach from=$errors_discount key=k item=error}
															<li>{$error|escape:'html':'UTF-8'}</li>
														{/foreach}
														</ul>
													{/if}
													{if $voucherAllowed}
														<form action="{if $opc}{$link->getPageLink('order-opc', true)}{else}{$link->getPageLink('order', true)}{/if}" method="post" id="voucher">
															<fieldset>
																<h4>{l s='Vouchers'}</h4>
																<input type="text" id="discount_name" class="form-control" name="discount_name" value="{if isset($discount_name) && $discount_name}{$discount_name}{/if}" />
																<input type="hidden" name="submitDiscount" />
																<button type="submit" name="submitAddDiscount" class="button btn btn-default button-small"><span>{l s='ok'}</span></button>
																{if $displayVouchers}
																	<p id="title" class="title_offers">{l s='Take advantage of our offers:'}</p>
																	<div id="display_cart_vouchers">
																	{foreach from=$displayVouchers item=voucher}
																		<span onclick="$('#discount_name').val('{$voucher.name}');return false;" class="voucher_name">{$voucher.name}</span> - {$voucher.description} <br />
																	{/foreach}
																	</div>
																{/if}
															</fieldset>
														</form>
													{/if}
												</div>
											</td>
										{/if}
										<td colspan="{if !$voucherAllowed}3{else}2{/if}" class="text-right total_price_container">
											<span>{l s='Total'}</span>
										</td>
										<td colspan="1" class="price total_price_container" id="total_price_container">
											<span id="total_price">{displayPrice price=$total_price_without_tax}</span>
										</td>
									</tr>
									{/if}
								</tfoot>

								<tbody>
									{foreach from=$products item=product name=productLoop}
										{assign var='productId' value=$product.id_product}
										{assign var='productAttributeId' value=$product.id_product_attribute}
										{assign var='quantityDisplayed' value=0}
										{assign var='cannotModify' value=1}
										{assign var='odd' value=$product@iteration%2}
										{assign var='noDeleteButton' value=1}

										{* Display the product line *}
										{include file="$tpl_dir./shopping-cart-product-line.tpl"}

										{* Then the customized datas ones*}
										{if isset($customizedDatas.$productId.$productAttributeId)}
						{foreach $customizedDatas.$productId.$productAttributeId[$product.id_address_delivery] as $id_customization=>$customization}
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
                                                        <tr id="product_{$product.id_product}_{$product.id_product_attribute}_{$id_customization}_{$product.id_address_delivery|intval}"
								class="product_customization_for_{$product.id_product}_{$product.id_product_attribute}_{$product.id_address_delivery|intval}{if $odd} odd{else} even{/if} customization alternate_item {if $product@last && $customization@last && !count($gift_products)}last_item{/if}">
								<td>{foreach $files[$product.id_product] as $file}
                                                                    {if $file['deco_key']==$col1_data[1]}
                                                                        <a class="upload_file_preview" id='upload_file_preview' filename='{$shop_path}/modules/orderfiles/productfiles/{$file['cookieid']}/{$file['filename']}'
                                                                           href='#file_preview'>
                                                                            {$file['filename']}
                                                                        </a>
                                                                        {break}
                                                                    {/if}
                                                                {/foreach}</td>
								<td colspan="3">
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
							</tr>
                                                        {/if}
						{/foreach}
							{assign var='quantityDisplayed' value=$quantityDisplayed+$customization.quantity}
						{/foreach}

						{* If it exists also some uncustomized products *}
						{if $product.quantity-$quantityDisplayed > 0}{include file="$tpl_dir./shopping-cart-product-line.tpl" productLast=$product@last productFirst=$product@first}{/if}
					{/if}
                                                              {/foreach}
									{assign var='last_was_odd' value=$product@iteration%2}
									{foreach $gift_products as $product}
										{assign var='productId' value=$product.id_product}
										{assign var='productAttributeId' value=$product.id_product_attribute}
										{assign var='quantityDisplayed' value=0}
										{assign var='odd' value=($product@iteration+$last_was_odd)%2}
										{assign var='ignoreProductLast' value=isset($customizedDatas.$productId.$productAttributeId)}
										{assign var='cannotModify' value=1}
										{* Display the gift product line *}
										{include file="./shopping-cart-product-line.tpl" productLast=$product@last productFirst=$product@first}
									{/foreach}
								</tbody>

								{if count($discounts)}
									<tbody>
										{foreach from=$discounts item=discount name=discountLoop}
											<tr class="cart_discount {if $smarty.foreach.discountLoop.last}last_item{elseif $smarty.foreach.discountLoop.first}first_item{else}item{/if}" id="cart_discount_{$discount.id_discount}">											
												<td class="cart_discount_name" colspan="{if $PS_STOCK_MANAGEMENT}3{else}2{/if}">{$discount.name}</td>
												<td class="cart_discount_price">
													<span class="price-discount">
														{if $discount.value_real > 0}
															{if !$priceDisplay}
																{displayPrice price=$discount.value_real*-1}
															{else}
																{displayPrice price=$discount.value_tax_exc*-1}
															{/if}
														{/if}
													</span>
												</td>
												<td class="cart_discount_delete">1</td>
												<td class="cart_discount_price">
													<span class="price-discount">
														{if $discount.value_real > 0}
															{if !$priceDisplay}
																{displayPrice price=$discount.value_real*-1}
															{else}
																{displayPrice price=$discount.value_tax_exc*-1}
															{/if}
														{/if}
													</span>
												</td>
											</tr>
										{/foreach}
									</tbody>
								{/if}
							</table>
						</div> <!-- end order-detail-content -->
					{/if}
				{else}
				{/if}
				{if !$opc}
         
<form action="{$link->getModuleLink('cashondelivery', 'validation', [], true)|escape:'html'}" method="post">
	<input type="hidden" name="confirm" value="1" />
	
	<p class="cart_navigation" id="cart_navigation">
            <button type="submit" name="processAddress" class="button btn btn-default button-medium">
                    <span>{l s='I confirm my order' mod='cashondelivery'}<i class="icon-chevron-right right"></i></span>
            </button>
        </p>
</form>
					<p class="cart_navigation clearfix">
						<a href="{$link->getPageLink('order', true, NULL, "step=2")|escape:'html':'UTF-8'}" title="{l s='Previous'}" class="button-exclusive btn btn-default">
							<i class="icon-chevron-left"></i>
							{l s='Continue shopping'}
						</a>
					</p>
				{else}
                                    <p>
		<br /><br />
		<br /><br />
		<b>{l s='Please confirm your order by clicking \'I confirm my order\'.' mod='cashondelivery'}</b>
	</p>
					</div> <!-- end opc_payment_methods -->
				{/if}
			</div> <!-- end HOOK_TOP_PAYMENT -->
                            <div style="display:none">	
    <div id="file_preview" class="upload_file_preview">
        <img id='image_upload_preview' src='' />
    </div>
                        </div>