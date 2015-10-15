{capture name=path}<a href="{$link->getPageLink('my-account', true)}">{l s='My account' mod='orderfiles'}</a><span class="navigation-pipe">{$navigationPipe}</span><a href="{$link->getModuleLink('orderfiles', 'myfiles')}" title="{l s='Upload own files for your orders' mod='orderfiles'}">{l s='Files uploader' mod='orderfiles'}</a><span class="navigation-pipe">{$navigationPipe}</span>{l s='Files manager' mod='orderfiles'}{/capture}
{* 
{include file="$tpl_dir./breadcrumb.tpl"}
 *}
<h2 style="margin-top:20px;">{l s='Your order' mod='orderfiles'}</h2>
<table id="order-list" class="std">
	<thead>
		<tr>
			<th class="first_item">{l s='Order Reference' mod='orderfiles'}</th>
			<th class="item">{l s='Date' mod='orderfiles'}</th>
			<th class="item">{l s='Total price' mod='orderfiles'}</th>
			<th class="item">{l s='Payment' mod='orderfiles'}</th>
		</tr>
	</thead>
	<tbody>
		<tr class="first_item ">
			<td class="history_link bold">
				{$order->reference}						
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
        <div class="bootstrap">
            <form method="post" action="{$link->getModuleLink('orderfiles', 'filesmanager')}">
			<div style="{if isset($smarty.post.pty)}{if $smarty.post.pty=="order" && $smarty.post.fid==$file.id && isset($smarty.post.editfile)}border:2px solid blue;{else}border:1px solid #c0c0c0;{/if}{else}border:1px solid #c0c0c0;{/if} padding:10px; position:relative; display:block; clear:both; overflow:hidden; margin-bottom:10px;">
                {if $file.adminfile==1}<div class='alert alert-info'>{l s='This file was uploaded by admin' mod='orderfiles'}</div>{/if}
				<img src="{$modules_dir}orderfiles/img/file.png" style="display:inline-block; float:left; margin-right:10px;"/>
				<div style="display:inline-block; float:left; padding-bottom:30px;">
					{if isset($smarty.post.pty)}{if $smarty.post.pty=="order" && $smarty.post.fid==$file.id && isset($smarty.post.editfile)}{l s='title' mod='orderfiles'}<br/><input type="text" name="title" value="{$file.title}"/>{else}<b>{$file.title}</b>{/if}{else}<b>{$file.title}</b>{/if} {$file.filename} - <a href="{$content_dir}modules/orderfiles/download.php?t=files&opt={$idorder}&f={$file.filename}" target="_blank"><strong>{l s='download' mod='orderfiles'}</strong></a>
					<p style="margin-top:5px; display:block; clear:both; width:420px; line-height:20px;">{if isset($smarty.post.pty)}{if $smarty.post.pty=="order" && $smarty.post.fid==$file.id && isset($smarty.post.editfile)}{l s='description' mod='orderfiles'}<br/><textarea name="description">{$file.description}</textarea>{else}{$file.description} &nbsp;{/if}{else}{$file.description} &nbsp;{/if}</p>
						<input type="hidden" name="oid" value="{$idorder}"/>
						<input type="hidden" name="fid" value="{$file.id}"/>
                        <input type="hidden" name="pty" value="order"/>
                        <div style="position:absolute; right:10px; bottom:10px;">
                            {if isset($smarty.post.pty)}{if $smarty.post.pty=="order" && $smarty.post.fid==$file.id && isset($smarty.post.editfile)}<input type="submit" name="savefile" value="{l s='Save' mod='orderfiles'}" class="button" />{/if}{/if}
                            <input type="submit" name="editfile" value="{l s='Edit' mod='orderfiles'}" class="button" />
    						<input type="submit" name="delfile" value="{l s='Delete' mod='orderfiles'}" class="button"/>
                        </div>
				</div>
			</div>
            </form>
        </div>
		{/foreach}
        {foreach from=$files['tocart'] key=id item=file}
        <div class="bootstrap">
			<div style="{if isset($smarty.post.pty)}{if $smarty.post.pty=="cart" && $smarty.post.fid==$file.id && isset($smarty.post.editfile)}border:2px solid blue;{else}border:1px solid #c0c0c0;{/if}{else}border:1px solid #c0c0c0;{/if} padding:10px; position:relative; display:block; clear:both; overflow:hidden; margin-bottom:10px;">        
				<img src="{$modules_dir}orderfiles/img/file.png" style="display:inline-block; float:left; margin-right:10px;"/>
                <form method="post" action="{$link->getModuleLink('orderfiles', 'filesmanager')}">
				<div style="display:inline-block; float:left; padding-bottom:30px;">
					{if isset($smarty.post.pty)}{if $smarty.post.pty=="cart" && $smarty.post.fid==$file.id && isset($smarty.post.editfile)}{l s='title' mod='orderfiles'}<br/><input type="text" name="title" value="{$file.title}"/> {$file.product->name}{else}<strong>{$file.title} {$file.product->name}</strong>{/if}{else}<strong>{$file.title} {$file.product->name}</strong>{/if} {$file.filename} - <a href="{$content_dir}modules/orderfiles/download.php?t=cartfiles&opt={$file.idcart}&f={$file.filename}" target="_blank"><strong>{l s='download' mod='orderfiles'}</strong></a>
					<p style="margin-top:5px; display:block; clear:both; width:420px; line-height:20px;">{if isset($smarty.post.pty)}{if $smarty.post.pty=="cart" && $smarty.post.fid==$file.id && isset($smarty.post.editfile)}{l s='description' mod='orderfiles'}<br/><textarea name="description">{$file.description}</textarea>{else}{$file.description} &nbsp;{/if}{else}{$file.description} &nbsp;{/if}</p>
						<input type="hidden" name="oid" value="{$idorder}"/>
						<input type="hidden" name="fid" value="{$file.id}"/>
                        <input type="hidden" name="pty" value="cart"/>
                        <div style="position:absolute; right:10px; bottom:10px;">
                            {if isset($smarty.post.pty)}{if $smarty.post.pty=="cart" && $smarty.post.fid==$file.id && isset($smarty.post.editfile)}<input type="submit" name="savefile" value="{l s='Save' mod='orderfiles'}" class="button" />{/if}{/if}                        
                            <input type="submit" name="editfile" value="{l s='Edit' mod='orderfiles'}" class="button" />
    						<input type="submit" name="delcartfile" value="{l s='Delete' mod='orderfiles'}" class="button"/>
                        </div>                        
				</div>
                </form>
			</div>
         </div>
		{/foreach}
        {foreach from=$files['toproduct'] key=id item=file}
        <div class="bootstrap">
			<div style="{if isset($smarty.post.pty)}{if $smarty.post.pty=="product" && $smarty.post.fid==$file.id && isset($smarty.post.editfile)}border:2px solid blue;{else}border:1px solid #c0c0c0;{/if}{else}border:1px solid #c0c0c0;{/if} padding:10px; position:relative; display:block; clear:both; overflow:hidden; margin-bottom:10px;">        
				<img src="{$modules_dir}orderfiles/img/file.png" style="display:inline-block; float:left; margin-right:10px;"/>
                <form method="post" action="{$link->getModuleLink('orderfiles', 'filesmanager')}">
				<div style="display:inline-block; float:left; padding-bottom:30px;">
                    {if isset($smarty.post.pty)}{if $smarty.post.pty=="product" && $smarty.post.fid==$file.id && isset($smarty.post.editfile)}{l s='title' mod='orderfiles'}<br/><input type="text" name="title" value="{$file.title}"/>  {$file.product->name} {else}<b>{$file.title} {$file.product->name}</b>{/if}{else}<b>{$file.title} {$file.product->name}</b>{/if} {$file.filename} - <a href="{$content_dir}modules/orderfiles/download.php?t=productfiles&opt={$file.cookieid}&f={$file.filename}" target="_blank"><strong>{l s='download' mod='orderfiles'}</strong></a>
					<p style="margin-top:5px; display:block; clear:both; width:420px; line-height:20px;">{if isset($smarty.post.pty)}{if $smarty.post.pty=="product" && $smarty.post.fid==$file.id && isset($smarty.post.editfile)}{l s='description' mod='orderfiles'}<br/><textarea name="description">{$file.description}</textarea>{else}{$file.description} &nbsp;{/if}{else}{$file.description} &nbsp;{/if}</p>
						<input type="hidden" name="oid" value="{$idorder}"/>
						<input type="hidden" name="fid" value="{$file.id}"/>
                        <input type="hidden" name="pty" value="product"/>
                        <div style="position:absolute; right:10px; bottom:10px;">
                            {if isset($smarty.post.pty)}{if $smarty.post.pty=="product" && $smarty.post.fid==$file.id && isset($smarty.post.editfile)}<input type="submit" name="savefile" value="{l s='Save' mod='orderfiles'}" class="button" />{/if}{/if}
                            <input type="submit" name="editfile" value="{l s='Edit' mod='orderfiles'}" class="button" />
    						<input type="submit" name="delproductfile" value="{l s='Delete' mod='orderfiles'}" class="button"/>
                        </div>
				</div>
                </form>
			</div>
        </div> 
		{/foreach}
	{/if}
    </div>

{if Configuration::get('OF_AJAXUPLOAD')==1}
        <script type="text/javascript">
            {literal}
                $(function() {
                    var uploadObj = $("#fileuploader").uploadFile({
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
                        	$('.filesmanager_contents').append(obj);
                        }
                        });
                });
            {/literal}
        </script>
        <div id="fileuploader">{l s='Upload' mod='orderfiles'}</div>
{else}        
<h2 style="margin-top:20px;">{l s='File uploader' mod='orderfiles'}</h2>
<div class="warning" style="clear:both; overflow:hidden;">
	<form method="post" action="{$link->getModuleLink('orderfiles', 'filesmanager')}" enctype="multipart/form-data" onsubmit="return Validate(this);">
		<div style="margin-bottom:15px; display:block; clear:both; overflow:hidden;">
			<label style="vertical-align:top; display:inline-block; min-width:150px; text-align:right; padding-right:10px;">{l s='title' mod='orderphoto'}:</label><input type="text" name="title"/>
		</div>
		<div style="margin-bottom:15px; display:block; clear:both; overflow:hidden;">
			<label style="vertical-align:top; display:inline-block; min-width:150px; text-align:right; padding-right:10px;">{l s='description' mod='orderphoto'}:</label><textarea style="margin:0px; padding:0px; display:inline-block; width:300px; height:60px;" name="description"></textarea>
		</div>
		<div style="margin-bottom:15px; display:block; clear:both; overflow:hidden;">
			<input type="hidden" name="oid" value="{$idorder}"/>
			<label style="vertical-align:top; display:inline-block; min-width:150px; text-align:right; padding-right:10px;">{l s='file' mod='orderphoto'}:</label><input type="file" name="file[]" multiple="multiple"/>
		</div>
		<div style="text-align:center; display:block; margin-top:25px; margin-bottom:15px;">
			<input type="submit" name="addfile" value="{l s='Add file' mod='orderfiles'}" class="button"/>
		</div>
	</form>
</div>
{/if}