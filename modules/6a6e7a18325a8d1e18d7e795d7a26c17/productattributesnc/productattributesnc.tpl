{if isset($groups)}

<!-- MODULE productattributesnc -->
<div id="product_attributes_block" class="table_block">
	<br />
	<ul class="idTabs">
		<li><a style="cursor: pointer">{l s='Product Options'}</a></li>
	</ul>
   	<br />
	<table class="std">
    	<thead>
            <tr>
            	<th id="radio_column" class="first_item">&nbsp;</th>
                {if $displayDescription}<th id="description_column" class="item">{l s='Description' mod='productattributesnc'}</th>{/if}
                {if $displayImage}<th id="image_column" class="item"{if $displayDescription} style="width: 20%"{/if}>{l s='Image' mod='productattributesnc'}</th>{/if}
                {if $displayWeight}<th id="weight_column" class="item">{l s='Weight' mod='productattributesnc'}</th>{/if}
                {if $priceFormat == 1 OR $priceFormat == 3}<th id="price_wt_column" class="item">{l s='Price Incl. Tax' mod='productattributesnc'}</th>{/if}
                {if $priceFormat > 1}<th id="price_column" class="last_item">{l s='Price Excl. Tax' mod='productattributesnc'}</th>{/if}
            </tr>
        </thead>
        <tbody>
			{foreach from=$groups key=id_attribute_group item=group}
				{assign var='groupName' value='group_'|cat:$id_attribute_group}
                {assign var='checkbox' value='1'}
                {if sizeof($group.attributes) == 2}
					{foreach from=$group.attributes key=id_attribute item=group_attribute name=group_attributes}
                    	{if $group_attribute == 'No'}
                        	{assign var='id_attribute_off' value=`$id_attribute`}
                        {elseif $group_attribute == 'Yes'}
                        	{assign var='id_attribute_on' value=`$id_attribute`}
                        {else}
                        	{assign var='checkbox' value='0'}
                        {/if}
                    {/foreach}
                {else}
                	{assign var='checkbox' value='0'}
                {/if}
                {if $checkbox}
					{if $group.label AND $group.label != $previousLabel}{assign var='previousLabel' value=$group.label}<tr class="group_name"><td colspan="{if $priceFormat == 2}{$displayDescription+$displayImage+$displayWeight+1}{else}{$displayDescription+$displayImage+$displayWeight+$priceFormat}{/if}">{$group.label|escape:'htmlall':'UTF-8'}</td></tr>{/if}
                    <td><input type="checkbox" name="group_{$id_attribute_group}" onclick="javascript:document.getElementById('group_{$id_attribute_group|intval}').value = (document.getElementById('group_{$id_attribute_group|intval}').value == {$id_attribute_on|intval} ? {$id_attribute_off|intval} : {$id_attribute_on|intval}); findCombination();" value="{$id_attribute|intval}" {if (isset($smarty.get.$groupName) && $smarty.get.$groupName|intval == $id_attribute) || $group.default == $id_attribute_on} checked="checked"{/if} /></td>
                    {if $displayDescription}<td>{$group.name|escape:'htmlall':'UTF-8'}</td>{/if}
                    {foreach from=$attributeImpacts key=id_attributeImpact item=attributeImpact}
                        {if $attributeImpact.id_attribute == $id_attribute_on}
		                    {if $displayImage}<td>{if file_exists($col_img_dir|cat:$id_attribute_on|cat:'.jpg')}<img src="{$img_col_dir}{$id_attribute_on}.jpg" alt="" title="{$group.name|escape:'htmlall':'UTF-8'}" />{/if}</td>{/if}                        
                        	{if $displayWeight}<td>{if $hideZeros AND $attributeImpact.weight == 0}&nbsp;{else}{if $fullPrice}{$product.weight+$attributeImpact.weight}{else}{$attributeImpact.weight}{/if} {$weightUnit}{/if}</td>{/if}
                            {if $priceFormat == 1 OR $priceFormat == 3}
                            <td>
								{if ($reductionPrice != 0 OR $reductionPercent != 0) AND ($reductionFrom == $reductionTo OR ($smarty.now|date_format:'%Y-%m-%d' <= $reductionTo AND $smarty.now|date_format:'%Y-%m-%d' >= $reductionFrom))}
                                <span>
									{if $reductionPrice != 0}{if $fullPrice}{convertPrice price=$price+$attributeImpact.price-$reductionPrice}{else}{convertPrice price=$price+$attributeImpact.price}{/if}
                                    {else}{math equation="(p + a) * (1 - (r / 100)) * g" p=$price a=$attributeImpact.price r=$reductionPercent g=$groupReduction assign="reducedPrice"}{convertPrice price=$reducedPrice}{/if}
								</span>&nbsp;{/if}
                                {if !($reductionPrice != 0 AND ($reductionFrom == $reductionTo OR ($smarty.now|date_format:'%Y-%m-%d' <= $reductionTo AND $smarty.now|date_format:'%Y-%m-%d' >= $reductionFrom)) AND !$fullPrice)}                                 
                            	<span{if ($reductionPrice != 0 OR $reductionPercent != 0) AND ($reductionFrom == $reductionTo OR ($smarty.now|date_format:'%Y-%m-%d' <= $reductionTo AND $smarty.now|date_format:'%Y-%m-%d' >= $reductionFrom))} class="strike"{/if}>{if $hideZeros AND $attributeImpact.price == 0}&nbsp;{else}{math equation="(p + a) * g" p=$price a=$attributeImpact.price g=$groupReduction assign="reducedPrice"}{convertPrice price=$reducedPrice}{/if}</span>
                                {/if}
                            </td>
                            {/if}
                            {if $priceFormat > 1}
                            <td>
								{if ($reductionPrice != 0 OR $reductionPercent != 0) AND ($reductionFrom == $reductionTo OR ($smarty.now|date_format:'%Y-%m-%d' <= $reductionTo AND $smarty.now|date_format:'%Y-%m-%d' >= $reductionFrom))}
                                <span>
									{if $reductionPrice != 0}{if $fullPrice}{convertPrice price=$price+$attributeImpact.price-$reductionPrice}{else}{math equation="(p + a) * t" p=$price a=$attributeImpact.price t=$tax assign="reducedPrice"}{convertPrice price=$reducedPrice}{/if}
                                    {else}{math equation="(p + a) * (1 - (r / 100)) * g * t" p=$price a=$attributeImpact.price r=$reductionPercent g=$groupReduction t=$tax assign="reducedPrice"}{convertPrice price=$reducedPrice}{/if}
								</span>&nbsp;{/if}
                                {if !($reductionPrice != 0 AND ($reductionFrom == $reductionTo OR ($smarty.now|date_format:'%Y-%m-%d' <= $reductionTo AND $smarty.now|date_format:'%Y-%m-%d' >= $reductionFrom)) AND !$fullPrice)}                                 
                            	<span{if ($reductionPrice != 0 OR $reductionPercent != 0) AND ($reductionFrom == $reductionTo OR ($smarty.now|date_format:'%Y-%m-%d' <= $reductionTo AND $smarty.now|date_format:'%Y-%m-%d' >= $reductionFrom))} class="strike"{/if}>{if $hideZeros AND $attributeImpact.price == 0}&nbsp;{else}{math equation="(p + a) * g * t" p=$price a=$attributeImpact.price g=$groupReduction t=$tax assign="reducedPrice"}{convertPrice price=$reducedPrice}{/if}</span>
                                {/if}
                            </td>
                            {/if}
                        {/if}
                    {/foreach}
                </tr>
                {else}
                {assign var='previousLabel' value=$group.name}
				<tr class="group_name"><td colspan="{if $priceFormat == 2}{$displayDescription+$displayImage+$displayWeight+1}{else}{$displayDescription+$displayImage+$displayWeight+$priceFormat}{/if}">{$group.name|escape:'htmlall':'UTF-8'}</td></tr>
				{foreach from=$group.attributes key=id_attribute item=group_attribute}
                <tr>
                    <td><input type="radio" name="group_{$id_attribute_group}" onclick="javascript:document.getElementById('group_{$id_attribute_group|intval}').value = {$id_attribute|intval}; findCombination();" value="{$id_attribute|intval}"{if (isset($smarty.get.$groupName) && $smarty.get.$groupName|intval == $id_attribute) || $group.default == $id_attribute} checked="checked"{/if} /></td>
                    {if $displayDescription}<td>{$group_attribute|escape:'htmlall':'UTF-8'}</td>{/if}
                    
                    {foreach from=$attributeImpacts key=id_attributeImpact item=attributeImpact}
                        {if $id_attribute == $attributeImpact.id_attribute}
                        	{if $displayImage}<td>{if file_exists($col_img_dir|cat:$attributeImpact.id_attribute|cat:'.jpg')}<img src="{$img_col_dir}{$id_attribute}.jpg" alt="" title="{$group.name|escape:'htmlall':'UTF-8'}" />{/if}</td>{/if}
                        	{if $displayWeight}<td>{if $hideZeros AND $attributeImpact.weight == 0}&nbsp;{else}{if $fullPrice}{$product.weight+$attributeImpact.weight}{else}{$attributeImpact.weight}{/if} {$weightUnit}{/if}</td>{/if}
                            {if $priceFormat == 1 OR $priceFormat == 3}
                            <td>
								{if ($reductionPrice != 0 OR $reductionPercent != 0) AND ($reductionFrom == $reductionTo OR ($smarty.now|date_format:'%Y-%m-%d' <= $reductionTo AND $smarty.now|date_format:'%Y-%m-%d' >= $reductionFrom))}
                                <span>
									{if $reductionPrice != 0}{if $fullPrice}{convertPrice price=$price+$attributeImpact.price-$reductionPrice}{else}{convertPrice price=$price+$attributeImpact.price}{/if}
                                    {else}{math equation="(p + a) * (1 - (r / 100)) * g" p=$price a=$attributeImpact.price r=$reductionPercent g=$groupReduction assign="reducedPrice"}{convertPrice price=$reducedPrice}{/if}
								</span>&nbsp;{/if}
                                {if !($reductionPrice != 0 AND ($reductionFrom == $reductionTo OR ($smarty.now|date_format:'%Y-%m-%d' <= $reductionTo AND $smarty.now|date_format:'%Y-%m-%d' >= $reductionFrom)) AND !$fullPrice)}                                 
                            	<span{if ($reductionPrice != 0 OR $reductionPercent != 0) AND ($reductionFrom == $reductionTo OR ($smarty.now|date_format:'%Y-%m-%d' <= $reductionTo AND $smarty.now|date_format:'%Y-%m-%d' >= $reductionFrom))} class="strike"{/if}>{if $hideZeros AND $attributeImpact.price == 0}&nbsp;{else}{math equation="(p + a) * g" p=$price a=$attributeImpact.price g=$groupReduction assign="reducedPrice"}{convertPrice price=$reducedPrice}{/if}</span>
                                {/if}
                            </td>
                            {/if}
                            {if $priceFormat > 1}
                            <td>
								{if ($reductionPrice != 0 OR $reductionPercent != 0) AND ($reductionFrom == $reductionTo OR ($smarty.now|date_format:'%Y-%m-%d' <= $reductionTo AND $smarty.now|date_format:'%Y-%m-%d' >= $reductionFrom))}
                                <span>
									{if $reductionPrice != 0}{if $fullPrice}{convertPrice price=$price+$attributeImpact.price-$reductionPrice}{else}{math equation="(p + a) * t" p=$price a=$attributeImpact.price t=$tax assign="reducedPrice"}{convertPrice price=$reducedPrice}{/if}
                                    {else}{math equation="(p + a) * (1 - (r / 100)) * g * t" p=$price a=$attributeImpact.price r=$reductionPercent g=$groupReduction t=$tax assign="reducedPrice"}{convertPrice price=$reducedPrice}{/if}
								</span>&nbsp;{/if}
                                {if !($reductionPrice != 0 AND ($reductionFrom == $reductionTo OR ($smarty.now|date_format:'%Y-%m-%d' <= $reductionTo AND $smarty.now|date_format:'%Y-%m-%d' >= $reductionFrom)) AND !$fullPrice)}                                 
                            	<span{if ($reductionPrice != 0 OR $reductionPercent != 0) AND ($reductionFrom == $reductionTo OR ($smarty.now|date_format:'%Y-%m-%d' <= $reductionTo AND $smarty.now|date_format:'%Y-%m-%d' >= $reductionFrom))} class="strike"{/if}>{if $hideZeros AND $attributeImpact.price == 0}&nbsp;{else}{math equation="(p + a) * g * t" p=$price a=$attributeImpact.price g=$groupReduction t=$tax assign="reducedPrice"}{convertPrice price=$reducedPrice}{/if}</span>
                                {/if}
                            </td>
                            {/if}
                        {/if}
                    {/foreach}
                </tr>
				{/foreach}
                {/if}
			{/foreach}
        </tbody>
    </table>
</div>
<!-- /MODULE productattributesnc -->
{/if}