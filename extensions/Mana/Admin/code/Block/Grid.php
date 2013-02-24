<?php
/**
 * @category    Mana
 * @package     Mana_Admin
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 * @method mixed getDataSource()
 */
class Mana_Admin_Block_Grid extends Mage_Adminhtml_Block_Widget_Grid {
    protected function _construct() {
        $this->setTemplate('mana/admin/grid.phtml');
    }
    protected function _prepareLayout() {
        $this->setId(str_replace('_', '-', str_replace('.', '-', $this->getNameInLayout())));

        $this->setChild('export_button',
            $this->getLayout()->createBlock('mana_admin/grid_action', "{$this->getNameInLayout()}.export")
                ->setData(array(
                'label' => Mage::helper('adminhtml')->__('Export'),
                'class' => 'task'
            ))
        );
        $this->setChild('reset_filter_button',
            $this->getLayout()->createBlock('mana_admin/grid_action', "{$this->getNameInLayout()}.reset")
                ->setData(array(
                'label' => Mage::helper('adminhtml')->__('Reset Filter'),
            ))
        );
        $this->setChild('search_button',
            $this->getLayout()->createBlock('mana_admin/grid_action', "{$this->getNameInLayout()}.search")
                ->setData(array(
                'label' => Mage::helper('adminhtml')->__('Search'),
                'class' => 'task'
            ))
        );

        $this->setDefaultSort('id');
        $this->setDefaultDir('desc');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);

        /* @var $layoutHelper Mana_Core_Helper_Layout */
        $layoutHelper = Mage::helper('mana_core/layout');
        $layoutHelper->delayPrepareLayout($this);

        return $this;

    }

    public function delayedPrepareLayout() {
        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper(strtolower('Mana_Core'));

        /* @var $layout Mage_Core_Model_Layout */
        $layout = Mage::getSingleton('core/layout');

        $this->addToParentGroup('content');

        // create client-side block
        $this->_prepareClientSideBlock();

        return $this;
    }

    protected function _prepareClientSideBlock() {
        $this->setMClientSideBlock(array(
            'type' => 'Mana/Admin/Block/Grid',
            'grid_url' => base64_encode($this->getGridUrl()),
            'html' => array(
                'id' => $this->getId(),
            ),
        ));

        return $this;
    }

    public function getRowClientSideBlock($index, $row) {
        return array(
            'type' => 'Mana/Admin/Block/Grid/Row',
        );
    }

    public function getCellClientSideBlock($row, $column) {
        return array(
            'type' => 'Mana/Admin/Block/Grid/Cell',
        );
    }
    protected function _prepareCollection() {
        /* @var $collection Mage_Core_Model_Mysql4_Collection_Abstract */
        $collection = Mage::helper('mana_db')->getResourceModel($this->getDataSource());

        $this->setCollection($collection);
        Mage::dispatchEvent('m_entity_grid_collection', array('grid' => $this));
        parent::_prepareCollection();

        return $this;
    }

    protected function _prepareColumns() {
        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper(strtolower('Mana_Core'));

        $columns = $this->getChildGroup('columns');
        uasort($columns, array($this, '_compareBySortOrder'));
        foreach ($columns as $alias => $column) {
            /* @var $column Mana_Admin_Block_Grid_Column */

            if ($alias == $column->getNameInLayout() && $core->startsWith($alias, $this->getNameInLayout().'.')) {
                $alias = substr($alias, strlen($this->getNameInLayout() . '.'));
            }
            $params = $column->getData();
            $this
                ->_removeParam($params, 'type')
                ->_removeParam($params, 'sort_order')
                ->_renameParam($params, 'column_type', 'type')
                ->_copyParam($params, 'source_model', 'options')
                ->_copyParam($params, 'title', 'header');

            $this->addColumnBlock($alias, $column->setData($params));
        }

        Mage::dispatchEvent('m_entity_grid_columns', array('grid' => $this));
        parent::_prepareColumns();

        return $this;
    }

    public function addColumnBlock($columnId, $column) {
        $column
            ->setGrid($this)
            ->setId($columnId);
        $this->_columns[$columnId] = $column;
        $this->_lastColumnId = $columnId;
        return $this;
    }

    public function getMainButtonsHtml() {
        $html = '';

        $actions = $this->getChildGroup('actions');
        uasort($actions, array($this, '_compareBySortOrder'));
        foreach ($actions as $alias => $action) {
            /* @var $action Mana_Admin_Block_Grid_Action */

            $params = $action->getData();
            $this->_copyParam($params, 'title', 'label');
            $action->setData($params);

            $html .= $this->getChildHtml($alias);
        }

        $html .= parent::getMainButtonsHtml();
        return $html;
    }

    protected function _removeParam(&$params, $key) {
        if (isset($params[$key])) {
            unset($params[$key]);
        }

        return $this;
    }

    protected function _copyParam(&$params, $sourceKey, $targetKey) {
        if (isset($params[$sourceKey])) {
            $params[$targetKey] = $params[$sourceKey];
        }

        return $this;
    }

    protected function _renameParam(&$params, $sourceKey, $targetKey) {
        return $this
            ->_copyParam($params, $sourceKey, $targetKey)
            ->_removeParam($params, $sourceKey);
    }

    protected function _compareBySortOrder($a, $b) {
        if ($a->getSortOrder() < $b->getSortOrder()) return -1;
        if ($a->getSortOrder() > $b->getSortOrder()) return 1;
        return 0;
    }
    public function canDisplayContainer() {
        return false;
    }

    public function getRowUrl($row) {
        if ($url = $this->_getData('row_url')) {
            return $url;
        }
        elseif ($url = $this->_raiseGetRowUrl($row)) {
            return $url;
        }
        else {
            return parent::getRowUrl($row);
        }
    }

    public function getGridUrl() {
        return $this->getUrl('*/'.$this->getGridController().'/{action}');
    }

    #region Events
    protected $_onGetRowUrl = array();
    public function onGetRowUrl($handler) {
        $this->_onGetRowUrl[] = $handler;

        return $this;
    }
    protected function _raiseGetRowUrl($row) {
        /* @var $eventHelper Mana_Entity_Helper_Event */
        $eventHelper = Mage::helper('mana_admin/event');

        return $eventHelper->raise($this, $this->_onGetRowUrl, compact('row'));
    }
    #endregion
}