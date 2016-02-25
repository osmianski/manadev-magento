/**
 * @category    Mana
 * @package     Local_Manadev
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

// the following function wraps code block that is executed once this javascript file is parsed. Lierally, this 
// notation says: here we define some anonymous function and call it once during file parsing. THis function has
// one parameter which is initialized with global jQuery object. Why use such complex notation: 
// 		a. 	all variables defined inside of the function belong to function's local scope, that is these variables
//			would not interfere with other global variables.
//		b.	we use jQuery $ notation in not conflicting way (along with prototype, ext, etc.)
(function($) {
	// the following function is executed when DOM ir ready. If not use this wrapper, code inside could fail if
	// executed when referenced DOM elements are still being loaded.
	$(function() {
        //region Download dialog
        var _downloadDialogOptions = null;
		if ($.options("#download-dialog")) {
			// prepare download dialog box
			$("#download-dialog").dialog({ 
				autoOpen: false, // hide dialog markup until dialog("open") is called
				modal: true, // lock other UI elements while dialog is active
				resizable: false, // disable resizable corner in dialog
//				title: $.__("Download Extension \"%s\"", $.options("#download-dialog").productName), // specify dialog title here
				width: 800, // set dialog width here
				buttons: [
				]
			});
			$("#download-dialog button").button();
			
			// handle register and download button
			$("#download-dialog .register button").click(function() {
				if ($("#download-no-thanks:checked").length) {
					// in case user have said "No, thanks" we ask server add download permission 
					// into current session  
					$.ajax({
						type: "POST",
						url: _downloadDialogOptions.guestUrl,
						data: {},
						success: function(data, status, request) {
							if (typeof(data) === 'string') { data = $.parseJSON(data); }
							if (!data || !data.error) {
								// close dialog and just open download page. Server should have been
								// prepared to accept this download attempt
                                $("#download-dialog").dialog("close");
								window.location.href = _downloadDialogOptions.downloadUrl;
							}
							else {
								alert(data.error);
							}
						},
						error: function(request, status, errorThrown) {
							// inform user in case of error. Person stays in dialog box
							alert($.__("Unexpected error: %s. Try again later.", errorThrown));
						}
					});
				}
				else {
					// ask server to create account for customer and to log him
					$.ajax({
						type: "POST",
						url: $.options("#download-dialog").registerUrl,
						data: $("#register-and-download-form").serializeArray(),
						success: function(data, status, request) {
							if (typeof(data) === 'string') { data = $.parseJSON(data); }
							if (!data || !data.error) {
								// close dialog and just open download page. Server should have been
								// prepared to accept this download attempt
                                $("#download-dialog").dialog("close");
								window.location.href = _downloadDialogOptions.downloadUrl;
							}
							else {
								alert(data.error);
							}
						},
						error: function(request, status, errorThrown) {
							// inform user in case of error. Person stays in dialog box
							alert($.__("Unexpected error: %s. Try again later.", errorThrown));
						}
					});
				}
			});
			
			// handle login and download button
			$("#download-dialog .login button").click(function() { 
				// ask server to log customer in
				$.ajax({
					type: "POST",
					url: $.options("#download-dialog").loginUrl,
					data: $("#login-and-download-form").serializeArray(),
					success: function(data, status, request) {
						if (typeof(data) === 'string') { data = $.parseJSON(data); }
						if (!data || !data.error) {
							// close dialog and just open download page. Server should have been
							// prepared to accept this download attempt
                            $("#download-dialog").dialog("close");
							window.location.href = _downloadDialogOptions.downloadUrl;
						}
						else {
							alert(data.error);
						}
					},
					error: function(request, status, errorThrown) {
						// inform user in case of error. Person stays in dialog box
						alert($.__("Unexpected error: %s. Try again later.", errorThrown));
					}
				});
			});
			
			// the following is exeuted when specified DOM elements are clicked
			$(".btn-download").click(function() {
				var classes = this.className.split(/\s+/);
				for (var i = 0; i < classes.length; i++) {
					if (classes[i].match(/^for-product-/)) {
						_downloadDialogOptions = $.options(".btn-download " + classes[i]);
						break;
					}
				}

				if ($.options("#download-dialog").customerIsLoggedIn) {
					// if customer is already logged in, just open download page for this product
					window.location.href = _downloadDialogOptions.downloadUrl;
				}
				else {
					// if customer is not logged in, show download dialog box
					$("#download-dialog").dialog({ 
						title: $.__("Download Extension \"%s\"", _downloadDialogOptions.productName) // specify dialog title here
					});
					$("#download-dialog .extension-name").html(_downloadDialogOptions.productName);
					$("#download-dialog").dialog("open");
				}
			});
			
			// hide/show registration fields when No, thanks checkbox changes its value.
			$("#download-no-thanks").click(function() {
				if ($(this).is(":checked")) { // hide if checked
					$("#reg-details-for-download").slideUp({ duration: 500 });
				}
				else { // show if not checked
					$("#reg-details-for-download").slideDown({ duration: 500 });
				}
			});
		}

		/***************** CLIENT LOGIC FOR DOWNLOAD INITIATOR CONTROL **********************/

		/***************** CLIENT LOGIC FOR FILE UPLOAD CONTROLS **********************/
		$('input.file').change(function() {
			var value = this.value;
			var pos = -1;
			if ((pos = value.lastIndexOf('/')) != -1) {
				value = value.substring(pos + 1);
			}
			else if ((pos = value.lastIndexOf('\\')) != -1) {
				value = value.substring(pos + 1);
			}
			$(this).next().children('input').val(value);
		});
        //endregion

        //region Register popup
		if ($.options("#register-dialog")) {
			// define validation rules once
			var _formToSubmitAfterRegistration = null;
			
			// prepare download dialog box
			$("#register-dialog").dialog({ 
				autoOpen: false, // hide dialog markup until dialog("open") is called
				modal: true, // lock other UI elements while dialog is active
				resizable: false, // disable resizable corner in dialog
				title: $.__("Register Now!"), // specify dialog title here
				width: 800 // set dialog width here
			});
			
			$("#register-dialog button").button();
			
			// handle register and download button
			$("#register-dialog .register button").click(function() { 
				// ask server to create account for customer and to log him
				$.ajax({
					type: "POST",
					url: $.options("#register-dialog").registerUrl,
					data: $("#register-form").serializeArray(),
					success: function(data, status, request) {
						if (typeof(data) === 'string') { data = $.parseJSON(data); }
						if (!data || !data.error) {
							// close dialog and just post the form that should have been posted
                            $("#register-dialog").dialog("close");
							_formToSubmitAfterRegistration.submit();
							_formToSubmitAfterRegistration = null;
						}
						else {
							alert(data.error);
						}
					},
					error: function(request, status, errorThrown) {
						// inform user in case of error. Person stays in dialog box
						alert($.__("Unexpected error: %s. Try again later.", errorThrown));
					}
				});
			});
			
			// handle login and download button
			$("#register-dialog .login button").click(function() { 
				// ask server to log customer in
				$.ajax({
					type: "POST",
					url: $.options("#register-dialog").loginUrl,
					data: $("#login-form").serializeArray(),
					success: function(data, status, request) {
						if (typeof(data) === 'string') { data = $.parseJSON(data); }
						if (!data || !data.error) {
							// close dialog and just post the form that should have been posted
                            $("#register-dialog").dialog("close");
							_formToSubmitAfterRegistration.submit();
							_formToSubmitAfterRegistration = null;
						}
						else {
							alert(data.error);
						}
					},
					error: function(request, status, errorThrown) {
						// inform user in case of error. Person stays in dialog box
						alert($.__("Unexpected error: %s. Try again later.", errorThrown));
					}
				});
			});

			// the following is exeuted when specified DOM elements are clicked
			$("button.and-register").click(function() {
				var form = $(this).parents('form.and-register')[0];
				if ($(form).valid()) {
					if ($.options("#register-dialog").customerIsLoggedIn) {
						// if customer is already logged in, just do default form action
						form.submit();
					}
					else {
						// if customer is not logged in, show download dialog box
						_formToSubmitAfterRegistration = form;
						$("#register-dialog").dialog("open");
					}
				}
			});
		}
        //endregion

        //region Home page carousel (obsolete)
//		$('.mana-store>div.sliding-carousel>ul.carousel-items').advListRotator({
//	         effect: 'fold',
//	         effectOptions: {size: 1},
//	         helper: '.mana-store>ul.carousel-labels',
//	         helperActiveItemClass: 'active-carousel-label',
//	         helperInteraction: 'click'
//	    });
//        //endregion

        //region Checkout

		var placingOrder = false;
		$('.checkout-onepage-index .btn-checkout').click(function() {
			if (placingOrder) return;
			
			// updating visual status thata we've started
			placingOrder = true;
			$('#review-buttons-container').addClass('disabled').css({ opacity: 0.5 });
			$('#review-please-wait').show();
			
			$.ajax({
				type: "POST",
				url: $.options("#checkout").saveUrl,
				data: $.merge( $("#register-login-form").serializeArray(), $("#checkout-agreements").serializeArray()),
				success: function(data, status, request) {
					if (data !== "") {
						if (typeof(data) === 'string') { data = $.parseJSON(data); }
			            if (data.redirect) {
			                location.href = data.redirect;
			                return;
			            }
			            if (data.success) {
			                this.isSuccess = true;
			                location.href=$.options("#checkout").successUrl;
			                return;
			            }
					}
					else {
						data = { error_messages: $.__("Server returned no result. Try again later.")};
					}

					var msg = data.error_messages;
	                if (typeof(msg)=='object') {
	                    msg = msg.join("\n");
	                }
	                alert(msg);

					$('#review-please-wait').hide();
					$('#review-buttons-container').removeClass('disabled').css({ opacity: 1 });
					placingOrder = false;
				},
				error: function(request, status, errorThrown) {
					// inform user in case of error. Person stays in dialog box
					alert($.__("Unexpected error: %s. Try again later.", errorThrown));

					// set the status visualizer back
					$('#review-please-wait').hide();
					$('#review-buttons-container').removeClass('disabled').css({ opacity: 1 });
					placingOrder = false;
				}
			});
		});
        //endregion
	});
    $(window).on('load', function () {
        if ($.options("#download-initiator")) {
            window.location.href = $.options("#download-initiator").fileUrl;
        }
    });
})(jQuery);

Mana.define('Mana/Checkout/LoginPopup', ['jquery', 'Mana/Core/PopupBlock'], function ($, PopupBlock) {
    return PopupBlock.extend('Mana/Checkout/LoginPopup', {
        prepare: function (options) {
            this._super(options);
            this.$().find('#email').val($('.billing-email input').val());
        }
    });
});
