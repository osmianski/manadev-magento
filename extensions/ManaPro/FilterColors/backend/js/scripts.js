/**
 * @category    Mana
 * @package     ManaPro_FilterColors
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
// the following function wraps code block that is executed once this javascript file is parsed. Lierally, this 
// notation says: here we define some anonymous function and call it once during file parsing. THis function has
// one parameter which is initialized with global jQuery object. Why use such complex notation: 
// 		a. 	all variables defined inside of the function belong to function's local scope, that is these variables
//			would not interfere with other global variables.
//		b.	we use jQuery $ notation in not conflicting way (along with prototype, ext, etc.)
;(function(window, $) {
	// the following function is executed when DOM ir ready. If not use this wrapper, code inside could fail if
	// executed when referenced DOM elements are still being loaded.
	$(function() {
		$('#mf_general_display').live('change', function() {
			if ($('#mf_general_display').val() == 'colors' || $('#mf_general_display').val() == 'colors_vertical' || $('#mf_general_display').val() == 'colors_label') {
				$('#tabs_colors').parent().show();
			}
			else {
				$('#tabs_colors').parent().hide();
			}
		});
        $('#mf_colors_header_image_width').live('change', function() {
            $('td.c-color div, td.c-normal_image div, td.c-selected_image div, td.c-normal_hovered_image div, td.c-selected_hovered_image div, ' +
                '#image_mf_colors_header_image_normal, #image_mf_colors_header_image_selected, ' +
                '#image_mf_colors_header_image_normal_hovered, #image_mf_colors_header_image_selected_hovered')
                .css({'width': $(this).val() + 'px'});
        });
        $('#mf_colors_header_image_height').live('change', function() {
            $('td.c-color div, td.c-normal_image div, td.c-selected_image div, td.c-normal_hovered_image div, td.c-selected_hovered_image div, ' +
                '#image_mf_colors_header_image_normal, #image_mf_colors_header_image_selected, ' +
                '#image_mf_colors_header_image_normal_hovered, #image_mf_colors_header_image_selected_hovered')
                .css({'height': $(this).val() + 'px'});
        });
        $('#mf_colors_header_image_border_radius').live('change', function() {
            $('td.c-color div, td.c-normal_image div, td.c-selected_image div, td.c-normal_hovered_image div, td.c-selected_hovered_image div, ' +
                '#image_mf_colors_header_image_normal, #image_mf_colors_header_image_selected, ' +
                '#image_mf_colors_header_image_normal_hovered, #image_mf_colors_header_image_selected_hovered')
            .css({
                '-webkit-border-radius': $(this).val() + 'px',
                '-moz-border-radius': $(this).val() + 'px',
                'border-radius': $(this).val() + 'px'
            });
        });
        $('#mf_colors_header_state_width').live('change', function() {
            $('td.c-state_image div, #image_mf_colors_header_state_image')
                .css({'width': $(this).val() + 'px'});
        });
        $('#mf_colors_header_state_height').live('change', function() {
            $('td.c-state_image div, #image_mf_colors_header_state_image')
                .css({'height': $(this).val() + 'px'});
        });
        $('#mf_colors_header_state_border_radius').live('change', function() {
            $('td.c-state_image div, #image_mf_colors_header_state_image')
            .css({
                '-webkit-border-radius': $(this).val() + 'px',
                '-moz-border-radius': $(this).val() + 'px',
                'border-radius': $(this).val() + 'px'
            });
        });
	});
})(window, jQuery);
