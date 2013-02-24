<?php
/**
 * @category    Mana
 * @package     Mana_Admin
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * @author Mana Team
 *
 */
class Mana_Admin_Block_Chooser_Cmsblock extends Mage_Adminhtml_Block_Cms_Block_Widget_Chooser {
    public function getGridUrl() {
        return $this->getUrl('*/chooser/cmsBlock', array(
            'cmsblocks_grid' => true,
            '_current' => true,
            'uniq_id' => $this->getId(),
            'use_massaction' => $this->getUseMassaction(),
            'hidden_blocks' => $this->getHiddenBlocks(),
        ));
    }

    public function getHiddenBlocks() {
        if (!($result = parent::getHiddenBlocks())) {
            $result = Mage::app()->getRequest()->getParam('hidden_blocks');
        }
        return $result;
    }

    protected function _prepareColumns() {
        if ($this->getUseMassaction()) {
            $this->addColumn('in_cmsblocks', array(
                'header_css_class' => 'a-center',
                'type' => 'checkbox',
                'name' => 'in_cmsblocks',
                'inline_css' => 'checkbox entities',
                'field_name' => 'in_cmsblocks',
                'values' => $this->getSelectedProducts(),
                'align' => 'center',
                'index' => 'block_id',
                'use_index' => true,
            ));
        }
       return parent::_prepareColumns();
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('cms/block')->getCollection();
        /* @var $collection Mage_Cms_Model_Mysql4_Block_Collection */

        if ($blockIds = $this->getHiddenBlocks()) {
            $collection->addFieldToFilter('block_id', array('nin' => explode(',', $blockIds)));
        }


        $this->setCollection($collection);
        return $this->_basePrepareCollection();
    }

    protected function _basePrepareCollection() {
        if ($this->getCollection()) {

            $this->_preparePage();

            $columnId = $this->getParam($this->getVarNameSort(), $this->_defaultSort);
            $dir = $this->getParam($this->getVarNameDir(), $this->_defaultDir);
            $filter = $this->getParam($this->getVarNameFilter(), null);

            if (is_null($filter)) {
                $filter = $this->_defaultFilter;
            }

            if (is_string($filter)) {
                $data = $this->helper('adminhtml')->prepareFilterString($filter);
                $this->_setFilterValues($data);
            } else if ($filter && is_array($filter)) {
                $this->_setFilterValues($filter);
            } else if (0 !== sizeof($this->_defaultFilter)) {
                $this->_setFilterValues($this->_defaultFilter);
            }

            if (isset($this->_columns[$columnId]) && $this->_columns[$columnId]->getIndex()) {
                $dir = (strtolower($dir) == 'desc') ? 'desc' : 'asc';
                $this->_columns[$columnId]->setDir($dir);
                $column = $this->_columns[$columnId]->getFilterIndex() ?
                        $this->_columns[$columnId]->getFilterIndex() : $this->_columns[$columnId]->getIndex();
                $this->getCollection()->setOrder($column, $dir);
            }

            if (!$this->_isExport) {
                $this->getCollection()->load();
                $this->_afterLoadCollection();
            }
        }

        return $this;
    }

}