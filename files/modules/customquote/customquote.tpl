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
*  International Registred Trademark & Property of PrestaShop SA
*}

<div class="row">
    <div class="col-md-5">
        <p class="custom-quote-headding">Tu Precio de Compra</p>
        <table class="table-data-sheet custom_quote_table">
            <tr>
                <td>{l s='Cantidad' mod='customquote'}</td>
                <td></td>
                <td><input id="quan" value="1" type="text" class="form-control calc_input custom_quote_input" /></td>
            </tr>
            <tr>
                <td></td>
                <td><b>{l s='x Pieza' mod='customquote'}</b></td>
                <td><b>{l s='Importe' mod='customquote'}</b></td>
            </tr>
            <tr>
                <td>{l s='Precio del producto' mod='customquote'}</td>
                <td><span id="psingle"></span></td>
                <td><span id="pprice"></span></td>
            </tr>
            <tr>
                <td>{l s='Cargo de Setup' mod='customquote'}</td>
                <td><span id="setup_single"></span></td>
                <td><span id="psetup"></span></td>
                <td></td>
            </tr>
            <tr>
                <td>{l s='Servicious de Impresion' mod='customquote'}</td>
                <td><span id="pdeco_single"></span></td>
                <td><span id="pdeco"></span></td>
            </tr>
            <tr>
                <td>{l s='Precio Unitario/Importe' mod='customquote'}</td>
                <td><span id="tsingle"></span></td>
                <td><span id="total"></span></td>
            </tr>
        </table>
    </div>
    
    
     <div class="col-md-5" style="display:none" >
         <div class="row">
             <div class="col-md-2"></div>
              <div class="radio-inline">
                    <label for="id_custom_1" class="top">
                            <input type="radio" name="id_margin_option" id="id_custom_1" value="1" />
                    {l s='Sobre Precio de Venta' mod='customquote'}
                    </label>
            </div>
              <div class="radio-inline">
                    <label for="id_custom_2" class="top">
                            <input type="radio" checked="checked" name="id_margin_option" id="id_custom_2" value="2" />
                    {l s='Sobre Precio de Compra' mod='customquote'}
                    </label>
            </div>
         </div>
                    <table class="table-data-sheet custom_quote_table">
            <tr>
                <td>% {l s='Margen' mod='customquote'}:</td>
                <td></td>
                <td><input id="margin_val" type="text" value="0" class="form-control custom_quote_input" />%
                    <span class="help">?</span></td>
            </tr>
            <tr>
                <td></td>
                <td><b>{l s='x Pieza' mod='customquote'}</b></td>
                <td><b>{l s='Importe' mod='customquote'}</b></td>
            </tr>
            <tr>
                <td>{l s='Costo / x1 Pza' mod='customquote'}</td>
                <td><span id="mprice"></span></td>
                <td><span id="mprice_tot"></span></td>
            </tr>
            <tr>
                <td>{l s='Precio + Margen' mod='customquote'} %</td>
                <td><span id="mprice_margin"></span></td>
                <td><span id="mprice_margin_tot"></span></td>
            </tr>
            <tr>
                <td>$ {l s='Ganancia' mod='customquote'}</td>
                <td><span id="margin"></span></td>
                <td><span id="mtotal"></span></td>
            </tr>
        </table>
    </div>
</div>

<div class="row">
    <div class="col-md-10">
        <p class="custom-quote-headding">{l s='Selecciona el decorado que deseas aplicar al total de los productos eligidos. Este debe ser identico
        para todos los colores.' mod='customquote'}</p>
    </div>
</div>
{$decoration = json_decode($product->decoration_details)}
<div class="row">
    <div class="col-md-12 quote-deco-table">
         <table class="table-data-sheet custom_deco_table">
            <tr>
                <td></td> <td></td>
                <td>{l s='Area' mod='customquote'}</td>
                <td>{l s='Tecnica' mod='customquote'}</td>
                <td>{l s='Superficia' mod='customquote'}</td>
                <td>{l s='Tintas' mod='customquote'}</td>
                <td>{l s='Total de Servicios' mod='customquote'}</td>
                <td># {l s='Total de Impresiones' mod='customquote'}</td>
                <td></td>
            </tr>
            {$count=0}
            {if (!isset($decoration->areasimp->ItemNumber))}
                    {$decoration=$decoration->areasimp}
                {/if}
            {foreach from=$decoration item=decoration_details}
                {$count = $count+1}
            <tr>
                
                 <td>
                    <div class="checkbox">
                        <input class="calc_input" type="checkbox" name="deco_1" id="deco_{$count}" value="{$count}" {if $decoration_details->IsDefault} checked="checked"{/if} />
                    </div>
                </td>
                
                <td>
                    {literal}
    <script type="text/javascript">
        var _validFileExtensions = [{/literal}{foreach from=$extensions item=ext name=loop}".{$ext}"{if !$smarty.foreach.loop.last}, {/if}{/foreach}{literal}];
        function Validate(oForm) {
            var arrInputs = oForm.getElementsByTagName("input");
            for (var i = 0; i < arrInputs.length; i++) {
                var oInput = arrInputs[i];
                if (oInput.type == "file") {
                    var sFileName = oInput.value;
                    if (sFileName.length > 0) {
                        var blnValid = false;
                        for (var j = 0; j < _validFileExtensions.length; j++) {
                            var sCurExtension = _validFileExtensions[j];
                            if (sFileName.substr(sFileName.length - sCurExtension.length, sCurExtension.length).toLowerCase() == sCurExtension.toLowerCase()) {
                                blnValid = true;
                                break;
                            }
                        }
        
                        if (!blnValid) {
                            alert("{/literal}{l s='Sorry, you can not upload this file: '}{literal}" + sFileName + "{/literal} {l s='Supported filetypes: '}{literal}" + _validFileExtensions.join(", "));
                            return false;
                        }
                    }
                }
            }
        
            return true;
        }
        </script>
    {/literal}
                    
    <form method="post" style="width:100%;" enctype="multipart/form-data" 
                           onsubmit="return Validate(this);" id="upload_file_{$count}" class='add_deco'>
                    <input name="idproduct" value="{$idproduct}" type="hidden"/>
                    <input type="hidden" name="upload_new_file_product" value="1"/>
                     {assign var=unique_id value=10|mt_rand:200000}
                    <input id="deco_key_{$count}" type="hidden" name="deco_key" value="{$unique_id}"/>
                    <input deco_id="{$count}" type="file" name="file[]" multiple="multiple" />
                   </form>
                </td>
                
                <td><span id="area_{$count}">{$decoration_details->Area}</span></td>
                <td><span id="tec_{$count}">{$decoration_details->TecnicaFull}</span></td>
                <td><span id="size_{$count}">{$decoration_details->Tamano} mm</span></td>
                <td><select class="calc_input" id="color_{$count}">
                    {for $i=1 to $decoration_details->TintasMax}
                        <option value="{$i}">{$i}</option>
                    {/for}</select></td>
                <td><span id="tdeco_{$count}"></span></td>
                <td><span id="print_{$count}"></span></td>
                <input id="tecnica_{$count}" type="hidden" value={$decoration_details->Tecnica} />
                <td><button class="btn-xs btn btn-danger deco_add" disabled="true" deco_id="{$count}" id="add_{$count}">Add</span></td>
                
            </tr>
            {/foreach}
        </table>
                <input id="max_deco" type="hidden" value={$count} />
                <input id="item_number" type="hidden" value={$product->reference} />
    </div>
    <div id="layered_ajax_loader" style="display: none;">
		<p id='ajax-loader'><img src="{$img_ps_dir}loader.gif" alt="" /><br />{l s='Loading...' mod='customquote'}</p>
	</div>
</div>
         <section id="product-decoration" class="page-product-box">
            <h3 class="page-product-heading">{l s='Decoration Added to product'}</h3>
            <div class="deco-block">            
            <table class="table-data-sheet deco_product_table">
                {counter start=0 assign='customizationField'}
                {$i=0}
                {$row=0}
                {foreach from=$customizationFields item='field' name='customizationFields'}
                    {if $i>1}
                        {if $field.type == 1}
                            {assign var='key' value='textFields_'|cat:$product->id|cat:'_'|cat:$field.id_customization_field}
                            {if isset($textFields.$key)} 
                                {if $row == 0}
                                    {$col1=$textFields.$key}
                                    {$i=$i+1}
                                    {$row=$row+1}
                                    {continue}
                                {/if}
                                {if $row==1}
                                {assign var=col1_info value=";"|explode:$col1}
                                {$col2=$textFields.$key}
                                {assign var=col2_info value=";"|explode:$col2}
                                {$inc=0}
                                {foreach $col1_info as $col1}
                                  {assign var=col1_data value="-"|explode:$col1}
                                <tr>
                                        <td>{foreach $files as $file}
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
                                        {$row=0}
                                </tr>
                                {/if}
                            {/if}
                            {counter}
                        {/if}
                     {/if}
                     {$i = $i+1}
                {/foreach}                
            </table>
        </section>
        <div class="add_deco_cart decorated_btn">
            <a class="btn btn-danger" id='deco_cart_btn'><span>Add to Cart</span></a>
        </div>
<div style="display:none">	
    <div id="file_preview" class="upload_file_preview">
        <img id='image_upload_preview' src='' />
    </div>
                        </div>
<!-- /Product Custom Quote module -->
