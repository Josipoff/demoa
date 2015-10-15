{if Configuration::get('OF_UNLOGGED')==1} 
    {if $logged}
        {assign var='disable_upload_form' value='0'}
    {else}
        {assign var='disable_upload_form' value='1'}
    {/if}
{else}
    {assign var='disable_upload_form' value='0'}    
{/if}

{if $disable_upload_form=='0'}
    {if Configuration::get('OF_AJAXUPLOAD')==1}
        {if $thisistab==1}
        <div id="CustomerFilesUploadTab" >
        {/if}
        <script type="text/javascript">
        {literal}
            jQuery(function() {
                var uploadObj = jQuery("#fileuploader").uploadFile({
                    url:baseDir+"modules/orderfiles/ajax/upload.php",
                    multiple:true,
                    autoSubmit:true,
                    fileName:"file",
                    maxFileSize:{/literal}{Configuration::get('OF_MAX_FILE_SIZE')}{literal}*1024,
                    {/literal}{$maxFileCount}{literal}
                    formData: {"idproduct":"{/literal}{$idproduct}{literal}","auptype":"product"},
                    allowedTypes:"{/literal}{if $extensions!=""}{foreach from=$extensions item=ext name=loop}{$ext}{if !$smarty.foreach.loop.last},{/if}{/foreach}{else}*{/if}{literal}",
                    showStatusAfterSuccess:true,
                    dragDropStr: "<span><b>{/literal}{l s='Drag & Drop files here' mod='orderfiles'}{literal}</b></span>",
                    abortStr:"{/literal}{l s='Abort' mod='orderfiles'}{literal}",
                    cancelStr:"{/literal}{l s='Cancel' mod='orderfiles'}{literal}",
                    doneStr:"{/literal}{l s='Done!' mod='orderfiles'}{literal}",
                    multiDragErrorStr: "{/literal}{l s='Several Drag & Drop files are not allowed.' mod='orderfiles'}{literal}",
                    extErrorStr:"{/literal}{l s='is not allowed. Allowed extensions:' mod='orderfiles'}{literal}",
                    sizeErrorStr:"{/literal}{l s='is not allowed. Allowed max size:' mod='orderfiles'}{literal}",
                    uploadErrorStr:"{/literal}{l s='Upload is not allowed' mod='orderfiles'}{literal}",
                    maxFileCountErrorStr: "{/literal}{l s='is not allowed. Maximum allowed files are:' mod='orderfiles'}{literal}",
                    afterUploadAll:function()
                    {
                    	location.reload();
                    }
                    });
            });
        {/literal}
        </script>
        {if $ver!=6 && $TabContent!=1}
        <h3>{l s='Upload files to this product' mod='orderfiles'}</h3>
        {/if}
        <div id="fileuploader">{l s='Upload' mod='orderfiles'}</div>
                <input id="fileCount" value="{$files|@count}" type="hidden"/>
    
    <table id="order_files" class="std">
        <thead>
    		<tr>
    			<th>{l s='Product' mod='orderfiles'}</th>
    			<th>{l s='Filename' mod='orderfiles'}</th>
    			<th>{l s='Options' mod='orderfiles'}</th>
    		</tr>
            {foreach from=$files key=id item=productfile}
            <tr>
                <td>{$productfile['product']->name}</td>
                <td>
                {$productfile['filename']}
                <form method="post" style="width:100%;" >
                    <input name="idproductfile" value="{$productfile['id']}" type="hidden"/>
                    <textarea style="resize:none; width:100%; height:50px;" name="description">{$productfile['description']}</textarea>
                    <input type="submit" name="update_productfile" value="{l s='update description' mod='orderfiles'}" class="button" style="margin-left:0px;"/>
                </form>
                </td>
                <td>
                <form method="post" style="width:100%;" >
                    <input name="idproductfile" value="{$productfile['id']}" type="hidden"/>
                    <input type="submit" name="remove_productfile" value="{l s='delete' mod='orderfiles'}" class="button" style="margin-left:0px;"/>
                </form>
                </td>
            </tr>
            {/foreach}
            {foreach from=$files_cart key=id item=cartfile}
            <tr>
                <td>{$cartfile['product']->name}</td>
                <td>
                {$cartfile['filename']}
                <form method="post" style="width:100%;" >
                    <input name="idcartfile" value="{$cartfile['id']}" type="hidden"/>
                    <textarea style="resize:none; width:100%; height:50px;" name="description">{$cartfile['description']}</textarea>
                    <input type="submit" name="update_cartfile" value="{l s='update description' mod='orderfiles'}" class="button" style="margin-left:0px;"/>
                </form>
                </td>
                <td>
                <form method="post" style="width:100%;" >
                    <input name="idcartfile" value="{$cartfile['id']}" type="hidden"/>
                    <input type="submit" name="remove_cartfile" value="{l s='delete' mod='orderfiles'}" class="button" style="margin-left:0px;"/>
                </form>
                </td>
            </tr>
            {/foreach}
            {if $files_cart|@count lte 0 && $files|@count lte 0}
                <tr>
                    <td colspan="3">
                    {l s='No files uploaded' mod='orderfiles'}
                    </td>
                </tr>
            {/if}
        </thead>
    </table> 
    {if $thisistab==1}
    </div>
    {/if} 
    {else}
    {if $thisistab==1}
    <div id="CustomerFilesUploadTab" >
    {/if}
    {literal}
    <script type="text/javascript">
        var _validFileExtensions = [{/literal}{foreach from=$extensions item=ext name=loop}".{$ext}"{if !$smarty.foreach.loop.last}, {/if}{/foreach}{literal}];
        function Validate(oForm) {
            var arrInputs = oForm.getElementsByTagName("input");
            for (var i = 0; i < arrInputs.length; i++) {
                var oInput = arrInputs[i];
                if (oInput.type == "file") {
                    var sFileName = oInput.value;
                    if (sFileName.length > 0) {
                        var blnValid = false;
                        for (var j = 0; j < _validFileExtensions.length; j++) {
                            var sCurExtension = _validFileExtensions[j];
                            if (sFileName.substr(sFileName.length - sCurExtension.length, sCurExtension.length).toLowerCase() == sCurExtension.toLowerCase()) {
                                blnValid = true;
                                break;
                            }
                        }
        
                        if (!blnValid) {
                            alert("{/literal}{l s='Sorry, you can not upload this file: '}{literal}" + sFileName + "{/literal} {l s='Supported filetypes: '}{literal}" + _validFileExtensions.join(", "));
                            return false;
                        }
                    }
                }
            }
        
            return true;
        }
        </script>
    {/literal}
    
    
    <table id="order_files" class="std">
        <thead>
            <tr>
    		<tr>
    			<th>{l s='Product' mod='orderfiles'}</th>
    			<th>{l s='Filename' mod='orderfiles'}</th>
    		</tr>
            {foreach from=$files key=id item=productfile}
            <tr>
                <td>{$productfile['product']->name}</td>
                <td>
                {$productfile['filename']}
                <form method="post" style="width:100%;" >
                    <input name="idproductfile" value="{$productfile['id']}" type="hidden"/>
                    <textarea style="resize:none; width:100%; height:50px;" name="description">{$productfile['description']}</textarea>
                    <input type="submit" name="update_productfile" value="{l s='update description' mod='orderfiles'}" class="button" style="margin-left:0px;"/>
                </form>
                </td>
            </tr>
            {/foreach}
            {foreach from=$files_cart key=id item=cartfile}
            <tr>
                <td>{$cartfile['product']->name}</td>
                <td>
                {$cartfile['filename']}
                <form method="post" style="width:100%;" >
                    <input name="idcartfile" value="{$cartfile['id']}" type="hidden"/>
                    <textarea style="resize:none; width:100%; height:50px;" name="description">{$cartfile['description']}</textarea>
                    <input type="submit" name="update_cartfile" value="{l s='update description' mod='orderfiles'}" class="button" style="margin-left:0px;"/>
                </form>
                </td>
                <td>
                <form method="post" style="width:100%;" >
                    <input name="idcartfile" value="{$cartfile['id']}" type="hidden"/>
                    <input type="submit" name="remove_cartfile" value="{l s='delete' mod='orderfiles'}" class="button" style="margin-left:0px;"/>
                </form>
                </td>
            </tr>
            {/foreach}
            {if $files_cart|@count lte 0 && $files|@count lte 0}
                <tr>
                    <td colspan="3">
                    {l s='No files uploaded' mod='orderfiles'}
                    </td>
                </tr>
            {/if}        
        </thead>
    </table>
    {if $thisistab==1}
    </div>
    {/if}
    {/if}

{else}
    <h3>{l s='Upload files to products' mod='orderfiles'}</h3>
    <div class="alert alert-danger">
        {l s='upload files to this product is possible only for logged customers' mod='orderfiles'}
    </div>
{/if}