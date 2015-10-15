{*
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
{extends file="helpers/view/view.tpl"}
{block name="override_tpl"}
	<script type="text/javascript">
		function chooseTypeTranslation(id_lang)
		{
			getE('translation_lang').value = id_lang;
			document.getElementById('typeTranslationForm').submit();
		}

		function addThemeSelect()
		{
			var list_type_for_theme = ['front', 'modules', 'pdf', 'mails'];
			var type = $('select[name=type]').val();

			$('select[name=theme]').hide();
			for (i=0; i < list_type_for_theme.length; i++)
				if (list_type_for_theme[i] == type)
				{
					$('select[name=theme]').show();
					if (type == 'front')
						$('select[name=theme]').children('option[value=""]').attr('disabled', true)
					else
						$('select[name=theme]').children('option[value=""]').attr('disabled', false)
				}
				else
					$('select[name=theme]').val('{$theme_default}');
		}

		$(document).ready(function(){
			addThemeSelect();
			$('select[name=type]').change(function() {
				addThemeSelect();
			});

			$('#translations-languages a').click(function(e) {
				e.preventDefault();
				$(this).parent().addClass('active').siblings().removeClass('active');
				$('#language-button').html($(this).html()+' <span class="caret"></span>');
			});

			$('#modify-translations').click(function(e) {
				var lang = $('#translations-languages li.active').data('type');

				if (lang == null)
					return !alert('{l s='Please select your language!'}');
				
				chooseTypeTranslation($('#translations-languages li.active').data('type'));
			});
		});
	</script>
	<form method="get" action="index.php" id="typeTranslationForm" class="form-horizontal">
		<div class="panel">
			<h3>
				<i class="icon-file-text"></i>
				{l s='Modify translations'}
			</h3>
			<p class="alert alert-info">
				{l s='Here you can modify translations for every line of text.'}<br />
				{l s='First, select a type of translation (such as "Back-office" or "Installed modules"), and then select the language you want to translate strings in.'}
			</p>
			<div class="form-group">
				<input type="hidden" name="controller" value="AdminTranslations" />
				<input type="hidden" name="lang" id="translation_lang" value="0" />
				<label class="control-label col-lg-3" for="type">{l s='Type of translation'}</label>
				<div class="col-lg-4">
					<select name="type" id="type">
						{foreach $translations_type as $type => $array}
							<option value="{$type}">{$array.name}</option>
						{/foreach}
					</select>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-lg-3" for="theme">{l s='Select your theme'}</label>
				<div class="col-lg-4">
					<select name="theme" id="theme">
						{if !$host_mode}
						<option value="">{l s='Core (no theme selected)'}</option>
						{/if}
						{foreach $themes as $theme}
							<option value="{$theme->directory}" {if $id_theme_current == $theme->id}selected=selected{/if}>{$theme->name}</option>
						{/foreach}
					</select>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-lg-3" for="language-button">{l s='Select your language'}</label>
				<div class="input-group col-lg-4">
					<button type="button" id="language-button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
						{l s='Language'} <span class="caret"></span>
					</button>
					<ul class="dropdown-menu" id="translations-languages">
						{foreach $languages as $language}
						<li data-type="{$language['iso_code']}"><a href="#">{$language['name']}</a></li>
						{/foreach}
					</ul>
				</div>
				<input type="hidden" name="token" value="{$token|escape:'html':'UTF-8'}" />
			</div>
			<div class="panel-footer">
				<button type="button" class="btn btn-default pull-right" id="modify-translations">
					<i class="process-icon-edit"></i> {l s='Modify'}
				</button>
			</div>
		</div>
	</form>
<script type="text/javascript">
	$(document).ready(function(){
		$('#file-selectbutton').click(function(e) {
			$('#importLanguage').trigger('click');
		});

		$('#file-name').click(function(e) {
			$('#importLanguage').trigger('click');
		});

		$('#importLanguage').change(function(e) {
			if ($(this)[0].files !== undefined)
			{
				var files = $(this)[0].files;
				var name  = '';

				$.each(files, function(index, value) {
					name += value.name+', ';
				});

				$('#file-name').val(name.slice(0, -2));
			}
			else // Internet Explorer 9 Compatibility
			{
				var name = $(this).val().split(/[\\/]/);
				$('#file-name').val(name[name.length-1]);
			}
		});
	});
</script>
{/block}