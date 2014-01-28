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
