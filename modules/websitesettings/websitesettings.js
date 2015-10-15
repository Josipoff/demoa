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
    if($('input[name=PRODUCT_DYNAMIC_PRICE]:checked').val()==0){
        $('input[name=PROFIT_MARGIN]').prop("readonly", true);
    }
    
    $('input[name=PRODUCT_DYNAMIC_PRICE]').click(function(){
            if($('input[name=PRODUCT_DYNAMIC_PRICE]:checked').val()==1){
            $('#PROFIT_MARGIN').prop("readonly", false);
        }else{            
            $('#PROFIT_MARGIN').prop("readonly", true);
        }
    });
    
    
    if($('input[name=ADMIN_CONFIRM_ORDER]:checked').val()==0){
        $('input[name=ORDER_CNF_MANAGER_EMAIL]').prop("readonly", true);
    }
    
    $('input[name=ADMIN_CONFIRM_ORDER]').click(function(){
            if($('input[name=PRODUCT_DYNAMIC_PRICE]:checked').val()==1){
            $('#ORDER_CNF_MANAGER_EMAIL').prop("readonly", false);
        }else{            
            $('#ORDER_CNF_MANAGER_EMAIL').prop("readonly", true);
        }
    });
        
});
