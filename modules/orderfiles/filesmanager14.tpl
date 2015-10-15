{capture name=path}<a href="{$link->getPageLink('my-account.php', true)}">{l s='My account' mod='orderfiles'}</a><span class="navigation-pipe">{$navigationPipe}</span><a href="{$base_dir_ssl}modules/orderfiles/myfiles14.php" title="{l s='Upload own files for your orders' mod='orderfiles'}">{l s='File uploader' mod='orderfiles'}</a><span class="navigation-pipe">{$navigationPipe}</span>{l s='Files manager' mod='orderfiles'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}
<h2 style="margin-top:20px;">{l s='Your order' mod='orderfiles'}</h2>
<table id="order-list" class="std">
	<thead>
		<tr>
			<th class="first_item">{l s='Order ID' mod='orderfiles'}</th>
			<th class="item">{l s='Date' mod='orderfiles'}</th>
			<th class="item">{l s='Total price' mod='orderfiles'}</th>
			<th class="item">{l s='Payment' mod='orderfiles'}</th>
		</tr>
	</thead>
	<tbody>
		<tr class="first_item ">
			<td class="history_link bold">
				{$smarty.post.oid}						
			</td>
			<td class="history_date bold">{$order->date_add}</td>
			<td class="history_price"><span class="price">{$order->total_paid} {$mod->currency_sign($order->id_currency)}</span></td>
			<td class="history_method">{$order->payment}</td>
		</tr>
	</tbody>
</table>
<h2 style="margin-top:20px;">{l s='Files manager' mod='orderfiles'}</h2>
    <div class="filesmanager_contents">
	{if empty($files['toorder']) && empty($files['tocart']) && empty($files['toproduct'])}
		<p class="warning">{l s='No files' mod='orderfiles'}</p>
	{else}
		{foreach from=$files['toorder'] key=id item=file}
			<div class="warning" style="position:relative; display:block; clear:both; overflow:hidden; margin-bottom:10px;">
				<img src="{$modules_dir}orderfiles/img/file.png" style="display:inline-block; float:left; margin-right:10px;"/>
				<div style="display:inline-block; float:left; padding-bottom:30px;">
					<b>{$file.title}</b> (<i>{$file.filename}</i>)
					<p style="margin-top:5px; display:block; clear:both; width:420px; line-height:20px;">{$file.description}</p>
					<form method="post" action="">
						<input type="hidden" name="oid" value="{$idorder}"/>
						<input type="hidden" name="fid" value="{$file.id}"/>
						<input type="submit" name="delfile" value="{l s='Delete' mod='orderfiles'}" class="button" style="position:absolute; right:10px; bottom:10px;"/>
					</form>
				</div>
			</div>
		{/foreach}
        {foreach from=$files['tocart'] key=id item=file}
			<div class="warning" style="position:relative; display:block; clear:both; overflow:hidden; margin-bottom:10px;">
				<img src="{$modules_dir}orderfiles/img/file.png" style="display:inline-block; float:left; margin-right:10px;"/>
				<div style="display:inline-block; float:left; padding-bottom:30px;">
					<b>{$file.product->name}</b> (<i>{$file.filename}</i>)
					<p style="margin-top:5px; display:block; clear:both; width:420px; line-height:20px;">{$file.description}</p>
					<form method="post" action="">
						<input type="hidden" name="oid" value="{$idorder}"/>
						<input type="hidden" name="fid" value="{$file.id}"/>
						<input type="submit" name="delcartfile" value="{l s='Delete' mod='orderfiles'}" class="button" style="position:absolute; right:10px; bottom:10px;"/>
					</form>
				</div>
			</div>
		{/foreach}
        {foreach from=$files['toproduct'] key=id item=file}
			<div class="warning" style="position:relative; display:block; clear:both; overflow:hidden; margin-bottom:10px;">
				<img src="{$modules_dir}orderfiles/img/file.png" style="display:inline-block; float:left; margin-right:10px;"/>
				<div style="display:inline-block; float:left; padding-bottom:30px;">
					<b>{$file.product->name}</b> (<i>{$file.filename}</i>)
					<p style="margin-top:5px; display:block; clear:both; width:420px; line-height:20px;">{$file.description}</p>
					<form method="post" action="">
						<input type="hidden" name="oid" value="{$idorder}"/>
						<input type="hidden" name="fid" value="{$file.id}"/>
						<input type="submit" name="delproductfile" value="{l s='Delete' mod='orderfiles'}" class="button" style="position:absolute; right:10px; bottom:10px;"/>
					</form>
				</div>
			</div>
		{/foreach}
	{/if}
    </div>

{if Configuration::get('OF_AJAXUPLOAD')==1}
        <script type="text/javascript">
            {literal}
                jQuery(function() {
                    var uploadObj = jQuery("#fileuploader").uploadFile({
                        url:baseDir+"modules/orderfiles/ajax/upload.php",
                        multiple:true,
                        autoSubmit:true,
                        fileName:"file",
                        maxFileSize:{/literal}{Configuration::get('OF_MAX_FILE_SIZE')}{literal}*1024,
                        allowedTypes:"{/literal}{if $extensions!=""}{foreach from=$extensions item=ext name=loop}{$ext}{if !$smarty.foreach.loop.last},{/if}{/foreach}{else}*{/if}{literal}",
                        showStatusAfterSuccess:true,
                        formData: {"oid":"{/literal}{$idorder}{literal}","auptype":"order"},
                        dragDropStr: "<span><b>{/literal}{l s='Drag & Drop files here' mod='orderfiles'}{literal}</b></span>",
                        abortStr:"{/literal}{l s='Abort' mod='orderfiles'}{literal}",
                        cancelStr:"{/literal}{l s='Cancel' mod='orderfiles'}{literal}",
                        doneStr:"{/literal}{l s='Done!' mod='orderfiles'}{literal}",
                        multiDragErrorStr: "{/literal}{l s='Several Drag & Drop files are not allowed.' mod='orderfiles'}{literal}",
                        extErrorStr:"{/literal}{l s='is not allowed. Allowed extensions:' mod='orderfiles'}{literal}",
                        sizeErrorStr:"{/literal}{l s='is not allowed. Allowed max size:' mod='orderfiles'}{literal}",
                        uploadErrorStr:"{/literal}{l s='Upload is not allowed' mod='orderfiles'}{literal}",
                        onSuccess:function(files,obj,xhr,pd)
                        {
                        	jQuery('.filesmanager_contents').append(obj);
                        }
                        });
                });
            {/literal}
        </script>
        <div id="fileuploader">{l s='Upload' mod='orderfiles'}</div>
{else}   
<h2 style="margin-top:20px;">{l s='File uploader' mod='orderfiles'}</h2>
<div class="warning" style="clear:both; overflow:hidden;">
	<form method="post" action="{$base_dir_ssl}modules/orderfiles/filesmanager14.php" enctype="multipart/form-data" >
		<div style="margin-bottom:15px; display:block; clear:both; overflow:hidden;">
			<label style="vertical-align:top; display:inline-block; min-width:150px; text-align:right; padding-right:10px;">{l s='title' mod='orderphoto'}:</label><input type="text" name="title"/>
		</div>
		<div style="margin-bottom:15px; display:block; clear:both; overflow:hidden;">
			<label style="vertical-align:top; display:inline-block; min-width:150px; text-align:right; padding-right:10px;">{l s='description' mod='orderphoto'}:</label><textarea style="margin:0px; padding:0px; display:inline-block; width:300px; height:60px;" name="description"></textarea>
		</div>
		<div style="margin-bottom:15px; display:block; clear:both; overflow:hidden;">
			<input type="hidden" name="oid" value="{$idorder}"/>
			<label style="vertical-align:top; display:inline-block; min-width:150px; text-align:right; padding-right:10px;">{l s='file' mod='orderphoto'}:</label><input type="file" name="file[]" multiple="multiple" />
		</div>
		<div style="text-align:center; display:block; margin-top:25px; margin-bottom:15px;">
			<input type="submit" name="addfile" value="{l s='Add file' mod='orderfiles'}" class="button"/>
		</div>
	</form>
</div>
{/if}