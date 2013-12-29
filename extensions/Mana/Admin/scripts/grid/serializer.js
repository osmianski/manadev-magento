/**
 * @category    Mana
 * @package     Mana_Admin
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

// the following function wraps code block that is executed once this javascript file is parsed. Lierally, this 
// notation says: here we define some anonymous function and call it once during file parsing. THis function has
// one parameter which is initialized with global jQuery object. Why use such complex notation: 
// 		a. 	all variables defined inside of the function belong to function's local scope, that is these variables
//			would not interfere with other global variables.
//		b.	we use jQuery $ notation in not conflicting way (along with prototype, ext, etc.)
;(function($, undefined) {
	var _gridObjects = {};
	var _gridInfos = {};
	var _gridEdits = {};
	function _updateReloadParams(id) {
        if (!_gridObjects[id].reloadParams) {
            _gridObjects[id].reloadParams = {};
        }
        if ($.options('edit-form')) {
            _gridObjects[id].reloadParams['m-edit'] = _gridEditedData(id);
            if (!$('#' + id + 'SerializedData').length) {
                $('#' + id).append('<input type="hidden" name="' + id + '" id="' + id + 'SerializedData" />');
            }
            $('#' + id + 'SerializedData').val(_gridObjects[id].reloadParams['m-edit']);
        }
    }
	$(document).bind('m-grid-reset', function(e, id, object, info, edit) {
        $.hideHelperPopup(true);
        _gridObjects[id] = object;
		_gridInfos[id] = info;
		_gridEdits[id] = $.extend({
		    pending: {},
		    saved: {},
		    deleted: {}
		}, edit);

        _updateReloadParams(id);
	});
    $(function() {
        for (id in _gridObjects) {
            _updateReloadParams(id);
        }
    });
    $(document).bind('m-options-changed', function() {
        for (id in _gridObjects) {
            _updateReloadParams(id);
        }
    });
	$(document).bind('m-before-save', function(e, request) {
	    for (id in _gridEdits) {
            request.push({name: id, value:
                encode_base64(Object.toJSON($.extend(
                    { sessionId: $.options('edit-form')['editSessionId'] },
                    _gridEdits[id]
                )))
            });
        }
    });

	$.gridCell = function(td) {
	    if (!td) {
	        alert('HHH');
	    }
		var table = $(td).parents('table.data')[0];
		var tr = $(td).parents('tr')[0];
		var col = td.className.match(/\bc-([a-z0-9_]*)/)[1];
		//var th = $(table).find('th.c-'+col)[0];
		return {
			gridId: table.id,
			id: tr.className.match(/\br-([0-9]*)/)[1], 
			col: col, 
			table: table, 
			//th: th,
			tr: tr, 
			td: td//,
			//columnData: th,
			//data: $(td).find('.ct-container')[0]
		};
	};
	$.gridData = function(td, arg) {
		var cell = $.gridCell(td);
		if (typeof(arg)=='string') {
			if (arg == 'value' || arg == 'is_default') {
				if (_gridEdits[cell.gridId].pending[cell.id] && _gridEdits[cell.gridId].pending[cell.id][cell.col] 
					&& _gridEdits[cell.gridId].pending[cell.id][cell.col][arg] !== undefined)
				{
					return _gridEdits[cell.gridId].pending[cell.id][cell.col][arg];
				}
				else {
					return _gridInfos[cell.gridId].cells[cell.id][cell.col][arg];
				}
				
			}
			else {
				return _gridInfos[cell.gridId].columns[cell.col][arg];
			}
		}
		else {
			var field = null;
			var changed = false;
			for (field in arg) {
				if (arg[field] == $.gridData(td, field)) {
					delete arg[field];
				}
				else {
					changed = true;
				}
			}
			if (changed) {
				if (!_gridEdits[cell.gridId].pending[cell.id]) {
					_gridEdits[cell.gridId].pending[cell.id] = {};
				}
				if (!_gridEdits[cell.gridId].pending[cell.id][cell.col]) {
					_gridEdits[cell.gridId].pending[cell.id][cell.col] = {};
				}
				$.extend(_gridEdits[cell.gridId].pending[cell.id][cell.col], arg);
				
                _updateReloadParams(cell.gridId);
			}
		}
	};

	$.gridAction = function(id, action, args) {
	    id = id + '_table';

	    var gridUrl = _gridObjects[id].url;
        _gridObjects[id].addVarToUrl('action', action);
	    if (args) {
            _gridObjects[id].addVarToUrl('actionArgs', encode_base64(Object.toJSON(args)));
	    }
        var url = _gridObjects[id].url;
        _gridObjects[id].url = gridUrl;

        _updateReloadParams(id);
        _gridObjects[id].reload(url);
	};

	function _gridEditedData(id) {
        return encode_base64(Object.toJSON($.extend(
            { sessionId:$.options('edit-form')['editSessionId'] },
            _gridEdits[id]
        )));
    }
    $.gridEditedData = function(id) {
        return _gridEditedData(id + '_table');
    }
})(jQuery);
