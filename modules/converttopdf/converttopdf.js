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

function stopAjaxQuery() {
	if (typeof(ajaxQueries) == 'undefined')
		ajaxQueries = new Array();
	for(i = 0; i < ajaxQueries.length; i++)
		ajaxQueries[i].abort();
	ajaxQueries = new Array();
}

function getBase64FromImageUrl(URL) {
    var img = new Image();
    img.src = URL;
    var canvas = document.createElement("canvas");
    canvas.width =img.width;
    canvas.height =img.height;

    var ctx = canvas.getContext("2d");
    ctx.drawImage(img, 0, 0);


    var dataURL = canvas.toDataURL("image/png");
    canvas.remove();
    return dataURL;
}

$("#download_btn").click(function(){
         var doc = new jsPDF('p','pt');
        var footer = function (doc, lastCellPos, pageCount, options) {
            var d = new Date();
            var date = d.toUTCString();

            var str = "Esta informacion fue generada el  " + date ;
            doc.text(str, options.margins.horizontal, doc.internal.pageSize.height - 30);
        };
        var startY = 40;
        // All units are in the set measurement for the document
        // This can be changed to "pt" (points), "mm" (Default), "cm", "in"
        var product = JSON.parse(product_pdf);
        doc.text(product.name, 40, 48);
        doc.setFontSize(12);
        doc.text("SKU: "+ product.reference, 40, 68);
        doc.text("Condition: "+ product.condition, 40, 88);
        var details = doc.splitTextToSize(product.details, 500);
        doc.text("Details: ", 40, 108);
        doc.text(40, 128, details);
        var lines = details.length;
        startY = 128 + lines*20;
        doc.text("Price: "+ product.price, 40, startY);
        startY= startY+20;
        doc.text("Color: "+ product_color, 40, startY);
        startY= startY+20;
        doc.text("Decoration Techniques: "+ product.decoration, 40, startY);
        startY= startY+40;
        
        doc.text("Inventory", 40, startY);
        var options = {renderFooter: footer, margins: {horizontal: 40, top: 80, bottom: 50},extendWidth: false, padding: 6, lineHeight: 12, fontSize: 8,startY:startY+20};
        doc.autoTable(JSON.parse(inventory_table_column), JSON.parse(inventory_table_data),options);
	startY = doc.autoTableEndPosY()+40;
        doc.text("Quick Quote Table", 40, startY);
        startY = startY + 20;
        var specialElementHandlers = {
	'#editor': function(element, renderer){
		return true;
            }
        };

// All units are in the set measurement for the document
// This can be changed to "pt" (points), "mm" (Default), "cm", "in"
        doc.fromHTML($('.quote-table1').get(0), 40, startY, {
                'width': 170, 
                'elementHandlers': specialElementHandlers
        });
        
        startY = startY+80;
    
        doc.autoTable(JSON.parse(quick_quote_table_column), JSON.parse(quick_quote_table_data),{extendWidth: false, padding: 14, lineHeight: 12, fontSize: 8,startY:startY});
	startY = doc.autoTableEndPosY()+40;
        doc.addPage();
        doc.text("Images", 40, 48);
        startY = 60;
        var images = JSON.parse(images_pdf);
        var j=0;
        $.each(images, function(index, value){
            var imgData = getBase64FromImageUrl(value);
            doc.addImage(imgData, 'PNG', 15, startY);
            j++;
            startY = startY + 280;
            if(j==3){
                doc.addPage();
                j=0;
                startY = 40;
            }
        });
        doc.save('ProductInfo.pdf');
    })
})