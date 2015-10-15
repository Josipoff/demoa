{if Configuration::get("OF_CACCOUNT")==1}
<li><a href="{$link->getModuleLink('orderfiles', 'myfiles')}" title="{l s='Upload own files for your orders' mod='orderfiles'}"><i class="icon-cloud-upload"></i><span>{l s='Files uploader' mod='orderfiles'}</span></a></li>
{/if}