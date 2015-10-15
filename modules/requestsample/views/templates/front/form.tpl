
{if isset($confirmation) && $confirmation==1}
	<p class="fancybox-error">{l s='Your message has been successfully sent to our team.'}</p>
{else}
    <h1 class="sample-form page-heading bottom-indent">
    {l s='Request A sample' mod='requestsample'}
</h1>
{include file="$tpl_dir./errors.tpl"}
    <form action="{$request_uri|escape:'html':'UTF-8'}" method="post" class="sample-form contact-form-box" enctype="multipart/form-data">
		<fieldset>
         <div class="clearfix">
                <div class="form-group selector1">
            <div class="col-xs-6">
                <p class="form-group">
                    <label for="email">{l s='Email address'}</label>
                    <input class="form-control grey validate" type="text" id="email" name="from" data-validate="isEmail" value="{$email|escape:'html':'UTF-8'}" />
                </p>
             <p class="form-group">
                    <label for="name">{l s='Name'}</label>
                    <input class="form-control grey validate" type="text" id="name" name="name" value="{$name|escape:'html':'UTF-8'}" />
                </p>
                </div>
            <div class="col-xs-6">
                
                <p class="form-group">
                    <label for="tel">{l s='Telphone No'}</label>
                    <input class="form-control grey validate" type="text" id="tel" name="tel" value="{$tel|escape:'html':'UTF-8'}" />
                </p>
                
                
                <p class="form-group">
                    <label for="mobile">{l s='Mobile'}</label>
                    <input class="form-control grey validate" type="text" id="mobile" name="mobile" value="{$mobile|escape:'html':'UTF-8'}" />
                </p>
                </div>
                
            </div>
            <div class="col-xs-9">
                <div class="form-group">
                    <label for="remark">{l s='Remark'}</label>
                    <textarea class="form-control" id="message" name="message">{if isset($message)}{$message|escape:'html':'UTF-8'|stripslashes}{/if}</textarea>
                </div>
            </div>
        </div>
        <div class="submit">
            <input type="hidden" id="product_id" name="product_id" value="{$product_id}" />
               <button type="submit" name="submitMessage" id="submitMessage" class="button btn btn-default button-medium"><span>{l s='Send'}<i class="icon-chevron-right right"></i></span></button>
		</div><br /><br />
	</fieldset>
</form>    
         
    {/if}
    
{addJsDefL name='form_submit'}{$confirmation}{/addJsDefL}
	      