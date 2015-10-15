{capture name=path}<a href="{$link->getPageLink('my-account.php', true)}">{l s='My account' mod='orderfiles'}</a><span class="navigation-pipe">{$navigationPipe}</span>{l s='File uploader' mod='orderfiles'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}
<h2 style="margin-top:20px;">{l s='Your orders' mod='orderfiles'}</h2>
<p class="warning">{l s='select order from your order list for which you want to upload files' mod='orderfiles'}</p>
<table id="order-list" class="std">
		<thead>
			<tr>
				<th class="first_item">{l s='Order ID' mod='orderfiles'}</th>
				<th class="item">{l s='Date' mod='orderfiles'}</th>
				<th class="item">{l s='Total price' mod='orderfiles'}</th>
				<th class="item">{l s='Payment' mod='orderfiles'}</th>
				<th class="item">{l s='Status' mod='orderfiles'}</th>
				<th class="last_item" style="width:65px">{l s='Files' mod='orderfiles'}</th>
			</tr>
		</thead>
	<tbody>
		{foreach from=$orders key=id item=order}
			{if empty($orders)}
			<tr class="first_item ">
				<td colspan="6">
					{l s='If you want upload files, order products first' mod='orderfiles'}
				</td>
			</tr>
			{else}
				<tr class="first_item ">
					<td class="history_link bold">
						{$order.id_order}						
					</td>
					<td class="history_date bold">{$order.date_add}</td>
					<td class="history_price"><span class="price">{$order.total_paid} {$mod->currency_sign($order.id_currency)}</span></td>
					<td class="history_method">{$order.payment}</td>
					<td class="history_state">{$order.order_state}</td>
					<td class="history_detail"><form method="post" action="{$base_dir_ssl}modules/orderfiles/filesmanager14.php"><input type="hidden" name="oid" value="{$order.id_order}" /><input type="submit" class="button" value="{l s='Manage' mod='orderfiles'}"/></form></td>
				</tr>
			{/if}
		{/foreach}
	</tbody>
</table>