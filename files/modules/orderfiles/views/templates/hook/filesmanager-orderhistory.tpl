<h2 style="margin-top:20px;">{l s='Files manager' mod='orderfiles'}</h2>
    <div class="filesmanager_contents">
	{if empty($files['toorder']) && empty($files['tocart']) && empty($files['toproduct'])}
		<p class="alert alert-info warning">{l s='No files' mod='orderfiles'}</p>
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
    <form method="post" action="{$link->getModuleLink('orderfiles', 'filesmanager')}" style="margin-bottom:20px; text-align:center;">
   	    <input type="hidden" name="oid" value="{$idorder}"/>
        <input type="hidden" name="pty" value="order"/>
        <input type="submit" name="manager" value="{l s='Upload files' mod='orderfiles'}" class="button" />
    </form>
