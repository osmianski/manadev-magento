/**
 * IDEALIAGroup srl
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@idealiagroup.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category   IG
 * @package    IG_LightBox
 * @copyright  Copyright (c) 2010-2011 IDEALIAGroup srl (http://www.idealiagroup.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Riccardo Tempesta <tempesta@idealiagroup.com>
*/

/* BEGIN: Configurable parameters */
var ig_lightbox_wrap_images				= 1;
var ig_lightbox_main_img				= 0;
var ig_lightbox_img_border				= 10;
var ig_lightbox_cmd_box_height			= 32;
var ig_lightbox_initial_width			= 100;
var ig_lightbox_initial_height			= 100;
var ig_lightbox_background_opactiy		= 0.8;
var ig_lightbox_imagebox_opactiy		= 1.0;
var ig_lightbox_toolbar_opacity			= 0.8;
var ig_lightbox_background_color		= '#000000';
var ig_lightbox_imagebox_color			= '#000000';
var ig_lightbox_toolbar_color			= '#000000';
var ig_lightbox_toolbar_text_size		= 12;
var ig_lightbox_toolbar_text_font		= 'Verdana';
var ig_lightbox_toolbar_text_color		= '#ffffff';
var ig_lightbox_border_size				= 1;
var ig_lightbox_border_color			= '#909090';
var ig_lightbox_fade_in_duration		= 1.0;
var ig_lightbox_fade_out_duration		= 1.0;
var ig_lightbox_image_resize_duration	= 1.0;
var ig_lightbox_image_swap_duration		= 1.0;
/* END: Configurable parameters */

var ig_lightbox_img_sequence	= new Array();
var ig_lightbox_img_labels		= new Array();
var ig_lightbox_cur_image_n		= -1;
var ig_lightbox_win_is_open		= false;
var ig_lightbox_commands_shown	= false;
var ig_lightbox_preload_done	= false;

function ig_lightbox_prev()
{
	ig_lightbox_cur_image_n--;
	if (ig_lightbox_cur_image_n<0) ig_lightbox_cur_image_n=ig_lightbox_img_sequence.length-1;

	ig_lightbox_show(ig_lightbox_cur_image_n);
}

function ig_lightbox_next()
{
	ig_lightbox_cur_image_n++;
	if (ig_lightbox_cur_image_n>=ig_lightbox_img_sequence.length) ig_lightbox_cur_image_n=0;

	ig_lightbox_show(ig_lightbox_cur_image_n);
}

function ig_lightbox_hide()
{
	$('ig-lightbox-image-commands').setStyle({ visibility : 'hidden' });
	$('ig-lightbox-image-close').setStyle({ display : 'none' });

	new Effect.Opacity('ig-lightbox-image', {
		duration	: ig_lightbox_fade_out_duration,
		from		: ig_lightbox_imagebox_opactiy,
		to			: 0.0,
		afterFinish	: function (effect) {
			ig_lightbox_reset();
		}
	});
	
	new Effect.Opacity('ig-lightbox-back', {
		duration	: ig_lightbox_fade_out_duration,
		from		: ig_lightbox_background_opactiy,
		to			: 0.0
	});
	
	$('ig-lightbox-back').setStyle({
		width			: '10px',
		height			: '10px'
	});

	new Effect.Fade('ig-lightbox-image-src', {
		duration	: ig_lightbox_fade_out_duration
	});
	
	$('ig-lightbox-image-commands-label-td').setStyle({
		width			: '10px'
	});
	
 	document.body.style.overflowX="auto";
}

function ig_lightbox_show(n)
{
	if (!ig_lightbox_img_sequence.length) return;
	
	if (n<0) n=ig_lightbox_main_img;
	
 	document.body.style.overflowX="hidden";
	
// 	if (typeof(window.innerHeight) == "undefined")
// 	{
// 		var win_width	= document.documentElement.clientWidth;
// 		var win_height	= document.documentElement.clientHeight;
//  		var win_height2	= document.documentElement.offsetHeight;
// 		vvar win_height2	= document.getElementsByTagName('body')[0].clientHeight
// 
// 	}
// 	else
// 	{
// 		var win_width	= window.innerWidth;
// 		var win_height	= Math.max(window.innerHeight, document.documentElement.offsetHeight);
// 		var win_height2	= Math.max(window.innerHeight, document.documentElement.offsetHeight);
// 	}
	
	var win_width	= document.getElementsByTagName('body')[0].clientWidth;
	var win_height	= document.documentElement.clientHeight;
	var win_height2	= Math.max(document.documentElement.offsetHeight, win_height);

	var img_loader	= new Image();

	img_loader.onload=function ()
	{
		var img_height		= img_loader.height;
		var img_width		= img_loader.width;

		ig_lightbox_cur_image_n	= n;
		
		if (!ig_lightbox_wrap_images) 
		{
			$('ig-lightbox-next').setStyle({
				'display': ((ig_lightbox_cur_image_n == ig_lightbox_img_sequence.length - 1) ? 'none' : 'block')
			})
			$('ig-lightbox-prev').setStyle({
				'display': ((ig_lightbox_cur_image_n == 0) ? 'none' : 'block')
			})
		}

		if (ig_lightbox_win_is_open)
		{
			new Effect.Fade('ig-lightbox-image-src',{
				duration	: ig_lightbox_image_swap_duration/2,
				afterFinish	: function (effect) {
				
					new Effect.Morph('ig-lightbox-image', {
						duration	: ig_lightbox_image_resize_duration,
						style		:
							'width	: '+img_width+'px;'+
							'height	: '+img_height+'px;'+
							'left 	: '+Math.floor(((win_width-img_width)/2)-2)+'px;'+
							'top 	: '+Math.floor(((win_height-img_height)/2)-1)+'px',
						afterFinish	: function (effect) {
							$('ig-lightbox-image-src').src	= ig_lightbox_img_sequence[ig_lightbox_cur_image_n];

							$('ig-lightbox-image-src').setStyle({
								left 	: Math.floor((win_width-img_width)/2+ig_lightbox_img_border+1)+'px',
								top 	: Math.floor((win_height-img_height)/2+ig_lightbox_img_border+1)+'px',
								width	: img_width+'px',
								height	: img_height+'px'
							});

							new Effect.Fade('image-label', {
								duration	: ig_lightbox_image_swap_duration/2,
								afterFinish	: function (effect) {
									$('image-label').innerHTML	= ig_lightbox_img_labels[ig_lightbox_cur_image_n];

									new Effect.Appear('image-label', {
										duration	: ig_lightbox_image_swap_duration/2
									});
								}
							});

							new Effect.Appear('ig-lightbox-image-src',{
								duration	: ig_lightbox_image_swap_duration/2
							});
						}
					});

					var img_left	= Math.floor((win_width-img_width)/2);
					var img_top		= Math.floor((win_height-img_height)/2);

					if (!ig_lightbox_commands_shown)
					{
						$('ig-lightbox-image-commands').setStyle({
							width	: img_width+'px',
							left	: Math.floor(img_left+ig_lightbox_img_border+1)+'px',
							top		: Math.floor(img_top+img_height/*-ig_lightbox_cmd_box_height*/+ig_lightbox_img_border)+'px'
						});
						
						$('ig-lightbox-image-commands-label-td').setStyle({
							width	: '100%'
						});

						$('ig-lightbox-image-close').setStyle({
							left	: Math.floor(img_left+img_width+(ig_lightbox_img_border*2)-($('ig-lightbox-image-close-img').width/2))+'px',
							top		: Math.floor(img_top-($('ig-lightbox-image-close-img').height/2))+'px'
						});

						$('ig-lightbox-image-commands').setStyle({ visibility : 'visible' });
						new Effect.Opacity('ig-lightbox-image-commands', {
							duration	: ig_lightbox_image_resize_duration+ig_lightbox_fade_in_duration,
							from		: 0.0,
							to			: ig_lightbox_toolbar_opacity
						});

						new Effect.Appear('ig-lightbox-image-close', {
							duration	: ig_lightbox_image_resize_duration+ig_lightbox_fade_in_duration
						});

						ig_lightbox_commands_shown=true;
					}
					else
					{
						new Effect.Morph('ig-lightbox-image-commands', {
							style		:
								'width	: '+img_width+'px;'+
								'left	: '+Math.floor((win_width-img_width)/2+ig_lightbox_img_border+1)+'px;'+
								'top	: '+Math.floor(img_top+img_height/*-ig_lightbox_cmd_box_height*/+ig_lightbox_img_border)+'px',
							duration	: ig_lightbox_image_resize_duration
						});

						new Effect.Morph('ig-lightbox-image-close', {
							style		:
								'left		: '+Math.floor(img_left+img_width+(ig_lightbox_img_border*2)-($('ig-lightbox-image-close-img').width/2))+'px;'+
								'top		: '+Math.floor(img_top-($('ig-lightbox-image-close-img').height/2))+'px;',
							duration	: ig_lightbox_image_resize_duration
						});
					}
				}
			});


		}
		else
		{
			$('ig-lightbox-image').setStyle({
				left	: Math.floor((win_width-ig_lightbox_initial_width)/2)+'px',
				top		: Math.floor((win_height-ig_lightbox_initial_height)/2)+'px'
			});

			$('ig-lightbox-image-commands').setStyle({
				height	: ig_lightbox_cmd_box_height+'px',
				display	: 'block'
			});

			$('ig-lightbox-image').setStyle({ visibility : 'visible' });
			new Effect.Opacity('ig-lightbox-image', {
				duration	: ig_lightbox_fade_in_duration,
				from		: 0.0,
				to			: ig_lightbox_imagebox_opactiy
			});

			ig_lightbox_win_is_open=true;
			ig_lightbox_show(n);
		}
	}
	
	if (ig_lightbox_win_is_open) 
	{
		if (!ig_lightbox_preload_done) 
		{
			new Effect.Opacity('ig-lightbox-loading', {
				duration: ig_lightbox_fade_in_duration,
				from: 1.0,
				to: 0.0
			});
			
			$('ig-lightbox-loading').setStyle({
				visibility	: 'hidden',
				left		: -1000,
				top			: -1000
			});
			
			for (var i=0; i<ig_lightbox_img_sequence.length; i++)
			{
				var foo = new Image();
				foo.src = ig_lightbox_img_sequence[i];
			}
			
			ig_lightbox_preload_done = true;
		}
	}
	else
	{
		$('ig-lightbox-back').setStyle({
			width: win_width + 'px',
			height: win_height2 + 'px',
			visibility: 'visible'
		});
		
		new Effect.Opacity('ig-lightbox-back', {
			duration: ig_lightbox_fade_in_duration,
			from: 0.0,
			to: ig_lightbox_background_opactiy
		});

		$('ig-lightbox-loading').setStyle({
			left: Math.floor((win_width-$('ig-lightbox-loading').getWidth())/2)+'px',
			top: Math.floor((win_height-$('ig-lightbox-loading').getHeight())/2)+'px',
			visibility: 'visible'
		});
		
		if (!ig_lightbox_preload_done) 
		{
			new Effect.Opacity('ig-lightbox-loading', {
				duration: ig_lightbox_fade_in_duration,
				from: 0.0,
				to: 1.0
			});
		}
	}

	img_loader.src = ig_lightbox_img_sequence[n];
}

function ig_lightbox_reset()
{
	ig_lightbox_win_is_open			= false;
	ig_lightbox_commands_shown		= false;

	$('ig-lightbox-image').setStyle({
		paddingLeft		: ig_lightbox_img_border+'px',
		paddingTop			: ig_lightbox_img_border+'px',
		paddingRight		: ig_lightbox_img_border+'px',
		paddingBottom		: 32+ig_lightbox_img_border+'px',
		width		: ig_lightbox_initial_width+'px',
		height		: ig_lightbox_initial_height+'px',
		border		: ig_lightbox_border_size+'px solid '+ig_lightbox_border_color
	});

	$('ig-lightbox-image').setStyle({
		visibility	: 'hidden',
		background	: ig_lightbox_imagebox_color,
		opacity		: 0.0
	});
	$('ig-lightbox-image-commands').setStyle({
		visibility		: 'hidden',
		background		: ig_lightbox_toolbar_color,
		opacity			: 0.0,
		'font-size'		: ig_lightbox_toolbar_text_size,
		'font-family'	: ig_lightbox_toolbar_text_font,
		color			: ig_lightbox_toolbar_text_color
	});
	$('ig-lightbox-image-close').setStyle({ display : 'none' });
	$('ig-lightbox-back').setStyle({
		visibility	: 'hidden',
		background	: ig_lightbox_background_color,
		opacity		: 0.0
	});
	
	$('ig-lightbox-loading').setStyle({
		visibility	: 'hidden',
		opacity		: 0.0,
		left		: -1000,
		top			: -1000
	});

	$('ig-lightbox-image-src').src="";
	new Effect.Fade('ig-lightbox-image-src', {
		duration	: 0.1
	});
	
	$('ig-lightbox-back').setStyle({
		width			: '10px',
		height			: '10px'
	});
	
	$('ig-lightbox-image-commands-label-td').setStyle({
		width			: '10px'
	});
}

function ig_lightbox_init()
{
	ig_lightbox_reset();
	Event.observe($('ig-lightbox-back'), 'click', function () {
		ig_lightbox_hide();
	});

	Event.observe(window, 'keypress', function (e) {
		if (!ig_lightbox_win_is_open) return true;

		if (e.keyCode == 27) ig_lightbox_hide();
	});
	
	ig_lightbox_wrap_images = parseInt(ig_lightbox_wrap_images);
	ig_lightbox_main_img = parseInt(ig_lightbox_main_img);
	ig_lightbox_img_border = parseInt(ig_lightbox_img_border);
	ig_lightbox_cmd_box_height = parseInt(ig_lightbox_cmd_box_height);
	ig_lightbox_initial_width = parseInt(ig_lightbox_initial_width);
	ig_lightbox_initial_height = parseInt(ig_lightbox_initial_height);
	ig_lightbox_background_opactiy = parseFloat(ig_lightbox_background_opactiy);
	ig_lightbox_imagebox_opactiy = parseFloat(ig_lightbox_imagebox_opactiy);
	ig_lightbox_toolbar_opacity	= parseFloat(ig_lightbox_toolbar_opacity);
	ig_lightbox_toolbar_text_size = parseInt(ig_lightbox_toolbar_text_size);
	ig_lightbox_border_size = parseInt(ig_lightbox_border_size);
	ig_lightbox_fade_in_duration = parseFloat(ig_lightbox_fade_in_duration);
	ig_lightbox_fade_out_duration = parseFloat(ig_lightbox_fade_out_duration);
	ig_lightbox_image_resize_duration = parseFloat(ig_lightbox_image_resize_duration);
	ig_lightbox_image_swap_duration	= parseFloat(ig_lightbox_image_swap_duration);
}

Event.observe(window, 'load', function() {
	ig_lightbox_init();
});
