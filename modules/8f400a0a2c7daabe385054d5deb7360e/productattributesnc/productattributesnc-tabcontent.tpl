{if isset($groups)}
<!-- MODULE productattributesnc -->
<ul id="idTab10" class="bullet">
   	<br />
	<table class="std">
    	<thead>
            <tr>
            	<th id="radio_column" class="first_item">&nbsp;</th>
                <th id="description_column" class="item">{l s='Description' mod='productattributesnc'}</th>
                <th id="price_wt_column" class="item">{l s='Price Inc. Tax' mod='productattributesnc'}</th>
                {if $displayTaxExcluded}<th id="price_column" class="last_item">{l s='Price Excl. Tax' mod='productattributesnc'}</th>{/if}
            </tr>
        </thead>
        <tbody>
			{foreach from=$groups key=id_attribute_group item=group}
				{assign var='groupName' value='group_'|cat:$id_attribute_group}
                {assign var='checkbox' value='1'}
                {if sizeof($group.attributes) == 2}
					{foreach from=$group.attributes key=id_attribute item=group_attribute name=group_attributes}
                    	{if $smarty.foreach.group_attributes.first && $group_attribute != 'No' ||
                            $smarty.foreach.group_attributes.last && $group_attribute != 'Yes'}
                        	{assign var='checkbox' value='0'}
                        {/if}
                        {if $smarty.foreach.group_attributes.first}
                        	{assign var='id_attribute_off' value=`$id_attribute`}
                        {elseif $smarty.foreach.group_attributes.last}
                        	{assign var='id_attribute_on' value=`$id_attribute`}
                        {/if}
                    {/foreach}
                {else}
                	{assign var='checkbox' value='0'}
                {/if}
                {if $checkbox}
              	<tr>
                    <td><input type="checkbox" name="group_{$id_attribute_group}" onclick="javascript:document.getElementById('group_{$id_attribute_group|intval}').value = (document.getElementById('group_{$id_attribute_group|intval}').value == {$id_attribute_on|intval} ? {$id_attribute_off|intval} : {$id_attribute_on|intval}); findCombination();" value="{$id_attribute|intval}" {if (isset($smarty.get.$groupName) && $smarty.get.$groupName|intval == $id_attribute) || $group.default == $id_attribute_on} checked="checked"{/if} /></td>
                    <td>{$group.name|escape:'htmlall':'UTF-8'}</td>
                    {foreach from=$attributeImpacts key=id_attributeImpact item=attributeImpact}
                        {if $id_attribute == $attributeImpact.id_attribute}
                            <td>{convertPrice price=$attributeImpact.price}</td>
                            {if $displayTaxExcluded}<td>{convertPrice price=$attributeImpact.price*$tax}</td>{/if}
                        {/if}
                    {/foreach}
                </tr>
                {else}
				<tr><td colspan="{if $displayTaxExcluded}4{else}3{/if}">{$group.name|escape:'htmlall':'UTF-8'}</td></tr>
				{foreach from=$group.attributes key=id_attribute item=group_attribute}
                <tr>
                    <td><input type="radio" name="group_{$id_attribute_group}" onclick="javascript:document.getElementById('group_{$id_attribute_group|intval}').value = {$id_attribute|intval}; findCombination();" value="{$id_attribute|intval}"{if (isset($smarty.get.$groupName) && $smarty.get.$groupName|intval == $id_attribute) || $group.default == $id_attribute} checked="checked"{/if} /></td>
                    <td>{$group_attribute|escape:'htmlall':'UTF-8'}</td>
                    
                    {foreach from=$attributeImpacts key=id_attributeImpact item=attributeImpact}
                        {if $id_attribute == $attributeImpact.id_attribute}
                            <td>{convertPrice price=$attributeImpact.price}</td>
                            {if $displayTaxExcluded}<td>{convertPrice price=$attributeImpact.price*$tax}</td>{/if}
                        {/if}
                    {/foreach}
                </tr>
				{/foreach}
                {/if}
			{/foreach}
        </tbody>
    </table>
</ul>
<!-- /MODULE productattributesnc -->
{/if}