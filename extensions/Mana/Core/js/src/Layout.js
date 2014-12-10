Mana.define('Mana/Core/Layout', ['jquery', 'singleton:Mana/Core'], function ($, core, undefined) {
    return Mana.Object.extend('Mana/Core/Layout', {
        _init: function () {
            this._pageBlock = null;
        },
        getPageBlock: function () {
            return this._pageBlock;
        },
        getBlock: function (blockName) {
            return this._getBlockRecursively(this.getPageBlock(), blockName);
        },
        getBlockForElement: function(el) {
            var blockInfo = this._getElementBlockInfo(el);
            return blockInfo ? this.getBlock(blockInfo.id) : null;
        },
        _getBlockRecursively: function (block, blockName) {
            if (block.getId() == blockName) {
                return block;
            }

            var self = this, result = null;
            $.each(block.getChildren(), function (index, child) {
                result = self._getBlockRecursively(child, blockName);
                return result ? false : true;
            });
            return result;
        },
        beginGeneratingBlocks: function (parentBlock) {
            var vars = {
                parentBlock: parentBlock,
                namedBlocks: {}
            };
            if (parentBlock) {
                parentBlock.trigger('unload', {}, false, true);
                parentBlock.trigger('unbind', {}, false, true);
                vars.namedBlocks = this._removeAnonymousBlocks(parentBlock);
            }
            return vars;
        },
        endGeneratingBlocks: function (vars) {
            var parentBlock = vars.parentBlock, namedBlocks = vars.namedBlocks;
            var self = this;
            this._collectBlockTypes(parentBlock ? parentBlock.getElement() : document, function (blockTypes) {
                if (!self._pageBlock) {
                    var body = document.body, $body = $(body);
                    var typeName = $body.attr('data-m-block');
                    var PageBlock = typeName ? blockTypes[typeName] : blockTypes['Mana/Core/PageBlock'];
                    self._pageBlock = new PageBlock()
                        .setElement($('body')[0])
                        .setId('page');
                }
                var initialPageLoad = (parentBlock === undefined);
                if (initialPageLoad) {
                    parentBlock = self.getPageBlock();
                }

                self._generateBlocksInElement(parentBlock.getElement(), parentBlock, blockTypes, namedBlocks);
                $.each(namedBlocks, function (id, namedBlock) {
                    namedBlock.parent.removeChild(namedBlock.child);
                });
                parentBlock.trigger('bind', {}, false, true);
                parentBlock.trigger('load', {}, false, true);

                // BREAKPOINT: all generated client side blocks
                var a = 1;
            });
        },
        _collectBlockTypes: function (element, callback) {
            var blockTypeNames = ['Mana/Core/PageBlock'];
            this._collectBlockTypesInElement(element, blockTypeNames);
            Mana.requireOptional(blockTypeNames, function () {
                var blockTypeValues = arguments;
                ;
                var blockTypes = {};
                $.each(blockTypeNames, function (key, value) {
                    blockTypes[value] = blockTypeValues[key];
//                    if (blockTypeValues[key]) {
//                        blockTypes[value] = blockTypeValues[key];
//                    }
//                    else {
//                        throw "Block type '" + value + "' is not defined.";
//                    }
                });
                callback(blockTypes);
            });
        },
        _collectBlockTypesInElement: function (element, blockTypeNames) {
            var layout = this;
            $(element).children().each(function () {
                var blockInfo = layout._getElementBlockInfo(this);
                if (blockInfo) {
                    if (blockTypeNames.indexOf(blockInfo.typeName) == -1) {
                        blockTypeNames.push(blockInfo.typeName);
                    }
                }
                layout._collectBlockTypesInElement(this, blockTypeNames);
            });
        },
        _removeAnonymousBlocks: function (parentBlock) {
            var self = this, result = {};
            $.each(parentBlock.getChildren().slice(0), function (key, block) {
                if (block.getId()) {
                    result[block.getId()] = { parent: parentBlock, child: block};
                    self._removeAnonymousBlocks(block);
                }
                else {
                    parentBlock.removeChild(block);
                }
            });
            return result;
        },
        _getElementBlockInfo: function (element) {
            var $element = $(element);
            var id, typeName;

            if ((id = core.getPrefixedClass(element, 'mb-'))
                || (typeName = $element.attr('data-m-block'))
                || $element.hasClass('m-block')) {
                return {
                    id: id || element.id,
                    typeName: typeName || $element.attr('data-m-block') || 'Mana/Core/Block'
                };
            }

            return null;
        },
        _generateBlocksInElement: function (element, block, blockTypes, namedBlocks) {
            var layout = this;
            $(element).children().each(function () {
                var childBlock = layout._createBlockFromElement(this, block, blockTypes, namedBlocks);
                layout._generateBlocksInElement(this, childBlock || block, blockTypes, namedBlocks);
            });
        },
        _createBlockFromElement: function (element, parent, blockTypes, namedBlocks) {
            var blockInfo = this._getElementBlockInfo(element);

            if (blockInfo) {
                var type = blockTypes[blockInfo.typeName], block, exists = false;
                if (blockInfo.id) {
                    block = parent.getChild(core.getBlockAlias(parent.getId(), blockInfo.id));
                    if (block) {
                        exists = true;
                        delete namedBlocks[blockInfo.id];
                    }
                    else {
                        if (type) {
                            block = new type();
                        }
                        else {
                            console.error("Block '" + blockInfo.typeName + "' is not defined");
                        }
                    }
                    if (block) {
                        block.setId(blockInfo.id);
                    }
                }
                else  {
                    if (type) {
                        block = new type();
                    }
                    else {
                        console.error("Block '" + blockInfo.typeName + "' is not defined");
                    }
                }
                if (block) {
                    block.setElement(element);
                    if (!exists) {
                        parent.addChild(block);
                    }
                    return block;
                }
                else {
                    return null;
                }
            }
            else {
                return null;
            }
        },
        preparePopup: function(options) {
            options = this._preparePopupOptions(options);
            if (options.$popup === undefined) {
                var $popup = $('#m-popup');
                $popup.css({"width": "auto", "height": "auto"});
                if (core.isString(options.content)) {
                    $popup.html(options.content);
                }
                else {
                    $popup.html($(options.content).html());
                }

                if (options.popup['class']) {
                    $popup.addClass(options.popup['class']);
                }

                options.$popup = $popup;
            }
            return options.$popup;
        },
        _preparePopupOptions: function(options) {
            if (options.overlay === undefined) { options.overlay = {}; }
            if (options.overlay.opacity === undefined) { options.overlay.opacity = 0.2; }

            if (options.popup === undefined) { options.popup = {}; }
            if (options.popup.blockName === undefined) { options.popup.blockName = 'Mana/Core/PopupBlock'; }

            if (options.popupBlock === undefined) { options.popupBlock = {}; }

            if (options.fadein === undefined) { options.fadein = {}; }
            if (options.fadein.overlayTime === undefined) { options.fadein.overlayTime = 0; }
            if (options.fadein.popupTime === undefined) { options.fadein.popupTime = 300; }

            if (options.fadeout === undefined) { options.fadeout = {}; }
            if (options.fadeout.overlayTime === undefined) { options.fadeout.overlayTime = 0; }
            if (options.fadeout.popupTime === undefined) { options.fadeout.popupTime = 500; }

            return options;
        },
        showPopup: function (options) {
            options = this._preparePopupOptions(options);

            var self = this;
            Mana.requireOptional([options.popup.blockName], function (PopupBlockClass) {
                var fadeoutCallback = options.fadeout.callback;
                options.fadeout.callback = function () {
                    $('#m-popup').fadeOut(options.fadeout.popupTime, function () {
                        if (fadeoutCallback) {
                            fadeoutCallback();
                        }
                    });
                };
                var overlay = self.getPageBlock().showOverlay('m-popup-overlay', options.fadeout);
                var $popup = self.preparePopup(options);
                overlay.animate({ opacity: options.overlay.opacity }, options.fadein.overlayTime, function () {
                    $popup.show();

                    var popupBlock = new PopupBlockClass();
                    popupBlock.setElement($popup[0]);
                    var vars = self.beginGeneratingBlocks(popupBlock);
                    self.endGeneratingBlocks(vars);
                    if (popupBlock.prepare) {
                        popupBlock.prepare(options.popupBlock);
                    }

                    $('.m-popup-overlay').on('click', function () {
                        self.hidePopup();
                        return false;
                    });
                    if (!self._popupEscListenerInitialized) {
                        self._popupEscListenerInitialized = true;
                        $(document).keydown(function (e) {
                            if ($('.m-popup-overlay').length) {
                                if (e.keyCode == 27) {
                                    self.hidePopup();
                                    return false;
                                }
                            }
                            return true;
                        });
                    }

                    $popup
                        .css("top", (($(window).height() - $popup.outerHeight()) / 2) + $(window).scrollTop() + "px")
                        .css("left", (($(window).width() - $popup.outerWidth()) / 2) + $(window).scrollLeft() + "px");

                    var popupHeight = $popup.height();
                    $popup.hide().css({"height": "auto"});

                    var css = {
                        left: $popup.css('left'),
                        top: $popup.css('top'),
                        width: $popup.width() + "px",
                        height: $popup.height() + "px"
                    };

                    $popup.children().each(function () {
                        $(this).css({
                            width: ($popup.width() + $(this).width() - $(this).outerWidth()) + "px",
                            height: ($popup.height() + $(this).height() - $(this).outerHeight()) + "px"
                        });
                    });
                    $popup
                        .css({
                            top: ($(window).height() / 2) + $(window).scrollTop() + "px",
                            left: ($(window).width() / 2) + $(window).scrollLeft() + "px",
                            width: 0 + "px",
                            height: 0 + "px"
                        })
                        .show();

                    $popup.animate(css, options.fadein.popupTime, options.fadein.callback);
                });
            });
        },
        hidePopup: function () {
            this.getPageBlock().hideOverlay();
        }
    });
});
