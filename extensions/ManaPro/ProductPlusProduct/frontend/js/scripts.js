/**
 * @category    Mana
 * @package     ManaPro_ProductPlusProduct
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
// the following function wraps code block that is executed once this javascript file is parsed. Lierally, this 
// notation says: here we define some anonymous function and call it once during file parsing. THis function has
// one parameter which is initialized with global jQuery object. Why use such complex notation: 
// 		a. 	all variables defined inside of the function belong to function's local scope, that is these variables
//			would not interfere with other global variables.
//		b.	we use jQuery $ notation in not conflicting way (along with prototype, ext, etc.)
;(function($) {
	var _checkboxPrefix = 'm-bought-together-option-';
	var _productPrefix = 'm-bought-together-product-';
	var _options = null;
	
	function updateTotals() {
		// calculate totals
		var price = 0;
		var count = 0;
		$('.m-bought-together-options input[type="checkbox"]').each(function(checkboxIndex, checkbox) {
			if ($(checkbox).attr('checked')) {
				// find product id
				var id = checkbox.id;
				if (!id.match("^"+_checkboxPrefix)==_checkboxPrefix) {
					throw 'Unexpected checkbox id';
				}
				id = id.substring(_checkboxPrefix.length);
				
				price += _options.prices[id] * 1;
				count++;
			}
		});
		if (count > 10) {
			count = 0;
		}
		
		// update total price and labels
		$('.m-bought-together-subtotal-label').html(_options.pricelabels[count]+':');
		$('.m-bought-together-subtotal span span').html(_options.numberFormat.replace('0', price.toFixed(2)));
		$('.m-bought-together-add-to-box .btn-cart span span').html(_options.addToCartLabels[count]);
		$('.m-bought-together-add-to-box .link-wishlist').html(_options.addToWishlistLabels[count]);
		$('.m-bought-together-add-to-box .link-compare').html(_options.addToCompareLabels[count]);
	}
	
	// the following function is executed when DOM ir ready. If not use this wrapper, code inside could fail if
	// executed when referenced DOM elements are still being loaded.
	$(function() {
		if (_options = $.options('#m-bought-together')) {
			updateTotals();
		}
	});
	$('.m-bought-together-options input[type="checkbox"]').live('click', function() {
		// find product id
		var id = this.id;
		if (!id.match("^"+_checkboxPrefix)==_checkboxPrefix) {
			throw 'Unexpected checkbox id';
		}
		id = id.substring(_checkboxPrefix.length);
		
		// hide or show the product image
		var product = $('#'+_productPrefix+id);
		if ($(this).attr('checked')) {
			product.show();
		}
		else {
			product.hide();
		}
		
		// hide and show separators
		$('.m-bought-together-products .m-separator').each(function(separatorIndex, separator) {
			separator = $(separator);
			var stopped = false;
			var nextProduct = false;
			separator.nextAll().each(function(elementIndex, element) {
				if (!stopped && !$(element).hasClass('m-bought-together-summary')) {
					if ($(element).is(':visible')) {
						nextProduct = !$(element).hasClass('m-separator');
						stopped = true;
					}
				}
			});
			stopped = false;
			var prevProduct = false;
			separator.prevAll().each(function(elementIndex, element) {
				if (!stopped && !$(element).hasClass('m-bought-together-summary')) {
					if ($(element).is(':visible')) {
						prevProduct = !$(element).hasClass('m-separator');
						stopped = true;
					}
				}
			});
			if (nextProduct && prevProduct) {
				separator.show();
			}
			else {
				separator.hide();
			}
		});
		
		// hide and show summary
		if ($('.m-bought-together-products .m-product').filter(':visible').length) {
			$('.m-bought-together-summary').show();
			updateTotals();
		}
		else {
			$('.m-bought-together-summary').hide();
		}
	});
	
	$('.m-bought-together-summary .btn-cart').live('click', function() {
		if ($('#'+_checkboxPrefix+_options.productId).attr('checked')) {
			if (!productAddToCartForm.validator.validate()) {
				return false;
			}
		}
		$('#m-bought-together-product-info').val($('#product_addtocart_form').serialize());
		$('#m-bought-together-form').submit();
		return false;
	});
	$('.m-bought-together-add-to-box .add-to-links a').live('click', function() {
		$('#m-bought-together-form')[0].setAttribute('action', this.href);
		$('#m-bought-together-form').submit();
		return false;
	});
})(jQuery);
