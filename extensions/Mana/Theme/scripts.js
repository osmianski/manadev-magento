/**
 * @category    Mana
 * @package     Mana_Theme
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

Mana.define('Mana/Theme/Grid', ['jquery', 'Mana/Core/Block'], function ($, Block) {
    return Block.extend('Mana/Theme/Grid', {
        _init: function() {
            this._super();
            this._cellWidth = 0;
            this._paddingWidth = 0;
        },
        _subscribeToBlockEvents: function() {
            return this
                ._super()
                .on('resize', this, this.decorateGrid);
        },
        _appleZoomFix: function () {
            var meta = document.querySelector( "meta[name=viewport]" ),
                    initialContent = meta && meta.getAttribute( "content" ),
                    disabledZoom = initialContent + ",maximum-scale=1",
                    enabledZoom = initialContent + ",maximum-scale=10",
                    enabled = true,
                    x, y, z, aig;

                if( !meta ){ return; }

                function restoreZoom(){
                    meta.setAttribute( "content", enabledZoom );
                    enabled = true;
                }

                function disableZoom(){
                    meta.setAttribute( "content", disabledZoom );
                    enabled = false;
                }

            disableZoom();
            restoreZoom();
        },
        decorateGrid: function() {
            //return;
            //alert('window: ' +  $(window).width());
            //console.log('window: ' +  $(window).width());
            var $el = $(this.getElement());
            var width = $el.width();
            var $cells = $el.find('li.item');
            if (!$cells.length) {
                return;
            }

            if (!this._cellWidth) {
                this._cellWidth = $cells.outerWidth(true);
                this._paddingWidth = $cells.outerWidth(true) - $cells.width();
            }
            var cellWidth = this._cellWidth;
            var columnCount = Math.floor(width/cellWidth);
            var $oldRows = $el.find('ul.products-grid');
            var newRows = [], $row, curRow = 0;
            var self = this;
            var maxRowHeight;
            $cells.each(function(index, cell) {
                /* remove old cell decoration */
                $(cell).removeClass('first').removeClass('last');
                /* add row */
                if (index >= curRow * columnCount) {
                    $row = $('<ul class="products-grid" style="width: ' + width + 'px;"></ul>');
                    newRows.push($row[0]);
                    curRow ++;
                    maxRowHeight = 0;
                    if ((curRow + 1) % 2 == 0) {
                       $row.addClass('even');
                    }
                    else {
                        $row.addClass('odd');
                    }
                }
                /* decorate and add cell*/
                if (index == ((curRow - 1) * columnCount)) {
                     $(cell).addClass('first');
                }
                if (index == (curRow * columnCount - 1)) {
                     $(cell).addClass('last');
                }

                var newWidth = Math.floor(width/columnCount - self._paddingWidth);
                $(cell).width(newWidth);
                $(cell).find('.product-image').width(newWidth).height(newWidth);
                $(cell).find('.product-image img').attr('width', newWidth).attr('height', newWidth);
                $(cell).find('.actions').width(newWidth);
                $row.append(cell);
            });
            /* add newRows and remove old rows */
            $.each(newRows, function(index, newRow) {
                var $newRow = $(newRow);
               $newRow.insertBefore($oldRows[0]);
               //var maxHeight = $newRow.innerHeight();
               //$newRow.find('li.item').each(function(itemIndex, item) {
               //    var $item = $(item);
               //    $item.height(maxHeight - ($item.outerHeight() - $item.height()));
               //});
            });
            $oldRows.remove();
        this._appleZoomFix();
      }
    });
});

Mana.define('Mana/Theme/Body', ['jquery', 'Mana/Core/PageBlock'], function ($, PageBlock) {
    return PageBlock.extend('Mana/Theme/Body', {
        _subscribeToBlockEvents: function() {
            return this
                ._super()
                 .on('resize', this, function () {
                    var $colMain = this.$().find('.col-main');
                    var leftSidebar = this.getChild('left-sidebar'),
                        rightSidebar = this.getChild('right-sidebar');
                    $colMain.width(this.$().find('.main').width() -
                        (leftSidebar ? leftSidebar.getWidth() : 0) -
                        (rightSidebar ? rightSidebar.getWidth() : 0) -
                        $colMain.outerWidth(true) +
                        $colMain.width());
                });
        }
    });
 });

Mana.define('Mana/Theme/Sidebar', ['jquery', 'Mana/Core/Block', 'singleton:Mana/Core/Layout'], function ($, Block, layout) {
    return Block.extend('Mana/Theme/Sidebar', {
        _init: function() {
            this._super();
            this._side = '';
            this._defaultSide = 'left';
            this._minPageWidth = 0;
            this._defaultMinPageWidth = 900;
            this._state = 'static';
//            this._float = 'none';
            this._handleWidth = 0;
            this._defaultHandleWidth = 20;

        },
        getSide:function () {
            if (!this._side) {
                this._side = this.$().data('side');
                if (!this._side) {
                    this._side = this._defaultSide;
                }
            }
            return this._side;
        },
        getMinPageWidth:function () {
            if (!this._minPageWidth) {
                this._minPageWidth = this.$().data('min-page-width');
                if (!this._minPageWidth) {
                    this._minPageWidth = this._defaultMinPageWidth;
                }
            }
            return this._minPageWidth;
        },
        getHandleWidth:function () {
            if (!this._handleWidth) {
                this._handleWidth = this.$().data('handle-width');
                if (!this._handleWidth) {
                    this._handleWidth = this._defaultHandleWidth;
                }
            }
            return this._handleWidth;
        },
        getWidth: function() {
            return this.getState() == 'static' ? this.$().outerWidth(true) : this.getHandleWidth();
        },
        getState:function () {
            return this._state;
        },
        setState:function (value) {
            this._state = value;
            return this;
        },
//        getFloat:function () {
//            return this._float;
//        },
//        setFloat:function (value) {
//            this._float = value;
//            return this;
//        },
        _makeTouchable:function () {
            if ($('.sidebar-container' + '.' + this.getSide()).length == 0) {
                $container = $('<div class="sidebar-container ' + this.getSide() + '"><div class="handle ' + this.getSide() + '"><div class="icon-press"></div></div></div>');
                $container.insertBefore(this.getElement());
                this.$().insertBefore($container.find('.handle'));
                layout.getPageBlock().resize();
                $('.handle' + '.' + this.getSide()) .css("min-height", this.$().height() );
            }
            return this;
        },
        _makeUntouchable:function () {
            $container = $('.sidebar-container' + '.' + this.getSide());
            if ($container.length == 1) {
                this.$().insertBefore($container);
                $container.remove();
                layout.getPageBlock().resize();
            }
            return this;
        },
        _expand:function () {
            this.$().css( "display", "block");
            return this;
        },
        _collapse:function () {
            this.$().css( "display", "none");
            return this;
        },
/*        $("p").click(function(){
          // action goes here!!
        }),*/
        _subscribeToBlockEvents: function() {
            return this
                ._super()
                .on('resize', this, function () {
                    if ($('body').width() < this.getMinPageWidth()) {
                        if (this.getState() == 'static') {
                            this
                                .setState('expanded')
                                ._makeTouchable()
                                ._collapse();
                        }
                    }
                    else {
                        if (this.getState() !='static') {
                            this
                                .setState('static')
                                ._makeUntouchable()
                                ._expand();
                        }
                    }
             //       var side = this.getSide();
                });
        }
    });

 });