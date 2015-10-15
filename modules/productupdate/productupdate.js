/*
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
*  International Registred Trademark & Property of PrestaShop SA
*/

var ajaxQueries = new Array();
var ajaxLoaderOn = 0;

$(document).ready(function()
{
        updateProduct(true);
        function updateProgress() {
            $.ajax(
	{
		type: 'GET',
		url: '/pfizer/modules/productupdate/progressStatus.php',
		dataType: 'json',
		cache: false, // @todo see a way to use cache and to add a timestamps parameter to refresh cache each 10 minutes for example
		success: function(result)
		{
                    result = JSON.parse(JSON.stringify(result));
                    var instruction = $('#instructions').html();
                    var content = '<div class="progress-bar progress-bar-striped active" role="progressbar" ';
                    content +='aria-valuenow="'+result+'" aria-valuemin="0" aria-valuemax="100" ';
                    content +='style="width: '+result+'%;min-width: 2em">'+result+'% Complete</div>';
                    $('.progress').html(content);
                    switch(result){
                        case 0:
                            instruction +='Starting Product Update<br />';
                            break;
                        case 10:
                            instruction +='Started Adding Product<br />';
                            break;
                        case 90:
                            instruction +='Deleting Remaining Products<br />';
                            break;
                        case 100:
                            instruction +='Product Updates Completed<br />';
                            break;
                    }
                    $('#instructions').html(instruction);
                    if(result!="100")
                      setTimeout(updateProgress, 50000);
		}
	})

    // your function code here

}

updateProgress();
        
});



function stopAjaxQuery() {
	if (typeof(ajaxQueries) == 'undefined')
		ajaxQueries = new Array();
	for(i = 0; i < ajaxQueries.length; i++)
		ajaxQueries[i].abort();
	ajaxQueries = new Array();
}

function updateProduct(params_plus)
{
	stopAjaxQuery();

	
	ajaxQuery = $.ajax(
	{
		type: 'GET',
		url: '/pfizer/webservice/Create.php?Create=Creating',
		dataType: 'json',
		cache: false, // @todo see a way to use cache and to add a timestamps parameter to refresh cache each 10 minutes for example
		success: function(result)
		{
                    result = JSON.parse(JSON.stringify(result));
                    updateProgress();
                    ajaxLoaderOn = 0;
		}
	});
	ajaxQueries.push(ajaxQuery);
}
