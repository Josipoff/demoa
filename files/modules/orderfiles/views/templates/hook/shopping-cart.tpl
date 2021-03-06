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
    {if $hideuploadform==0}
        {if Configuration::get("OF_SCART")==1}
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
                                {/literal}{$maxFileCount}{literal}
                                formData: {"idcart":"{/literal}{$idcart}{literal}","auptype":"cart"},
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
                <h3>{l s='Upload files to products' mod='orderfiles'}</h3>
                <div id="fileuploader">{l s='Upload' mod='orderfiles'}</div>
            <table id="order_files" class="std">
                <thead>
            		<tr>
            			<th>{l s='Product' mod='orderfiles'}</th>
            			<th>{l s='Filename' mod='orderfiles'}</th>
            			<th>{l s='Options' mod='orderfiles'}</th>
            		</tr>
                    {foreach from=$files key=id item=cartfile}
                    <tr>
                        <td>
                        <form method="post" style="width:100%;" id="f{$cartfile.id}cartfile" name="f{$cartfile.id}cartfile">
                            <input type="hidden" name="changecartfile" value="{$cartfile.id}"/>
                            <select name="idproduct" onchange="f{$cartfile.id}cartfile.submit();" style="width:100px;">
                                <option>{l s='- select -' mod='orderfiles'}</option>
                                {foreach from=$ofproducts key=id item=product}
                                    <option value="{$product.id_product}" {if $cartfile['idproduct']==$product.id_product}selected="yes"{/if}>{$product.name}</option>
                                {/foreach}
                            </select>
                        </form>
                        </td>
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
                    
                    {foreach from=$files_product key=id item=productfile}
                    <tr>
                        <td>
                        <form method="post" style="width:100%;" id="f{$productfile.id}productfile" name="f{$productfile.id}productfile">
                            <input type="hidden" name="changeproductfile" value="{$productfile.id}"/>
                            <select name="idproduct" onchange="f{$productfile.id}productfile.submit();" style="width:100px;">
                                <option>{l s='- select -' mod='orderfiles'}</option>
                                {foreach from=$ofproducts key=id item=product}
                                    <option value="{$product.id_product}" {if $productfile['idproduct']==$product.id_product}selected="yes"{/if}>{$product.name}</option>
                                {/foreach}
                            </select>
                        </form>
                        </td>
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
                </thead>
            </table>        
            {else}
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
                    <td colspan="3" style="text-align:center;">
                        <script>
                        {literal}
                        $( document ).ready(function() {
                            $('.add_description').toggle(function(){
                                $('.customerfile_description_field').show();
                            }, function(){
                                $('.customerfile_description_field').hide();
                            });
                        });
                        {/literal}
                        </script>
                        <form method="post" style="width:100%;" enctype="multipart/form-data" onsubmit="return Validate(this);">
                        <input name="idcart" value="{$idcart}" type="hidden"/>
                        <input type="file" name="file[]" multiple="multiple" />
                        <label for="">{l s='for:' mod='orderfiles'}</label>
                        <select name="idproduct" style="width:100px; margin-right:10px;">
                            {foreach from=$ofproducts key=id item=product}
                                <option value="{$product.id_product}">{$product.name}</option>
                            {/foreach}
                        </select>
                        <span class="button extra add_description" style="opacity:0.5">{l s='add description' mod='orderfiles'}</span>
                        <input type="submit" name="upload_new_file_cart" value="{l s='upload' mod='orderfiles'}" class="button" style="margin-left:50px;"/>
                        <div class="customerfile_description_field" style="display:none; width:100%; height:100%; padding-top:10px; clear:both; overflow:hidden;">
                            <textarea style="width:99%; height:99%;" name="description" class="customerfile_description"></textarea>
                        </div>
                        </form>
                    </td>
                    </tr>
            		<tr>
            			<th>{l s='Product' mod='orderfiles'}</th>
            			<th>{l s='Filename' mod='orderfiles'}</th>
            			<th>{l s='Options' mod='orderfiles'}</th>
            		</tr>
                    {foreach from=$files key=id item=cartfile}
                    <tr>
                        <td>
                        <form method="post" style="width:100%;" id="f{$cartfile.id}cartfile" name="f{$cartfile.id}cartfile">
                            <input type="hidden" name="changecartfile" value="{$cartfile.id}"/>
                            <select name="idproduct" onchange="f{$cartfile.id}cartfile.submit();" style="width:100px;">
                                {foreach from=$ofproducts key=id item=product}
                                    <option value="{$product.id_product}" {if $cartfile['idproduct']==$product.id_product}selected="yes"{/if}>{$product.name}</option>
                                {/foreach}
                            </select>
                        </form>
                        </td>
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
                    
                    {foreach from=$files_product key=id item=productfile}
                    <tr>
                        <td>
                        <form method="post" style="width:100%;" id="f{$productfile.id}productfile" name="f{$productfile.id}productfile">
                            <input type="hidden" name="changeproductfile" value="{$productfile.id}"/>
                            <select name="idproduct" onchange="f{$productfile.id}productfile.submit();" style="width:100px;">
                                {foreach from=$ofproducts key=id item=product}
                                    <option value="{$product.id_product}" {if $productfile['idproduct']==$product.id_product}selected="yes"{/if}>{$product.name}</option>
                                {/foreach}
                            </select>
                        </form>
                        </td>
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
                </thead>
            </table>
            {/if}
        {/if}
    {/if}


{else}
    <h3>{l s='Upload files to products' mod='orderfiles'}</h3>
    <div class="alert alert-danger">
        {l s='upload files to products is possible only for logged customers' mod='orderfiles'}
    </div>
{/if}