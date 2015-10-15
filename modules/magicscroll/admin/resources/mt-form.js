if(jQueryNoConflictLevel == 1) {
    var $jq = jQuery.noConflict();
} else if(jQueryNoConflictLevel == 2) {
    var $jq = jQuery.noConflict(true);
} else {
    var $jq = jQuery;
}
$jq(document).ready(function($) {
    $('.mt-tabs a[data-mt-tab]').click(function(e){
    	$('.mt-tabs a[data-mt-tab], .mt-tab-pane').removeClass('mt-active');
    	$('#'+$(this).attr('data-mt-tab')).addClass('mt-active');
    	$(this).addClass('mt-active');
        $('#magicscroll-active-tab').val($(this).attr('id').replace('-tab', ''));
    	e.preventDefault();
    })

    $('.mt-settings-form a.show-upgrade-instructions').click(function(e){
    	$(this).parent().parent().find('ol').show();
    	e.preventDefault();
    })
    
    $(".mt-parameter-keyword").keyup(function(){
        var filter = $(this).val().trim();

        var searchsource = $('#'+$(this).attr('data-search-source'));

        searchsource.find("fieldset").attr('data-hidden',1);

        searchsource.find(".mt-param-name").each(function(){
            $(this).removeHighlight();
            $(this).parent().removeClass('mt-not-matched-search');
            if ($(this).text().search(new RegExp(filter, "i")) < 0) {
            	$(this).parent().addClass('mt-not-matched-search');
                //$(this).parent().next().fadeOut(0);            
            } else {
                $(this).highlight(filter);
                //$(this).parent().parent().parent().attr('data-hidden',0);
            }
        });

        mt_reset_fieldsets(searchsource);

    });

    $('.mt-show-hide-advanced').click(function(){
        var searchsource = $('#'+$(this).attr('data-search-source'));
        if ($(this).is(':checked')) {
        	searchsource.addClass('mt-show-advanced');
        } else {
        	searchsource.removeClass('mt-show-advanced');
        }

        $(".mt-parameter-keyword").trigger('keyup')

        mt_reset_fieldsets(searchsource);
    })

    $('.mt-table .mt-icon-trash').mouseover(function(){
    	$(this).parents('tr:first').addClass('mt-red');
    });
    $('.mt-table .mt-icon-trash').mouseout(function(){
    	$(this).parents('tr:first').removeClass('mt-red');
    });

    function mt_reset_fieldsets(searchsource) {
        searchsource.find("fieldset").each(function(){
        	var visible = false;
			$(this).find('.mt-form-item').each(function(){
				visible = visible || !($(this).hasClass('mt-not-matched-search') || $(this).hasClass('mt-advanced') && !$(this).parents('.mt-tab-pane:first').find('.mt-show-hide-advanced:first').prop('checked') );
			})
            if (visible) {
                $(this).show();
            } else {
                $(this).fadeOut(0);
            }
        });
    }

    $('div.mt-buttons').each(function() {    
    	var topPosition = $(this).offset().top;
		$(this).affix({
    		offset: {
    			top: function () {
        				return (this.top = topPosition)
      				}
    		}
  		});
    });

    $('input.mt-button').click(function(e){
        $('#magicscroll-submit-action').val($(this).attr('data-submit-action'));
        e.preventDefault();
        $('#magictoolbox-settings-form').submit();
    });

    mt_reset_fieldsets($('.mt-tab-pane:not([data-skip-showhide])'));

    $('a.mt-switch-option-link').click(function(e){
        var name = $(this).attr('data-name');
        var generalName = $(this).attr('data-general-name');

        if($(this).hasClass('option-disabled')) {
            $('#magictoolbox-settings-form [name=\''+name+'\']').removeAttr('disabled');
            $(this).html('use default option').removeClass('option-disabled');
        } else {
            var elements = $('#magictoolbox-settings-form').find('select[name=\''+name+'\'], input[type=\'text\'][name=\''+name+'\']');
            if(elements.length) {
                var value = $('#magictoolbox-settings-form [name=\''+generalName+'\']').val();
                elements.val(value).attr('disabled', true);
            } else {
                elements = elements.end().find('input[type=\'radio\'][name=\''+name+'\']');
                var value = $('#magictoolbox-settings-form [name=\''+generalName+'\']:checked').val();
                elements.val([value]).attr('disabled', true);
            }
            $(this).html('edit').addClass('option-disabled');
        }
        return false;
    });

    $('#mt-tab-0').find('select, input[type=\'text\']').bind('change', function(){
        var value = $(this).val();
        var id = this.id.replace(magictoolboxProfiles[0]+'-', '');
        for(mtProfileIndex in magictoolboxProfiles) {
            if(mtProfileIndex == 0) continue;
            $('#'+magictoolboxProfiles[mtProfileIndex]+'-'+id+':disabled').val(value);
        }
    }).end().find('input[type=\'radio\']').bind('change', function(){
        var value = $(this).val();
        var name = '';
        for(mtProfileIndex in magictoolboxProfiles) {
            if(mtProfileIndex == 0) continue;
            name = this.name.replace(magictoolboxProfiles[0], magictoolboxProfiles[mtProfileIndex]);
            $('input[name=\''+name+'\']:disabled').val([value]);
        }
    });

});
