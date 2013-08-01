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
 * @method bool getIsMassActionable()
 * @method Mana_Admin_Block_Grid setIsMassActionable(bool $value)
 */
class Mana_Admin_Block_Grid extends Mage_Adminhtml_Block_Widget_Grid {
    #region 3-phase construction
    protected function _prepareLayout() {
        $this->setTemplate('mana/admin/grid.phtml');
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

        $columnName = $this->getNameInLayout() . '.edit_massaction';
        $column = $this->getLayout()->createBlock('mana_admin/grid_column', $columnName, array(
            'title' => $this->__('Selected'),
            'index' => 'edit_massaction',
            'column_type' => 'massaction',
            'width' => '50px',
            'align' => 'center',
            'sort_order' => -1,
        ));
        $this->insert($column);

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
            'type' => 'Mana/Admin/Grid',
            'url' => $urlTemplate->encodeAttribute($this->getGridUrl()),
            'html' => array(
                'id' => $this->getId(),
            ),
        ));

        return $this;
    }

    /**
     * @param int $index
     * @param Mana_Db_Model_Entity $row
     * @return array
     */
    public function getRowClientSideBlock($index, $row) {
        return array(
            'type' => 'Mana/Admin/Grid/Row',
            'row_id' => $row->getId(),
        );
    }

    /**
     * @param Mana_Db_Model_Entity $row
     * @param Mana_Admin_Block_Grid_Column $column
     * @return array
     */
    public function getCellClientSideBlock($row, $column) {
        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper('mana_core');
        $standardPrefix = 'adminhtml/widget_grid_column_renderer_';
        $editablePrefix = 'mana_admin/grid_column_';

        $rendererClass = $column->getData('renderer');
        if (!$rendererClass) {
            $rendererClass = $column->getRendererClass();
        }

        $type = 'Mana/Admin/Grid/Cell';
        if ($core->startsWith($rendererClass, $standardPrefix)) {
            $type .= '/' . ucfirst(substr($rendererClass, strlen($standardPrefix)));
        }
        elseif ($core->startsWith($rendererClass, $editablePrefix)) {
            $type .= '/' . ucfirst(substr($rendererClass, strlen($editablePrefix)));
        }

        return compact('type');
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
            ->setId($columnId)
            ->setData('index', $columnId);
        $this->_columns[$columnId] = $column;
        $this->_lastColumnId = $columnId;
        return $this;
    }

    /**
     * Add column to grid
     *
     * @param   string $columnId
     * @param   array || Varien_Object $column
     * @return  Mage_Adminhtml_Block_Widget_Grid
     */
    public function addColumn($columnId, $column)
    {
        if (is_array($column)) {
            $columnName = $this->getNameInLayout().'.'.$columnId;

            /* @var $column Mana_Admin_Block_Grid_Column */
            $column = $this->getLayout()->createBlock('mana_admin/grid_column', $columnName);

            $this->setChild($columnName, $column);

            /* @var $pageHelper Mana_Admin_Helper_Page */
            $pageHelper = Mage::helper('mana_admin/page');

            $params = $column->getData();
            $pageHelper
                ->removeParam($params, 'type')
                ->removeParam($params, 'sort_order')
                ->renameParam($params, 'column_type', 'type')
                ->copyParam($params, 'source_model', 'options')
                ->copyParam($params, 'title', 'header');

            $column->setData($params);
            $this->addColumnBlock($columnId, $column);
        }
        else {
            throw new Exception(Mage::helper('adminhtml')->__('Wrong column format.'));
        }

        return $this;
    }

    #endregion
    #region Overrides
    protected function _prepareCollection() {
        /* @var $admin Mana_Admin_Helper_Data */
        $admin = Mage::helper('mana_admin');
        /* @var $dataSource Mana_Admin_Block_Data_Collection */
        $dataSource = $admin->getDataSource($this);

        /* @var $collection Mana_Db_Resource_Entity_Collection */
        $collection = $dataSource->createCollection();

        $collection->setEditFilter($this->getEdit() ? $this->getEdit() : true, $dataSource->getParentCondition());

        $this->setCollection($collection);
        Mage::dispatchEvent('m_entity_grid_collection', array('grid' => $this));

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
            }
            else if ($filter && is_array($filter)) {
                $this->_setFilterValues($filter);
            }
            else if (0 !== sizeof($this->_defaultFilter)) {
                $this->_setFilterValues($this->_defaultFilter);
            }

            if (isset($this->_columns[$columnId]) && $this->_columns[$columnId]->getIndex()) {
                $dir = (strtolower($dir) == 'desc') ? 'desc' : 'asc';
                $this->_columns[$columnId]->setDir($dir);
                $column = $this->_columns[$columnId]->getFilterIndex() ?
                    $this->_columns[$columnId]->getFilterIndex() : $this->_columns[$columnId]->getIndex();

                $this->_columns[$columnId]->setOrder($this->getCollection(), $column, $dir);
            }

            if (!$this->_isExport) {
                $this->getCollection()->load();
                $this->_afterLoadCollection();
            }
        }

        return $this;
    }

    protected function _prepareColumns() {
        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper(strtolower('Mana_Core'));
        /* @var $pageHelper Mana_Admin_Helper_Page */
        $pageHelper = Mage::helper('mana_admin/page');

        $columns = $this->getChildGroup('columns');
        uasort($columns, array($pageHelper, 'compareBySortOrder'));
        foreach ($columns as $alias => $column) {
            if ($alias == 'edit_massaction' && !$this->getIsMassActionable()) {
                continue;
            }

            /* @var $column Mana_Admin_Block_Grid_Column */

            if ($alias == $column->getNameInLayout() && $core->startsWith($alias, $this->getNameInLayout() . '.')) {
                $alias = substr($alias, strlen($this->getNameInLayout() . '.'));
            }

            $params = $column->getData();
            $pageHelper
                ->removeParam($params, 'type')
                ->removeParam($params, 'sort_order')
                ->renameParam($params, 'column_type', 'type')
                ->copyParam($params, 'source_model', 'options')
                ->copyParam($params, 'title', 'header');
            $column->setData($params);

            $this->addColumnBlock($alias, $column);
        }

        Mage::dispatchEvent('m_entity_grid_columns', array('grid' => $this));

        parent::_prepareColumns();

        return $this;
    }

    public function getMainButtonsHtml() {
        /* @var $pageHelper Mana_Admin_Helper_Page */
        $pageHelper = Mage::helper('mana_admin/page');

        return $pageHelper->getActionHtml($this).parent::getMainButtonsHtml();
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