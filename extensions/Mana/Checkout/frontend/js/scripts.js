/**
 * @category    Mana
 * @package     Mana_Checkout
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * the following function wraps code block that is executed once this javascript file is parsed. Lierally, this
 * notation says: here we define some anonymous function and call it once during file parsing. THis function has
 * one parameter which is initialized with global jQuery object. Why use such complex notation:
 *         a.     all variables defined inside of the function belong to function's local scope, that is these variables
 *            would not interfere with other global variables.
 *        b.    we use jQuery $ notation in not conflicting way (along with prototype, ext, etc.)
 */
(function ($, p$) {
    function _showPaymentMethodForm() {
        $('.payment-method-form ul').hide('fast');
        $('.payment-method:checked').each(function() {
            var dd = $(this).parent().next();
            if (dd.length && dd[0].tagName.toLowerCase()=='dd') {
                dd.find('ul').show('fast');
            }
        });
    }
    function _validatePaymentMethodForm() {
        var dd = $('.radio.payment-method:checked').parent().next();
        if (dd.length && dd[0].tagName.toLowerCase() == 'dd') {
            return _validateFieldsInsideElement(dd);
        }
        else {
            return true;
        }
    }
    function _enablePersonalMessageFields() {
        var fields = $('.message-to input,.message-from input,.message-text textarea');
        var labels = $('.message-to label,.message-from label,.message-text label');
        if ($('.message-send').is(':checked')) {
            fields.removeAttr('disabled');
            fields.removeClass('disabled');
            labels.removeClass('disabled');
        }
        else {
            fields.attr('disabled', 'disabled');
            fields.addClass('disabled');
            labels.addClass('disabled');
        }
    }

    function _validateFields(fields) {
        var result = true;
        fields.each(function(field) {
            if (p$(field)) {
                /* , {
                 useTitle:Validation.defaultOptions.useTitles,
                 onElementValidate:Validation.defaultOptions.onElementValidate
                 }*/
                //result = Validation.test(field, p$(field), false) && result;
                result = Validation.validate(p$(field), {}) && result;
            }
        });
        return result;
    }

    function _validateFieldsInsideElement(el) {
        var result = true;
        $(el).find('input,select,textarea').each(function() {
            result = Validation.validate(this, {}) && result;
        });
        return result;
    }
    function _validateOrder () {
        var result = true;

        // billing address
        result = _validateFieldsInsideElement('.billing-address-form') && result;

        // shipping address
        if (!$('.billing-use-for-shipping').is(':checked')) {
            result = _validateFieldsInsideElement('.shipping-address-form') && result;
        }

        // registration form
        if ($('.billing-create-account').is(':checked')) {
            result = _validateFieldsInsideElement('.create-account-form') && result;
        }

        // payment method
        if (!$('.radio.payment-method:checked').length) {
//            var advice = Validation.getAdvice('validate-payment-method', $('m-payment-method h2')[0]);
//            if (advice == null) {
//                advice = Validation.createAdvice('validate-payment-method', $('m-payment-method h2')[0], false, 'Please specify payment method.');
//            }
//            Validation.showAdvice($('m-payment-method h2')[0], advice, 'validate-payment-method');
//            Validation.updateCallback($('m-payment-method h2')[0], 'failed');
            alert('Please specify payment method.');
            result = false;
        }
        result = _validatePaymentMethodForm() && result;

        // personal message
        if ($('.message-send').is(':checked')) {
            result = _validateFieldsInsideElement('.message-form') && result;
        }


        return result;

    }

    function _showPlaceOrderAnimation() {
        $('#review-buttons-container')
            .addClass('disabled').css({opacity: 0.5})
            .find('*').attr('disabled', 'disabled');
        $('#review-please-wait').show();
    }

    function _hidePlaceOrderAnimation() {
        $('#review-please-wait').hide();
        $('#review-buttons-container')
            .removeClass('disabled').css({opacity:1})
            .find('*').removeAttr('disabled');
    }

    function _submitOrder() {
        if (!_validateOrder()) {
            return false;
        }

        _showPlaceOrderAnimation();
        $.post($.options('.m-checkout').placeOrderUrl, $('#checkout-form').serializeArray())
            .done(function(response) {
                response = $.parseJSON(response);
                if (!response) {
                    if ($.options('.m-checkout').debug) {
                        alert('No response.');
                    }
                }
                else if (response.error) {
                    if ($.options('.m-checkout').debug) {
                        alert(response.error);
                    }
                }
                else if (response.redirect) {
                    location.href = response.redirect;
                }
                else {
                    if ($.options('.m-checkout').debug) {
                        alert('Unexpected response');
                    }
                }
            })
            .fail(function(error) {
                if ($.options('.m-checkout').debug) {
                    alert(error.status + (error.responseText ? ': ' + error.responseText : ''));
                }
            })
            .complete(function() {
                _hidePlaceOrderAnimation();
            });
        return false;
    }

    $(function() {
        $(document).on('click', '.billing-use-for-shipping', function() {
            if ($(this).is(':checked')) {
                $('.shipping-address-form').hide('fast');
            }
            else {
                $('.shipping-address-form').show('fast');
            }
        });
        $(document).on('click', '.billing-create-account', function () {
            if (!$(this).is(':checked')) {
                $('.create-account-form').hide('fast');
            }
            else {
                $('.create-account-form').show('fast');
            }
        });

        _showPaymentMethodForm();
        $(document).on('click', '.payment-method', _showPaymentMethodForm);

        //_enablePersonalMessageFields();
        $(document).on('click', '.message-send', function () {
            if (!$(this).is(':checked')) {
                $('.message-form').hide('fast');
            }
            else {
                $('.message-form').show('fast');
            }
        });

        $(document).on('click', '.btn-checkout', _submitOrder);

        $('.cvv-what-is-this').mPopup('cvv-description');
    });
})(jQuery, $);
