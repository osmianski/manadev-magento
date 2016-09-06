/**
 * @category    Mana
 * @package     ManaPro_ProductFaces
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

// the following function wraps code block that is executed once this javascript file is parsed. Lierally, this 
// notation says: here we define some anonymous function and call it once during file parsing. THis function has
// one parameter which is initialized with global jQuery object. Why use such complex notation: 
// 		a. 	all variables defined inside of the function belong to function's local scope, that is these variables
//			would not interfere with other global variables.
//		b.	we use jQuery $ notation in not conflicting way (along with prototype, ext, etc.)
;(function($, window, $get) {
	/////////////////////////////////////////////////////////////////////////////////////////////
	// PRIVATE VARIABLES
	/////////////////////////////////////////////////////////////////////////////////////////////
	
	// holds reference to custom gris serializer object. See classes _serializerControllerClass in this file and
	//serializerController in grid.js for more details.
	var _serializer = null;
	
	// contains last AJAX request to recalculate representing qties. Used to filter out repeating requests
	var _oldRepresentingInventoryUpdateRequest = null;
	
	var _lastRepresentingInventoryUpdateResponse = null;
	var _recalculationPending = false;
	
	// contains data passed initially from server when first rendring "Repredsenting Products" tab
	var _options = null;
	
	var _chooserContent = null;
	var _chooserDialog = null;
	var _existingIds = {};
	
	/////////////////////////////////////////////////////////////////////////////////////////////
	// PRIVATE FUNCTIONS
	/////////////////////////////////////////////////////////////////////////////////////////////
	
	
	function _updateRepresentingInventory(forceRecalculation) {
		if ($('#m_representing_messages_wrapper').length && (forceRecalculation || _options.recalculateAutomatically)) {
			// prepare data for request
			var productData = {
					qty: $('#inventory_qty').val(),
					status: $('#status').val(),
					is_qty_decimal: $('#inventory_is_qty_decimal').val()
				};
			var representingProductData = [];
			_serializer.gridData.each(function(item) {
				data = { entity_id: item[0] };
				for (attribute in item[1]) {
					data[attribute] = item[1][attribute];
				}
				representingProductData.push(data);
			});
			var request = { 
				productData: Object.toJSON(productData), 
				representingProductData: Object.toJSON(representingProductData), 
				form_key: FORM_KEY 
			};	

			// compare current request with previous one
			var same = false;
			if (_oldRepresentingInventoryUpdateRequest) {
				var oldProductData = _oldRepresentingInventoryUpdateRequest.productData.evalJSON();
				var newProductData = request.productData.evalJSON();
				if (oldProductData.qty == newProductData.qty && oldProductData.status == newProductData.status && oldProductData.is_qty_decimal == newProductData.is_qty_decimal) {
					var oldRepresentingProductData = _oldRepresentingInventoryUpdateRequest.representingProductData.evalJSON();
					var newRepresentingProductData = request.representingProductData.evalJSON();
					if (oldRepresentingProductData.length == newRepresentingProductData.length) {
						same = true;
						newRepresentingProductData.each(function(data, index) {
							if (data.linked_product_id != oldRepresentingProductData[index].linked_product_id || 
								data.m_parts != oldRepresentingProductData[index].m_parts || 
								data.m_unit != oldRepresentingProductData[index].m_unit ||
								data.position != oldRepresentingProductData[index].position ||
                                data.m_pack_qty != oldRepresentingProductData[index].m_pack_qty )
							{
								same = false;
							}
						});
					}
				}
			}

			// if request is new, do roundtrip to server, silently, without disturbing user's work
			if (!same) {
				$.post(_options.updateUrl, request)
					.done(function (response) {
						_recalculationPending = false;
						try {
							response = $.parseJSON(response);
							if (response.error) {
		                        alert(response.message);
		                    }
		                    if(response.ajaxExpired && response.ajaxRedirect) {
		                        setLocation(response.ajaxRedirect);
		                    }
		                    else {
		                    	_lastRepresentingInventoryUpdateResponse = response;
		                    	_oldRepresentingInventoryUpdateRequest = request;
		                    	_refreshRepresentingInventory();
		                    }
						}
						catch (error) {
							alert(response || error.message || error);
						}
					});
			}
		}
	}
	
	function _refreshRepresentingInventory() {
		if (_oldRepresentingInventoryUpdateRequest) {
			var request = _oldRepresentingInventoryUpdateRequest;
			var response = _lastRepresentingInventoryUpdateResponse;
			$('.m_representing_header_qty').html('<strong>' + request.productData.evalJSON().qty + '</strong>');
			for (id in response.qties) {
				$('#m_representing_product_qty_' + id).html(response.qties[id]);
			}
			$('#m_representing_messages_wrapper').html(response.message_html);
		}
	}

	function _findThisRow() {
		var result = { index: -1, row: null };
		_serializer.gridData.each(function(item, i) {
			if (item[0] == 'this') {
				result.index = i;
				result.row = item[1];
			}
		});
		return result;
	}
	function _generateCopyId() {
		var result = 1;
		_serializer.gridData.each(function(item, i) {
			if (typeof(item[0])=='string' && item[0].startsWith('copy-')) {
				var i = parseInt(item[0].substring(5));
				if (result <= i) {
					result = i + 1;
				}
			}
		});
		return 'copy-' + result;
	}
	function _updateHiddenState() {
		_serializer.hiddenDataHolder.value = _serializer.serializeObject();
		_serializer.grid.reloadParams = {};
		_serializer.grid.reloadParams[_serializer.reloadParamName+'[]'] = _serializer.getDataForReloadParam();
	}	
	var _updatingThisFields = false;
	function _updateThisFields(updateGrid) {
		if (_updatingThisFields) {
			return;
		}
		_updatingThisFields = true;
		
		var t = _findThisRow();
		for (attribute in t.row) {
			var input = $('#' + attribute);
			if (input.length) {
				if (updateGrid) {
					_serializer.gridData.get('this')[attribute] = input.val();
					_updateHiddenState();
				}
				var checkbox = $('#m_representing_product_grid div.grid tbody tr td input[value="this"]');
				if (checkbox.length) {
					var td = checkbox.parent().parent().find('td.mc-' + attribute);
					if (attribute == 'sku' || attribute == 'name') {
						if (updateGrid) {
							td.html(input.val());
						}
					}
					else {
						if (updateGrid) {
							var tdInput = td.find('input');
							if (tdInput.length > 0) {
								tdInput.val(input.val());
								//console.log('update '+input[0].id+' to input in td.mc-'+attribute+': '+input.val());
							}
							else {
								tdInput = td.find('select');
								tdInput.val(input.val());
								//console.log('update '+input[0].id+' to select in td.mc-'+attribute+': '+input.val());
							}
						}
						else {
							var tdInput = td.find('input');
							if (tdInput.length > 0) {
								input.val(tdInput.val());
								//console.log('update '+input[0].id+' from input in td.mc-'+attribute+': '+tdInput.val());
							}
							else {
								tdInput = td.find('select');
								input.val(tdInput.val());
								//console.log('update '+input[0].id+' from select in td.mc-'+attribute+': '+tdInput.val());
							}
						}
					}
				}
			}
		}
		_updatingThisFields = false;
	} 
	
	function _showHide() {
		if ($('#m_representing_enabled').val() == '1') {
			$('#m_representing_enabled').parent().parent().siblings().show();
			$('#m_representing_product_grid').show();
		}
		else {
			$('#m_representing_enabled').parent().parent().siblings().hide();
			$('#m_representing_product_grid').hide();
		}
	}
	
	// OBSOLETE: sends command to server not to show specified warning in future
	function _hideProductFacesWarning(option) {
		$.post(_options.hideWarningUrl, {option: option})
			.done(function (response) {
	  			_oldRepresentingInventoryUpdateRequest = null;
	  			_updateRepresentingInventory();
			});
	}
	
	function _addCopy() {
		var t = _findThisRow();
		var id = _generateCopyId();
		var data = Object.clone(t.row);
		data['entity_id'] = id;
		data['m_pack_qty'] = _options.defaultPackQty;
		data['position'] = _options.defaultPosition;
		data['m_parts'] = _options.defaultParts;
		data['m_unit'] = _options.defaultUnitOfMeasure;
		_serializer.gridData.set(id, data);
		_updateHiddenState();
		
		// call server to update visuals
		_recalculationPending = true;
		_serializer.grid.reload(_serializer.grid.url);
	}

	function _getRepresentingProductsId() {
		var keys = _serializer.gridData.keys();
        for(var i in keys) {
			if(keys[i] == 'this') {
				keys[i] = _options.productId;
				break;
			}
		}

		return keys;
	}
	
	function _addExisting() {
        $.mChooseProducts({
            url: _options.chooserUrl,
			params: {
				hidden_products: _getRepresentingProductsId
			},
            result: function (ids) {
                $('#m_productfaces_clone_override').change(function () {
                    var override = $('#m_productfaces_clone_override').val();
                    var decision = $('#m_productfaces_cloning_override_decision').val();
                    if (decision === '' && (override == 1 || override == 3)) {
                        $('#m_productfaces_cloning_override_decision').val(confirm(_options.confirmOverrideMsg) ? '1' : '0');
                    }
                });
                if (ids.length) {
                    $.get(_options.productDataUrl, {selected_ids: ids})
                        .done(function(response) {
                            var data = response.evalJSON();
                            data.each(function(item) {
                                _serializer.gridData.set(item['entity_id'], item);
                            });

                            _updateHiddenState();

                            // call server to update visuals
                            _recalculationPending = true;
                            _serializer.grid.reload(_serializer.grid.url);
                            var override = $('#m_productfaces_clone_override').val();
                            var decision = $('#m_productfaces_cloning_override_decision').val();
                            if (decision === '' && (override == 1 || override == 3)) {
                                $('#m_productfaces_cloning_override_decision').val(confirm(_options.confirmOverrideMsg) ? '1' : '0');
                            }
                            var selected = {};
                            ids.each(function(i) {
                                selected[i] = i;
                            });
                            _existingIds = $.extend(_existingIds, selected);
                            $('#m_productfaces_cloning_override_ids').val(Object.toJSON(_existingIds));
                        });
                }
            }
        });
	}
	function _remove() {
		var selectedCheckboxes = $('td.mc-massaction input:checked');
		if (selectedCheckboxes.length > 0) {
			var removed = false;
			selectedCheckboxes.each(function() {
				var id = $(this).val();
				if (id != 'this') {
					_serializer.gridData.unset(id);
					removed = true;
				}
			});
			
			if (removed) {
				_updateHiddenState();
				
				// call server to update visuals
				_recalculationPending = true;
				_serializer.grid.reload(_serializer.grid.url);
			}
		}
		else {
			alert(_options.noItemsSelectedMsg);
		}
	}
	
	window.m_warnAndNavigate = function(url) {
		if(confirm(_options.navigationWarning)){
			//setLocation(url);
			window.open(url, '_blank');
			return true;
		}
		return false;
	};
	
    function _reload(url){
        if (!this.reloadParams) {
            this.reloadParams = {form_key: FORM_KEY};
        }
        else {
            this.reloadParams.form_key = FORM_KEY;
        }
        url = url || this.url;
        if(this.useAjax){
            new Ajax.Request(url + (url.match(new RegExp('\\?')) ? '&ajax=true' : '?ajax=true' ), {
                loaderArea: this.containerId,
                parameters: this.reloadParams || {},
                evalScripts: true,
                onFailure: this._processFailure.bind(this),
                onComplete: this.initGridAjax.bind(this),
                onSuccess: function(transport) {
                    try {
                        if (transport.responseText.isJSON()) {
                            var response = transport.responseText.evalJSON()
                            if (response.error) {
                                alert(response.message);
                            }
                            if(response.ajaxExpired && response.ajaxRedirect) {
                                setLocation(response.ajaxRedirect);
                            }
                        } else {
                        	$get(this.containerId).update(transport.responseText);
                        	_rebind();
                        }
                    }
                    catch (e) {
                    	$get(this.containerId).update(transport.responseText);
                    	_rebind();
                    }
                }.bind(this)
            });
            return;
        }
        else{
            if(this.reloadParams){
                $H(this.reloadParams).each(function(pair){
                    url = this.addVarToUrl(pair.key, pair.value);
                }.bind(this));
            }
            location.href = url;
        }
    };

	// custom grid serializer
	var _serializerControllerClass = Class.create(serializerController);
	_serializerControllerClass.prototype.rowClick = function(grid, event) {
    };
    _serializerControllerClass.prototype.inputChange = function(event) {
	    var element = Event.element(event);
	    if(element && element.checkboxElement){
	        this.gridData.get(element.checkboxElement.value)[element.name] = element.value;
	        this.hiddenDataHolder.value = this.serializeObject();
	        this.grid.reloadParams = {};
	        this.grid.reloadParams[this.reloadParamName+'[]'] = this.getDataForReloadParam();
	    }
	    if (element.name == 'm_parts' || element.name == 'm_unit' || element.name == 'position' || element.name == 'm_pack_qty') {
	    	_updateRepresentingInventory();
	    }
	    else {
	    	_updateThisFields(false);
	    }
	};
	_serializerControllerClass.prototype.rowInit = function(grid, row) {
        if(this.multidimensionalMode){
            var checkbox = $get(row).select('.checkbox')[0];
            var selectors = this.inputsToManage.map(function (name) { return ['input[name="' + name + '"]', 'select[name="' + name + '"]']; });
            var inputs = $get(row).select.apply($get(row), selectors.flatten());
            if(checkbox && inputs.length > 0) {
                checkbox.inputElements = inputs;
                for(var i = 0; i < inputs.length; i++) {
                    inputs[i].checkboxElement = checkbox;
                    if(this.gridData.get(checkbox.value) && this.gridData.get(checkbox.value)[inputs[i].name]) {
                        inputs[i].value = this.gridData.get(checkbox.value)[inputs[i].name];
                    }
                    //inputs[i].disabled = !checkbox.checked;
                    inputs[i].tabIndex = this.tabIndex++;
                    Event.observe(inputs[i],'keyup', this.inputChange.bind(this));
                    Event.observe(inputs[i],'change', this.inputChange.bind(this));
                }
            }
        }
        this.getOldCallback('init_row')(grid, row);
    };
    _serializerControllerClass.prototype.getDataForReloadParam = function(){
        return this.multidimensionalMode ? this.serializeObject() : this.gridData.values();
    };
    
	$(document).bind('m-productfaces-backform-ready', function(event, options) {
		_options = options;
	});
	$(document).bind('m-productfaces-ready', function(event, options) {
		_options = options;
		
		// Subscribe to changes in grid to update hidden field above. This hidden field holds all the information
		// presented in grid and goes to server on most ajax calls as well as while saving a product
		_serializer = new _serializerControllerClass(options.hiddenGridDataHolder, 
			options.predefinedGridData, options.gridInputsToManage, options.grid, options.reloadParamName);

		// update inventory calculations in grid when changing these fields 
		$('#inventory_qty').change(_updateRepresentingInventory);
		$('#inventory_is_qty_decimal').change(_updateRepresentingInventory);

		
		// update "this" row when product's fields change
		var row = null;
		
		_serializer.gridData.each(function(item) {
			row = item[1];
		});
		for (attribute in row) {
			$('#' + attribute).change(function() {
				_updateThisFields(true);
			});
		}
		
		// attach show/hide behavior
		$('#m_representing_enabled').change(_showHide);
		
		// logic of adding copy of a product, adding another existing product, removing product
		$('button.m-add-copy').click(_addCopy);
		$('button.m-add-existing').click(_addExisting);
		$('button.m-remove').click(_remove);
		
		var _oldReload = _serializer.grid.reload;
		_serializer.grid.reload = _reload.bind(_serializer.grid);
		
		// fetch changes already made to the moment of loading this tab
		_updateRepresentingInventory();
		_updateThisFields(true);
		_showHide();
		_updateHiddenState();
	});
	
    function _rebind() {
		// logic of adding copy of a product, adding another existing product, removing product
		$('button.m-add-copy').click(_addCopy);
		$('button.m-add-existing').click(_addExisting);
		$('button.m-remove').click(_remove);
		
		if (_recalculationPending) {
			_updateRepresentingInventory(true);
		}
		else {
			_refreshRepresentingInventory();
		}
    }
})(jQuery, window, $);