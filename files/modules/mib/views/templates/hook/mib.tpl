<div class="block" id="mypresta_mib">
    <p class="title_block">{l s='Manufacturers' mod='mib'}</p>
    <ul>
       	{foreach from=$manufacturers item=manufacturer name=manufacturer_list}
           {if $manufacturer.image}
        	   <li class="{if $smarty.foreach.manufacturer_list.last}last_item{elseif $smarty.foreach.manufacturer_list.first}first_item{else}item{/if}">
                   <a href="{$link->getmanufacturerLink($manufacturer.id_manufacturer, $manufacturer.link_rewrite)|escape:'html'}" title="{l s='More about %s' sprintf=[$manufacturer.name] mod='mib'}">
                      <img src="{$content_dir}img/m/{$manufacturer.image_url}" alt="{$manufacturer.name|escape:'html':'UTF-8'}"/>
                   </a>
               </li>
           {/if}
    	{/foreach}
    </ul>
</div>