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
                    //alert('screen.width: ' +  screen.width);
                    //console.log('window: ' +  $(window).width());
                    //console.log('main: ' +  $('.main').width());
                    //console.log('col-left: ' +  $('.col-left').innerWidth());
                    //console.log('col-right: ' +  $('.col-right').innerWidth());
                //$('.logo').html($('.main').width());
               // var colmainPadding = $cells.outerWidth(true) - $cells.width();
                $('.col-main').width($('.main').width() - $('.col-left').outerWidth(true) - $('.col-right').outerWidth(true) - $('.col-main').outerWidth(true) + $('.col-main').width());
                //if ($('.col-left').outerWidth(true))  + $('.col-right').outerWidth(true) >0 ) {
                //    $('.col-main').width($('.col-main').width-20);
                //}
                   // console.log('after col-main: ' +  $('.col-main').width());

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
                        //var percentWidth = Math.floor((width - columnCount* self._paddingWidth) * 100 / columnCount);
                        //var percentWidth = Math.floor(((self._cellWidth - self._paddingWidth )/width)*100);
                        //$(cell).css({width: percentWidth + '%'});
                        var newWidth = Math.floor(width/columnCount - self._paddingWidth);
                        $(cell).width(newWidth);
                        $(cell).find('.product-image').width(newWidth).height(newWidth);
                        $(cell).find('.product-image img').attr('width', newWidth).attr('height', newWidth);
                        $row.append(cell);
                    });
                    /* add newRows and remove old rows */
                    $.each(newRows, function(index, newRow) {
                        var $newRow = $(newRow);
                       $newRow.insertBefore($oldRows[0]);
                       var maxHeight = $newRow.innerHeight();
                       $newRow.find('li.item').each(function(itemIndex, item) {
                           var $item = $(item);
                           $item.height(maxHeight - ($item.outerHeight() - $item.height()));
                       });
                    });
                    $oldRows.remove();

                this._appleZoomFix();
      }
    });
});
/*
Mana.require(['jquery'], function($) {
    function _fix() {
        var meta = document.querySelector( "meta[name=viewport]" ),
                initialContent = "width=device-width, initial-scale=1",
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
    }
    $(function () {
        _fix();
        $(window).on("orientationchange", _fix);
    });
});
*/
/*
Mana.require(['jquery'], function($) {
    $(function() {

        // Fix viewport zoom bug on iOS. Script by Sergio Lopes, Public Domain.
        //
        // See README for details.

        if (/iPhone|iPad|iPod/.test(navigator.platform) && navigator.userAgent.indexOf("AppleWebKit") > -1) {
            (function (win, doc) {

                // change viewport to landscape size (device-height)
                var viewport = doc.querySelector('meta[name=viewport]');
                viewport.content = 'width=device-height';

                // creates height guard
                var heightGuard = doc.createElement('div');
                heightGuard.id = 'heightGuard';
                doc.body.appendChild(heightGuard);

                // must know if it's an iPad since it has a different screen proportion
                var isiPad = /iPad/.test(navigator.platform);

                // new style element
                var css = doc.createElement('style');
                doc.body.appendChild(css);
                css.innerText =
                    "@media screen and (orientation:portrait){" +
                        "body{" +
                        "position:relative;" +
                        "}" +
                        "#heightGuard{" +
                        "position:absolute;" +
                        "top:0;" +
                        "left:0;" +
                        "width:1px;" +
                        "zIndex:-1;" +
                        "visibility:hidden;" +
                        "height:" + (isiPad ? '133.333%' : '150%') + ";" +
                        "}" +
                        "#" + (doc.body.getAttribute('data-container') || 'container') + "{" +
                        "-webkit-transform:" + (isiPad ? 'scale(1.33333)' : 'scale(1.5)') + ";" +
                        "-webkit-transform-origin:top left;" +
                        "width:" + (isiPad ? '768px' : '320px') + ";" +
                        "}" +
                        "}";

            })(window, document);
        }
    });
});
*/