<?php
/** 
 * @category    Mana
 * @package     Mana_AttributePage
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
abstract class Mana_AttributePage_Model_AttributePage_Abstract extends Mage_Core_Model_Abstract {
    const DM_IS_ACTIVE = 0;
    const DM_TITLE = 1;
    const DM_DESCRIPTION = 2;
    const DM_IMAGE = 3;
    const DM_INCLUDE_IN_MENU = 4;
    const DM_URL_KEY = 5;
    const DM_TEMPLATE = 6;
    const DM_SHOW_ALPHANUMERIC_SEARCH = 7;
    const DM_PAGE_LAYOUT = 8;
    const DM_LAYOUT_XML = 9;
    const DM_CUSTOM_DESIGN_ACTIVE_FROM = 10;
    const DM_CUSTOM_DESIGN_ACTIVE_TO = 11;
    const DM_CUSTOM_DESIGN = 12;
    const DM_CUSTOM_LAYOUT_XML = 13;
    const DM_META_TITLE = 14;
    const DM_META_KEYWORDS = 15;
    const DM_META_DESCRIPTION = 17;

    const MAX_ATTRIBUTE_COUNT = 5;
}