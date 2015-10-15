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
                <td></td> 
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
                {$deco_c=0}
                <input type="hidden" id="textField{$deco_c}" />
                {$deco_c = $deco_c+1}
                <input type="hidden" id="textField{$deco_c}" />
            {foreach from=$decoration item=decoration_details}
                {$count = $count+1}
            <tr>
                
                 <td>
                    <div class="checkbox">
                        <input class="calc_input" type="checkbox" name="deco_1" id="deco_{$count}" value="{$count}" {if $decoration_details->IsDefault} checked="checked"{/if} />
                    </div>
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
                <td><button class="btn-xs btn btn-danger deco_add" disabled="true" deco_id="{$count}" id="add_{$count}">Add</span></button></td>
                {$deco_c = $deco_c+1}
                <input type="hidden" id="textField{$deco_c}" />
                {$deco_c = $deco_c+1}
                <input type="hidden" id="textField{$deco_c}" />
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
            <h5 class="page-product-heading">{l s='Decoration Added to product'}</h5>
            <div class="deco-block">            
            </div>
        </section>
            <input type="hidden" id="decoration_added" value="" />
<div style="display:none">	
    <div id="file_preview" class="upload_file_preview">
        <img id='image_upload_preview' src='' />
    </div>
                        </div>
<!-- /Product Custom Quote module -->
