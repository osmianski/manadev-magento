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
    $(function () {
        Mana.Theme.beautifySelects();
    });

    function ManaMenu(config) {
        var IS_IN_MOBILE_STATE = false;

        return {
            isInMobileState: function() {
                return IS_IN_MOBILE_STATE;
            },
            parentLinks: $("#nav li.parent a"),
            initWidth: config.initWidth,
            enabled: function() {
                try {
                    if (!parseInt(this.initWidth)) {
                        throw "Disabled";
                    }
                } catch (e) {
                    return false;
                }

                return true;
            },
            toggle: function() {
                if(!this.enabled()) {
                    return;
                }

                var w = window,
                    d = document,
                    e = d.documentElement,
                    g = d.getElementsByTagName('body')[0],
                    x = w.innerWidth || e.clientWidth || g.clientWidth,
                    y = w.innerHeight || e.clientHeight || g.clientHeight;

                if(this.initWidth > x && !this.isInMobileState()) {
                    IS_IN_MOBILE_STATE = true;
                    this._transformToMobileState();
                }
                if(this.initWidth < x && this.isInMobileState()) {
                    IS_IN_MOBILE_STATE = false;
                    this._transformToDesktopState();
                }

                return this;
            },
            showMenuPopup: function () {
                document.getElementsByTagName("body")[0].style.overflow = "hidden";
                $("#menuPopup").css("top", $(window).scrollTop());
                var self = this;
                $("#menuPopup").slideDown(null,null, function() {
                    $("#menuPopup .modal-content").css("margin-right", "17px");
                }).draggable({
                    axis: "x",
                    scroll: false,
                    revert: function() {
                        //determine the start/end positions
                        var end = $(this).position().left;
                        var start = $(this).data('start');
                        //subtract end and start to get the (absolute) distance
                        var distance = Math.abs(end - start);
                        var width = $(this).width();
                        //if the distance is more than 80% of the width don't revert
                        if(distance > (width * .2))
                        {
                            self.hideMenuPopup();
                        }
                        //else revert
                        return true;
                    },
                    start: function(){
                        //get the start position
                        var start = $(this).position().left;
                        //store the start position on this element
                        $(this).data('start', start);
                    }
                });
                mainNav("menuPopup", {"show_delay": "100", "hide_delay": "100"});
            },
            hideMenuPopup: function() {
                document.getElementsByTagName("body")[0].style.overflow = "initial";
                $("#menuPopup .modal-content").css("margin-right", "0");
                $("#menuPopup").fadeOut();
            },
            onParentLinkClick: function (e) {
                this.showMenuPopup();
                e.preventDefault();
            },
            onParentLinkMouseOver: function (e) {
                if(this.isInMobileState()) {
                    // Prevent varien/menu.js mouseover event
                    e.stopPropagation();
                }
            },
            _transformToMobileState: function () {
                this.parentLinks.on("click", $.proxy(this.onParentLinkClick, this));
                this.parentLinks.on("mouseover", $.proxy(this.onParentLinkMouseOver, this));
            },
            _transformToDesktopState: function () {
                this.parentLinks.off("click", $.proxy(this.onParentLinkClick, this));
                this.parentLinks.off("mouseover", $.proxy(this.onParentLinkMouseOver, this));
            }
        };
    }

    var manaMenuSingleton = (function () {
        var instance;

        function createInstance(config) {
            return new ManaMenu(config);
        }

        return {
            getInstance: function (config) {
                if (!instance) {
                    instance = createInstance(config);
                }
                return instance;
            }
        };
    })();

    window.initManaMenu = function(config) {
        return manaMenuSingleton.getInstance(config).toggle();
    }
})(jQuery, window, document);