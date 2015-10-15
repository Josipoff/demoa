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
reloadQuote(true);	
if(parseInt($("#decoration_added").val())>0){
    $('#quantity_wanted').prop('disabled',true);
    $('#quan').prop('disabled',true);
}else{
    $('#quantity_wanted').prop('disabled',false);
    $('#quan').prop('disabled',false);
}


enable_add_btn();

$('#upload_file_preview').click(function(){
    var filename = $(this).attr("filename");
    $('#image_upload_preview').prop("src",filename);
})

$('.upload_file_preview').fancybox({
			'autoSize' : false,
			'width' : 600,
			'height' : 'auto',
			'hideOnContentClick': false
		});

$('input:file').change(
            function(){
                if ($(this).val()) {
                    var deco_id = $(this).attr('deco_id');
                    $("button[deco_id='"+deco_id+"']").attr('disabled',false);
                    // or, as has been pointed out elsewhere:
                    // $('input:submit').removeAttr('disabled'); 
                } 
            }
            );

$('.calc_input').change(function(){
    enable_add_btn();
    reloadQuote(true);
})

function saveCustomization(deco_id)
{
	$('#quantityBackup').val($('#quantity_wanted').val());
	customAction = $('#customizationForm').attr('action');
	$('body select[id^="group_"]').each(function() {
		customAction = customAction.replace(new RegExp(this.id + '=\\d+'), this.id +'=' + this.value);
	});
	$('#customizationForm').attr('action', customAction);
        
        $.post($("#customizationForm").attr("action"), $("#customizationForm").serialize(),
          function(data) {
              $('#upload_file_'+deco_id).submit();
          });
}

function deleteCustomization(deco_id)
{
	$('#quantityBackup').val($('#quantity_wanted').val());
	customAction = $('#customizationForm').attr('action');
	$('body select[id^="group_"]').each(function() {
		customAction = customAction.replace(new RegExp(this.id + '=\\d+'), this.id +'=' + this.value);
	});
	$('#customizationForm').attr('action', customAction);
        
        $.post($("#customizationForm").attr("action"), $("#customizationForm").serialize(),
          function(data) {
              $('#delete_file_'+deco_id).submit();
          });
}

        $(".deco_delete").unbind("click").on("click",function(e){
           var deco_id=parseInt($(this).attr('deco_id'));
           if(deco_id){
           var deco_id1 = (deco_id)-1;
           var deco_c = parseInt($(this).attr('count'));
           var deco = $('#textField'+deco_id1).html();
           deco_str=$('#deco_info_'+deco_c).html();
           deco_str +='-'+$(this).attr('deco_key');          
           deco_array=deco.split(';');
           if(deco_array[deco_array.length-1]==deco_str)
                deco = deco.replace(deco_str,'');
            else
                deco = deco.replace(deco_str+';','')
           $('#textField'+deco_id1).html(deco);
           
           var deco_price = $('#textField'+(deco_id1+1)).html();
           deco_price_str=$('#deco_price_'+deco_c).html();
           deco_price_array=deco_price.split(';');
           if(deco_price_array[deco_price_array.length-1]==deco_price_str)
                deco_price = deco_price.replace(deco_price_str,'');
            else
                deco_price = deco_price.replace(deco_price_str+';','')
           $('#textField'+(deco_id1+1)).html(deco_price);
           var count = $("#textField0").val();
           count = parseInt(count)-1;
           if(count==0)
           $("#textField0").html("");
            else   
           $("#textField0").html(count);
           deleteCustomization(deco_c);
       }
       return false;
        });



$(document).on('blur', '#quan', function(e)
	{
            $('input[name=qty]').val($('#quan').val()).trigger('keyup');
           reloadQuote(true);
        });

$(document).on('blur', '#margin_val', function(e)
	{
           loadMarginTable();
        });
        
 $(document).on('change', 'input:radio[name=id_margin_option]', function(e)
	{
           loadMarginTable();
        });
        
        $('#deco_cart_btn').click(function(){
           $('#add_to_cart button').click(); 
        });
        
        $('.deco_add').click(function(e){
           e.preventDefault();
           var deco_id=parseInt($(this).attr('deco_id'));
           var deco_id1 = (deco_id)*2;
           var deco = $('#textField'+deco_id1).html();
           if(deco!='')
               deco +=';';
           deco +=$('#area_'+deco_id).html()+','+$('#tec_'+deco_id).html()+',';
           deco += $('#size_'+deco_id).html().substring(0,1)+','+$('#color_'+deco_id).val();
           deco +='-'+$('#deco_key_'+deco_id).val();          
           $('#textField'+deco_id1).html(deco);
           
           var deco_price = $('#textField'+(deco_id1+1)).html();
           if(deco_price!='')
               deco_price +=';';
           deco_price +=$('#tdeco_'+deco_id).html();
           $('#textField'+(deco_id1+1)).html(deco_price);
           var count = $("#textField0").val();
           if(count=='')
               count = 1;
           else
               count = parseInt(count)+1;
           $("#textField0").html(count);
           saveCustomization(deco_id);
        });
        
        
     
        
        
});

function enable_add_btn(){
    var checkboxID = '';
    for(i=1;i<=parseInt($('#max_deco').val());i++){
            var check_id = '#deco_'+i;
            if($(check_id).is(':checked')){
                $('#add_'+i).removeClass('disabled');    
                checkboxID +=i+',';
            }else{
                $('#add_'+i).addClass('disabled');
            }
        }
        $("#textField1").html(checkboxID.substring(0,checkboxID.length-1));
}


function stopAjaxQuery() {
	if (typeof(ajaxQueries) == 'undefined')
		ajaxQueries = new Array();
	for(i = 0; i < ajaxQueries.length; i++)
		ajaxQueries[i].abort();
	ajaxQueries = new Array();
}

function loadcheckbox(){
    var checkbox1 = "";
    if($("#textField1").html()){
    checkbox1 = $("#textField1").html();
}
    var check_array = checkbox1.split(",");
    for(var i=0; i<check_array.length;i++){
        var check_id = '#deco_'+check_array[i];
        $(check_id).attr('checked', true);
    }
}

function reloadQuote(params_plus)
{
	stopAjaxQuery();
        loadcheckbox();
	
        var i=1,j=0;
        var technique='', area='', color='', size='';
        for(i=1;i<=parseInt($('#max_deco').val());i++){
            var check_id = '#deco_'+i;
            if($(check_id).is(':checked')){
                technique += $('#tecnica_'+i).val()+',';
                size += $('#size_'+i).html().substring(0,1)+',';
                area += $('#area_'+i).html()+',';
                color += $('#color_'+i).val()+',';
                j++;
            }
        }
        technique = technique.substring(0, technique.length - 1);
        size = size.substring(0, size.length - 1);
        area = area.substring(0, area.length - 1);
        color = color.substring(0, color.length - 1);
        if(j!=0){
            if (!ajaxLoaderOn)
	{
		$('#myCustomHook').prepend($('#layered_ajax_loader').html());
		$('#myCustomHook').css('opacity', '0.7');
		ajaxLoaderOn = 1;
	}
        
	ajaxQuery = $.ajax(
	{
		type: 'GET',
		url: baseDir + 'modules/customquote/customquote-ajax.php',
		data:"quantity="+$('#quan').val()+"&item_no="+$('#item_number').val()+"&technique="+technique+"&area="+area+"&size="+size+"&color="+color,                   
                dataType: 'json',
		cache: false, // @todo see a way to use cache and to add a timestamps parameter to refresh cache each 10 minutes for example
		success: function(result)
		{
                    result = JSON.parse(JSON.stringify(result));
                    var i=1;
                    if(result['Prints']){
                    var prints = result['Prints'].split(',');
                    var tdeco = result['TotalDecorated'].split(',');
                    var count=0;
                    var deco = '';
                    for(i=1;i<=parseInt($('#max_deco').val());i++){
                        var check_id = '#deco_'+i;
                        if($(check_id).is(':checked')){
                            $('#print_'+i).html(parseInt(prints[count]));
                            $('#tdeco_'+i).html(parseFloat(tdeco[count]).toFixed(2));
                            deco += $('#tec_'+i).html() + ', ';
                            count++;
                        }else{
                            $('#print_'+i).html("0");
                            $('#tdeco_'+i).html("0");                            
                        }
                    }
                    $('#psingle').html(parseFloat(result['ProductPriceSingle']).toFixed(2));
                    $('#pprice').html(parseFloat(result['ProductPrice']).toFixed(2));
                    $('#setup_single').html(parseFloat(result['SetupChargeSingle']).toFixed(2));
                    $('#psetup').html(parseFloat(result['SetupCharge']).toFixed(2));
                    $('#pdeco_single').html(parseFloat(result['DecoratedPriceSingle']).toFixed(2));
                    $('#pdeco').html(parseFloat(result['DecoratedPrice']).toFixed(2));
                    $('#tsingle').html(parseFloat(result['TotalSingle']).toFixed(2));
                    $('#total').html(parseFloat(result['Total']).toFixed(2));
                    $('#mprice').html(parseFloat(result['TotalSingle']).toFixed(2));
                    $('#mprice_tot').html(parseFloat(result['Total']).toFixed(2));
                    loadMarginTable();
                    $('#ajax-loader').remove();
                    $('#myCustomHook').css('opacity', '1.0');
                    ajaxLoaderOn = 0;
                }
		}
	});
	ajaxQueries.push(ajaxQuery);
    }else{
         for(i=1;i<=parseInt($('#max_deco').val());i++){
                $('#print_'+i).html("0");
                 $('#tdeco_'+i).html("0");      
         }
         $('#psingle').html("0.00");
         $('#pprice').html("0.00");
         $('#setup_single').html("0.00");
         $('#psetup').html("0.00");
         $('#pdeco_single').html("0.00");
         $('#pdeco').html("0.00");
         $('#tsingle').html("0.00");
         $('#total').html("0.00");
         $('#mprice').html("0.00");
         $('#mprice_tot').html("0.00");
         loadMarginTable();        
    }
}

function loadMarginTable(){
    var mtype=$('input:radio[name=id_margin_option]:checked').val();
    if(mtype=="2"){
        var pSingle = parseFloat($('#mprice').html());
        var pTotal = parseFloat($('#mprice_tot').html());
        var margin = parseFloat($('#margin_val').val());
        var mSingle = (margin/100)*pSingle;
        var mTotal = (margin/100)*pTotal;
        $('#margin').html(parseFloat(mSingle).toFixed(2));
        $('#mtotal').html(parseFloat(mTotal).toFixed(2));
        $('#mprice_margin').html(parseFloat(mSingle+pSingle).toFixed(2));
        $('#mprice_margin_tot').html(parseFloat(mTotal+pTotal).toFixed(2));
    }
    
     if(mtype=="1"){
        var pSingle = parseFloat($('#mprice').html());
        var pTotal = parseFloat($('#mprice_tot').html());
        var margin = parseFloat($('#margin_val').val());
        var mSingle = pSingle/((100-margin)/100);
        var mTotal = pTotal/((100-margin)/100);
        $('#margin').html(parseFloat(mSingle-pSingle).toFixed(2));
        $('#mtotal').html(parseFloat(mTotal-pTotal).toFixed(2));
        $('#mprice_margin').html(parseFloat(mSingle).toFixed(2));
        $('#mprice_margin_tot').html(parseFloat(mTotal).toFixed(2));
    }
}
