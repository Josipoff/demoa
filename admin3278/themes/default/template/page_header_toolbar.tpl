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

{* retro compatibility *}
{if !isset($title) && isset($page_header_toolbar_title)}
	{assign var=title value=$page_header_toolbar_title}
{/if}
{if isset($page_header_toolbar_btn)}
	{assign var=toolbar_btn value=$page_header_toolbar_btn}
{/if}

<div class="bootstrap">
	<div class="page-head">
		{block name=pageTitle}
		<h2 class="page-title">
			{*if isset($toolbar_btn['back'])}
			<a id="page-header-desc-{$table}{if isset($toolbar_btn['back'].imgclass)}-{$toolbar_btn['back'].imgclass}{/if}" class="page-header-toolbar-back" {if isset($toolbar_btn['back'].href)}href="{$toolbar_btn['back'].href}"{/if} title="{$toolbar_btn['back'].desc}" {if isset($toolbar_btn['back'].target) && $toolbar_btn['back'].target}target="_blank"{/if}{if isset($toolbar_btn['back'].js) && $toolbar_btn['back'].js}onclick="{$toolbar_btn['back'].js}"{/if}>
				
			</a>
			{/if*}
			{if is_array($title)}{$title|end|escape}{else}{$title|escape}{/if}
		</h2>
		{/block}

		
		{block name=toolbarBox}
		<div class="page-bar toolbarBox">
			<div class="btn-toolbar">
				<a href="#" class="toolbar_btn dropdown-toolbar navbar-toggle" data-toggle="collapse" data-target="#toolbar-nav"><i class="process-icon-dropdown"></i><span>{l s='Menu'}</span></a>
				<ul id="toolbar-nav" class="nav nav-pills pull-right collapse navbar-collapse">
					{foreach from=$toolbar_btn item=btn key=k}
					{if $k != 'back' && $k != 'modules-list'}
					<li>
						<a id="page-header-desc-{$table}-{if isset($btn.imgclass)}{$btn.imgclass|escape}{else}{$k}{/if}" class="toolbar_btn" {if isset($btn.href)}href="{$btn.href|escape}"{/if} title="{$btn.desc|escape}"{if isset($btn.target) && $btn.target} target="_blank"{/if}{if isset($btn.js) && $btn.js} onclick="{$btn.js}"{/if}{if isset($btn.modal_target) && $btn.modal_target} data-target="{$btn.modal_target}" data-toggle="modal"{/if}>
							<i class="{if isset($btn.icon)}{$btn.icon|escape}{else}process-icon-{if isset($btn.imgclass)}{$btn.imgclass|escape}{else}{$k}{/if}{/if}{if isset($btn.class)} {$btn.class|escape}{/if}"></i>
							<span {if isset($btn.force_desc) && $btn.force_desc == true } class="locked"{/if}>{$btn.desc|escape}</span>
						</a>
					</li>
					{/if}
					{/foreach}
					{if isset($help_link)}
					
					{/if}
				</ul>
				{if (isset($tab_modules_open) && $tab_modules_open) || isset($tab_modules_list)}
				<script type="text/javascript">
				//<![CDATA[
					var modules_list_loaded = false;
					{if isset($tab_modules_open) && $tab_modules_open}
						$(function() {
								$('#modules_list_container').modal('show');
								openModulesList();
							
						});
					{/if}
					{if isset($tab_modules_list)}
						$('.process-icon-modules-list').parent('a').unbind().bind('click', function (){
							$('#modules_list_container').modal('show');
							openModulesList();
						});
					{/if}
				//]]>
				</script>
				{/if}				
			</div>
		</div>
		{/block}
	</div>
</div>