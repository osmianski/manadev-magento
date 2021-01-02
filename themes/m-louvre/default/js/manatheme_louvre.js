/**
 * @category    Mana
 * @package     ManaTheme_Louvre
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
// the following function wraps code block that is executed once this javascript file is parsed. Lierally, this 
// notation says: here we define some anonymous function and call it once during file parsing. THis function has
// one parameter which is initialized with global jQuery object. Why use such complex notation: 
// 		a. 	all variables defined inside of the function belong to function's local scope, that is these variables
//			would not interfere with other global variables.
//		b.	we use jQuery $ notation in not conflicting way (along with prototype, ext, etc.)
(function($, window, document) {
    function initDownloadMenus() {
        $('.m-download-menu-button').each(function() {
            initDownloadMenu($(this), $(this).next('.m-download-menu'));
        });
    }

    function initDownloadMenu($button, $menu) {
        $button.click(function(e) {
            $(document.body).append($menu);
            var buttonOffset = $button.offset();
            $menu.css({
                top: (buttonOffset.top + $button.outerHeight()) + "px",
                left: buttonOffset.left + "px"
            });
            $menu.show();
            e.stopPropagation();
        });

        document.on('click', function (e) {
            if (!$menu.is(':visible')) {
                return;
            }

            // if ($.contains($menu[0], e.target)) {
            //     return;
            // }
            //
            $menu.hide();
        });
    }

    $(function () {
        Mana.Theme.beautifySelects();
        Mana.Theme.scrollToTop();
        if ($(document).lightGallery) {
            $(".more-views").lightGallery();
        }

        initDownloadMenus();
    });

    // function ManaMenuModal(ManaMenuInstance) {
    //     var menuInstance = ManaMenuInstance;
    //     var xDown = null;
    //     var yDown = null;
    //     var overrideEffect = null;
    //     var overrideEffectParams = {};
    //
    //     return {
    //         handleTouchStart: function(evt) {
    //             xDown = evt.touches[0].clientX;
    //             yDown = evt.touches[0].clientY;
    //         },
    //         handleTouchMove: function(evt) {
    //             if (!xDown || !yDown) {
    //                     return;
    //                 }
    //
    //                 var xUp = evt.touches[0].clientX;
    //                 var yUp = evt.touches[0].clientY;
    //
    //                 var xDiff = xDown - xUp;
    //                 var yDiff = yDown - yUp;
    //
    //                 if (Math.abs(xDiff) > Math.abs(yDiff)) {/*most significant*/
    //                     overrideEffect = "slide";
    //                     if (xDiff > 0) {
    //                         overrideEffectParams = {direction:"left"};
    //                     } else {
    //                         overrideEffectParams = {direction: "right"};
    //                     }
    //                     menuInstance.hideMenuTrigger();
    //                 }
    //                 /* reset values */
    //                 xDown = null;
    //                 yDown = null;
    //         },
    //         show: function() {
    //             $("body, html").addClass("noscroll");
    //             $("#menuPopup").css("top", $(window).scrollTop());
    //             $("#menuPopup").show(null, null, function () {
    //                 $("#menuPopup .modal-content").css("margin-right", "17px");
    //             });
    //
    //             document.getElementById("menuPopup").addEventListener('touchstart', $.proxy(this.handleTouchStart, this), false);
    //             document.getElementById("menuPopup").addEventListener('touchmove', $.proxy(this.handleTouchMove, this), false);
    //         },
    //         hide: function() {
    //             var effect = (overrideEffect == null) ? "fadeOut" : overrideEffect;
    //             var effectCallback = function () {
    //                 $("#menuPopup .modal-content").css("margin-right", "0");
    //                 $("body, html").removeClass("noscroll");
    //             };
    //
    //             if(effect == "fadeOut") {
    //                 $("#menuPopup").fadeOut(null, null, effectCallback);
    //             } else {
    //                 $("#menuPopup").hide(effect, overrideEffectParams, 300);
    //                 effectCallback();
    //             }
    //
    //             overrideEffect = null;
    //             overrideEffectParams = {};
    //
    //             document.getElementById("menuPopup").removeEventListener('touchstart', $.proxy(this.handleTouchStart, this), false);
    //             document.getElementById("menuPopup").removeEventListener('touchmove', $.proxy(this.handleTouchMove, this), false);
    //         }
    //     };
    // }
    //
    // function ManaMenu(config) {
    //     var IS_IN_MOBILE_STATE = false;
    //     var Modal = null;
    //
    //     return {
    //         setModal: function(modal) {
    //             Modal = modal;
    //         },
    //         isInMobileState: function() {
    //             return IS_IN_MOBILE_STATE;
    //         },
    //         parentLinks: $("#nav li.parent a"),
    //         initWidth: config.initWidth,
    //         enabled: function() {
    //             try {
    //                 if (!parseInt(this.initWidth)) {
    //                     throw "Disabled";
    //                 }
    //             } catch (e) {
    //                 return false;
    //             }
    //
    //             return true;
    //         },
    //         toggle: function() {
    //             if(!this.enabled()) {
    //                 return;
    //             }
    //
    //             var w = window,
    //                 d = document,
    //                 e = d.documentElement,
    //                 g = d.getElementsByTagName('body')[0],
    //                 x = w.innerWidth || e.clientWidth || g.clientWidth,
    //                 y = w.innerHeight || e.clientHeight || g.clientHeight;
    //
    //             if(this.initWidth > x && !this.isInMobileState()) {
    //                 IS_IN_MOBILE_STATE = true;
    //                 this._transformToMobileState();
    //             }
    //             if(this.initWidth < x && this.isInMobileState()) {
    //                 IS_IN_MOBILE_STATE = false;
    //                 this._transformToDesktopState();
    //             }
    //
    //             return this;
    //         },
    //         showMenuPopup: function () {
    //             Modal.show();
    //             // Initialize menu javascript from Magento `varien/menu.js`
    //             mainNav("menuPopup", {"show_delay": "100", "hide_delay": "100"});
    //         },
    //         hideMenuTrigger: function() {
    //             window.history.back();
    //         },
    //         showMenuTrigger: function () {
    //             window.location.hash = HASH_MENU_OPEN;
    //         },
    //         hideMenuPopup: function() {
    //             Modal.hide();
    //         },
    //         onParentLinkClick: function (e) {
    //             // this.showMenuPopup();
    //             this.showMenuTrigger();
    //             e.preventDefault();
    //         },
    //         onParentLinkMouseOver: function (e) {
    //             if(this.isInMobileState()) {
    //                 // Prevent varien/menu.js mouseover event
    //                 e.stopPropagation();
    //             }
    //         },
    //         _transformToMobileState: function () {
    //             this.parentLinks.on("click", $.proxy(this.onParentLinkClick, this));
    //             this.parentLinks.on("mouseover", $.proxy(this.onParentLinkMouseOver, this));
    //         },
    //         _transformToDesktopState: function () {
    //             this.parentLinks.off("click", $.proxy(this.onParentLinkClick, this));
    //             this.parentLinks.off("mouseover", $.proxy(this.onParentLinkMouseOver, this));
    //         }
    //     };
    // }
    //
    // var manaMenuSingleton = (function () {
    //     var instance;
    //
    //     function createInstance(config) {
    //         var menuInstance = new ManaMenu(config);
    //         menuInstance.setModal(new ManaMenuModal(menuInstance));
    //         return menuInstance;
    //     }
    //
    //     return {
    //         getInstance: function (config) {
    //             if (!instance) {
    //                 instance = createInstance(config);
    //             }
    //             return instance;
    //         }
    //     };
    // })();
    //
    // window.initManaMenu = function(config) {
    //     return manaMenuSingleton.getInstance(config).toggle();
    // };
    //
    //
    // const HASH_MENU_OPEN = "menu_open";
    //
    // function _onHashChange() {
    //     var hash = window.location.hash.slice(1);
    //     if (hash == HASH_MENU_OPEN) {
    //         initManaMenu().showMenuPopup();
    //     } else {
    //         initManaMenu().hideMenuPopup();
    //     }
    // }
    //
    // $(window).bind('hashchange', function () {
    //     _onHashChange();
    // });
    //
    // $(function() {
    //     _onHashChange();
    // });

})(jQuery, window, document);