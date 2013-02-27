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
 * @method array getEdit()
 * @method Mana_Admin_Block_Grid setUseAjax(bool $value)
 * @method array getMClientSideBlock()
 * @method Mana_Admin_Block_Grid setMClientSideBlock(array $value)
 * @method string getGridController()
 */
class Mana_Admin_Block_Grid extends Mage_Adminhtml_Block_Widget_Grid {
    #region 3-phase construction
    protected function _construct() {
        $this->setTemplate('mana/admin/grid.phtml');
    }
    protected function _prepareLayout() {
        $this->setId(str_replace('_', '-', str_replace('.', '-', $this->getNameInLayout())));

        /* @var $button Mana_Admin_Block_Grid_Action */
        $button = $this->getLayout()->createBlock('mana_admin/grid_action', "{$this->getNameInLayout()}.export")
            ->setData(array(
            'label' => Mage::helper('adminhtml')->__('Export'),
            'class' => 'task'
        ));

        $this->setChild('export_button', $button);

        $button = $this->getLayout()->createBlock('mana_admin/grid_action', "{$this->getNameInLayout()}.reset")
            ->setData(array(
            'label' => Mage::helper('adminhtml')->__('Reset Filter'),
        ));
        $this->setChild('reset_filter_button', $button);

        $button = $this->getLayout()->createBlock('mana_admin/grid_action', "{$this->getNameInLayout()}.search")
            ->setData(array(
            'label' => Mage::helper('adminhtml')->__('Search'),
            'class' => 'task'
        ));
        $this->setChild('search_button', $button);

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
        $this->addToParentGroup('content');

        /* @var $pageBlock Mana_Admin_Block_Page */
        if ($pageBlock = $this->getLayout()->getBlock('page')) {
            $pageBlock->setBeginEditingSession(true);
        }
        // create client-side block
        $this->_prepareClientSideBlock();

        return $this;
    }
    #endregion
    #region Client side block support
    protected function _prepareClientSideBlock() {
        /* @var $urlTemplate Mana_Core_Helper_UrlTemplate */
        $urlTemplate = Mage::helper('mana_core/urlTemplate');

        $this->setMClientSideBlock(array(
            'type' => 'Mana/Admin/Block/Grid',
            'url' => $urlTemplate->encodeAttribute($this->getGridUrl()),
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
    #endregion
    #region Enhanced column collection
    /**
     * @param string $columnId
     * @param Mana_Admin_Block_Grid_Column $column
     * @return Mana_Admin_Block_Grid
     */
    public function addColumnBlock($columnId, $column) {
        $column
            ->setGrid($this)
            ->setId($columnId);
        $this->_columns[$columnId] = $column;
        $this->_lastColumnId = $columnId;
        return $this;
    }
    #endregion
    #region Overrides
    protected function _prepareCollection() {
        /* @var $collection Mage_Core_Model_Mysql4_Collection_Abstract */
        $collection = $this->createCollection();

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

            if ($alias == $column->getNameInLayout() && $core->startsWith($alias, $this->getNameInLayout() . '.')) {
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
        /* @var $urlModel Mage_Adminhtml_Model_Url */
        $urlModel = $this->_getUrlModel()
            ->setData('no_secret', 1);
        return $urlModel->getUrl('*/' . $this->getGridController() . '/{action}');
    }
    #endregion
    #region Parameter handling
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

    /**
     * @param Varien_Object $a
     * @param Varien_Object $b
     * @return int
     */
    protected function _compareBySortOrder($a, $b) {
        if ($a->getData('sort_order') < $b->getData('sort_order')) return -1;
        if ($a->getData('sort_order') > $b->getData('sort_order')) return 1;
        return 0;
    }

    #endregion
    #region Handling of data edited in browser
    public function setEdit($edit) {
        /* @var $json Mana_Core_Helper_Json */
        $json = Mage::helper('mana_core/json');

        $this->setData('edit', $edit);

        $clientSideBlock = $this->getMClientSideBlock();
        if (!$clientSideBlock) {
            $clientSideBlock = array();
        }
        $clientSideBlock = array_merge($clientSideBlock, array(
            'edit' => $json->encodeAttribute($this->getEdit()),
        ));
        $this->setMClientSideBlock($clientSideBlock);
        return $this;
    }
    #endregion
    #region Data source operations
    public function createCollection() {
        /* @var $db Mana_Db_Helper_Data */
        $db = Mage::helper('mana_db');

        return $db->getResourceModel($this->getDataSource().'_collection');
    }

    /**
     * @return Mage_Core_Model_Abstract
     */
    public function getParentModel() {
        return null;
    }

    /**
     * @return Mana_Db_Model_Entity
     */
    public function createModel() {
        /* @var $db Mana_Db_Helper_Data */
        $db = Mage::helper('mana_db');

        return $db->getModel($this->getDataSource());
    }

    /**
     * @param $id
     * @return Mana_Db_Model_Entity
     */
    public function loadModel($id) {
        /* @var $db Mana_Db_Helper_Data */
        $db = Mage::helper('mana_db');

        return $db->getModel($this->getDataSource())->load($id);
    }

    /**
     * @param $edit
     * @return Mana_Db_Resource_Entity_Collection
     */
    public function loadModels($edit) {
        /* @var $db Mana_Db_Helper_Data */
        $db = Mage::helper('mana_db');
        /* @var $collection Mana_Db_Resource_Entity_Collection */
        $collection = $this->createCollection();
        $collection
            ->setEditFilter($edit)
            ->addFieldToFilter('edit_massaction', 1);

        return $collection;
    }

    /**
     * @param $id
     * @param $sessionId
     * @return Mana_Db_Model_Entity
     */
    public function loadEditedModel($id, $sessionId) {
        /* @var $db Mana_Db_Helper_Data */
        $db = Mage::helper('mana_db');

        return $db->getModel($this->getDataSource())->loadEdited($id, $sessionId);
    }

    #endregion
    #region Events
    protected $_onGetRowUrl = array();
    public function onGetRowUrl($handler) {
        $this->_onGetRowUrl[] = $handler;

        return $this;
    }
    protected function _raiseGetRowUrl($row) {
        /* @var $eventHelper Mana_Admin_Helper_Event */
        $eventHelper = Mage::helper('mana_admin/event');

        return $eventHelper->raise($this, $this->_onGetRowUrl, compact('row'));
    }
    #endregion
}