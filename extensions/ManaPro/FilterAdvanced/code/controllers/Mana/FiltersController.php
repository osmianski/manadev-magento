<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterAdvanced
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_FilterAdvanced_Mana_FiltersController extends Mana_Admin_Controller_Crud {
    protected function _getEntityName() {
        return 'mana_filters/filter2';
    }
    public function tabAdvancedAction() {
        $model = $this->_registerModel();

        // layout
        $this->addActionLayoutHandles();
        $this->loadLayoutUpdates();
        $this->generateLayoutXml()->generateLayoutBlocks();
        $this->_isLayoutLoaded = true;

        // render AJAX result
        $this->renderLayout();
    }
}