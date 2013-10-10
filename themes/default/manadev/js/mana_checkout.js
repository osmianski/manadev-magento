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
    Mana.require(['singleton:Mana/Core/Ajax','singleton:Mana/Core/Config'], function(ajax, config) {
        //region Expand-collapse in billing address
        function expand(element, duration) {
            $(element).removeClass('m-collapsed').addClass('m-expanded');
            $(element).next().slideDown(duration);
        }

        function collapse(element, duration) {
            $(element).removeClass('m-expanded').addClass('m-collapsed');
            $(element).next().slideUp(duration);
        }

        $(function () {
            $('.m-billing-address .m-collapseable').each(function (index, element) {
                if ($(element).attr('data-initially') == 'collapsed') {
                    collapse(element, 0);
                }
                else {
                    expand(element, 0);
                }
            }).live('click', function () {
                    var element = this;
                    if ($(element).hasClass('m-expanded')) {
                        collapse(element, 200);
                    }
                    else {
                        expand(element, 200);
                    }
                });
        });
        //endregion

        //region Update country when email address changes
        var _email;
        function _domain(internetAddress) {
            var pos = internetAddress.lastIndexOf('.');
            return (pos == -1) ? '' : internetAddress.substr(pos);

        }
        var _gettingCountryByEmail = false;
        function _updateCountryByEmail() {
            if (!$('.billing-country').hasClass('updatable')) {
                return;
            }
            if (_gettingCountryByEmail) {
                return;
            }
            _gettingCountryByEmail = true;
            _showUpdatingAnimation();
            var gettingEmail = _email;
            $.get($.options('.m-checkout').countryByEmailUrl.replace('__0__', _email))
                .done(function (response) {
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
                    else if (response.countryId) {
                        $('.billing-country select')
                            .val(response.countryId);

                        _updateOrderRequest.updateTotals = true;
                        _updateOrderDetails();

                    }
                })
                .fail(function (error) {
                    if ($.options('.m-checkout').debug) {
                        alert(error.status + (error.responseText ? ': ' + error.responseText : ''));
                    }
                })
                .complete(function () {
                    _gettingCountryByEmail = false;
                    _hideUpdatingAnimation();
                    if (gettingEmail != _email) {
                        _updateCountryByEmail();
                    }
                });
        }

        $(function() {
            _email = $('.billing-email input').val();
            $('.billing-email input').live('change', function() {
                var domainChanged = _domain(_email) != _domain($(this).val());
                _email = $(this).val();
                if (domainChanged) {
                    _updateCountryByEmail();
                }
            });
        });
        //endregion

        //region Update order details (and more) when VAT or country changes
        var _updatingOrderDetails = false;
        var _emptyUpdateOrderRequest = {
            checkVat:false,
            updateTotals:false
        };
        var _updateOrderRequest = $.extend({}, _emptyUpdateOrderRequest);

        function _updateOrderDetails() {
            if (_updatingOrderDetails) {
                return;
            }
            _updatingOrderDetails = true;
            _showUpdatingAnimation();
            var request = $.extend({}, _updateOrderRequest);
            _updateOrderRequest = $.extend({}, _emptyUpdateOrderRequest);
            var url = $.options('.m-checkout').updateOrderUrl;
            url += url.indexOf('?') == -1 ? '?' : '&';
            url += 'country=' + $('.billing-country select').val();
            if (request.checkVat !== false) {
                url += url.indexOf('?') == -1 ? '?' : '&';
                url += 'vat=' + request.checkVat;
            }
            ajax.get(url, function (response) {
                _updatingOrderDetails = false;
                _hideUpdatingAnimation();
                ajax.update(response);
                if (response.vat) {
                    if (!$('.billing-vat input').val().trim()) {
                        $('#advice-required-entry-billing_company_vat').remove();
                        $('.billing-vat input').removeClass('validation-passed').removeClass('validation-failed');
                        $('.vat-status')
                            .removeClass('verified')
                            .removeClass('invalid')
                            .html('');
                    }
                    else if (response.vat.success) {
                        $('#advice-required-entry-billing_company_vat').remove();
                        $('.billing-vat input')
                            .addClass('validation-passed')
                            .removeClass('validation-failed');
                        $('.vat-status')
                            .removeClass('invalid')
                            .addClass('verified')
                            .html('Verified');
                    }
                    else if (response.vat.error) {
                        $('#advice-required-entry-billing_company_vat').remove();
                        $('.billing-vat input')
                            .removeClass('validation-passed')
                            .addClass('validation-failed');
                        $('<div id="advice-required-entry-billing_company_vat" class="validation-advice" style="text-align:justify;">'
                                 + '<strong style="float: left;">EU countries only:</strong><br />' + response.vat.error
                                 + ' or VAT number format (for more details see <a target="_blank" href="http://ec.europa.eu/taxation_customs/vies/faq.html#item_11">here</a>). <br />You may proceed to checkout but VAT will be applied.<br /><span style="color: #32cd32;"><strong style="float: left;">Non EU customers:</strong><br /> Just continue. VAT will NOT be applied. </span></div>')
                            .insertAfter($('.billing-vat input'));
                        $('.vat-status')
                            .removeClass('verified')
                            .removeClass('invalid')
                            .html('');
                    }
                    else {
                        $('#advice-required-entry-billing_company_vat').remove();
                        $('.billing-vat input').removeClass('validation-passed').removeClass('validation-failed');
                        $('.vat-status')
                            .removeClass('verified')
                            .removeClass('invalid')
                            .html('');
                    }
                }
                var isRequestEmpty = true;
                for (var field in _updateOrderRequest) {
                    //noinspection JSUnfilteredForInLoop
                    if (_updateOrderRequest[field] != _emptyUpdateOrderRequest[field]) {
                        isRequestEmpty = false;
                        break;
                    }
                }
                if (!isRequestEmpty) {
                    _updateOrderDetails();
                }
            }, {showWait: false});
        }

        $(function () {
            if ($('.billing-vat input').val()) {
                _updateOrderRequest.checkVat = $('.billing-vat input').val();
                _updateOrderRequest.updateTotals = true;
                _updateOrderDetails();
            }
            $('.billing-vat input').live('change', function() {
                _updateOrderRequest.checkVat = $(this).val();
                _updateOrderRequest.updateTotals = true;
                _updateOrderDetails();
            });
            $('.billing-country select').live('change', function () {
                _updateOrderRequest.updateTotals = true;
                $('.billing-country').removeClass('updatable');
                _updateOrderDetails();
            });
        });
        //endregion

        //region Validation
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
            result = _validateFieldsInsideElement('.billing-account-form') && result;
            result = _validateFieldsInsideElement('.billing-company-form') && result;
            result = _validateFieldsInsideElement('.billing-address-form') && result;

            return result;

        }
        //endregion

        //region Submitting an Order
        var _updatingAnimationRequests = 0;
        function _showUpdatingAnimation() {
            if (!_updatingAnimationRequests) {
                $('#review-buttons-container')
                    .addClass('disabled').css({opacity:0.5})
                    .find('*').attr('disabled', 'disabled');
                $('#review-updating').show();
            }
            _updatingAnimationRequests++;
        }

        function _hideUpdatingAnimation() {
            _updatingAnimationRequests--;
            if (!_updatingAnimationRequests) {
                $('#review-updating').hide();
                $('#review-buttons-container')
                    .removeClass('disabled').css({opacity:1})
                    .find('*').removeAttr('disabled');
            }
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
            $('.btn-checkout').live('click', _submitOrder);
        });
        //endregion


    });
})(jQuery, $);
