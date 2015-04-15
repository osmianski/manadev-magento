/**
 * @category    Mana
 * @package     ManaPro_FilterContent
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

; // make JS merging easier

Mana.define('ManaPro/FilterContent/TabContainer', ['jquery', 'Mana/Admin/Container'],
function($, Container) {
    return Container.extend('ManaPro/FilterContent/TabContainer', {

    });
});

Mana.define('ManaPro/FilterContent/TabContainer/Global', ['jquery', 'ManaPro/FilterContent/TabContainer'],
function($, TabContainer) {
    return TabContainer.extend('ManaPro/FilterContent/TabContainer/Global', {

    });
});
Mana.define('ManaPro/FilterContent/TabContainer/Store', ['jquery', 'ManaPro/FilterContent/TabContainer'],
function($, TabContainer) {
    return TabContainer.extend('ManaPro/FilterContent/TabContainer/Store', {

    });
});

Mana.define('ManaPro/FilterContent/Option/IsActiveCell', ['jquery', 'Mana/Admin/Grid/Cell/Checkbox'],
function($, Select) {
    return Select.extend('ManaPro/FilterContent/Option/IsActiveCell', {
        onClick: function() {
            this._super();
            if (this.$input().is('checked')) {
                this.getGrid().expandChildRow(this.$tr());
            }
            else {
                this.getGrid().collapseChildRow(this.$tr());
            }
        },
        $tr: function() {
            return this.$().parent('tr');
        }
    });
});

Mana.define('ManaPro/FilterContent/Option/Grid', ['jquery', 'Mana/Admin/Grid', 'singleton:Mana/Core/Json'],
function($, Grid, json) {
    return Grid.extend('ManaPro/FilterContent/Option/Grid', {
            _init: function () {
                this._super();
                this._rowExpandCollapseStates = {};
            },
        _subscribeToHtmlEvents: function () {
            var self = this;

            return this
                ._super()
                .on('bind', this, function () {
                    self._varienGrid.rowClickCallback = self.rowClick.bind(self);
                });
        },
        rowClick: function(grid, evt) {
            if ($(evt.element()).is('#content-grid_table > tbody > tr > td')) {
                this.toggleChildRow($(Event.findElement(evt, 'tr')));
            }
        },
        expandChildRow: function($tr) {
            $tr.next().removeClass('hidden');
            this._rowExpandCollapseStates[$tr.data('row-id')] = !$tr.next().hasClass('hidden');
        },
        collapseChildRow: function($tr) {
            $tr.next().addClass('hidden');
            this._rowExpandCollapseStates[$tr.data('row-id')] = !$tr.next().hasClass('hidden');
        },
        toggleChildRow: function($tr) {
            $tr.next().toggleClass('hidden');
            this._rowExpandCollapseStates[$tr.data('row-id')] = !$tr.next().hasClass('hidden');
        },
        _updateReloadParams: function () {
            this._super();
            this._varienGrid.reloadParams['row_expand_collapse_states'] = json.stringify(this._rowExpandCollapseStates);
        }
    });
});
