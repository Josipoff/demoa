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

<!-- Block manufacturers module -->
<div id="manufacturers_block_left" class="block blockmanufacturer">
	
	<div class="block_content list-block">
		{if $manufacturers}
			{if $text_list}
			<ul>
                            {$i=0}
				{foreach from=$manufacturers item=manufacturer name=manufacturer_list}
					{if $manufacturer.type == 1 && $i<6}
					<li class="{if $smarty.foreach.manufacturer_list.last}last_item{elseif $smarty.foreach.manufacturer_list.first}first_item{else}item{/if}">
						<a target="_blank"
						href="{$manufacturer.url|escape:'html':'UTF-8'}" title="{l s='More about %s' mod='blockmanufacturer' sprintf=[$manufacturer.name]}">
							 <img src="{$img_manu_dir}{$manufacturer.id_manufacturer}.jpg" 
                                                              width="150" height="80" title="{$manufacturer.name|escape:'html':'UTF-8'}" />
						</a>
					</li>
                                        {$i = $i+1}
					{/if}
				{/foreach}
			</ul>
			{/if}
			
		{else}
			<p>{l s='No manufacturer' mod='blockmanufacturer'}</p>
		{/if}
	</div>
</div>
<!-- /Block manufacturers module -->
