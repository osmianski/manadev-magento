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
        _subscribeToHtmlEvents: function() {
            var self = this;
            var inResize = false;
            function _raiseResize() {
                if (inResize) {
                    return;
                }
                inResize = true;
                self.decorateGrid();
                inResize = false;
            }

            return this
                ._super()
                .on('bind', this, function() {
                    this.decorateGrid();
                    $(window).on('resize', _raiseResize);
                })
                .on('unbind', this, function() {
                    $(window).off('resize', _raiseResize);
                });
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
            $('.col-main').width($('.main').width() - $('.col-left').outerWidth(true) - $('.col-right').outerWidth(true) - $('.col-main').outerWidth(true) + $('.col-main').width());
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
